<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;
use App\Models\Admin;
use App\Models\User;
use App\Models\Notification;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the notification system by creating a test announcement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing notification system...');

        // Get the first admin (or create one if none exists)
        $admin = Admin::first();
        if (!$admin) {
            $this->error('No admin found. Please create an admin first.');
            return;
        }

        // Get user count
        $userCount = User::count();
        $this->info("Found {$userCount} users in the system.");

        if ($userCount === 0) {
            $this->error('No users found. Please create some users first.');
            return;
        }

        // Create a test announcement
        $announcement = Announcement::create([
            'title' => 'Test Notification Announcement',
            'content' => 'This is a test announcement to verify that the notification system is working correctly.',
            'is_published' => true,
            'admin_id' => $admin->id,
        ]);

        $this->info("Created test announcement: {$announcement->title}");

        // Check if notifications were created
        $notificationCount = Notification::where('content_id', $announcement->id)
            ->where('content_type', 'App\Models\Announcement')
            ->count();

        $this->info("Created {$notificationCount} notifications for the announcement.");

        if ($notificationCount === $userCount) {
            $this->info('✅ Notification system is working correctly!');
        } else {
            $this->error('❌ Notification system may not be working correctly.');
            $this->error("Expected {$userCount} notifications, but got {$notificationCount}.");
        }

        // Show some sample notifications
        $sampleNotifications = Notification::where('content_id', $announcement->id)
            ->where('content_type', 'App\Models\Announcement')
            ->with('user')
            ->take(3)
            ->get();

        if ($sampleNotifications->count() > 0) {
            $this->info("\nSample notifications created:");
            foreach ($sampleNotifications as $notification) {
                $this->line("- User: {$notification->user->first_name} {$notification->user->surname}");
                $this->line("  Title: {$notification->title}");
                $this->line("  Message: {$notification->message}");
                $this->line("");
            }
        }

        return 0;
    }
}
