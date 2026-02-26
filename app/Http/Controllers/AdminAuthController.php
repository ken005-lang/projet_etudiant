<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Handle Admin Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $throttleKey = 'admin_logins:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => "Trop de tentatives. Veuillez réessayer dans {$seconds} secondes.",
            ]);
        }

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            // Check if the user is actually an admin
            if (Auth::user()->type_role !== 'admin') {
                Auth::logout();
                return back()->withErrors(['username' => 'Vous n\'avez pas les droits d\'administration.']);
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        RateLimiter::hit($throttleKey);

        return back()->withErrors([
            'username' => 'Les informations d\'identification d\'administration sont incorrectes.',
        ])->onlyInput('username');
    }
}
