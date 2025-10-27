<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeolocationService
{
    /**
     * Get geolocation data from IP address
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
                    'location_details' => 'Local network (127.0.0.1 or local IP)'
                ];
            }

            // Use ip-api.com (free tier, no API key required)
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,lat,lon,query");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    return [
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'location_details' => $this->formatLocationDetails($data)
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to get geolocation from IP', [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Format location details into readable string
     * 
     * @param array $data
     * @return string
     */
    protected function formatLocationDetails($data)
    {
        $parts = [];
        
        if (!empty($data['city'])) {
            $parts[] = $data['city'];
        }
        
        if (!empty($data['regionName'])) {
            $parts[] = $data['regionName'];
        }
        
        if (!empty($data['country'])) {
            $parts[] = $data['country'];
        }
        
        return !empty($parts) ? implode(', ', $parts) : 'Unknown location';
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

