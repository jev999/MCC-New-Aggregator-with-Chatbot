<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Announcement;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing announcement creation...\n";

try {
    // Test basic announcement creation
    $announcement = Announcement::create([
        'title' => 'Test Announcement',
        'content' => 'Test content for debugging',
        'admin_id' => 1,
        'is_published' => true,
        'visibility_scope' => 'all'
    ]);
    
    echo "SUCCESS! Created announcement ID: " . $announcement->id . "\n";
    echo "Title: " . $announcement->title . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
