<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'username',
        'password',
        'role',
        'department',
        'office',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be encrypted.
     *
     * @var array
     */

    protected $appends = [
        'department_display',
        'office_display',
        'role_display'
    ];

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    /**
     * Check if the admin is a super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if the admin is a regular admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the admin is a department admin
     */
    public function isDepartmentAdmin()
    {
        return $this->role === 'department_admin';
    }

    /**
     * Check if the admin is an office admin
     */
    public function isOfficeAdmin()
    {
        return $this->role === 'office_admin';
    }

    /**
     * Scope to get only super admins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('role', 'superadmin');
    }

    /**
     * Scope to get only regular admins
     */
    public function scopeRegularAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope to get only department admins
     */
    public function scopeDepartmentAdmins($query)
    {
        return $query->where('role', 'department_admin');
    }

    /**
     * Scope to get only office admins
     */
    public function scopeOfficeAdmins($query)
    {
        return $query->where('role', 'office_admin');
    }

    /**
     * Scope to get admins by department
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get the display name for the admin role
     */
    public function getRoleDisplayAttribute()
    {
        switch($this->role) {
            case 'superadmin':
                return 'Super Administrator';
            case 'department_admin':
                return 'Department Administrator';
            case 'office_admin':
                return 'Office Administrator';
            case 'admin':
                return 'Administrator';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get the department display name
     */
    public function getDepartmentDisplayAttribute()
    {
        if (!$this->department) {
            return 'N/A';
        }

        switch($this->department) {
            case 'BSIT':
                return 'Bachelor of Science in Information Technology';
            case 'BSBA':
                return 'Bachelor of Science in Business Administration';
            case 'EDUC':
                return 'College of Education';
            case 'BSHM':
                return 'Bachelor of Science in Hospitality Management';
            case 'BSED':
                return 'Bachelor of Secondary Education';
            default:
                return $this->department;
        }
    }

    /**
     * Get the office display name
     */
    public function getOfficeDisplayAttribute()
    {
        if (!$this->office) {
            return 'N/A';
        }

        switch($this->office) {
            case 'NSTP':
                return 'National Service Training Program Office';
            case 'SSC':
                return 'Student Supreme Council Office';
            case 'GUIDANCE':
                return 'Guidance Office';
            case 'REGISTRAR':
                return 'Registrar Office';
            case 'CLINIC':
                return 'Clinic Office';
            default:
                return $this->office;
        }
    }

    /**
     * Check if admin has a profile picture
     */
    public function getHasProfilePictureAttribute()
    {
        return !empty($this->profile_picture) && \Storage::disk('public')->exists($this->profile_picture);
    }

    /**
     * Get the profile picture URL
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->hasProfilePicture) {
            return asset('storage/' . $this->profile_picture);
        }
        return null;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

    }
}
