<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Announcement;

echo "=== MEDIA FILES DIAGNOSTIC ===\n\n";

// Check storage link
echo "1. Checking storage link...\n";
$storageLink = public_path('storage');
$storageTarget = storage_path('app/public');

if (is_link($storageLink)) {
    echo "✅ Storage link exists: $storageLink\n";
    echo "   Points to: " . readlink($storageLink) . "\n";
} else {
    echo "❌ Storage link missing or broken\n";
}

if (is_dir($storageLink)) {
    echo "✅ Storage directory accessible\n";
} else {
    echo "❌ Storage directory not accessible\n";
}

echo "\n2. Checking announcements with media...\n";

$announcements = Announcement::where(function($query) {
    $query->whereNotNull('image_path')
          ->orWhereNotNull('video_path')
          ->orWhereNotNull('csv_path');
})->get();

echo "Found " . $announcements->count() . " announcements with media files\n\n";

foreach ($announcements as $announcement) {
    echo "--- Announcement ID: {$announcement->id} ---\n";
    echo "Title: {$announcement->title}\n";
    echo "Published: " . ($announcement->is_published ? 'Yes' : 'No') . "\n";
    
    // Check image
    if ($announcement->image_path) {
        echo "\n📷 IMAGE:\n";
        echo "  Database path: {$announcement->image_path}\n";
        
        $storagePath = storage_path('app/public/' . $announcement->image_path);
        $publicPath = public_path('storage/' . $announcement->image_path);
        $assetUrl = asset('storage/' . $announcement->image_path);
        
        echo "  Storage path: $storagePath\n";
        echo "  Public path: $publicPath\n";
        echo "  Asset URL: $assetUrl\n";
        
        if (file_exists($storagePath)) {
            echo "  ✅ File exists in storage\n";
            echo "  Size: " . formatBytes(filesize($storagePath)) . "\n";
        } else {
            echo "  ❌ File missing in storage\n";
        }
        
        if (file_exists($publicPath)) {
            echo "  ✅ File accessible via public link\n";
        } else {
            echo "  ❌ File not accessible via public link\n";
        }
    }
    
    // Check video
    if ($announcement->video_path) {
        echo "\n🎥 VIDEO:\n";
        echo "  Database path: {$announcement->video_path}\n";
        
        $storagePath = storage_path('app/public/' . $announcement->video_path);
        $publicPath = public_path('storage/' . $announcement->video_path);
        $assetUrl = asset('storage/' . $announcement->video_path);
        
        echo "  Storage path: $storagePath\n";
        echo "  Public path: $publicPath\n";
        echo "  Asset URL: $assetUrl\n";
        
        if (file_exists($storagePath)) {
            echo "  ✅ File exists in storage\n";
            echo "  Size: " . formatBytes(filesize($storagePath)) . "\n";
            
            // Check video format
            $extension = pathinfo($announcement->video_path, PATHINFO_EXTENSION);
            echo "  Format: $extension\n";
            
            $mimeType = match(strtolower($extension)) {
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
                'wmv' => 'video/x-ms-wmv',
                'flv' => 'video/x-flv',
                default => 'video/mp4'
            };
            echo "  MIME type: $mimeType\n";
        } else {
            echo "  ❌ File missing in storage\n";
        }
        
        if (file_exists($publicPath)) {
            echo "  ✅ File accessible via public link\n";
        } else {
            echo "  ❌ File not accessible via public link\n";
        }
    }
    
    // Check CSV
    if ($announcement->csv_path) {
        echo "\n📄 CSV:\n";
        echo "  Database path: {$announcement->csv_path}\n";
        
        $storagePath = storage_path('app/public/' . $announcement->csv_path);
        $publicPath = public_path('storage/' . $announcement->csv_path);
        
        if (file_exists($storagePath)) {
            echo "  ✅ File exists in storage\n";
            echo "  Size: " . formatBytes(filesize($storagePath)) . "\n";
        } else {
            echo "  ❌ File missing in storage\n";
        }
        
        if (file_exists($publicPath)) {
            echo "  ✅ File accessible via public link\n";
        } else {
            echo "  ❌ File not accessible via public link\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "3. Checking storage directories...\n";
$directories = [
    'announcement-images',
    'announcement-videos', 
    'announcement-csv'
];

foreach ($directories as $dir) {
    $path = storage_path("app/public/$dir");
    if (is_dir($path)) {
        $files = glob($path . '/*');
        echo "✅ $dir: " . count($files) . " files\n";
    } else {
        echo "❌ $dir: Directory missing\n";
    }
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
