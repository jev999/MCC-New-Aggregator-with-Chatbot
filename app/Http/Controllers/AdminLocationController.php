<?php

namespace App\Http\Controllers;

use App\Models\AdminAccessLog;
use App\Models\Location;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminLocationController extends Controller
{
    /**
     * Store precise device GPS location for the current admin's active access log
     */
    public function storePrecise(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        try {
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

            $latitude = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');
            $accuracy = $request->input('accuracy');

            // Get structured location data using GeolocationService
            $geolocationService = new GeolocationService();
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
            $location = Location::create($locationData);

            // Build location details string for backward compatibility
            $locationDetails = $location->full_address;
            $sourceTag = 'Device GPS (Precise)';
            if ($accuracy !== null && $accuracy !== '') {
                $locationDetails .= ' [' . $sourceTag . ', ±' . (float)$accuracy . 'm]';
            } else {
                $locationDetails .= ' [' . $sourceTag . ']';
            }

            // Update the access log with GPS coordinates and location_id
            $log->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_details' => $locationDetails,
                'location_id' => $location->id,
            ]);

            Log::info('Precise GPS location stored for admin access log', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username ?? null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_details' => $locationDetails,
                'source' => 'Device GPS (Precise)'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Precise location stored successfully.',
                'location' => $locationDetails,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing precise GPS location', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store precise location. Please try again.'
            ], 500);
        }
    }

    /**
     * Reverse geocode coordinates to get exact address
     */
    protected function reverseGeocodeCoordinates(float $latitude, float $longitude): string
    {
        try {
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
                    'addressdetails' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['address'])) {
                    return $this->formatExactLocationDetails($data['address']);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Reverse geocoding failed (AdminLocationController)', [
                'error' => $e->getMessage(),
                'coordinates' => "$latitude, $longitude"
            ]);
        }

        return "Exact Location: {$latitude}, {$longitude}";
    }

    /**
     * Format exact location details from Nominatim reverse geocoding
     */
    protected function formatExactLocationDetails(array $address): string
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

        return !empty($parts) ? implode(', ', $parts) : 'Location data available';
    }

    /**
     * Store location during login (before authentication)
     * Stores location in session to be linked to access log after login
     */
    public function storeLoginLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        try {
            $latitude = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');
            $accuracy = $request->input('accuracy');

            // Get structured location data using GeolocationService
            $geolocationService = new GeolocationService();
            $structuredLocation = $geolocationService->getStructuredLocationFromCoordinates($latitude, $longitude);

            // Prepare location data
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

            // Store location data in session to be used after login
            $request->session()->put('login_location_data', $locationData);

            Log::info('Login location stored in session', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'has_structured_data' => !empty($structuredLocation),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location stored successfully.',
                'location' => $locationData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing login location', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store location. Please try again.'
            ], 500);
        }
    }
}
