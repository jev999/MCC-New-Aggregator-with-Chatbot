<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;

class NotificationService
{
    /**
     * Create notifications for all users when content is published.
     */
    public function createContentNotification($content, string $type)
    {
        // Get all users (students and faculty)
        $users = User::all();
        
        // Get the admin who published the content
        $admin = $content->admin;
        
        // Prepare notification data
        $title = $this->getNotificationTitle($type, $admin);
        $message = $this->getNotificationMessage($type, $content->title, $admin);
        
        // Create notifications for all users
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'content_id' => $content->id,
                'content_type' => get_class($content),
                'admin_id' => $admin->id,
                'is_read' => false,
            ]);
        }
    }

    /**
     * Create notification when announcement is published.
     */
    public function createAnnouncementNotification(Announcement $announcement)
    {
        $this->createContentNotification($announcement, 'announcement');
    }

    /**
     * Create notification when event is published.
     */
    public function createEventNotification(Event $event)
    {
        $this->createContentNotification($event, 'event');
    }

    /**
     * Create notification when news is published.
     */
    public function createNewsNotification(News $news)
    {
        $this->createContentNotification($news, 'news');
    }

    /**
     * Get notification title based on type and admin.
     */
    private function getNotificationTitle(string $type, Admin $admin): string
    {
        $adminTitle = $this->getAdminTitle($admin);
        
        return match($type) {
            'announcement' => "New Announcement from {$adminTitle}",
            'event' => "New Event from {$adminTitle}",
            'news' => "New News from {$adminTitle}",
            default => "New Update from {$adminTitle}",
        };
    }

    /**
     * Get notification message based on type, content title, and admin.
     */
    private function getNotificationMessage(string $type, string $contentTitle, Admin $admin): string
    {
        $adminTitle = $this->getAdminTitle($admin);
        
        return match($type) {
            'announcement' => "{$adminTitle} has published a new announcement: \"{$contentTitle}\"",
            'event' => "{$adminTitle} has published a new event: \"{$contentTitle}\"",
            'news' => "{$adminTitle} has published a new news article: \"{$contentTitle}\"",
            default => "{$adminTitle} has published new content: \"{$contentTitle}\"",
        };
    }

    /**
     * Get admin title for display.
     */
    private function getAdminTitle(Admin $admin): string
    {
        return match($admin->role) {
            'superadmin' => 'MCC Administration',
            'department_admin' => ($admin->department ? $admin->department . ' Department' : 'Department Administration'),
            'office_admin' => ($admin->office ? $admin->office . ' Office' : 'Office Administration'),
            default => $admin->username,
        };
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Get recent notifications for a user.
     */
    public function getRecentNotifications(User $user, int $limit = 10)
    {
        return Notification::where('user_id', $user->id)
            ->with(['admin', 'content'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Mark specific notification as read.
     */
    public function markAsRead(int $notificationId, User $user): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Delete notifications when content is deleted.
     */
    public function deleteContentNotifications($content, string $type)
    {
        $contentId = $content->id;
        $contentType = get_class($content);
        
        // Delete all notifications related to this content
        $deletedCount = Notification::where('content_id', $contentId)
            ->where('content_type', $contentType)
            ->delete();
            
        return $deletedCount;
    }

    /**
     * Delete notifications when announcement is deleted.
     */
    public function deleteAnnouncementNotifications(Announcement $announcement)
    {
        return $this->deleteContentNotifications($announcement, 'announcement');
    }

    /**
     * Delete notifications when event is deleted.
     */
    public function deleteEventNotifications(Event $event)
    {
        return $this->deleteContentNotifications($event, 'event');
    }

    /**
     * Delete notifications when news is deleted.
     */
    public function deleteNewsNotifications(News $news)
    {
        return $this->deleteContentNotifications($news, 'news');
    }

    /**
     * Delete notifications when admin is deleted.
     */
    public function deleteAdminNotifications(Admin $admin)
    {
        // Delete all notifications created by this admin
        $deletedCount = Notification::where('admin_id', $admin->id)->delete();
        
        return $deletedCount;
    }

    /**
     * Delete notifications when admin is deleted (alias for consistency).
     */
    public function deletePublisherNotifications(Admin $admin)
    {
        return $this->deleteAdminNotifications($admin);
    }
}
