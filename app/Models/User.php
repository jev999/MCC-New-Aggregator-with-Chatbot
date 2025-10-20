<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\DataEncryptionService;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'first_name',
        'middle_name',
        'surname',
        'ms365_account',
        'gmail_account',
        'full_name',
        'password',
        'role',
        'department',
        'year_level',
        'profile_picture',
        'email_verified_at',
        'ms365_account',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'ms365_account' => 'string',
    ];

    /**
     * The attributes that should be encrypted.
     *
     * @var array
     */
    protected $encrypted = [
        'ms365_account',
        'gmail_account',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->surname;
    }

    /**
     * Get the user's name (alias for full_name for compatibility)
     */
    public function getNameAttribute()
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Get the email address that should be used for password resets.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->ms365_account ?? $this->gmail_account;
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        // For password reset notifications, ensure we use the correct email
        if (method_exists($notification, 'toMail')) {
            return $this->ms365_account ?? $this->gmail_account;
        }
        return $this->ms365_account ?? $this->gmail_account;
    }

    /**
     * Get the email attribute for password resets (Laravel's default method)
     */
    public function getEmailAttribute()
    {
        return $this->ms365_account ?? $this->gmail_account;
    }

    /**
     * Get the primary email address (ms365_account takes precedence over gmail_account)
     */
    public function getPrimaryEmailAttribute()
    {
        return $this->ms365_account ?: $this->gmail_account;
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Get the profile picture URL.
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return null;
    }

    /**
     * Check if user has a profile picture.
     */
    public function getHasProfilePictureAttribute()
    {
        return !empty($this->profile_picture);
    }

    /**
     * Get user initials for avatar fallback.
     */
    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->surname, 0, 1));
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            foreach ($model->encrypted as $field) {
                if (isset($model->attributes[$field]) && !empty($model->attributes[$field])) {
                    $model->attributes[$field] = DataEncryptionService::encrypt($model->attributes[$field]);
                }
            }
        });

        static::retrieved(function ($model) {
            foreach ($model->encrypted as $field) {
                if (isset($model->attributes[$field]) && !empty($model->attributes[$field])) {
                    try {
                        $model->attributes[$field] = DataEncryptionService::decrypt($model->attributes[$field]);
                    } catch (\Exception $e) {
                        // If decryption fails, keep original value (might not be encrypted yet)
                        \Log::warning('Failed to decrypt field in User model', [
                            'field' => $field,
                            'user_id' => $model->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        });

        // Automatically assign role when user is created
        static::created(function ($user) {
            try {
                // Assign role based on the user's role field
                if ($user->role === 'student') {
                    $user->assignRole('student');
                } elseif ($user->role === 'faculty') {
                    $user->assignRole('faculty');
                } else {
                    // Default to student role if no role is specified
                    $user->assignRole('student');
                }
                
                \Log::info('Role assigned to new user', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'assigned_roles' => $user->getRoleNames()
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to assign role to new user', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }
}