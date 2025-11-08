<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login', ['type' => 'office-admin']);
        }

        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isOfficeAdmin()) {
            // Redirect non-office admins to their appropriate dashboard
            if ($admin->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard')->with('error', 'Access denied. Office admin privileges required.');
            } elseif ($admin->isDepartmentAdmin()) {
                return redirect()->route('department-admin.dashboard')->with('error', 'Access denied. Office admin privileges required.');
            } else {
                return redirect()->route('login', ['type' => 'office-admin'])->with('error', 'Access denied. Office admin privileges required.');
            }
        }

        return $next($request);
    }
}
