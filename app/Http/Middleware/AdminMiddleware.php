<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use the authenticated user instance directly and check admin via helper on model
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            // Optionally redirect or abort
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
