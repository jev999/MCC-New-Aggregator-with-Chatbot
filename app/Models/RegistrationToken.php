<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationToken extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    /**
     * Create or update a registration token
     */
    public static function createToken($email, $token, $expiresAt)
    {
        return static::updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Find valid token
     */
    public static function findValidToken($token)
    {
        return static::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Delete token
     */
    public static function deleteToken($token)
    {
        return static::where('token', $token)->delete();
    }
}

