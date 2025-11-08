<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            // Fallback: allow session snapshot for superadmin-only areas
            $snapshot = $request->session()->get('admin_session_snapshot');
            if ($snapshot && ($snapshot['role'] ?? null) === 'superadmin') {
                return $next($request);
            }
            return redirect()->route('login', ['type' => 'superadmin'])
                ->with('info', 'Please login as Super Admin using the unified login form.');
        }
        
        if (!$admin->isSuperAdmin()) {
            // Redirect non-super admins to their appropriate dashboard
            if ($admin->isDepartmentAdmin()) {
                return redirect()->route('department-admin.dashboard')->with('error', 'Access denied. Super admin privileges required.');
            } else {
                return redirect()->route('login', ['type' => 'superadmin'])->with('error', 'Access denied. Super admin privileges required.');
            }
        }

        // Add cache control headers to prevent back button access after logout
        $response = $next($request);
        
        return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
                       ->header('Pragma', 'no-cache')
                       ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
