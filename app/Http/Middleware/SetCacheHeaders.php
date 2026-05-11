<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCacheHeaders
{
    /**
     * Apply a Cache-Control header to the response.
     *
     * Usage in routes:
     *   ->middleware('cache:public,3600')   // CF-cacheable, 1 hour
     *   ->middleware('cache:no-store')       // never cache (admin, user-specific)
     */
    public function handle(Request $request, Closure $next, string $directive = 'no-store'): Response
    {
        $response = $next($request);

        // Never stamp cache headers on non-GET responses or errors
        if (!$request->isMethod('GET') || $response->getStatusCode() >= 400) {
            return $response;
        }

        $response->headers->set('Cache-Control', $directive);

        return $response;
    }
}
