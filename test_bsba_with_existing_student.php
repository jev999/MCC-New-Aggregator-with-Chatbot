<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;
use App\Models\User;
use App\Models\Admin;

echo "=== TESTING BSBA VISIBILITY WITH EXISTING STUDENT ===\n\n";

// Get BSBA department admin
$bsbaAdmin = Admin::where('role', 'department_admin')
    ->where('department', 'BSBA')
    ->first();

echo "BSBA Admin: " . $bsbaAdmin->username . " (Department: " . $bsbaAdmin->department . ")\n\n";

// Use an existing student and temporarily test with BSBA department
$testStudent = User::first();
echo "Test Student: " . $testStudent->name . " (Original Department: " . $testStudent->department . ")\n\n";

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
    
    // Test visibility with different department scenarios
    echo "  Visibility Tests:\n";
    
    // Test 1: If student was BSBA (full name)
    $testStudent->department = 'Bachelor of Science in Business Administration';
    echo "    - If student dept = 'Bachelor of Science in Business Administration': " . ($announcement->isVisibleToUser($testStudent) ? 'YES' : 'NO') . "\n";
    
    // Test 2: If student was BSBA (abbreviated)
    $testStudent->department = 'BSBA';
    echo "    - If student dept = 'BSBA': " . ($announcement->isVisibleToUser($testStudent) ? 'YES' : 'NO') . "\n";
    
    // Test 3: Check normalization logic
    $normalizedTarget = null;
    if (method_exists($announcement, 'normalizeDepartmentName')) {
        $normalizedTarget = $announcement->normalizeDepartmentName($announcement->target_department);
    }
    echo "    - Target department normalized: '" . $normalizedTarget . "'\n";
    
    echo "\n";
}

// Reset student department
$testStudent->department = 'Bachelor of Science in Information Technology';

// Check if there's a normalization issue
echo "=== DEPARTMENT NORMALIZATION TEST ===\n";
$testAnnouncement = new Announcement();

$departments = [
    'Bachelor of Science in Business Administration',
    'BSBA',
    'Bachelor of Science in Information Technology',
    'BSIT'
];

foreach ($departments as $dept) {
    $normalized = $testAnnouncement->normalizeDepartmentName($dept);
    echo "'" . $dept . "' -> '" . $normalized . "'\n";
}
