<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPasswordExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if password has expired or must be changed
            if ($user->mustChangePassword()) {
                // Allow access to password change routes
                if (!$request->routeIs('password.change') && !$request->routeIs('logout')) {
                    return redirect()->route('password.change')
                        ->with('warning', 'Your password has expired. Please change it to continue.');
                }
            }
        }

        return $next($request);
    }
}