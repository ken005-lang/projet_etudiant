<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->type_role !== 'groupe') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Non autorisé ou session expirée. Veuillez vous reconnecter.'], 401);
            }
            return redirect('/login')->withErrors(['login' => 'Accès restreint aux groupes inscrits.']);
        }
        return $next($request);
    }
}
