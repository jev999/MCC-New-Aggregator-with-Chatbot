<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the child comments (replies).
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user', 'replies')->orderBy('created_at', 'asc');
    }

    /**
     * Get the commentable model (announcement, event, or news).
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get formatted time ago for display.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if the comment can be edited by the given user.
     */
    public function canEdit($user)
    {
        return $user && $this->user_id === $user->id;
    }

    /**
     * Check if the comment can be deleted by the given user.
     */
    public function canDelete($user)
    {
        return $user && ($this->user_id === $user->id || $user->role === 'admin');
    }
}
