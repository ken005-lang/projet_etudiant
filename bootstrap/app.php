<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (\Illuminate\Http\Request $request) => $request->is('admin*') ? route('admin.login') : route('login'));
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
            'groupe' => \App\Http\Middleware\EnsureIsGroup::class,
            'visiteur' => \App\Http\Middleware\EnsureIsVisitor::class,
            'prevent-back' => \App\Http\Middleware\PreventBackHistory::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\VerifyTabSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('login')->withErrors([
                'login' => 'La page a expiré car vous êtes resté inactif trop longtemps. Veuillez réessayer.'
            ]);
        });
    })->create();
