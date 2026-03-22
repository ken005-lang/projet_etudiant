<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyTabSession
{
    /**
     * Gère la requête entrante et vérifie si l'onglet a été déclaré fermé.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && $request->session()->has('tab_closed_at')) {
            $closedAt = $request->session()->get('tab_closed_at');
            $timeout = 5; // Délai de grâce pour le rechargement F5 (en secondes)
            
            if (now()->timestamp - $closedAt > $timeout) {
                // Le délai est dépassé, l'onglet a bien été fermé.
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Session sécurisée terminée (fermeture d\'onglet).');
            } else {
                // Fausse alerte : le délai est court, c'est probablement un F5.
                // On annule la marque de fermeture pour permettre à la session de continuer.
                $request->session()->forget('tab_closed_at');
            }
        }

        return $next($request);
    }
}
