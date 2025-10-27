<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $primaryKey = 'email';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Create or update a password reset record
     */
    public static function updateOrCreateToken($email, $token)
    {
        return static::updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
    }

    /**
     * Verify token
     */
    public static function verifyToken($email, $token)
    {
        $record = static::where('email', $email)->first();
        
        if (!$record) {
            return false;
        }

        return Hash::check($token, $record->token);
    }

    /**
     * Delete reset record
     */
    public static function deleteToken($email)
    {
        return static::where('email', $email)->delete();
    }

    /**
     * Check if token is expired
     */
    public function isExpired($minutes = 60)
    {
        return now()->diffInMinutes($this->created_at) > $minutes;
    }
}

