<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ms365Account extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'display_name',
        'user_principal_name'
    ];


    /**
     * Check if the account is valid for registration
     */
    public function isValidForRegistration(): bool
    {
        return !empty($this->user_principal_name);
    }

    /**
     * Get the email address (from user_principal_name)
     */
    public function getEmailAttribute(): string
    {
        return $this->user_principal_name;
    }

    /**
     * Get the full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->display_name ?? '';
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('user_principal_name');
    }

    /**
     * Find account by email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('user_principal_name', $email);
    }
}
