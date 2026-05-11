<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireEditor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!Auth::check() || !$user->isWikiEditor()) {
            abort(404);
        }

        // Has editor/admin role but 2FA is off — show a clear, actionable error
        if (!$user->mfa_enabled) {
            abort(403, 'no_mfa');
        }

        return $next($request);
    }
}
