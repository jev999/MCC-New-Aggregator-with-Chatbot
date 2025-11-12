<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please login to access this page.');
        }

        // Check if admin has any of the specified permissions
        foreach ($permissions as $permission) {
            if ($admin->hasPermissionTo($permission, 'admin')) {
                return $next($request);
            }
        }

        // Log unauthorized access attempt
        \Log::warning('Unauthorized permission access attempt', [
            'admin_id' => $admin->id,
            'admin_username' => $admin->username,
            'required_permissions' => $permissions,
            'admin_permissions' => $admin->getAllPermissions()->pluck('name')->toArray(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);

        abort(403, 'Unauthorized. You do not have the required permission to access this resource.');
    }
}
