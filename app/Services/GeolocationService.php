<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeolocationService
{
    /**
     * Get geolocation data from IP address using multiple providers for accuracy
     * 
     * @param string $ip
     * @return array|null
     */
    public function getLocationFromIp($ip)
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

