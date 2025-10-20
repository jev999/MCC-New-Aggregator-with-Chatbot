<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\User;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;

echo "=== Testing 'All Departments' Posting Feature ===\n\n";

// Get BSIT admin
$bsitAdmin = Admin::where('role', 'department_admin')
    ->where('department', 'BSIT')
    ->first();

if (!$bsitAdmin) {
    echo "No BSIT admin found!\n";
    exit;
}

echo "Testing with BSIT Admin: {$bsitAdmin->username}\n";
echo "Admin department: {$bsitAdmin->department}\n\n";

// Check existing content posted by BSIT admin
echo "=== Current Content by BSIT Admin ===\n";

$announcements = Announcement::where('admin_id', $bsitAdmin->id)->get();
echo "Announcements by BSIT Admin:\n";
foreach ($announcements as $announcement) {
    echo "  - '{$announcement->title}'\n";
    echo "    Visibility: {$announcement->visibility_scope}\n";
    echo "    Target Dept: " . ($announcement->target_department ?: 'N/A') . "\n";
    echo "    Publisher Info: '{$announcement->publisherInfo}'\n";
    echo "    Expected for 'all': 'Posted by BSIT Department'\n";
    echo "\n";
}

$events = Event::where('admin_id', $bsitAdmin->id)->get();
echo "Events by BSIT Admin:\n";
foreach ($events as $event) {
    echo "  - '{$event->title}'\n";
    echo "    Visibility: {$event->visibility_scope}\n";
    echo "    Target Dept: " . ($event->target_department ?: 'N/A') . "\n";
    echo "    Publisher Info: '{$event->publisherInfo}'\n";
    echo "    Expected for 'all': 'Posted by BSIT Department'\n";
    echo "\n";
}

$news = News::where('admin_id', $bsitAdmin->id)->get();
echo "News by BSIT Admin:\n";
foreach ($news as $newsItem) {
    echo "  - '{$newsItem->title}'\n";
    echo "    Visibility: {$newsItem->visibility_scope}\n";
    echo "    Target Dept: " . ($newsItem->target_department ?: 'N/A') . "\n";
    echo "    Publisher Info: '{$newsItem->publisherInfo}'\n";
    echo "    Expected for 'all': 'Posted by BSIT Department'\n";
    echo "\n";
}

// Test creating a sample announcement with 'all' visibility
echo "=== Testing New 'All Departments' Content ===\n";

try {
    $testAnnouncement = new Announcement([
        'title' => 'Test All Departments Announcement',
        'content' => 'This is a test announcement for all departments',
        'visibility_scope' => 'all',
        'target_department' => null,
        'is_published' => true,
        'admin_id' => $bsitAdmin->id
    ]);
    
    // Load the admin relationship
    $testAnnouncement->admin = $bsitAdmin;
    
    echo "Test announcement publisher info: '{$testAnnouncement->publisherInfo}'\n";
    echo "Expected: 'Posted by BSIT Department'\n";
    
    if (strpos($testAnnouncement->publisherInfo, 'Posted by BSIT Department') !== false) {
        echo "✅ SUCCESS: Publisher info shows department attribution!\n";
    } else {
        echo "❌ ISSUE: Publisher info doesn't show expected department attribution\n";
    }
    
} catch (Exception $e) {
    echo "Error creating test announcement: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
