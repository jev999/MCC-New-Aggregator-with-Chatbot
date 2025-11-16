<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireLocationBeforeLogin
{
    /**
     * Redirect to location permission route if session lacks location.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user already authenticated, skip
        if (auth()->check() || auth('admin')->check()) {
            return $next($request);
        }

        // Allow accessing the location permission route itself and assets
        // Check if session doesn't have user_location and not requesting the permission endpoints
        if (!$request->session()->has('user_location')
            && !$request->is('location-permission')
            && !$request->is('save-location')
            && !$request->is('logout')
            && !$request->is('password*')
            && !$request->is('api/*')
        ) {
            return redirect()->route('location.permission');
        }

        return $next($request);
    }
}
