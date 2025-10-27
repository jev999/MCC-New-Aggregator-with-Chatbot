<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'context',
        'is_sensitive',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'is_sensitive' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user associated with this activity
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter sensitive activities
     */
    public function scopeSensitive($query)
    {
        return $query->where('is_sensitive', true);
    }

    /**
     * Scope to filter by user type
     */
    public function scopeByUserType($query, string $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope to filter recent activities
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

