<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Announcement;

echo "Checking and fixing file paths...\n";

$announcements = Announcement::whereNotNull('image_path')
    ->orWhereNotNull('video_path')
    ->orWhereNotNull('csv_path')
    ->get();

echo "Found " . $announcements->count() . " announcements with media files\n";

foreach ($announcements as $announcement) {
    $updated = false;
    
    echo "\nAnnouncement ID: " . $announcement->id . "\n";
    echo "Title: " . $announcement->title . "\n";
    
    // Fix image path
    if ($announcement->image_path) {
        $oldPath = $announcement->image_path;
        $newPath = str_replace('\\', '/', $oldPath);
        if ($oldPath !== $newPath) {
            $announcement->image_path = $newPath;
            $updated = true;
            echo "Fixed image path: $oldPath -> $newPath\n";
        } else {
            echo "Image path OK: $newPath\n";
        }
    }
    
    // Fix video path
    if ($announcement->video_path) {
        $oldPath = $announcement->video_path;
        $newPath = str_replace('\\', '/', $oldPath);
        if ($oldPath !== $newPath) {
            $announcement->video_path = $newPath;
            $updated = true;
            echo "Fixed video path: $oldPath -> $newPath\n";
        } else {
            echo "Video path OK: $newPath\n";
        }
    }
    
    // Fix CSV path
    if ($announcement->csv_path) {
        $oldPath = $announcement->csv_path;
        $newPath = str_replace('\\', '/', $oldPath);
        if ($oldPath !== $newPath) {
            $announcement->csv_path = $newPath;
            $updated = true;
            echo "Fixed CSV path: $oldPath -> $newPath\n";
        } else {
            echo "CSV path OK: $newPath\n";
        }
    }
    
    if ($updated) {
        $announcement->save();
        echo "Updated announcement in database\n";
    }
}

echo "\nDone!\n";
