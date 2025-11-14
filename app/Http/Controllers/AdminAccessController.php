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
     * Get real-time IP and location (public endpoint for login page)
     * This allows the login page to detect the user's real IP and location before authentication
     */
    public function getRealtimeLocation(Request $request)
    {
        try {
            $clientIp = $this->resolveClientIp($request);
            $geolocationService = new \App\Services\GeolocationService();
            $geoData = $geolocationService->getLocationFromIp($clientIp);
            
            return response()->json([
                'success' => true,
                'ip_address' => $clientIp,
                'latitude' => $geoData['latitude'] ?? null,
                'longitude' => $geoData['longitude'] ?? null,
                'location_details' => $geoData['location_details'] ?? 'Location unavailable',
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting real-time location', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get location',
                'ip_address' => $request->ip() ?? 'Unknown'
            ], 500);
        }
    }

    /**
     * Resolve client IP address (helper method)
     */
    protected function resolveClientIp(Request $request): string
    {
        $headersToInspect = [
            'CF-Connecting-IP',
            'True-Client-IP',
            'X-Forwarded-For',
            'X-Real-IP',
            'X-Client-IP',
            'X-Cluster-Client-IP',
            'Forwarded',
        ];

        foreach ($headersToInspect as $header) {
            $values = $request->headers->get($header);
            if (!$values) {
                continue;
            }

            $candidates = $this->extractIpCandidates($header, $values);

            foreach ($candidates as $candidate) {
                if ($this->isValidPublicIp($candidate)) {
                    return $candidate;
                }
            }
        }

        $fallback = $request->getClientIp();
        return is_string($fallback) ? $fallback : '0.0.0.0';
    }

    /**
     * Extract potential IPs from a header value
     */
    private function extractIpCandidates(string $header, string $value): array
    {
        if (strcasecmp($header, 'Forwarded') === 0) {
            preg_match_all('/for="?([^;"]+)"?/i', $value, $matches);
            return array_map([$this, 'sanitizeIpValue'], $matches[1] ?? []);
        }

        $parts = array_map('trim', explode(',', $value));
        return array_map([$this, 'sanitizeIpValue'], $parts);
    }

    /**
     * Sanitize IP value
     */
    private function sanitizeIpValue(string $value): string
    {
        $value = trim($value, " \t\n\r\0\x0B\"");

        if (str_contains($value, ':') && str_contains($value, '.')) {
            $lastColon = strrpos($value, ':');
            if ($lastColon !== false) {
                $maybeIp = substr($value, 0, $lastColon);
                $maybePort = substr($value, $lastColon + 1);
                if (ctype_digit($maybePort)) {
                    return $maybeIp;
                }
            }
        }

        if (preg_match('/^\[(.*)\]:(\d+)$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    /**
     * Check if IP is valid public IP
     */
    private function isValidPublicIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
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

    /**
     * Bulk delete admin access log entries
     */
    public function bulkDestroy(Request $request)
    {
        // Double-check authorization
        $admin = Auth::guard('admin')->user();
        
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Access denied. Only SuperAdmins can delete Admin Access Logs.');
        }

        $request->validate([
            'log_ids' => 'required|array|min:1',
            'log_ids.*' => 'required|integer|exists:admin_access_logs,id'
        ]);

        try {
            $logIds = $request->log_ids;
            $count = count($logIds);
            
            // Get log details for audit before deletion
            $logs = AdminAccessLog::whereIn('id', $logIds)->get();
            
            // Log the bulk deletion for audit purposes
            \Log::info('Bulk admin access logs deleted', [
                'deleted_by' => $admin->username,
                'deleted_count' => $count,
                'deleted_log_ids' => $logIds,
                'deleted_logs_details' => $logs->map(function($log) {
                    return [
                        'id' => $log->id,
                        'admin' => $log->admin ? $log->admin->username : 'Unknown',
                        'role' => $log->role,
                        'time_in' => $log->time_in,
                    ];
                })->toArray(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Delete the logs
            AdminAccessLog::whereIn('id', $logIds)->delete();
            
            return response()->json([
                'success' => true,
                'message' => "{$count} access log(s) deleted successfully."
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid log IDs provided.'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting admin access logs', [
                'error' => $e->getMessage(),
                'log_ids' => $request->log_ids ?? [],
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete access logs. Please try again.'
            ], 500);
        }
    }
}
