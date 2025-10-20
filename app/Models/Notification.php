<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'content_id',
        'content_type',
        'admin_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who created the content.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the content that this notification is about.
     */
    public function content()
    {
        return $this->morphTo('content', 'content_type', 'content_id');
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Get the icon for the notification type.
     */
    public function getIconAttribute()
    {
        return match($this->type) {
            'announcement' => 'fas fa-bullhorn',
            'event' => 'fas fa-calendar-alt',
            'news' => 'fas fa-newspaper',
            default => 'fas fa-bell',
        };
    }

    /**
     * Get the color class for the notification type.
     */
    public function getColorClassAttribute()
    {
        return match($this->type) {
            'announcement' => 'text-blue-600',
            'event' => 'text-green-600',
            'news' => 'text-purple-600',
            default => 'text-gray-600',
        };
    }
}
