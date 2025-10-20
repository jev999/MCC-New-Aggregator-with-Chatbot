<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;

echo "=== Testing Fixed Department Visibility ===\n\n";

// Get the actual BSIT student (with full department name)
$bsitStudent = User::where('role', 'student')
    ->where('department', 'Bachelor of Science in Information Technology')
    ->first();

if (!$bsitStudent) {
    echo "No BSIT student found!\n";
    exit;
}

echo "Testing with student: {$bsitStudent->name}\n";
echo "Student department: '{$bsitStudent->department}'\n\n";

// Test visibility with the fixed models
echo "=== Testing Visibility After Fix ===\n";

// Test announcements
$visibleAnnouncements = Announcement::where('is_published', true)
    ->visibleToUser($bsitStudent)
    ->get();

echo "Visible announcements: {$visibleAnnouncements->count()}\n";
foreach ($visibleAnnouncements as $announcement) {
    echo "  - '{$announcement->title}' (scope: {$announcement->visibility_scope}, target: {$announcement->target_department})\n";
}

// Test events
$visibleEvents = Event::where('is_published', true)
    ->visibleToUser($bsitStudent)
    ->get();

echo "Visible events: {$visibleEvents->count()}\n";
foreach ($visibleEvents as $event) {
    echo "  - '{$event->title}' (scope: {$event->visibility_scope}, target: {$event->target_department})\n";
}

// Test news
$visibleNews = News::where('is_published', true)
    ->visibleToUser($bsitStudent)
    ->get();

echo "Visible news: {$visibleNews->count()}\n";
foreach ($visibleNews as $news) {
    echo "  - '{$news->title}' (scope: {$news->visibility_scope}, target: {$news->target_department})\n";
}

echo "\n=== Specific BSIT Department Content Test ===\n";

// Check specifically for BSIT department content
$bsitAnnouncements = Announcement::where('is_published', true)
    ->where('visibility_scope', 'department')
    ->where('target_department', 'BSIT')
    ->get();

echo "BSIT department announcements in DB: {$bsitAnnouncements->count()}\n";

// Test if each BSIT announcement is visible to the student
foreach ($bsitAnnouncements as $announcement) {
    $isVisible = $announcement->isVisibleToUser($bsitStudent);
    echo "  - '{$announcement->title}' -> " . ($isVisible ? 'VISIBLE' : 'NOT VISIBLE') . "\n";
}

echo "\n=== Test Complete ===\n";
