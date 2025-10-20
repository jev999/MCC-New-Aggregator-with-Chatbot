<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking recent announcements...\n";

try {
    $announcements = \App\Models\Announcement::orderBy('created_at', 'desc')->take(5)->get();
    
    echo "Found " . $announcements->count() . " recent announcements:\n";
    
    foreach ($announcements as $announcement) {
        echo "ID: " . $announcement->id . "\n";
        echo "Title: " . $announcement->title . "\n";
        echo "Created: " . $announcement->created_at . "\n";
        echo "Admin ID: " . $announcement->admin_id . "\n";
        echo "Published: " . ($announcement->is_published ? 'Yes' : 'No') . "\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
