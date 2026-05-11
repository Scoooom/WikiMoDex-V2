<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevents session cookies from being written on cacheable routes.
 *
 * Must run BEFORE StartSession in the middleware stack to be effective.
 * Registered as a priority middleware in bootstrap/app.php.
 *
 * Sets the session driver to 'array' before StartSession initialises,
 * so StartSession boots but never persists anything or writes a cookie.
 * Laravel then has no reason to stamp "Cache-Control: no-cache, private".
 */
class SuppressSession
{
    public function handle(Request $request, Closure $next): Response
    {
        config(['session.driver' => 'array']);

        $response = $next($request);

        // Belt-and-braces: strip any session/CSRF cookies that snuck through
        $response->headers->remove('Set-Cookie');

        return $response;
    }
}
