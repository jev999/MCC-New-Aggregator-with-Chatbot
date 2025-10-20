<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking announcements table structure:\n";
echo "=====================================\n";

try {
    // Get table columns
    $columns = Schema::getColumnListing('announcements');
    echo "Columns in announcements table:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\nChecking if required columns exist:\n";
    $requiredColumns = ['image_paths', 'video_paths', 'visibility_scope', 'target_department', 'target_office'];
    foreach ($requiredColumns as $column) {
        $exists = Schema::hasColumn('announcements', $column);
        echo "- $column: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    echo "\nTesting announcement creation:\n";
    
    // Try to create a test announcement
    $testData = [
        'title' => 'Test Announcement',
        'content' => 'Test content',
        'admin_id' => 1,
        'image_paths' => json_encode(['test/path.jpg']),
        'video_paths' => null,
        'visibility_scope' => 'all',
        'target_department' => null,
        'target_office' => null,
        'is_published' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    DB::table('announcements')->insert($testData);
    echo "✓ Test announcement created successfully\n";
    
    // Clean up
    DB::table('announcements')->where('title', 'Test Announcement')->delete();
    echo "✓ Test announcement deleted\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
