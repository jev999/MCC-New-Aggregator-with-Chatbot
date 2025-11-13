<?php

namespace App\Http\Controllers;

use App\Models\AdminLoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminLocationController extends Controller
{
    public function storePrecise(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        $admin = $this->resolveAdmin($request);

        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $lat = (float) $request->input('latitude');
        $lng = (float) $request->input('longitude');

        $reverse = $this->reverseGeocode($lat, $lng);

        $log = AdminLoginLog::where('admin_id', $admin->id)
            ->latest('logged_at')
            ->first();

        if ($log) {
            $log->update([
                'latitude' => $lat,
                'longitude' => $lng,
                'province' => $reverse['province'] ?? $log->province,
                'city' => $reverse['city'] ?? $log->city,
                'barangay' => $reverse['barangay'] ?? $log->barangay,
                'raw_response' => [
                    'reverse' => $reverse['raw'] ?? null,
                    'accuracy' => $request->input('accuracy'),
                ],
            ]);
        } else {
            $log = AdminLoginLog::create([
                'admin_id' => $admin->id,
                'role' => $admin->role ?? null,
                'ip' => request()->ip(),
                'latitude' => $lat,
                'longitude' => $lng,
                'province' => $reverse['province'] ?? null,
                'city' => $reverse['city'] ?? null,
                'barangay' => $reverse['barangay'] ?? null,
                'raw_response' => [
                    'reverse' => $reverse['raw'] ?? null,
                    'accuracy' => $request->input('accuracy'),
                ],
                'user_agent' => $request->userAgent(),
                'logged_at' => now(),
            ]);
        }

        Log::info('Precise admin location stored', [
            'admin_id' => $admin->id,
            'log_id' => $log->id ?? null,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        return response()->json([
            'ok' => true,
            'province' => $log->province,
            'city' => $log->city,
            'barangay' => $log->barangay,
        ]);
    }

    protected function resolveAdmin(Request $request)
    {
        $guardAdmin = Auth::guard('admin')->user();
        if ($guardAdmin) {
            return $guardAdmin;
        }

        $default = $request->user();

        return ($default instanceof \App\Models\Admin) ? $default : null;
    }

    protected function reverseGeocode(float $latitude, float $longitude): array
    {
        $result = [
            'barangay' => null,
            'city' => null,
            'province' => null,
            'raw' => null,
        ];

        try {
            if ($apiKey = env('GOOGLE_GEOCODE_API_KEY')) {
                $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => "{$latitude},{$longitude}",
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $result['raw'] = $json;

                    if (!empty($json['results'])) {
                        foreach ($json['results'] as $item) {
                            foreach ($item['address_components'] as $component) {
                                $types = $component['types'] ?? [];

                                if (!$result['barangay'] && $this->isBarangayType($types)) {
                                    $result['barangay'] = $component['long_name'];
                                }

                                if (!$result['city'] && $this->isCityType($types)) {
                                    $result['city'] = $component['long_name'];
                                }

                                if (!$result['province'] && $this->isProvinceType($types)) {
                                    $result['province'] = $component['long_name'];
                                }
                            }
                        }
                    }
                }
            } else {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'User-Agent' => 'MCC-NAC-Admin-Tracker/1.0',
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'zoom' => 18,
                        'addressdetails' => 1,
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $result['raw'] = $json;

                    if (isset($json['address'])) {
                        $address = $json['address'];
                        $result['barangay'] = $address['neighbourhood']
                            ?? $address['suburb']
                            ?? $address['village']
                            ?? $address['hamlet']
                            ?? null;
                        $result['city'] = $address['municipality']
                            ?? $address['city']
                            ?? $address['town']
                            ?? $address['county']
                            ?? null;
                        $result['province'] = $address['province']
                            ?? $address['state']
                            ?? null;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Reverse geocoding (precise) failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    protected function isBarangayType(array $types): bool
    {
        return !empty(array_intersect($types, [
            'sublocality_level_1',
            'neighborhood',
            'sublocality',
            'political',
        ]));
    }

    protected function isCityType(array $types): bool
    {
        return !empty(array_intersect($types, [
            'locality',
            'administrative_area_level_2',
            'postal_town',
        ]));
    }

    protected function isProvinceType(array $types): bool
    {
        return !empty(array_intersect($types, [
            'administrative_area_level_1',
            'administrative_area_level_2',
        ]));
    }
}

