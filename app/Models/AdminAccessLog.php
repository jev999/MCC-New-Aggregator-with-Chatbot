<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'role',
        'status',
        'username_attempted',
        'ip_address',
        'latitude',
        'longitude',
        'location_details',
        'time_in',
        'time_out',
        'duration',
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    /**
     * Start a new admin access session while gracefully closing any active session
     */
    public static function startSession(array $attributes): self
    {
        if (empty($attributes['admin_id'])) {
            return static::query()->create($attributes);
        }

        $now = $attributes['time_in'] ?? Carbon::now();
        $timeOutInstance = $now instanceof Carbon ? $now->copy() : Carbon::parse($now);

        $activeLog = static::where('admin_id', $attributes['admin_id'])
            ->where('status', 'success')
            ->whereNull('time_out')
            ->orderByDesc('time_in')
            ->orderByDesc('id')
            ->first();

        if ($activeLog) {
            $timeIn = $activeLog->time_in ?? $activeLog->created_at;
            $duration = null;

            if ($timeIn) {
                $timeInInstance = $timeIn instanceof Carbon ? $timeIn->copy() : Carbon::parse($timeIn);
                $duration = $timeOutInstance->diffForHumans($timeInInstance, true);
            }

            $activeLog->update([
                'time_out' => $timeOutInstance,
                'duration' => $duration,
            ]);
        }

        if (!isset($attributes['time_in'])) {
            $attributes['time_in'] = $timeOutInstance;
        }

        return static::query()->create($attributes);
    }

    /**
     * Filter attributes to only include columns that exist in the database
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Get actual columns from the database
            $schema = \Illuminate\Support\Facades\Schema::getColumnListing('admin_access_logs');
            
            // Filter $model->attributes to only include columns that exist
            $attributes = $model->getAttributes();
            foreach (array_keys($attributes) as $key) {
                if (!in_array($key, $schema)) {
                    unset($model->attributes[$key]);
                }
            }
        });

        static::updating(function ($model) {
            // Get actual columns from the database
            $schema = \Illuminate\Support\Facades\Schema::getColumnListing('admin_access_logs');
            
            // Filter $model->attributes to only include columns that exist
            $attributes = $model->getAttributes();
            foreach (array_keys($attributes) as $key) {
                if (!in_array($key, $schema)) {
                    unset($model->attributes[$key]);
                }
            }
        });
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
