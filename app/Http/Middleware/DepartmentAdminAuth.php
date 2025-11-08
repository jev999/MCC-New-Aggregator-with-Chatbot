<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login', ['type' => 'department-admin']);
        }

        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isDepartmentAdmin()) {
            // Redirect non-department admins to their appropriate dashboard
            if ($admin->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard')->with('error', 'Access denied. Department admin privileges required.');
            } else {
                return redirect()->route('admin.login')->with('error', 'Access denied. Department admin privileges required.');
            }
        }

        return $next($request);
    }
}
