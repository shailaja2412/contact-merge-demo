<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class GuardLoginSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // skip running on logout to avoid interrupting the logout flow
        if ($request->route() && $request->route()->getName() === 'logout') {
            return $next($request);
        }

        $user = Auth::user();

        if ($user) {
            $current = $request->session()->get('guard');

            if ($user->isAdmin() && $current !== 'admin') {
                $request->session()->put('guard', 'admin');

                if ($request->expectsJson()) {
                    return $next($request);
                }

                return redirect()->route('admin.index');
            }

            if ($user->isUser() && $current !== 'user') {
                $request->session()->put('guard', 'user');

                if ($request->expectsJson()) {
                    return $next($request);
                }

                return redirect()->route('contacts.index');
            }
        }

        return $next($request);
    }
}
