<?php

namespace App\Observers;

use App\Models\Admin;
use App\Services\NotificationService;

class AdminObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Admin "deleted" event.
     */
    public function deleted(Admin $admin): void
    {
        // Delete all notifications created by this admin
        $deletedCount = $this->notificationService->deleteAdminNotifications($admin);
        
        // Log the deletion for debugging
        \Log::info("Deleted {$deletedCount} notifications for admin ID: {$admin->id} (Username: {$admin->username})");
    }
}
