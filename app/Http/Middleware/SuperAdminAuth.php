<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login')->with('info', 'Please login as Super Admin using the unified login form.');
        }

        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isSuperAdmin()) {
            // Redirect non-super admins to their appropriate dashboard
            if ($admin->isDepartmentAdmin()) {
                return redirect()->route('department-admin.dashboard')->with('error', 'Access denied. Super admin privileges required.');
            } else {
                return redirect()->route('admin.login')->with('error', 'Access denied. Super admin privileges required.');
            }
        }

        return $next($request);
    }
}
