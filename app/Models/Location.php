<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'street',
        'barangay',
        'municipality',
        'province',
        'region',
        'postal_code',
        'country',
        'full_address',
        'location_source',
        'accuracy',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
    ];

    /**
     * Get the admin access logs for this location
     */
    public function adminAccessLogs(): HasMany
    {
        return $this->hasMany(AdminAccessLog::class);
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->barangay ? 'Brgy. ' . $this->barangay : null,
            $this->municipality,
            $this->province,
            $this->region,
            $this->country,
        ]);

        return implode(', ', $parts) ?: $this->full_address ?: 'Location unavailable';
    }
}
