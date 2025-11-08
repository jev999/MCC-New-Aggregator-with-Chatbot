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
                return $location;
            }

            // Fallback to ip-api.com (free, 45/min limit)
            $location = $this->getFromIpApi($ip);
            if ($location) {
                return $location;
            }

            // Second fallback: ipinfo.io (free, 50k/month)
            $location = $this->getFromIpInfo($ip);
            if ($location) {
                return $location;
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
        return $ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || 
               strpos($ip, '10.') === 0 || strpos($ip, '172.') === 0;
    }
}

