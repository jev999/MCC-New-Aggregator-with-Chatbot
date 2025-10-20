<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;
use App\Models\User;
use App\Models\Admin;

echo "=== SIMPLE BSBA VISIBILITY TEST ===\n\n";

// Get BSBA department admin
$bsbaAdmin = Admin::where('role', 'department_admin')
    ->where('department', 'BSBA')
    ->first();

echo "BSBA Admin: " . $bsbaAdmin->username . " (Department: " . $bsbaAdmin->department . ")\n\n";

// Check BSBA admin announcements
echo "=== BSBA ADMIN ANNOUNCEMENTS ===\n";
$bsbaAnnouncements = Announcement::where('admin_id', $bsbaAdmin->id)->get();

echo "Found " . $bsbaAnnouncements->count() . " total announcements by BSBA admin\n";
foreach ($bsbaAnnouncements as $announcement) {
    echo "Announcement ID: " . $announcement->id . "\n";
    echo "  Title: " . $announcement->title . "\n";
    echo "  Published: " . ($announcement->is_published ? 'YES' : 'NO') . "\n";
    echo "  Visibility Scope: " . $announcement->visibility_scope . "\n";
    echo "  Target Department: " . $announcement->target_department . "\n\n";
}

// Test with a fake BSBA student
$fakeStudent = new User();
$fakeStudent->name = 'Test BSBA Student';
$fakeStudent->department = 'Bachelor of Science in Business Administration';
$fakeStudent->role = 'student';

echo "=== VISIBILITY TEST WITH FAKE BSBA STUDENT ===\n";
echo "Test Student Department: " . $fakeStudent->department . "\n\n";

foreach ($bsbaAnnouncements as $announcement) {
    if ($announcement->is_published) {
        echo "Testing announcement: " . $announcement->title . "\n";
        echo "  Visible to BSBA student: " . ($announcement->isVisibleToUser($fakeStudent) ? 'YES' : 'NO') . "\n\n";
    }
}

// Test user dashboard query
echo "=== USER DASHBOARD QUERY TEST ===\n";
$visibleAnnouncements = Announcement::where('is_published', true)
    ->visibleToUser($fakeStudent)
    ->with('admin')
    ->latest()
    ->get();

echo "Announcements visible to BSBA student: " . $visibleAnnouncements->count() . "\n";
foreach ($visibleAnnouncements as $announcement) {
    echo "  - " . $announcement->title . " (by " . $announcement->admin->username . " - " . $announcement->admin->role . ")\n";
    echo "    Visibility: " . $announcement->visibility_scope . " | Target: " . $announcement->target_department . "\n";
}
