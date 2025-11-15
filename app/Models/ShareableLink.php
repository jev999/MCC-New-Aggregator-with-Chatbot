<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShareableLink extends Model
{
    protected $fillable = [
        'token',
        'content_type',
        'content_id',
        'expires_at',
        'access_count',
        'last_accessed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'access_count' => 'integer',
    ];

    /**
     * Generate a secure unique token for sharing
     */
    public static function generateToken(): string
    {
        do {
            $token = hash('sha256', Str::random(32) . now()->timestamp . config('app.key'));
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * Create or get a shareable link for content
     */
    public static function createOrGetLink(string $contentType, int $contentId, $expiresInDays = null)
    {
        // Check if link already exists
        $existingLink = static::where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where(function($query) use ($expiresInDays) {
                if ($expiresInDays) {
                    $query->where('expires_at', '>', now())
                          ->orWhereNull('expires_at');
                } else {
                    $query->whereNull('expires_at');
                }
            })
            ->first();

        if ($existingLink && (!$expiresInDays || !$existingLink->expires_at || $existingLink->expires_at->isFuture())) {
            return $existingLink;
        }

        // Create new link
        return static::create([
            'token' => static::generateToken(),
            'content_type' => $contentType,
            'content_id' => $contentId,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);
    }

    /**
     * Find valid link by token
     */
    public static function findValidLink(string $token)
    {
        return static::where('token', $token)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    /**
     * Get the content associated with this link
     */
    public function getContent()
    {
        $modelClass = match($this->content_type) {
            'announcement' => Announcement::class,
            'event' => Event::class,
            'news' => News::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->content_id);
    }

    /**
     * Increment access count and update last accessed time
     */
    public function recordAccess()
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
    }
}
