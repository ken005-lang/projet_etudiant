<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\GroupRecoveryRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecoveryController extends Controller
{
    public function __construct(private readonly PasswordResetService $resetService)
    {}

    /**
     * Show the form to request a password reset link.
     */
    public function showLinkRequestForm(Request $request): View
    {
        $mode = $request->query('mode', 'groupe'); // 'groupe' or 'visiteur'
        return view('auth.forgot-password', compact('mode'));
    }

    /**
     * Handle the initial request (from login page).
     */
    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $this->resetService->requestReset(
            email: $request->email,
            ip: $request->ip(),
            mode: $request->mode
        );

        if ($request->mode === 'visiteur') {
            session(['verify_email' => $request->email]);
            return redirect()->route('verify.code');
        }

        return back()->with('status', 'Un message a été envoyé s\'il correspond à nos enregistrements.');
    }

    /**
     * Show recovery options (Visitor only).
     */
    public function showRecoveryOptions(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email', '');
        $mode = $request->query('mode', 'visiteur');

        if ($mode === 'groupe') {
             return redirect()->route('recovery.choice');
        }

        if (!$this->resetService->validateToken($email, $token)) {
            return redirect()->route('login')->withErrors(['email' => 'Code invalide ou expiré. Veuillez refaire une demande.']);
        }

        return view('auth.reset-password', compact('token', 'email', 'mode'));
    }

    /**
     * Visitor: Show Verify Code Form
     */
    public function showVerifyCodeForm(Request $request): View|RedirectResponse
    {
        $email = session('verify_email') ?? $request->query('email');
        if (!$email) {
            return redirect()->route('login');
        }

        // Fetch the token creation time to calculate the countdown
        $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', strtolower($email))
            ->first();

        $expirySeconds = 120; // 2 minutes
        $remainingSeconds = $expirySeconds;

        if ($tokenRecord && $tokenRecord->created_at) {
            $elapsed = (int) now()->diffInSeconds($tokenRecord->created_at);
            $remainingSeconds = max(0, $expirySeconds - $elapsed);
        }

        return view('auth.verify-code', compact('email', 'remainingSeconds'));
    }

    /**
     * Visitor: Submit verification code
     */
    public function submitVerifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string'
        ]);

        // Clean code (trim spaces just in case)
        $code = strtoupper(trim($request->code));
        $isValid = $this->resetService->validateToken($request->email, $code);

        if (!$isValid) {
            return back()->withInput()->withErrors(['code' => 'Le code de vérification est invalide ou a expiré.']);
        }

        // Keep email in session for the next step just in case, but pass it in URL
        return redirect()->route('password.reset', [
            'token' => $code,
            'email' => $request->email,
            'mode' => 'visiteur'
        ]);
    }

    /**
     * Group: Show direct choice page (Modify or Recover Code ID).
     */
    public function showDirectRecoveryChoice(Request $request): View
    {
        return view('auth.recovery-choice');
    }

    /**
     * Group: Show form to modify Code ID using the old one.
     */
    public function showModifyCodeIdForm(Request $request): View
    {
        return view('auth.modify-code-id');
    }

    /**
     * Group: Submit Code ID modification.
     */
    public function submitCodeIdModification(Request $request): RedirectResponse
    {
        $request->validate([
            'old_code_id' => ['required', 'string'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(6)->mixedCase()->numbers()],
        ]);

        $ip = $request->ip();
        $throttleKey = "code_modify_attempt:{$ip}";
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withInput()->withErrors(['old_code_id' => 'Trop de tentatives. Veuillez réessayer plus tard.']);
        }

        // Find group by old code id
        $group = \App\Models\User::where('type_role', 'groupe')
                                 ->where('username', $request->old_code_id)
                                 ->first();

        if (!$group || !\Illuminate\Support\Facades\Hash::check($request->old_code_id, $group->password)) {
             \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300); // 5 min
             return back()->withInput()->withErrors(['old_code_id' => 'L\'ancien Code ID est incorrect.']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($group, $request, $ip) {
                // Archive old password logic
                \Illuminate\Support\Facades\DB::table('password_history')->insert([
                    'user_id' => $group->id,
                    'password_hash' => $group->password,
                    'created_at' => now(),
                ]);

                // Maintain max 5
                $idsToKeep = \Illuminate\Support\Facades\DB::table('password_history')
                    ->where('user_id', $group->id)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->pluck('id');
        
                \Illuminate\Support\Facades\DB::table('password_history')
                    ->where('user_id', $group->id)
                    ->whereNotIn('id', $idsToKeep)
                    ->delete();

                $group->update([
                    'username' => $request->password,
                    'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                    'password_changed_at' => now(),
                    'remember_token' => null,
                ]);

                // Revoke sessions
                \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $group->id)->delete();

                // Log change
                \Illuminate\Support\Facades\DB::table('password_change_log')->insert([
                    'user_id' => $group->id,
                    'method' => 'user_change',
                    'ip_address' => $ip,
                    'user_agent' => substr(request()->userAgent(), 0, 500),
                    'all_sessions_revoked' => true,
                    'created_at' => now(),
                ]);

                // Update access_codes table so the admin dashboard shows the new code
                $groupProfile = \App\Models\GroupProfile::where('user_id', $group->id)->first();
                if ($groupProfile && $groupProfile->access_code_id) {
                    \Illuminate\Support\Facades\DB::table('access_codes')
                        ->where('id', $groupProfile->access_code_id)
                        ->update(['code' => $request->password]);
                }
            });

            // Broadcast real-time update to admin dashboard
            $groupProfile = \App\Models\GroupProfile::where('user_id', $group->id)->first();
            if ($groupProfile) {
                event(new \App\Events\CodeIdChangedEvent($groupProfile->id, $request->password));
            }

            if ($group->email) {
                \Illuminate\Support\Facades\Mail::to($group->email)->queue(new \App\Mail\PasswordChangedConfirmationMail($group, 'forgot_password', $ip));
            }

            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            return redirect()->route('login')->with('status', 'Votre Code ID a été modifié avec succès. Toutes les autres sessions ont été déconnectées.');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Code ID modification failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['old_code_id' => 'Une erreur est survenue.']);
        }
    }

    /**
     * Show identity recovery form for groups.
     */
    public function showIdentityForm(Request $request): View
    {
        return view('auth.recover-identity');
    }

    /**
     * Handle identity recovery submission to admin.
     */
    public function submitIdentityRecovery(GroupRecoveryRequest $request): RedirectResponse
    {
        $this->resetService->requestIdentityRecovery($request->validated(), $request->ip());

        return redirect()->route('home')->with('identity_recovery_success', 'Vous allez recevoir votre code id par mail, merci de bien vouloir patienter un bon moment. En cas d\'une grande attente, veuillez nous contacté au +225 01 42 79 31 99');
    }

    /**
     * Handle the final reset/modification.
     */
    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $success = $this->resetService->resetPassword(
            email: $request->email,
            rawToken: $request->token,
            newCredential: $request->password,
            ip: $request->ip(),
            userAgent: $request->userAgent() ?? ''
        );

        if (!$success) {
            return back()->withErrors(['email' => 'Erreur lors de la réinitialisation.']);
        }

        return redirect()->route('login')->with('status', 'Succès ! Vous pouvez maintenant vous connecter.');
    }
}
