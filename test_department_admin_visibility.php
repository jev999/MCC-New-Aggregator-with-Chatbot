<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use App\Models\User;
use App\Models\Admin;

echo "=== TESTING DEPARTMENT ADMIN CONTENT VISIBILITY ===\n\n";

// Get a department admin
$departmentAdmin = Admin::where('role', 'department_admin')->first();
if (!$departmentAdmin) {
    echo "No department admin found in database\n";
    exit;
}

echo "Testing with department admin: " . $departmentAdmin->username . " (Department: " . $departmentAdmin->department . ")\n\n";

// Get a student from the same department
$user = User::where('department', 'Bachelor of Science in Information Technology')->first();
if (!$user) {
    echo "No BSIT student found in database\n";
    exit;
}

echo "Testing with student: " . $user->name . " (Department: " . $user->department . ")\n\n";

// Check announcements created by department admin
echo "=== DEPARTMENT ADMIN ANNOUNCEMENTS ===\n";
$deptAnnouncements = Announcement::where('admin_id', $departmentAdmin->id)
    ->where('is_published', true)
    ->get();

echo "Found " . $deptAnnouncements->count() . " published announcements by department admin\n";
foreach ($deptAnnouncements as $announcement) {
    echo "Announcement ID: " . $announcement->id . "\n";
    echo "  Title: " . $announcement->title . "\n";
    echo "  Visibility Scope: " . $announcement->visibility_scope . "\n";
    echo "  Target Department: " . $announcement->target_department . "\n";
    echo "  Visible to user: " . ($announcement->isVisibleToUser($user) ? 'YES' : 'NO') . "\n\n";
}

// Test visibility query (same as UserDashboardController)
echo "=== USER DASHBOARD QUERY RESULTS ===\n";
$visibleAnnouncements = Announcement::where('is_published', true)
    ->visibleToUser($user)
    ->with('admin')
    ->latest()
    ->get();

echo "Announcements visible to user: " . $visibleAnnouncements->count() . "\n";
foreach ($visibleAnnouncements as $announcement) {
    echo "  - " . $announcement->title . " (by " . $announcement->admin->username . " - " . $announcement->admin->role . ")\n";
}

// Check events created by department admin
echo "\n=== DEPARTMENT ADMIN EVENTS ===\n";
$deptEvents = Event::where('admin_id', $departmentAdmin->id)
    ->where('is_published', true)
    ->get();

echo "Found " . $deptEvents->count() . " published events by department admin\n";
foreach ($deptEvents as $event) {
    echo "Event ID: " . $event->id . "\n";
    echo "  Title: " . $event->title . "\n";
    echo "  Visibility Scope: " . $event->visibility_scope . "\n";
    echo "  Target Department: " . $event->target_department . "\n";
    echo "  Visible to user: " . ($event->isVisibleToUser($user) ? 'YES' : 'NO') . "\n\n";
}

$visibleEvents = Event::where('is_published', true)
    ->visibleToUser($user)
    ->with('admin')
    ->get();

echo "Events visible to user: " . $visibleEvents->count() . "\n";
foreach ($visibleEvents as $event) {
    echo "  - " . $event->title . " (by " . $event->admin->username . " - " . $event->admin->role . ")\n";
}

// Check news created by department admin
echo "\n=== DEPARTMENT ADMIN NEWS ===\n";
$deptNews = News::where('admin_id', $departmentAdmin->id)
    ->where('is_published', true)
    ->get();

echo "Found " . $deptNews->count() . " published news by department admin\n";
foreach ($deptNews as $news) {
    echo "News ID: " . $news->id . "\n";
    echo "  Title: " . $news->title . "\n";
    echo "  Visibility Scope: " . $news->visibility_scope . "\n";
    echo "  Target Department: " . $news->target_department . "\n";
    echo "  Visible to user: " . ($news->isVisibleToUser($user) ? 'YES' : 'NO') . "\n\n";
}

$visibleNews = News::where('is_published', true)
    ->visibleToUser($user)
    ->with('admin')
    ->get();

echo "News visible to user: " . $visibleNews->count() . "\n";
foreach ($visibleNews as $news) {
    echo "  - " . $news->title . " (by " . $news->admin->username . " - " . $news->admin->role . ")\n";
}
