<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Don't redirect MS365 signup routes
        if ($request->is('ms365/signup*', 'ms365/register*', 'auth/ms365/*')) {
            return null;
        }

        // All other unauthenticated requests redirect to unified login
        return route('login');
    }
}
