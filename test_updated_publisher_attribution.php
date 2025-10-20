<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;

echo "=== Testing Updated Publisher Attribution ===\n\n";

// Get all department admins
$departmentAdmins = Admin::where('role', 'department_admin')->get();

echo "Found {$departmentAdmins->count()} department admins\n\n";

foreach ($departmentAdmins as $admin) {
    echo "Testing with {$admin->username} ({$admin->department}):\n";
    echo "Department Display: '{$admin->department_display}'\n";
    
    // Test with a mock announcement for 'all departments'
    $testAnnouncement = new Announcement([
        'title' => 'Test All Departments Announcement',
        'content' => 'This is a test announcement for all departments',
        'visibility_scope' => 'all',
        'target_department' => null,
        'is_published' => true,
        'admin_id' => $admin->id
    ]);
    
    // Load the admin relationship
    $testAnnouncement->admin = $admin;
    
    echo "Publisher Attribution: '{$testAnnouncement->publisherInfo}'\n";
    echo "Expected: 'Posted by {$admin->department_display}'\n";
    
    if ($testAnnouncement->publisherInfo === "Posted by {$admin->department_display}") {
        echo "✅ SUCCESS: Full department name displayed correctly!\n";
    } else {
        echo "❌ ISSUE: Publisher attribution doesn't match expected format\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Test with department-specific content (should show username format)
echo "=== Testing Department-Specific Content ===\n";

$bsitAdmin = Admin::where('department', 'BSIT')->first();
if ($bsitAdmin) {
    $testDeptAnnouncement = new Announcement([
        'title' => 'Test BSIT Only Announcement',
        'content' => 'This is a test announcement for BSIT only',
        'visibility_scope' => 'department',
        'target_department' => 'BSIT',
        'is_published' => true,
        'admin_id' => $bsitAdmin->id
    ]);
    
    $testDeptAnnouncement->admin = $bsitAdmin;
    
    echo "BSIT Department-Only Content:\n";
    echo "Publisher Attribution: '{$testDeptAnnouncement->publisherInfo}'\n";
    echo "Expected format: 'Published by {$bsitAdmin->username} ({$bsitAdmin->department_display})'\n";
    
    $expectedFormat = "Published by {$bsitAdmin->username} ({$bsitAdmin->department_display})";
    if ($testDeptAnnouncement->publisherInfo === $expectedFormat) {
        echo "✅ SUCCESS: Department-specific content shows username format!\n";
    } else {
        echo "❌ ISSUE: Department-specific attribution doesn't match expected format\n";
    }
}

echo "\n=== Test Complete ===\n";
