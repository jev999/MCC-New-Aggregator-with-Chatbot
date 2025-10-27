<?php

namespace App\Http\Controllers;

use App\Models\AdminAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAccessController extends Controller
{
    public function __construct()
    {
        // Ensure only SuperAdmins can access this controller
        $this->middleware('auth:admin');
        $this->middleware(function ($request, $next) {
            $admin = Auth::guard('admin')->user();
            
            // Check if user is authenticated and is a SuperAdmin
            if (!$admin || !$admin->isSuperAdmin()) {
                abort(403, 'Access denied. Only SuperAdmins can view Admin Access Logs.');
            }
            
            return $next($request);
        });
    }

    public function index()
    {
        // Double-check authorization
        $admin = Auth::guard('admin')->user();
        
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Access denied. Only SuperAdmins can view Admin Access Logs.');
        }

        try {
            $logs = AdminAccessLog::with('admin')->latest()->paginate(10);
            
            // Calculate statistics
            $totalAttempts = AdminAccessLog::count();
            $successfulLogins = AdminAccessLog::where('status', 'success')->count();
            $failedLogins = AdminAccessLog::where('status', 'failed')->count();
            $activeSessions = AdminAccessLog::where('status', 'success')->whereNotNull('time_in')->whereNull('time_out')->count();
            $completedSessions = AdminAccessLog::where('status', 'success')->whereNotNull('time_in')->whereNotNull('time_out')->count();
            
            // Role-based statistics
            $superadminLogins = AdminAccessLog::where('role', 'superadmin')->where('status', 'success')->count();
            $departmentAdminLogins = AdminAccessLog::where('role', 'department_admin')->where('status', 'success')->count();
            $officeAdminLogins = AdminAccessLog::where('role', 'office_admin')->where('status', 'success')->count();
            
            $stats = [
                'total_attempts' => $totalAttempts,
                'successful_logins' => $successfulLogins,
                'failed_logins' => $failedLogins,
                'active_sessions' => $activeSessions,
                'completed_sessions' => $completedSessions,
                'superadmin_logins' => $superadminLogins,
                'department_admin_logins' => $departmentAdminLogins,
                'office_admin_logins' => $officeAdminLogins,
            ];
            
            return view('superadmin.access_logs', compact('logs', 'stats'));
        } catch (\Exception $e) {
            \Log::error('Error loading admin access logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty collection if there's an error
            $logs = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, 10, 1, ['path' => request()->url()]
            );
            $stats = [
                'total_attempts' => 0,
                'successful_logins' => 0,
                'failed_logins' => 0,
                'active_sessions' => 0,
                'completed_sessions' => 0,
                'superadmin_logins' => 0,
                'department_admin_logins' => 0,
                'office_admin_logins' => 0,
            ];
            return view('superadmin.access_logs', compact('logs', 'stats'));
        }
    }

    /**
     * Delete an admin access log entry
     */
    public function destroy($id)
    {
        // Double-check authorization
        $admin = Auth::guard('admin')->user();
        
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Access denied. Only SuperAdmins can delete Admin Access Logs.');
        }

        try {
            $log = AdminAccessLog::findOrFail($id);
            
            // Log the deletion for audit purposes
            \Log::info('Admin access log deleted', [
                'deleted_by' => $admin->username,
                'deleted_log_id' => $id,
                'deleted_admin' => $log->admin ? $log->admin->username : 'Unknown',
                'deleted_role' => $log->role,
                'deleted_time_in' => $log->time_in,
                'timestamp' => now()->toISOString()
            ]);
            
            $log->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Access log deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting admin access log', [
                'error' => $e->getMessage(),
                'log_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete access log. Please try again.'
            ], 500);
        }
    }
}
