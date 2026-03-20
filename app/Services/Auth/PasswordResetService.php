<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Mail\PasswordResetMail;
use App\Mail\PasswordChangedConfirmationMail;
use App\Mail\AdminIdentityRecoveryMail;
use Illuminate\Support\Facades\{DB, Hash, Mail, RateLimiter, Cache};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    private const TOKEN_EXPIRY_MINUTES  = 2;   // Code expires in 2 minutes
    private const MAX_RESET_ATTEMPTS    = 3;
    private const REQUEST_THROTTLE      = 2;   // Minutes between requests per email
    private const PASSWORD_HISTORY_SIZE = 5;
    private const TOKEN_LENGTH_BYTES    = 32;

    /**
     * Get the actual email address for a user (visitors store it in username).
     */
    private function getUserEmail(User $user): string
    {
        if ($user->type_role === 'visiteur') {
            return $user->username;
        }
        return $user->email ?? $user->username;
    }

    /**
     * Request a reset link for either Visitor (Password) or Group (Code ID).
     */
    public function requestReset(string $email, string $ip, string $mode): void
    {
        $throttleKey = "password_reset_request:{$ip}";
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $this->artificialDelay();
            return;
        }
        RateLimiter::hit($throttleKey, 900); // 15 min window

        $startTime = microtime(true);

        // Normalize email
        $email = strtolower(trim($email));

        // Find user by role-specific email column
        if ($mode === 'visiteur') {
            $user = User::where('type_role', 'visiteur')
                ->where('username', $email)
                ->first();
        } else if ($mode === 'groupe') {
            $user = User::where('type_role', 'groupe')
                ->where(function($q) use ($email) {
                    $q->where('email', $email)
                      ->orWhereHas('groupProfile', function($sq) use ($email) {
                          $sq->where('contact_email', $email);
                      });
                })->first();
        } else {
            $user = User::where('email', $email)->first();
        }

        // Anti-enumeration: always return same result
        if (!$user) {
            $this->enforceMinimumDelay($startTime, 400);
            return;
        }

        // Email throttle (anti-spam)
        $emailThrottleKey = "password_reset_email:" . hash('sha256', $email);
        if (Cache::has($emailThrottleKey)) {
            $this->enforceMinimumDelay($startTime, 400);
            return;
        }

        // Generate a 6 character alphanumeric code
        $rawToken = strtoupper(Str::random(6));
        $hashedToken = hash('sha256', $rawToken);

        // Store token (UPSERT)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $hashedToken,
                'created_at' => now(),
                'attempts' => 0
            ]
        );

        Cache::put($emailThrottleKey, true, now()->addMinutes(self::REQUEST_THROTTLE));

        // Send Email — use the normalized $email (which is always valid)
        if ($mode === 'visiteur') {
            Mail::to($email)->queue(new \App\Mail\VisitorPasswordResetCodeMail($user, $rawToken));
        } else {
            Mail::to($email)->queue(new PasswordResetMail($user, $rawToken, $mode));
        }

        $this->enforceMinimumDelay($startTime, 400);
    }

    /**
     * Validate the token without consuming it.
     */
    public function validateToken(string $email, string $rawToken): bool
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', strtolower($email))
            ->first();

        if (!$record) return false;

        if (now()->diffInMinutes($record->created_at) > self::TOKEN_EXPIRY_MINUTES) {
            $this->deleteToken($email);
            return false;
        }

        if ($record->attempts >= self::MAX_RESET_ATTEMPTS) {
            $this->deleteToken($email);
            return false;
        }

        return hash_equals($record->token, hash('sha256', $rawToken));
    }

    /**
     * Reset the password/code ID.
     */
    public function resetPassword(
        string $email,
        string $rawToken,
        string $newCredential,
        string $ip,
        string $userAgent
    ): bool {
        $email = strtolower($email);
        
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        if ($record) {
            DB::table('password_reset_tokens')->where('email', $email)->increment('attempts');
        }

        if (!$this->validateToken($email, $rawToken)) {
            return false;
        }

        // Find user — for visitors the email is in username, for groups in email or groupProfile
        $user = User::where(function($q) use ($email) {
            $q->where('email', $email)
              ->orWhere('username', $email);
        })->first();

        if (!$user) return false;

        // Check against CURRENT password first
        if (Hash::check($newCredential, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ["Ce mot de passe est identique à votre mot de passe actuel. Choisissez-en un différent."]
            ]);
        }

        // Check password history (last 5)
        if ($this->isCredentialInHistory($user, $newCredential)) {
            throw ValidationException::withMessages([
                'password' => ["Ce mot de passe a déjà été utilisé récemment. Choisissez-en un autre."]
            ]);
        }

        // Get the actual email address for this user
        $actualEmail = $this->getUserEmail($user);

        DB::transaction(function () use ($user, $newCredential, $ip, $userAgent, $email) {
            // Archive old
            $this->archiveCurrentCredential($user);

            // Update
            $updateData = [
                'password' => Hash::make($newCredential),
                'password_changed_at' => now(),
                'must_change_password' => false,
                'reset_attempts' => 0,
                'remember_token' => null,
            ];

            // If it's a group, also update the username (Code ID)
            if ($user->type_role === 'groupe') {
                $updateData['username'] = $newCredential;
            }

            $user->update($updateData);

            // Revoke all sessions
            DB::table('sessions')->where('user_id', $user->id)->delete();

            // Log change
            DB::table('password_change_log')->insert([
                'user_id' => $user->id,
                'method' => 'forgot_password',
                'ip_address' => $ip,
                'user_agent' => substr($userAgent, 0, 500),
                'all_sessions_revoked' => true,
                'created_at' => now(),
            ]);

            // Delete token using the $email that was used to store it — NOT $user->email
            $this->deleteToken($email);
        });

        // Send confirmation email using the CORRECT email address
        try {
            Mail::to($actualEmail)->queue(new PasswordChangedConfirmationMail($user, 'forgot_password', $ip));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to queue password change confirmation email: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Handle identity recovery request for groups (Email to admin).
     */
    public function requestIdentityRecovery(array $data, string $ip): void
    {
        $adminEmail = 'itesprojet@gmail.com';
        
        Mail::to($adminEmail)->queue(new AdminIdentityRecoveryMail($data, $ip));
    }

    private function isCredentialInHistory(User $user, string $credential): bool
    {
        $history = DB::table('password_history')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(self::PASSWORD_HISTORY_SIZE)
            ->pluck('password_hash');

        foreach ($history as $oldHash) {
            if (Hash::check($credential, $oldHash)) {
                return true;
            }
        }
        return false;
    }

    private function archiveCurrentCredential(User $user): void
    {
        if (!$user->password) return;

        DB::table('password_history')->insert([
            'user_id' => $user->id,
            'password_hash' => $user->password,
            'created_at' => now(),
        ]);
        
        // Cleanup old history
        $idsToKeep = DB::table('password_history')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(self::PASSWORD_HISTORY_SIZE)
            ->pluck('id');

        DB::table('password_history')
            ->where('user_id', $user->id)
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }

    private function deleteToken(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', strtolower($email))->delete();
    }

    private function enforceMinimumDelay(float $startTime, int $minMs): void
    {
        $elapsed = (microtime(true) - $startTime) * 1000;
        $remaining = $minMs - $elapsed;
        if ($remaining > 0) {
            usleep((int) ($remaining * 1000));
        }
    }

    private function artificialDelay(): void
    {
        usleep(random_int(400000, 600000));
    }
}
