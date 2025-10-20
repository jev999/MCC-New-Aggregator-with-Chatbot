<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;
use App\Models\User;
use App\Models\Admin;

echo "=== DEBUGGING BSBA DEPARTMENT ADMIN ANNOUNCEMENTS ===\n\n";

// Get BSBA department admin
$bsbaAdmin = Admin::where('role', 'department_admin')
    ->where('department', 'BSBA')
    ->first();

if (!$bsbaAdmin) {
    echo "No BSBA department admin found. Checking all department admins:\n";
    $allDeptAdmins = Admin::where('role', 'department_admin')->get();
    foreach ($allDeptAdmins as $admin) {
        echo "  - " . $admin->username . " (Department: " . $admin->department . ")\n";
    }
    exit;
}

echo "Found BSBA admin: " . $bsbaAdmin->username . " (Department: " . $bsbaAdmin->department . ")\n\n";

// Get BSBA student
$bsbaStudent = User::where('department', 'Bachelor of Science in Business Administration')->first();
if (!$bsbaStudent) {
    echo "No BSBA student found. Checking all student departments:\n";
    $allStudents = User::select('department')->distinct()->get();
    foreach ($allStudents as $student) {
        echo "  - " . $student->department . "\n";
    }
    exit;
}

echo "Found BSBA student: " . $bsbaStudent->name . " (Department: " . $bsbaStudent->department . ")\n\n";

// Check BSBA admin announcements
echo "=== BSBA ADMIN ANNOUNCEMENTS ===\n";
$bsbaAnnouncements = Announcement::where('admin_id', $bsbaAdmin->id)->get();

echo "Found " . $bsbaAnnouncements->count() . " total announcements by BSBA admin\n";
foreach ($bsbaAnnouncements as $announcement) {
    echo "Announcement ID: " . $announcement->id . "\n";
    echo "  Title: " . $announcement->title . "\n";
    echo "  Published: " . ($announcement->is_published ? 'YES' : 'NO') . "\n";
    echo "  Visibility Scope: " . $announcement->visibility_scope . "\n";
    echo "  Target Department: " . $announcement->target_department . "\n";
    echo "  Created: " . $announcement->created_at . "\n";
    echo "  Visible to BSBA student: " . ($announcement->isVisibleToUser($bsbaStudent) ? 'YES' : 'NO') . "\n\n";
}

// Test department name normalization
echo "=== DEPARTMENT NAME NORMALIZATION TEST ===\n";
$testAnnouncement = new Announcement();
$normalizedStudentDept = $testAnnouncement->normalizeDepartmentName($bsbaStudent->department);
$normalizedAdminDept = $testAnnouncement->normalizeDepartmentName($bsbaAdmin->department);

echo "Student department: '" . $bsbaStudent->department . "' -> normalized: '" . $normalizedStudentDept . "'\n";
echo "Admin department: '" . $bsbaAdmin->department . "' -> normalized: '" . $normalizedAdminDept . "'\n";
echo "Match: " . ($normalizedStudentDept === $normalizedAdminDept ? 'YES' : 'NO') . "\n\n";

// Test user dashboard query
echo "=== USER DASHBOARD QUERY FOR BSBA STUDENT ===\n";
$visibleAnnouncements = Announcement::where('is_published', true)
    ->visibleToUser($bsbaStudent)
    ->with('admin')
    ->latest()
    ->get();

echo "Announcements visible to BSBA student: " . $visibleAnnouncements->count() . "\n";
foreach ($visibleAnnouncements as $announcement) {
    echo "  - " . $announcement->title . " (by " . $announcement->admin->username . " - " . $announcement->admin->role . ")\n";
    echo "    Visibility: " . $announcement->visibility_scope . " | Target: " . $announcement->target_department . "\n";
}
