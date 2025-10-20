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

echo "=== BSIT Department Visibility Debug ===\n\n";

// 1. Check BSIT department admin
echo "1. BSIT Department Admin:\n";
$bsitAdmin = Admin::where('role', 'department_admin')
    ->where('department', 'BSIT')
    ->first();

if ($bsitAdmin) {
    echo "   Found BSIT Admin: {$bsitAdmin->username}\n";
    echo "   Department: {$bsitAdmin->department}\n";
    echo "   Role: {$bsitAdmin->role}\n";
} else {
    echo "   No BSIT department admin found!\n";
}

echo "\n";

// 2. Check BSIT students
echo "2. BSIT Students:\n";
$bsitStudents = User::where('role', 'student')
    ->where('department', 'BSIT')
    ->get();

echo "   Found {$bsitStudents->count()} BSIT students\n";
if ($bsitStudents->count() > 0) {
    $firstStudent = $bsitStudents->first();
    echo "   Sample student: {$firstStudent->name}\n";
    echo "   Department: '{$firstStudent->department}'\n";
    echo "   Role: {$firstStudent->role}\n";
}

echo "\n";

// 3. Check content published by BSIT admin
if ($bsitAdmin) {
    echo "3. Content published by BSIT Admin:\n";
    
    // Announcements
    $announcements = Announcement::where('admin_id', $bsitAdmin->id)
        ->where('is_published', true)
        ->get();
    
    echo "   Announcements: {$announcements->count()}\n";
    foreach ($announcements as $announcement) {
        echo "     - '{$announcement->title}'\n";
        echo "       Visibility: {$announcement->visibility_scope}\n";
        echo "       Target Dept: {$announcement->target_department}\n";
        echo "       Published: " . ($announcement->is_published ? 'Yes' : 'No') . "\n";
    }
    
    // Events
    $events = Event::where('admin_id', $bsitAdmin->id)
        ->where('is_published', true)
        ->get();
    
    echo "   Events: {$events->count()}\n";
    foreach ($events as $event) {
        echo "     - '{$event->title}'\n";
        echo "       Visibility: {$event->visibility_scope}\n";
        echo "       Target Dept: {$event->target_department}\n";
        echo "       Published: " . ($event->is_published ? 'Yes' : 'No') . "\n";
    }
    
    // News
    $news = News::where('admin_id', $bsitAdmin->id)
        ->where('is_published', true)
        ->get();
    
    echo "   News: {$news->count()}\n";
    foreach ($news as $newsItem) {
        echo "     - '{$newsItem->title}'\n";
        echo "       Visibility: {$newsItem->visibility_scope}\n";
        echo "       Target Dept: {$newsItem->target_department}\n";
        echo "       Published: " . ($newsItem->is_published ? 'Yes' : 'No') . "\n";
    }
}

echo "\n";

// 4. Test visibility for BSIT student
if ($bsitStudents->count() > 0 && $bsitAdmin) {
    $testStudent = $bsitStudents->first();
    echo "4. Testing visibility for BSIT student '{$testStudent->name}':\n";
    
    // Test announcements
    $visibleAnnouncements = Announcement::where('is_published', true)
        ->visibleToUser($testStudent)
        ->count();
    
    $departmentAnnouncements = Announcement::where('is_published', true)
        ->where('visibility_scope', 'department')
        ->where('target_department', 'BSIT')
        ->count();
    
    echo "   Total visible announcements: {$visibleAnnouncements}\n";
    echo "   BSIT department announcements: {$departmentAnnouncements}\n";
    
    // Test events
    $visibleEvents = Event::where('is_published', true)
        ->visibleToUser($testStudent)
        ->count();
    
    $departmentEvents = Event::where('is_published', true)
        ->where('visibility_scope', 'department')
        ->where('target_department', 'BSIT')
        ->count();
    
    echo "   Total visible events: {$visibleEvents}\n";
    echo "   BSIT department events: {$departmentEvents}\n";
    
    // Test news
    $visibleNews = News::where('is_published', true)
        ->visibleToUser($testStudent)
        ->count();
    
    $departmentNews = News::where('is_published', true)
        ->where('visibility_scope', 'department')
        ->where('target_department', 'BSIT')
        ->count();
    
    echo "   Total visible news: {$visibleNews}\n";
    echo "   BSIT department news: {$departmentNews}\n";
}

echo "\n";

// 5. Check all department-specific content
echo "5. All department-specific content:\n";
$deptAnnouncements = Announcement::where('visibility_scope', 'department')
    ->where('is_published', true)
    ->get(['title', 'target_department', 'admin_id']);

echo "   Department Announcements:\n";
foreach ($deptAnnouncements as $ann) {
    $admin = Admin::find($ann->admin_id);
    echo "     - '{$ann->title}' -> {$ann->target_department} (by " . ($admin ? $admin->username : 'Unknown') . ")\n";
}

$deptEvents = Event::where('visibility_scope', 'department')
    ->where('is_published', true)
    ->get(['title', 'target_department', 'admin_id']);

echo "   Department Events:\n";
foreach ($deptEvents as $event) {
    $admin = Admin::find($event->admin_id);
    echo "     - '{$event->title}' -> {$event->target_department} (by " . ($admin ? $admin->username : 'Unknown') . ")\n";
}

$deptNews = News::where('visibility_scope', 'department')
    ->where('is_published', true)
    ->get(['title', 'target_department', 'admin_id']);

echo "   Department News:\n";
foreach ($deptNews as $newsItem) {
    $admin = Admin::find($newsItem->admin_id);
    echo "     - '{$newsItem->title}' -> {$newsItem->target_department} (by " . ($admin ? $admin->username : 'Unknown') . ")\n";
}

echo "\n=== Debug Complete ===\n";
