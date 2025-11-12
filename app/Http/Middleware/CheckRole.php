<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please login to access this page.');
        }

        // Check if admin has any of the specified roles
        foreach ($roles as $role) {
            if ($admin->hasRole($role)) {
                return $next($request);
            }
        }

        // Log unauthorized access attempt
        \Log::warning('Unauthorized role access attempt', [
            'admin_id' => $admin->id,
            'admin_username' => $admin->username,
            'required_roles' => $roles,
            'admin_roles' => $admin->getRoleNames()->toArray(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);

        abort(403, 'Unauthorized. You do not have the required role to access this resource.');
    }
}
