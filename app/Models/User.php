<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
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
        'password_changed_at',
        'password_expires_at',
        'password_must_change',
        'password_history',
        'failed_login_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'password_expires_at' => 'datetime',
        'password_must_change' => 'boolean',
        'password_history' => 'array',
        'locked_until' => 'datetime',
        'ms365_account' => 'string',
    ];

    /**
     * The attributes that should be encrypted.
     *
     * @var array
     */

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
                
                // Set initial password security settings
                $user->password_changed_at = now();
                $user->password_expires_at = now()->addDays(90); // 90 days expiration
                $user->password_history = [];
                $user->save();
                
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

    /**
     * Check if password has expired
     */
    public function isPasswordExpired()
    {
        return $this->password_expires_at && $this->password_expires_at->isPast();
    }

    /**
     * Check if password must be changed
     */
    public function mustChangePassword()
    {
        return $this->password_must_change || $this->isPasswordExpired();
    }

    /**
     * Check if account is locked due to failed login attempts
     */
    public function isLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Get remaining lock time in minutes
     */
    public function getLockTimeRemaining()
    {
        if (!$this->isLocked()) {
            return 0;
        }
        return now()->diffInMinutes($this->locked_until);
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedLoginAttempts()
    {
        $this->failed_login_attempts++;
        
        // Lock account after 5 failed attempts for 30 minutes
        if ($this->failed_login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(30);
        }
        
        $this->save();
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedLoginAttempts()
    {
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }

    /**
     * Update password with security checks
     */
    public function updatePassword($newPassword)
    {
        // Add current password to history (keep last 5 passwords)
        $history = $this->password_history ?? [];
        array_unshift($history, $this->password);
        $history = array_slice($history, 0, 5);
        
        $this->password = $newPassword;
        $this->password_changed_at = now();
        $this->password_expires_at = now()->addDays(90); // 90 days expiration
        $this->password_must_change = false;
        $this->password_history = $history;
        $this->resetFailedLoginAttempts();
        
        $this->save();
    }

    /**
     * Check if password was used recently (in last 5 passwords)
     */
    public function wasPasswordRecentlyUsed($password)
    {
        $history = $this->password_history ?? [];
        
        foreach ($history as $oldPassword) {
            if (Hash::check($password, $oldPassword)) {
                return true;
            }
        }
        
        return false;
    }
}