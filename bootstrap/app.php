<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens([
            'discord/interactions',
        ]);
        $middleware->alias([
            'admin'     => \App\Http\Middleware\RequireAdmin::class,
            'cache'     => \App\Http\Middleware\SetCacheHeaders::class,
            'nosession' => \App\Http\Middleware\SuppressSession::class,
        ]);

        // SuppressSession must run before StartSession so the array driver
        // is set before the session is initialised for that request.
        $middleware->priority([
            \App\Http\Middleware\SuppressSession::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
