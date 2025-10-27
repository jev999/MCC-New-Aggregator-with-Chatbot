<?php

namespace App\Models;

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
