<?php

namespace App\Observers;

use App\Models\News;
use App\Services\NotificationService;

class NewsObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the News "created" event.
     */
    public function created(News $news): void
    {
        if (empty($news->share_token)) {
            try {
                $news->share_token = bin2hex(random_bytes(16));
                $news->saveQuietly();
            } catch (\Throwable $e) {
                \Log::error('Failed to generate share token for news', [
                    'news_id' => $news->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Create notifications when news is published
        if ($news->is_published) {
            $this->notificationService->createNewsNotification($news);
        }
    }

    /**
     * Handle the News "updated" event.
     */
    public function updated(News $news): void
    {
        // Create notifications when news is published for the first time
        if ($news->is_published && $news->wasChanged('is_published')) {
            $this->notificationService->createNewsNotification($news);
        }
    }

    /**
     * Handle the News "deleted" event.
     */
    public function deleted(News $news): void
    {
        // Delete all notifications related to this news
        $deletedCount = $this->notificationService->deleteNewsNotifications($news);
        
        // Log the deletion for debugging
        \Log::info("Deleted {$deletedCount} notifications for news ID: {$news->id}");
    }
}
