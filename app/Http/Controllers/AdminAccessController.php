<?php

namespace App\Http\Controllers;

use App\Models\AdminAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminAccessController extends Controller
{
    public function __construct()
    {
        // Ensure only SuperAdmins or session snapshots can access this controller
        $this->middleware(function ($request, $next) {
            $admin = Auth::guard('admin')->user();

            // Allow if authenticated superadmin
            if ($admin && method_exists($admin, 'isSuperAdmin') && $admin->isSuperAdmin()) {
                return $next($request);
            }

            // Fallback: allow if we have a valid session snapshot indicating prior superadmin login
            $snapshot = $request->session()->get('admin_session_snapshot');
            if ($snapshot && ($snapshot['role'] ?? null) === 'superadmin') {
                return $next($request);
            }

            abort(403, 'Access denied. Only SuperAdmins can view Admin Access Logs.');
        });
    }

    public function index()
    {
        // Double-check authorization (allow snapshot)
        $admin = Auth::guard('admin')->user();
        if (!($admin && $admin->isSuperAdmin())) {
            $snapshot = request()->session()->get('admin_session_snapshot');
            if (!$snapshot || ($snapshot['role'] ?? null) !== 'superadmin') {
                abort(403, 'Access denied. Only SuperAdmins can view Admin Access Logs.');
            }
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
     * Update admin access log with GPS coordinates from browser
     */
    public function updateGpsLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        try {
            // Get the current admin
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authenticated admin found.'
                ], 401);
            }

            // Find the most recent active access log for this admin
            $log = AdminAccessLog::where('admin_id', $admin->id)
                ->where('status', 'success')
                ->whereNull('time_out')
                ->latest('time_in')
                ->first();

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active access log found.'
                ], 404);
            }

            $latitude = $request->latitude;
            $longitude = $request->longitude;

            // Use reverse geocoding to get exact address from GPS coordinates
            $locationDetails = $this->reverseGeocodeCoordinates($latitude, $longitude);

            // Update the access log with GPS coordinates
            $log->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_details' => $locationDetails
            ]);

            Log::info('GPS location updated for admin access log', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_details' => $locationDetails,
                'source' => 'GPS (Browser Geolocation)'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully with GPS coordinates.',
                'location' => $locationDetails
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating GPS location', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update location. Please try again.'
            ], 500);
        }
    }

    /**
     * Reverse geocode coordinates to get exact address
     */
    protected function reverseGeocodeCoordinates($latitude, $longitude)
    {
        try {
            // Use Nominatim reverse geocoding for exact address
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'MCC-NAC-Admin-Tracker/1.0'
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'zoom' => 18,
                    'addressdetails' => 1
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['address'])) {
                    $location = $this->formatExactLocationDetails($data['address']);
                    // Add GPS indicator and accuracy note
                    return $location . ' [GPS - Exact Device Location]';
                }
            }

            // Fallback to coordinates if geocoding fails
            return "GPS: {$latitude}, {$longitude} [Exact Coordinates]";

        } catch (\Exception $e) {
            Log::warning('Reverse geocoding failed', [
                'error' => $e->getMessage(),
                'coordinates' => "{$latitude}, {$longitude}"
            ]);
            
            return "GPS: {$latitude}, {$longitude} [Exact Coordinates]";
        }
    }

    /**
     * Format exact location details from Nominatim reverse geocoding
     */
    protected function formatExactLocationDetails($address)
    {
        $parts = [];

        // Barangay / Neighborhood / Suburb (most specific)
        if (!empty($address['neighbourhood'])) {
            $parts[] = 'Brgy. ' . $address['neighbourhood'];
        } elseif (!empty($address['suburb'])) {
            $parts[] = 'Brgy. ' . $address['suburb'];
        } elseif (!empty($address['village'])) {
            $parts[] = 'Brgy. ' . $address['village'];
        } elseif (!empty($address['hamlet'])) {
            $parts[] = $address['hamlet'];
        }

        // Municipality / City
        if (!empty($address['municipality'])) {
            $parts[] = $address['municipality'];
        } elseif (!empty($address['city'])) {
            $parts[] = $address['city'];
        } elseif (!empty($address['town'])) {
            $parts[] = $address['town'];
        }

        // Province / State
        if (!empty($address['province'])) {
            $parts[] = $address['province'];
        } elseif (!empty($address['state'])) {
            $parts[] = $address['state'];
        }

        // Region (for Philippines)
        if (!empty($address['region'])) {
            $parts[] = $address['region'];
        }

        // Country
        if (!empty($address['country'])) {
            $parts[] = $address['country'];
        }

        // Postal Code
        if (!empty($address['postcode'])) {
            $parts[] = 'Postal: ' . $address['postcode'];
        }

        // Road/Street (if available and specific)
        if (!empty($address['road']) && count($parts) > 0) {
            $parts[] = 'Street: ' . $address['road'];
        }

        // Return formatted location (GPS indicator added by caller)
        return !empty($parts) ? implode(', ', $parts) : 'Location data available';
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
