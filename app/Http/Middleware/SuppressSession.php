<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Suppresses the Laravel session on routes that don't need it.
 *
 * Without a session, Laravel won't set XSRF-TOKEN or laravel-session
 * cookies, and won't stamp "Cache-Control: no-cache, private" on the
 * response. This allows Cloudflare to cache the page normally.
 *
 * Safe to use on any route where auth state is handled client-side
 * via /me.json (i.e. all our JS-injected nav routes).
 */
class SuppressSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // Switch to array driver — session exists in memory only,
        // never persisted, no cookie written to the response.
        config(['session.driver' => 'array']);

        return $next($request);
    }
}
