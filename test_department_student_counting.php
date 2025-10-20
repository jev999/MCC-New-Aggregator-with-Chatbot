<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\User;

echo "=== Testing Department Student Counting ===\n\n";

// Get all department admins
$departmentAdmins = Admin::where('role', 'department_admin')->get();

echo "Found {$departmentAdmins->count()} department admins\n\n";

foreach ($departmentAdmins as $admin) {
    echo "Testing {$admin->username} ({$admin->department}):\n";
    
    // Department name mapping
    $departmentMap = [
        'BSIT' => 'Bachelor of Science in Information Technology',
        'BSBA' => 'Bachelor of Science in Business Administration',
        'BEED' => 'Bachelor of Elementary Education',
        'BSHM' => 'Bachelor of Science in Hospitality Management',
        'BSED' => 'Bachelor of Secondary Education',
    ];
    
    $fullDepartmentName = $departmentMap[$admin->department] ?? $admin->department;
    echo "Full department name: '{$fullDepartmentName}'\n";
    
    // Test the old counting method (should show 0 for most departments)
    $oldCount = User::where('role', 'student')
                   ->where('department', $admin->department)
                   ->count();
    
    // Test the new counting method (should include both abbreviated and full names)
    $newCount = User::where('role', 'student')
                   ->where(function($query) use ($admin, $fullDepartmentName) {
                       $query->where('department', $admin->department)
                             ->orWhere('department', $fullDepartmentName);
                   })
                   ->count();
    
    echo "Old counting method (abbreviated only): {$oldCount} students\n";
    echo "New counting method (both formats): {$newCount} students\n";
    
    // Show actual students found
    $students = User::where('role', 'student')
                   ->where(function($query) use ($admin, $fullDepartmentName) {
                       $query->where('department', $admin->department)
                             ->orWhere('department', $fullDepartmentName);
                   })
                   ->get();
    
    if ($students->count() > 0) {
        echo "Students found:\n";
        foreach ($students as $student) {
            echo "  - {$student->name} (Department: '{$student->department}')\n";
        }
    } else {
        echo "No students found for this department\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Test faculty counting as well
echo "=== Testing Faculty Counting ===\n";

$bsitAdmin = Admin::where('department', 'BSIT')->first();
if ($bsitAdmin) {
    $departmentMap = [
        'BSIT' => 'Bachelor of Science in Information Technology',
        'BSBA' => 'Bachelor of Science in Business Administration',
        'BEED' => 'Bachelor of Elementary Education',
        'BSHM' => 'Bachelor of Science in Hospitality Management',
        'BSED' => 'Bachelor of Secondary Education',
    ];
    
    $fullDepartmentName = $departmentMap[$bsitAdmin->department] ?? $bsitAdmin->department;
    
    $facultyCount = User::where('role', 'faculty')
                       ->where(function($query) use ($bsitAdmin, $fullDepartmentName) {
                           $query->where('department', $bsitAdmin->department)
                                 ->orWhere('department', $fullDepartmentName);
                       })
                       ->count();
    
    echo "BSIT Faculty count: {$facultyCount}\n";
    
    $faculty = User::where('role', 'faculty')
                  ->where(function($query) use ($bsitAdmin, $fullDepartmentName) {
                      $query->where('department', $bsitAdmin->department)
                            ->orWhere('department', $fullDepartmentName);
                  })
                  ->get();
    
    if ($faculty->count() > 0) {
        echo "Faculty found:\n";
        foreach ($faculty as $facultyMember) {
            echo "  - {$facultyMember->name} (Department: '{$facultyMember->department}')\n";
        }
    } else {
        echo "No faculty found for BSIT department\n";
    }
}

echo "\n=== Test Complete ===\n";
