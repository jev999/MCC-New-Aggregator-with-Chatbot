<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Services\NotificationService;

class AnnouncementObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Announcement "created" event.
     */
    public function created(Announcement $announcement): void
    {
        // Create notifications when announcement is published
        if ($announcement->is_published) {
            $this->notificationService->createAnnouncementNotification($announcement);
        }
    }

    /**
     * Handle the Announcement "updated" event.
     */
    public function updated(Announcement $announcement): void
    {
        // Create notifications when announcement is published for the first time
        if ($announcement->is_published && $announcement->wasChanged('is_published')) {
            $this->notificationService->createAnnouncementNotification($announcement);
        }
    }

    /**
     * Handle the Announcement "deleted" event.
     */
    public function deleted(Announcement $announcement): void
    {
        // Delete all notifications related to this announcement
        $deletedCount = $this->notificationService->deleteAnnouncementNotifications($announcement);
        
        // Log the deletion for debugging
        \Log::info("Deleted {$deletedCount} notifications for announcement ID: {$announcement->id}");
    }
}
