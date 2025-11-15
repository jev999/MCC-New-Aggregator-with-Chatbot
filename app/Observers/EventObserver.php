<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\NotificationService;
use Illuminate\Support\Str;

class EventObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        // Ensure a share token exists for public sharing
        if (empty($event->share_token)) {
            $event->share_token = Str::random(48);
            $event->saveQuietly();
        }
        // Create notifications when event is published
        if ($event->is_published) {
            $this->notificationService->createEventNotification($event);
        }
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        // Create notifications when event is published for the first time
        if ($event->is_published && $event->wasChanged('is_published')) {
            $this->notificationService->createEventNotification($event);
        }
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        // Delete all notifications related to this event
        $deletedCount = $this->notificationService->deleteEventNotifications($event);
        
        // Log the deletion for debugging
        \Log::info("Deleted {$deletedCount} notifications for event ID: {$event->id}");
    }
}
