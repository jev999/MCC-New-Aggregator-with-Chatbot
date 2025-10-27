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
        'time_in',
        'time_out',
        'duration',
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
