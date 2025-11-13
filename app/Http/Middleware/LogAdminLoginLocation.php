<?php

namespace App\Http\Middleware;

use App\Models\AdminLoginLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogAdminLoginLocation
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $user = $this->resolveAdmin($request);

            if ($user && $this->shouldLog($request, $user)) {
                // Check if AdminLoginLog model exists
                if (!class_exists(\App\Models\AdminLoginLog::class)) {
                    Log::warning('AdminLoginLog model not found, skipping location logging');
                    return $response;
                }

                $ip = $this->resolveClientIp($request);
                $ipData = $this->fetchIpGeolocation($ip);

                $latitude = $ipData['latitude'] ?? null;
                $longitude = $ipData['longitude'] ?? null;
                $reverseGeo = $this->reverseGeocode($latitude, $longitude);

                AdminLoginLog::create([
                    'admin_id' => $user->id,
                    'role' => $user->role ?? null,
                    'ip' => $ip,
                    'isp' => $ipData['isp'] ?? null,
                    'country' => $ipData['country'] ?? null,
                    'region' => $ipData['region'] ?? null,
                    'province' => $reverseGeo['province'] ?? $ipData['province'] ?? null,
                    'city' => $reverseGeo['city'] ?? $ipData['city'] ?? null,
                    'barangay' => $reverseGeo['barangay'] ?? ($ipData['barangay'] ?? null),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'raw_response' => [
                        'ipapi' => $ipData['raw'] ?? null,
                        'reverse' => $reverseGeo['raw'] ?? null,
                    ],
                    'user_agent' => $request->userAgent(),
                    'logged_at' => now(),
                ]);

                $this->rememberLogged($request, $user, AdminLoginLog::latest()->first()->id ?? null);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to capture admin login location', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? 'unknown',
                'request_path' => $request->path(),
            ]);
        }

        return $response;
    }

    protected function resolveAdmin(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            return $admin;
        }

        $default = $request->user();

        return ($default instanceof \App\Models\Admin) ? $default : null;
    }

    protected function shouldLog(Request $request, $admin): bool
    {
        if (!in_array($admin->role ?? '', ['superadmin', 'office_admin', 'department_admin'], true)) {
            return false;
        }

        $sessionKey = $this->sessionKey($admin);

        return !$request->session()->has($sessionKey);
    }

    protected function rememberLogged(Request $request, $admin, int $logId): void
    {
        $request->session()->put($this->sessionKey($admin), $logId);
    }

    protected function sessionKey($admin): string
    {
        return 'admin_login_location_logged_' . $admin->getAuthIdentifier();
    }

    protected function resolveClientIp(Request $request): string
    {
        $ipHeaders = [
            'CF-Connecting-IP',
            'True-Client-IP',
            'X-Forwarded-For',
            'X-Real-IP',
        ];

        foreach ($ipHeaders as $header) {
            if ($request->headers->has($header)) {
                $ipList = explode(',', $request->headers->get($header));
                $candidate = trim($ipList[0]);
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }

        return $request->ip();
    }

    protected function fetchIpGeolocation(?string $ip): array
    {
        if (!$ip || in_array($ip, ['127.0.0.1', '::1'], true)) {
            return [];
        }

        try {
            $response = Http::timeout(5)->get("https://ipapi.co/{$ip}/json/");

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();

            if (isset($data['error']) && $data['error']) {
                return [];
            }

            return [
                'isp' => $data['org'] ?? null,
                'country' => $data['country_name'] ?? ($data['country'] ?? null),
                'region' => $data['region'] ?? null,
                'province' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : (isset($data['lat']) ? (float) $data['lat'] : null),
                'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : (isset($data['lon']) ? (float) $data['lon'] : null),
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            Log::debug('ipapi lookup failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function reverseGeocode(?float $latitude, ?float $longitude): array
    {
        if (!$latitude || !$longitude) {
            return [];
        }

        $results = [
            'barangay' => null,
            'city' => null,
            'province' => null,
            'raw' => null,
        ];

        try {
            if ($apiKey = env('GOOGLE_GEOCODE_API_KEY')) {
                $url = 'https://maps.googleapis.com/maps/api/geocode/json';
                $response = Http::timeout(5)->get($url, [
                    'latlng' => "{$latitude},{$longitude}",
                    'key' => $apiKey,
                    'result_type' => 'sublocality_level_1|neighborhood|political',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $results['raw'] = $json;
                    if (!empty($json['results'])) {
                        foreach ($json['results'] as $result) {
                            foreach ($result['address_components'] as $component) {
                                $types = $component['types'] ?? [];
                                if (!$results['barangay'] && $this->isBarangayType($types)) {
                                    $results['barangay'] = $component['long_name'];
                                }
                                if (!$results['city'] && $this->isCityType($types)) {
                                    $results['city'] = $component['long_name'];
                                }
                                if (!$results['province'] && $this->isProvinceType($types)) {
                                    $results['province'] = $component['long_name'];
                                }
                            }
                        }
                    }
                }
            } else {
                $response = Http::timeout(5)->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'MCC-NAC-Admin-Tracker/1.0',
                ])->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'zoom' => 18,
                    'addressdetails' => 1,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $results['raw'] = $json;

                    if (isset($json['address'])) {
                        $address = $json['address'];
                        $results['barangay'] = $address['neighbourhood']
                            ?? $address['suburb']
                            ?? $address['village']
                            ?? $address['hamlet']
                            ?? null;
                        $results['city'] = $address['municipality']
                            ?? $address['city']
                            ?? $address['town']
                            ?? $address['county']
                            ?? null;
                        $results['province'] = $address['province']
                            ?? $address['state']
                            ?? null;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Reverse geocoding failed', ['error' => $e->getMessage()]);
        }

        return $results;
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

