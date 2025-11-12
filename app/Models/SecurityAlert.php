<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SecurityAlert extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'activity_type',
        'severity',
        'admin_id',
        'ip_address',
        'user_agent',
        'url',
        'description',
        'data',
        'resolved',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'data' => 'array',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the admin associated with the alert
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get the admin who resolved the alert
     */
    public function resolver()
    {
        return $this->belongsTo(Admin::class, 'resolved_by');
    }

    /**
     * Scope for unresolved alerts
     */
    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope for resolved alerts
     */
    public function scopeResolved($query)
    {
        return $query->where('resolved', true);
    }

    /**
     * Scope for critical alerts
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for high severity alerts
     */
    public function scopeHigh($query)
    {
        return $query->whereIn('severity', ['critical', 'high']);
    }

    /**
     * Scope for today's alerts
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get severity badge color
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => '#dc2626',
            'high' => '#ef4444',
            'medium' => '#f59e0b',
            'low' => '#3b82f6',
            default => '#6b7280',
        };
    }

    /**
     * Get severity display name
     */
    public function getSeverityDisplayAttribute(): string
    {
        return ucfirst($this->severity);
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['resolved', 'resolved_by', 'resolved_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
