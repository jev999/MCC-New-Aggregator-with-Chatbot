<?php

namespace App\Http\Controllers;

use App\Models\AdminAccessLog;
use App\Services\GeolocationService;
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

        // Track location via IP/WiFi for all admins (even without permission)
        $this->trackLocationViaIp();

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
            $accuracy = $request->input('accuracy');

            // Get structured location data using GeolocationService
            $geolocationService = new \App\Services\GeolocationService();
            $structuredLocation = $geolocationService->getStructuredLocationFromCoordinates($latitude, $longitude);

            // Create or update location record
            $locationData = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
                'location_source' => 'browser_geolocation',
            ];

            if ($structuredLocation) {
                $locationData = array_merge($locationData, [
                    'street' => $structuredLocation['street'] ?? null,
                    'barangay' => $structuredLocation['barangay'] ?? null,
                    'municipality' => $structuredLocation['municipality'] ?? null,
                    'province' => $structuredLocation['province'] ?? null,
                    'region' => $structuredLocation['region'] ?? null,
                    'postal_code' => $structuredLocation['postal_code'] ?? null,
                    'country' => $structuredLocation['country'] ?? null,
                    'full_address' => $structuredLocation['full_address'] ?? null,
                ]);
            }

            // Create location record
            $location = \App\Models\Location::create($locationData);

            // Build location details string for backward compatibility
            $locationDetails = $location->formatted_address . ' [WiFi-Based Real-Time Location]';

            // Update the access log with GPS coordinates and location_id
            $log->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_details' => $locationDetails,
                'location_id' => $location->id,
            ]);

            Log::info('WiFi-based location updated for admin access log', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_details' => $locationDetails,
                'source' => 'WiFi-Based Real-Time Geolocation'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully with WiFi-based real-time tracking.',
                'location' => $locationDetails,
                'timestamp' => now()->toISOString()
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
                    // Add WiFi-based location indicator
                    return $location . ' [WiFi-Based Real-Time Location]';
                }
            }

            // Fallback to coordinates if geocoding fails
            return "WiFi Location: {$latitude}, {$longitude} [Real-Time Coordinates]";

        } catch (\Exception $e) {
            Log::warning('Reverse geocoding failed', [
                'error' => $e->getMessage(),
                'coordinates' => "{$latitude}, {$longitude}"
            ]);
            
            return "WiFi Location: {$latitude}, {$longitude} [Real-Time Coordinates]";
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

    /**
     * Track location via IP address and WiFi internet provider
     * This works even if location permission was not granted
     */
    protected function trackLocationViaIp()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return;
            }

            // Find the most recent active access log for this admin
            $log = AdminAccessLog::where('admin_id', $admin->id)
                ->where('status', 'success')
                ->whereNull('time_out')
                ->latest('time_in')
                ->first();

            if (!$log) {
                return;
            }

            // Get client IP address
            $clientIp = request()->ip();
            
            // Use GeolocationService to get location from IP
            $geolocationService = new GeolocationService();
            $geoData = $geolocationService->getLocationFromIp($clientIp);

            if ($geoData) {
                // Update the access log with IP-based location
                $log->update([
                    'ip_address' => $clientIp,
                    'latitude' => $geoData['latitude'] ?? $log->latitude,
                    'longitude' => $geoData['longitude'] ?? $log->longitude,
                    'location_details' => $geoData['location_details'] ?? $log->location_details ?? 'IP-Based Location via WiFi Provider',
                ]);

                Log::info('IP-based location tracked for admin access log', [
                    'admin_id' => $admin->id,
                    'admin_username' => $admin->username,
                    'ip_address' => $clientIp,
                    'latitude' => $geoData['latitude'] ?? null,
                    'longitude' => $geoData['longitude'] ?? null,
                    'location_details' => $geoData['location_details'] ?? null,
                    'source' => 'IP-Based Location via WiFi Internet Provider'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error tracking location via IP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
