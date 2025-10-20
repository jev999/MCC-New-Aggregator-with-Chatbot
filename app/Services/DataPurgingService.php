<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DataPurgingService
{
    /**
     * Purge old and unnecessary data based on retention policies
     */
    public static function purgeOldData(): array
    {
        $results = [];
        
        try {
            // Purge old notifications (older than 90 days)
            $results['notifications'] = self::purgeOldNotifications();
            
            // Purge old comments from deleted content
            $results['orphaned_comments'] = self::purgeOrphanedComments();
            
            // Purge inactive user accounts (older than 2 years with no activity)
            $results['inactive_users'] = self::purgeInactiveUsers();
            
            // Purge old password reset tokens
            $results['password_reset_tokens'] = self::purgePasswordResetTokens();
            
            // Purge old session data
            $results['old_sessions'] = self::purgeOldSessions();
            
            Log::info('Data purging completed successfully', $results);
            
        } catch (\Exception $e) {
            Log::error('Data purging failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
        
        return $results;
    }

    /**
     * Purge notifications older than 90 days
     */
    private static function purgeOldNotifications(): int
    {
        $cutoffDate = Carbon::now()->subDays(90);
        
        $deletedCount = Notification::where('created_at', '<', $cutoffDate)
            ->where('read_at', '!=', null) // Only delete read notifications
            ->delete();
            
        return $deletedCount;
    }

    /**
     * Purge orphaned comments (comments whose parent content no longer exists)
     */
    private static function purgeOrphanedComments(): int
    {
        $deletedCount = 0;
        
        // Get all comments
        $comments = Comment::all();
        
        foreach ($comments as $comment) {
            $commentableModel = $comment->commentable_type;
            $commentableId = $comment->commentable_id;
            
            // Check if the parent content still exists
            if (!class_exists($commentableModel)) {
                $comment->delete();
                $deletedCount++;
                continue;
            }
            
            $parentExists = $commentableModel::where('id', $commentableId)->exists();
            if (!$parentExists) {
                $comment->delete();
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }

    /**
     * Purge inactive user accounts (2 years with no activity)
     */
    private static function purgeInactiveUsers(): int
    {
        $cutoffDate = Carbon::now()->subYears(2);
        
        // Only purge users who have never logged in and have no content
        $inactiveUsers = User::where('created_at', '<', $cutoffDate)
            ->whereNull('email_verified_at')
            ->whereDoesntHave('notifications')
            ->whereDoesntHave('comments')
            ->get();
            
        $deletedCount = 0;
        foreach ($inactiveUsers as $user) {
            // Double-check: ensure user has no activity
            $hasActivity = $user->notifications()->exists() || 
                          $user->comments()->exists() ||
                          $user->updated_at > $user->created_at->addDays(30);
                          
            if (!$hasActivity) {
                $user->delete();
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }

    /**
     * Purge old password reset tokens
     */
    private static function purgePasswordResetTokens(): int
    {
        $cutoffDate = Carbon::now()->subHours(24); // Tokens expire after 24 hours
        
        return \DB::table('password_reset_tokens')
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Purge old session data
     */
    private static function purgeOldSessions(): int
    {
        $cutoffDate = Carbon::now()->subDays(30);
        
        return \DB::table('sessions')
            ->where('last_activity', '<', $cutoffDate->timestamp)
            ->delete();
    }

    /**
     * Get data retention statistics
     */
    public static function getRetentionStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_admins' => Admin::count(),
            'total_notifications' => Notification::count(),
            'total_comments' => Comment::count(),
            'old_notifications' => Notification::where('created_at', '<', Carbon::now()->subDays(90))->count(),
            'inactive_users' => User::where('created_at', '<', Carbon::now()->subYears(2))
                ->whereNull('email_verified_at')
                ->whereDoesntHave('notifications')
                ->whereDoesntHave('comments')
                ->count(),
            'old_sessions' => \DB::table('sessions')
                ->where('last_activity', '<', Carbon::now()->subDays(30)->timestamp)
                ->count(),
        ];
    }
}
