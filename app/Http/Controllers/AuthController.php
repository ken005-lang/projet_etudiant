<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\GroupProfile;
use App\Models\VisitorProfile;
use App\Models\AccessCode;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle Login Request (Groups & Visitors)
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $throttleKey = 'logins:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'login' => "Trop de tentatives. Veuillez réessayer dans {$seconds} secondes.",
            ]);
        }

        // We check if it's an email (Visitor) or just a string (could be Group code or Visitor email)
        $password = $request->password ?: $request->login; // If password is not provided (e.g. group login), use login as password
        $credentials = [
            'username' => $request->login,
            'password' => $password,
        ];

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->type_role === 'visiteur') {
                return redirect()->route('visiteur.dashboard');
            } elseif ($user->type_role === 'groupe') {
                return redirect()->route('groupe.dashboard');
            }

            // Fallback
            Auth::logout();
            return redirect('/login')->withErrors(['login' => 'Type de compte non autorisé ici.']);
        }

        RateLimiter::hit($throttleKey);

        return back()->withErrors([
            'login' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.',
        ])->withInput($request->except('password'));
    }

    /**
     * Handle Visitor Registration
     */
    public function registerVisitor(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'genre' => 'required|string|in:homme,femme',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,username', // username stores the email for visitors
            'password' => [
                'required',
                'string',
                'min:6',             // must be at least 6 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
            ],
        ], [
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit faire au moins 6 caractères.',
            'password.regex' => 'Le mot de passe ne respecte pas les critères de sécurité (minuscule, majuscule, chiffre).',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide (ex: nom@domaine.com).',
            'nom.required' => 'Votre nom est obligatoire.',
            'prenom.required' => 'Votre prénom est obligatoire.',
            'genre.required' => 'Veuillez sélectionner votre genre.'
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['nom'] . ' ' . $validated['prenom'],
                'username' => $validated['email'], // We use email as the unique login username for visitors
                'password' => Hash::make($validated['password']),
                'type_role' => 'visiteur',
            ]);

            // Note: first_name, last_name, and email are saved in the User model via `name` and `username`.
            $profile = VisitorProfile::create([
                'user_id' => $user->id,
                'gender' => $validated['genre'],
            ]);

            // Notifier les administrateurs pour la mise à jour des stats et tableaux
            broadcast(new \App\Events\NewUserRegisteredEvent($user, $profile));

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('visiteur.dashboard');
        });
    }

    /**
     * Handle Group Registration
     */
    public function registerGroup(Request $request)
    {
        // For groups, the "username" is the access code itself.
        $validated = $request->validate([
            'projet_nom' => 'required|string|max:255',
            'domaine' => 'required|string|max:255',
            'chef_nom' => 'required|string|max:255',
            'chef_prenom' => 'required|string|max:255',
            'filiere' => 'required|string|max:255',
            'niveau' => 'required|string|max:50',
            'code_acces' => 'required|string|exists:access_codes,code', // Code must exist in access_codes table
        ], [
            'projet_nom.required' => 'Le nom du projet est obligatoire.',
            'domaine.required' => 'Le domaine de votre projet est obligatoire.',
            'chef_nom.required' => 'Le nom du chef de groupe est obligatoire.',
            'chef_prenom.required' => 'Le prénom du chef de groupe est obligatoire.',
            'filiere.required' => 'La filière est obligatoire.',
            'niveau.required' => 'Le niveau est obligatoire.',
            'code_acces.required' => 'Le code d\'accès est obligatoire.',
            'code_acces.exists' => 'Ce code d\'accès n\'existe pas.'
        ]);

        // Check if the code is already used (it should disappear from available codes theoretically, but we check if a user already claimed it)
        $codeRecord = AccessCode::where('code', $validated['code_acces'])->first();
        if ($codeRecord->is_used) {
            return back()->withErrors(['code_acces' => 'Ce code d\'accès a déjà été utilisé.'])->withInput();
        }

        // Optionally verify if a user already has this code as username
        if (User::where('username', $validated['code_acces'])->exists()) {
            return back()->withErrors(['code_acces' => 'Ce code est déjà lié à un compte.'])->withInput();
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $codeRecord, $request) {
            $user = User::create([
                'name' => 'Groupe ' . $validated['projet_nom'],
                'username' => $validated['code_acces'], // Groups login using their access code as username
                'password' => Hash::make($validated['code_acces']),
                'type_role' => 'groupe',
            ]);

            $groupProfile = GroupProfile::create([
                'user_id' => $user->id,
                'access_code_id' => $codeRecord->id,
                'project_name' => $validated['projet_nom'],
                'project_domain' => $validated['domaine'],
                'leader_name' => $validated['chef_nom'] . ' ' . $validated['chef_prenom'],
                'leader_sector' => $validated['filiere'],
                'leader_level' => $validated['niveau'],
            ]);

            // Mark code as used
            $codeRecord->update(['is_used' => true]);

            // Notifier les administrateurs
            broadcast(new \App\Events\NewUserRegisteredEvent($user, $groupProfile));

            // Diffuser publiquement pour afficher ce nouveau groupe chez les visiteurs (temps réel)
            $groupProfile->load(['reports', 'members']);
            broadcast(new \App\Events\GroupUpdatedEvent($groupProfile));

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('groupe.dashboard');
        });
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken(); // Secure against CSRF

        return redirect('/');
    }
}
