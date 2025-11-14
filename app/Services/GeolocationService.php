<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeolocationService
{
    /**
     * Get geolocation data from IP address using multiple providers for accuracy
     * Uses parallel requests and averages results for better accuracy
     * 
     * @param string $ip
     * @param bool $useParallel Whether to use parallel requests for better accuracy
     * @return array|null
     */
    public function getLocationFromIp($ip, $useParallel = true)
    {
        try {
            // Skip for local IPs
            if ($this->isLocalIp($ip)) {
                return [
                    'latitude' => null,
                    'longitude' => null,
                    'location_details' => 'Local Network (LAN IP Address)'
                ];
            }

            // Use parallel requests for better accuracy
            if ($useParallel) {
                $locations = $this->getLocationFromMultipleProviders($ip);
                
                if (!empty($locations)) {
                    // Average coordinates from multiple providers for better accuracy
                    $averagedLocation = $this->averageLocations($locations);
                    if ($averagedLocation) {
                        // Enhance with reverse geocoding for exact address
                        return $this->enhanceWithReverseGeocoding($averagedLocation);
                    }
                }
            }

            // Fallback to sequential requests
            // Try primary provider: ipapi.co (most accurate, free tier 30k/month)
            $location = $this->getFromIpapiCo($ip);
            if ($location) {
                // Enhance with reverse geocoding for exact address
                return $this->enhanceWithReverseGeocoding($location);
            }

            // Fallback to ip-api.com (free, 45/min limit)
            $location = $this->getFromIpApi($ip);
            if ($location) {
                // Enhance with reverse geocoding for exact address
                return $this->enhanceWithReverseGeocoding($location);
            }

            // Second fallback: ipinfo.io (free, 50k/month)
            $location = $this->getFromIpInfo($ip);
            if ($location) {
                // Enhance with reverse geocoding for exact address
                return $this->enhanceWithReverseGeocoding($location);
            }

            // Third fallback: ipgeolocation.io (more accurate for WiFi locations)
            $location = $this->getFromIpGeolocation($ip);
            if ($location) {
                return $this->enhanceWithReverseGeocoding($location);
            }

            // If all providers fail, return null
            Log::warning('All geolocation providers failed for IP: ' . $ip);
            return null;

        } catch (\Exception $e) {
            Log::error('Geolocation service error', [
                'ip' => $ip,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Get location from multiple providers in parallel for better accuracy
     * Uses Laravel's Http pool for concurrent requests
     * 
     * @param string $ip
     * @return array
     */
    protected function getLocationFromMultipleProviders($ip)
    {
        $locations = [];
        
        // Use Laravel's Http pool for parallel execution
        try {
            $responses = Http::pool(function ($pool) use ($ip) {
                return [
                    $pool->timeout(3)
                        ->withHeaders(['Accept' => 'application/json'])
                        ->get("https://ipapi.co/{$ip}/json/"),
                    $pool->timeout(3)
                        ->get("http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,isp,org,as,query"),
                    $pool->timeout(3)
                        ->withHeaders(['Accept' => 'application/json'])
                        ->get("https://ipinfo.io/{$ip}/json"),
                ];
            });
            
            // Process ipapi.co response
            if (isset($responses[0]) && $responses[0]->successful()) {
                $data = $responses[0]->json();
                if (!empty($data['latitude']) && !empty($data['longitude']) && !isset($data['error'])) {
                    $locations[] = [
                        'latitude' => (float) $data['latitude'],
                        'longitude' => (float) $data['longitude'],
                        'source' => 'ipapi.co',
                        'weight' => 1.0 // Higher weight for primary provider
                    ];
                }
            }
            
            // Process ip-api.com response
            if (isset($responses[1]) && $responses[1]->successful()) {
                $data = $responses[1]->json();
                if (isset($data['status']) && $data['status'] === 'success' && !empty($data['lat']) && !empty($data['lon'])) {
                    $locations[] = [
                        'latitude' => (float) $data['lat'],
                        'longitude' => (float) $data['lon'],
                        'source' => 'ip-api.com',
                        'weight' => 0.9
                    ];
                }
            }
            
            // Process ipinfo.io response
            if (isset($responses[2]) && $responses[2]->successful()) {
                $data = $responses[2]->json();
                if (!empty($data['loc'])) {
                    $coords = explode(',', $data['loc']);
                    if (count($coords) === 2) {
                        $locations[] = [
                            'latitude' => (float) trim($coords[0]),
                            'longitude' => (float) trim($coords[1]),
                            'source' => 'ipinfo.io',
                            'weight' => 0.8
                        ];
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::warning('Parallel geolocation request failed', [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
        }
        
        return $locations;
    }

    /**
     * Average multiple location results for better accuracy
     * Uses weighted average based on provider reliability
     * 
     * @param array $locations
     * @return array|null
     */
    protected function averageLocations($locations)
    {
        if (empty($locations)) {
            return null;
        }
        
        if (count($locations) === 1) {
            return [
                'latitude' => $locations[0]['latitude'],
                'longitude' => $locations[0]['longitude'],
                'location_details' => 'Location from ' . $locations[0]['source']
            ];
        }
        
        // Calculate weighted average
        $totalWeight = 0;
        $weightedLat = 0;
        $weightedLng = 0;
        $sources = [];
        
        foreach ($locations as $loc) {
            $weight = $loc['weight'] ?? 1.0;
            $totalWeight += $weight;
            $weightedLat += $loc['latitude'] * $weight;
            $weightedLng += $loc['longitude'] * $weight;
            $sources[] = $loc['source'];
        }
        
        if ($totalWeight > 0) {
            return [
                'latitude' => $weightedLat / $totalWeight,
                'longitude' => $weightedLng / $totalWeight,
                'location_details' => 'Averaged from: ' . implode(', ', array_unique($sources))
            ];
        }
        
        return null;
    }

    /**
     * Get location from ipgeolocation.io (Better accuracy for WiFi locations)
     * 
     * @param string $ip
     * @return array|null
     */
    protected function getFromIpGeolocation($ip)
    {
        try {
            // Using free tier endpoint
            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("https://api.ipgeolocation.io/ipgeo", [
                    'ip' => $ip,
                    'apiKey' => '' // Free tier doesn't require API key for basic queries
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['latitude']) && !empty($data['longitude'])) {
                    $parts = [];
                    if (!empty($data['city'])) $parts[] = $data['city'];
                    if (!empty($data['state_prov'])) $parts[] = $data['state_prov'];
                    if (!empty($data['country_name'])) $parts[] = $data['country_name'];
                    if (!empty($data['zipcode'])) $parts[] = 'ZIP: ' . $data['zipcode'];
                    if (!empty($data['isp'])) $parts[] = 'ISP: ' . $data['isp'];
                    
                    return [
                        'latitude' => (float) $data['latitude'],
                        'longitude' => (float) $data['longitude'],
                        'location_details' => !empty($parts) ? implode(', ', $parts) : 'Location from ipgeolocation.io'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('ipgeolocation.io request failed', ['ip' => $ip, 'error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Enhance location data with reverse geocoding for exact address details
     * Uses Nominatim (OpenStreetMap) to get exact province, municipality, barangay
     * 
     * @param array $location
     * @return array
     */
    protected function enhanceWithReverseGeocoding($location)
    {
        // If coordinates are not available, return original location
        if (empty($location['latitude']) || empty($location['longitude'])) {
            return $location;
        }

        try {
            $lat = $location['latitude'];
            $lng = $location['longitude'];

            // Use Nominatim reverse geocoding for exact address
            // Zoom level 18 for exact address, includes barangay/neighborhood
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'MCC-NAC-Admin-Tracker/1.0'
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => 18,
                    'addressdetails' => 1,
                    'extratags' => 1,
                    'namedetails' => 1
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['address'])) {
                    // Format exact location details from reverse geocoding
                    $exactLocation = $this->formatExactLocationDetails($data['address']);
                    
                    // Update location details with exact address
                    $location['location_details'] = $exactLocation;
                    
                    Log::info('Reverse geocoding successful', [
                        'coordinates' => "$lat, $lng",
                        'exact_location' => $exactLocation
                    ]);
                }
            } else {
                Log::warning('Nominatim reverse geocoding failed', [
                    'status' => $response->status(),
                    'coordinates' => "$lat, $lng"
                ]);
            }
        } catch (\Exception $e) {
            // If reverse geocoding fails, just use the original location
            Log::warning('Reverse geocoding error', [
                'error' => $e->getMessage(),
                'coordinates' => ($location['latitude'] ?? 'N/A') . ', ' . ($location['longitude'] ?? 'N/A')
            ]);
        }

        return $location;
    }

    /**
     * Format exact location details from Nominatim reverse geocoding
     * Prioritizes Philippine administrative divisions: Barangay, Municipality, Province, Region
     * 
     * @param array $address
     * @return string
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

        // If we have no parts, return a default message
        if (empty($parts)) {
            return 'Exact location: Lat ' . ($address['lat'] ?? 'N/A') . ', Lon ' . ($address['lon'] ?? 'N/A');
        }

        return implode(', ', $parts);
    }

    /**
     * Get location from ipapi.co (Primary - Most Accurate)
     * Provides: City, Region, Country, Postal, ISP, Organization, Timezone
     * 
     * @param string $ip
     * @return array|null
     */
    protected function getFromIpapiCo($ip)
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("https://ipapi.co/{$ip}/json/");
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Check if error exists
                if (isset($data['error']) && $data['error']) {
                    Log::warning('ipapi.co returned error', ['ip' => $ip, 'error' => $data['reason'] ?? 'Unknown']);
                    return null;
                }
                
                if (!empty($data['latitude']) && !empty($data['longitude'])) {
                    return [
                        'latitude' => (float) $data['latitude'],
                        'longitude' => (float) $data['longitude'],
                        'location_details' => $this->formatLocationDetailsIpapiCo($data)
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('ipapi.co request failed', ['ip' => $ip, 'error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Get location from ip-api.com (Fallback)
     * Free tier with good accuracy
     * 
     * @param string $ip
     * @return array|null
     */
    protected function getFromIpApi($ip)
    {
        try {
            $response = Http::timeout(5)->get(
                "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,isp,org,as,query"
            );
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success' && !empty($data['lat']) && !empty($data['lon'])) {
                    return [
                        'latitude' => (float) $data['lat'],
                        'longitude' => (float) $data['lon'],
                        'location_details' => $this->formatLocationDetailsIpApi($data)
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('ip-api.com request failed', ['ip' => $ip, 'error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Get location from ipinfo.io (Second Fallback)
     * Free tier 50k requests/month
     * 
     * @param string $ip
     * @return array|null
     */
    protected function getFromIpInfo($ip)
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("https://ipinfo.io/{$ip}/json");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['loc'])) {
                    $coords = explode(',', $data['loc']);
                    if (count($coords) === 2) {
                        return [
                            'latitude' => (float) trim($coords[0]),
                            'longitude' => (float) trim($coords[1]),
                            'location_details' => $this->formatLocationDetailsIpInfo($data)
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('ipinfo.io request failed', ['ip' => $ip, 'error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Format location details from ipapi.co (Most detailed)
     * 
     * @param array $data
     * @return string
     */
    protected function formatLocationDetailsIpapiCo($data)
    {
        $parts = [];
        
        // Add district/barangay if available
        if (!empty($data['city'])) {
            $parts[] = $data['city'];
        }
        
        // Add region/province
        if (!empty($data['region'])) {
            $parts[] = $data['region'];
        }
        
        // Add country
        if (!empty($data['country_name'])) {
            $parts[] = $data['country_name'];
        }
        
        // Add postal code if available
        if (!empty($data['postal'])) {
            $parts[] = 'Postal: ' . $data['postal'];
        }
        
        // Add ISP/Org for more context
        if (!empty($data['org'])) {
            $parts[] = 'ISP: ' . $data['org'];
        }
        
        // Add timezone
        if (!empty($data['timezone'])) {
            $parts[] = 'TZ: ' . $data['timezone'];
        }
        
        return !empty($parts) ? implode(', ', $parts) : 'Location data available';
    }

    /**
     * Format location details from ip-api.com
     * 
     * @param array $data
     * @return string
     */
    protected function formatLocationDetailsIpApi($data)
    {
        $parts = [];
        
        // Add district/barangay if available
        if (!empty($data['district'])) {
            $parts[] = $data['district'];
        }
        
        if (!empty($data['city'])) {
            $parts[] = $data['city'];
        }
        
        if (!empty($data['regionName'])) {
            $parts[] = $data['regionName'];
        }
        
        if (!empty($data['country'])) {
            $parts[] = $data['country'];
        }
        
        // Add postal code
        if (!empty($data['zip'])) {
            $parts[] = 'ZIP: ' . $data['zip'];
        }
        
        // Add ISP
        if (!empty($data['isp'])) {
            $parts[] = 'ISP: ' . $data['isp'];
        }
        
        return !empty($parts) ? implode(', ', $parts) : 'Location data available';
    }

    /**
     * Format location details from ipinfo.io
     * 
     * @param array $data
     * @return string
     */
    protected function formatLocationDetailsIpInfo($data)
    {
        $parts = [];
        
        if (!empty($data['city'])) {
            $parts[] = $data['city'];
        }
        
        if (!empty($data['region'])) {
            $parts[] = $data['region'];
        }
        
        if (!empty($data['country'])) {
            $parts[] = $data['country'];
        }
        
        if (!empty($data['postal'])) {
            $parts[] = 'Postal: ' . $data['postal'];
        }
        
        if (!empty($data['org'])) {
            $parts[] = 'ISP: ' . $data['org'];
        }
        
        return !empty($parts) ? implode(', ', $parts) : 'Location data available';
    }

    /**
     * Check if IP is local
     * 
     * @param string $ip
     * @return bool
     */
    protected function isLocalIp($ip)
    {
        if (!is_string($ip) || $ip === '') {
            return true;
        }

        if (in_array($ip, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }

        // If the IP remains valid after excluding private/reserved ranges, it is public.
        $isPublic = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        return $isPublic === false;
    }

    /**
     * Get more accurate location using multiple data sources
     * Combines IP geolocation with ISP info to provide context
     * 
     * @param string $ip
     * @return array
     */
    public function getAccurateLocation($ip)
    {
        try {
            // Get base location from IP
            $ipLocation = $this->getLocationFromIp($ip);
            
            if (!$ipLocation) {
                return [
                    'latitude' => null,
                    'longitude' => null,
                    'location_details' => 'Location unavailable - Waiting for GPS',
                    'location_source' => 'none',
                    'accuracy_note' => 'IP geolocation failed. GPS location will be used when available.'
                ];
            }

            // Add accuracy note about IP geolocation
            $accuracyNote = $this->isLocalIp($ip) 
                ? 'Local IP - GPS required for exact location' 
                : 'ISP/Network location - May not reflect actual WiFi location. GPS will provide exact location.';

            $ipLocation['location_source'] = $this->isLocalIp($ip) ? 'local_ip' : 'ip_geolocation';
            $ipLocation['accuracy_note'] = $accuracyNote;
            $ipLocation['location_details'] = ($ipLocation['location_details'] ?? 'Unknown') . ' [IP-Based, Not Exact]';

            return $ipLocation;

        } catch (\Exception $e) {
            Log::error('Accurate location service error', [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
            
            return [
                'latitude' => null,
                'longitude' => null,
                'location_details' => 'Location service error - GPS will be used',
                'location_source' => 'error',
                'accuracy_note' => 'Waiting for GPS coordinates from browser'
            ];
        }
    }
}

