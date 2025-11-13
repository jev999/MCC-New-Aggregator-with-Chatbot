<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'role',
        'ip',
        'isp',
        'country',
        'region',
        'province',
        'city',
        'barangay',
        'latitude',
        'longitude',
        'raw_response',
        'user_agent',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'raw_response' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}

