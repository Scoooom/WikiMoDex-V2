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
            'editor'    => \App\Http\Middleware\RequireEditor::class,
            'cache'     => \App\Http\Middleware\SetCacheHeaders::class,
            'nosession' => \App\Http\Middleware\SuppressSession::class,
        ]);

        // SuppressSession must run before StartSession.
        // SetCacheHeaders must run last so it overrides Laravel's own no-cache stamp.
        $middleware->priority([
            \App\Http\Middleware\SuppressSession::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\SetCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 403 && $e->getMessage() === 'no_mfa') {
                return response()->view('errors.no-mfa', [], 403);
            }
        });
    })->create();
