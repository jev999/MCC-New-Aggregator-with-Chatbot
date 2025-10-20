<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== All Students Debug ===\n\n";

// Get all students
$students = User::where('role', 'student')->get();

echo "Total students found: {$students->count()}\n\n";

if ($students->count() > 0) {
    echo "Student details:\n";
    foreach ($students as $student) {
        echo "- Name: {$student->name}\n";
        echo "  Email: {$student->email}\n";
        echo "  Department: '{$student->department}'\n";
        echo "  Year Level: '{$student->year_level}'\n";
        echo "  Role: {$student->role}\n";
        echo "  Created: {$student->created_at}\n";
        echo "\n";
    }
    
    // Group by department
    echo "Students by department:\n";
    $byDepartment = $students->groupBy('department');
    foreach ($byDepartment as $dept => $deptStudents) {
        $deptName = $dept ?: 'NULL/Empty';
        echo "- {$deptName}: {$deptStudents->count()} students\n";
    }
} else {
    echo "No students found in the database!\n";
}

echo "\n=== Debug Complete ===\n";
