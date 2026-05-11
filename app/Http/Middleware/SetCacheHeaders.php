<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCacheHeaders
{
    public function handle(Request $request, Closure $next, string $directive = 'no-store'): Response
    {
        $response = $next($request);

        if (!$request->isMethod('GET') || $response->getStatusCode() >= 400) {
            return $response;
        }

        $response->headers->set('Cache-Control', $directive, true);
        $response->headers->set('X-Accel-Cache-Control', $directive, true);

        return $response;
    }
}
