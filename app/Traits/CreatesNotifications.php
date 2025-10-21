<?php

namespace App\Traits;

use App\Services\NotificationService;

trait CreatesNotifications
{
    /**
     * Boot the trait and register model events.
     */
    protected static function bootCreatesNotifications()
    {
        // Create notifications when content is created and published
        static::created(function ($model) {
            if ($model->is_published && $model->admin_id) {
                $notificationService = app(NotificationService::class);
                $notificationService->createContentNotification($model, $model->getNotificationType());
            }
        });

        // Create notifications when content is updated to published
        static::updated(function ($model) {
            // Check if the content was just published (is_published changed from false to true)
            if ($model->is_published && $model->isDirty('is_published') && $model->getOriginal('is_published') === false) {
                if ($model->admin_id) {
                    $notificationService = app(NotificationService::class);
                    $notificationService->createContentNotification($model, $model->getNotificationType());
                }
            }
        });

        // Delete notifications when content is deleted
        static::deleted(function ($model) {
            $notificationService = app(NotificationService::class);
            $notificationService->deleteContentNotifications($model, $model->getNotificationType());
        });
    }

    /**
     * Get the notification type for this content.
     * This method should be implemented by the model using this trait.
     */
    abstract public function getNotificationType(): string;
}
