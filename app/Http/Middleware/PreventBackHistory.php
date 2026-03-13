<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Empêche le navigateur de mettre en cache les pages protégées.
 * Après une déconnexion, le bouton "Retour" du navigateur ne pourra
 * plus afficher la page depuis le cache — il fera une vraie requête
 * au serveur, qui redirigera vers la page de login.
 */
class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
