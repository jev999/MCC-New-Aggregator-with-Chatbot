<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Creating Sample Notifications for Testing ===\n";

// Get admins
$superadmin = \App\Models\Admin::where('role', 'superadmin')->first();
$officeAdmin = \App\Models\Admin::where('role', 'office_admin')->first();
$departmentAdmin = \App\Models\Admin::where('role', 'department_admin')->first();

// Get users
$users = \App\Models\User::all();

if ($users->count() === 0) {
    echo "❌ No users found to create notifications for\n";
    exit;
}

echo "Found {$users->count()} users to notify\n";

// Create sample content from different admin types
$contentItems = [];

if ($superadmin) {
    echo "Creating content from Superadmin...\n";
    
    $announcement = \App\Models\Announcement::create([
        'title' => 'Important Campus Announcement',
        'content' => 'This is an important announcement from the MCC Administration regarding campus policies.',
        'admin_id' => $superadmin->id,
        'is_published' => true,
    ]);
    $contentItems[] = $announcement;
    
    $news = \App\Models\News::create([
        'title' => 'MCC Achieves Academic Excellence',
        'content' => 'MCC has been recognized for outstanding academic performance this semester.',
        'admin_id' => $superadmin->id,
        'is_published' => true,
    ]);
    $contentItems[] = $news;
}

if ($officeAdmin) {
    echo "Creating content from Office Admin...\n";
    
    $announcement = \App\Models\Announcement::create([
        'title' => 'Office Hours Update',
        'content' => 'Please note the updated office hours for student services.',
        'admin_id' => $officeAdmin->id,
        'is_published' => true,
    ]);
    $contentItems[] = $announcement;
    
    $event = \App\Models\Event::create([
        'title' => 'Student Registration Event',
        'description' => 'Annual student registration and orientation event.',
        'event_date' => now()->addDays(14)->toDateString(),
        'event_time' => '09:00:00',
        'location' => 'MCC Main Building',
        'admin_id' => $officeAdmin->id,
        'is_published' => true,
    ]);
    $contentItems[] = $event;
}

if ($departmentAdmin) {
    echo "Creating content from Department Admin...\n";
    
    $announcement = \App\Models\Announcement::create([
        'title' => 'Department Meeting Notice',
        'content' => 'All department members are invited to attend the monthly meeting.',
        'admin_id' => $departmentAdmin->id,
        'is_published' => true,
    ]);
    $contentItems[] = $announcement;
    
    $event = \App\Models\Event::create([
        'title' => 'Department Workshop',
        'description' => 'Professional development workshop for department staff.',
        'event_date' => now()->addDays(10)->toDateString(),
        'event_time' => '14:00:00',
        'location' => 'Department Conference Room',
        'admin_id' => $departmentAdmin->id,
        'is_published' => true,
    ]);
    $contentItems[] = $event;
}

echo "✅ Created " . count($contentItems) . " content items\n";

// Count total notifications created
$totalNotifications = 0;
foreach ($contentItems as $content) {
    $count = \App\Models\Notification::where('content_id', $content->id)
        ->where('content_type', get_class($content))
        ->count();
    $totalNotifications += $count;
}

echo "📬 Total notifications created: {$totalNotifications}\n";

// Show sample notifications for the first user
$firstUser = $users->first();
$userNotifications = \App\Models\Notification::where('user_id', $firstUser->id)
    ->with('admin')
    ->latest()
    ->take(5)
    ->get();

echo "\nSample notifications for user '{$firstUser->first_name} {$firstUser->surname}':\n";
foreach ($userNotifications as $notification) {
    echo "- {$notification->title}\n";
    echo "  From: " . ($notification->admin ? $notification->admin->username : 'Unknown') . "\n";
    echo "  Type: {$notification->type}\n";
    echo "  Read: " . ($notification->is_read ? 'Yes' : 'No') . "\n";
    echo "---\n";
}

echo "\n✅ Sample notifications created successfully!\n";
echo "\nNow you can:\n";
echo "1. Go to http://127.0.0.1:8000/login\n";
echo "2. Login as a student/faculty user\n";
echo "3. Check the notification bell in the dashboard\n";
echo "4. You should see notifications from superadmin, office admin, and department admin\n";

echo "\nTest user credentials:\n";
echo "- MS365 Account: test.student@mcclawis.edu.ph\n";
echo "- Password: student123\n";
