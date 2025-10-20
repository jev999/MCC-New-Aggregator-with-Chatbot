<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== DEBUGGING MANUAL NEWS CREATION ISSUE ===\n\n";

// Get all news articles and analyze them
$allNews = News::orderBy('id')->get();

echo "Total news articles: " . $allNews->count() . "\n\n";

foreach ($allNews as $news) {
    echo "--- News ID: {$news->id} ---\n";
    echo "Title: {$news->title}\n";
    echo "Created: {$news->created_at}\n";
    echo "Admin ID: {$news->admin_id}\n";
    echo "Published: " . ($news->is_published ? 'Yes' : 'No') . "\n";
    
    // Check if this looks like a manually created article
    $isManual = !str_contains($news->title, 'Test') && !str_contains($news->title, 'Campus') && 
                !str_contains($news->title, 'Student') && !str_contains($news->title, 'Upcoming') && 
                !str_contains($news->title, 'Library');
    
    echo "Type: " . ($isManual ? 'MANUAL (by superadmin)' : 'GENERATED (by script)') . "\n";
    
    // Media information
    echo "Image: " . ($news->image ?? 'NULL') . "\n";
    echo "Video: " . ($news->video ?? 'NULL') . "\n";
    echo "CSV: " . ($news->csv_file ?? 'NULL') . "\n";
    
    // Check if files exist
    if ($news->image) {
        $imagePath = storage_path('app/public/' . $news->image);
        $imageExists = file_exists($imagePath);
        echo "Image file exists: " . ($imageExists ? 'Yes' : 'No') . "\n";
        if ($imageExists) {
            echo "Image size: " . number_format(filesize($imagePath) / 1024, 2) . " KB\n";
        }
    }
    
    if ($news->video) {
        $videoPath = storage_path('app/public/' . $news->video);
        $videoExists = file_exists($videoPath);
        echo "Video file exists: " . ($videoExists ? 'Yes' : 'No') . "\n";
        if ($videoExists) {
            echo "Video size: " . number_format(filesize($videoPath) / (1024 * 1024), 2) . " MB\n";
        }
    }
    
    echo "\n";
}

echo "=== CHECKING FOR ORPHANED FILES ===\n\n";

// Check for files that might belong to manual news but aren't linked
$newsImageDir = storage_path('app/public/news-images');
$newsVideoDir = storage_path('app/public/news-videos');

if (is_dir($newsImageDir)) {
    $imageFiles = array_diff(scandir($newsImageDir), ['.', '..']);
    echo "Files in news-images directory:\n";
    foreach ($imageFiles as $file) {
        echo "  - $file\n";
        
        // Check if this file is referenced in database
        $referenced = News::where('image', 'like', "%$file%")->exists();
        echo "    Referenced in database: " . ($referenced ? 'Yes' : 'No') . "\n";
        
        // Check if this might be a manual upload (not test or generated)
        $isManualFile = !str_contains($file, 'test_') && !str_contains($file, 'news_');
        if ($isManualFile && !$referenced) {
            echo "    ⚠️ POTENTIAL ORPHANED MANUAL UPLOAD\n";
        }
    }
    echo "\n";
}

if (is_dir($newsVideoDir)) {
    $videoFiles = array_diff(scandir($newsVideoDir), ['.', '..']);
    echo "Files in news-videos directory:\n";
    foreach ($videoFiles as $file) {
        echo "  - $file\n";
        
        // Check if this file is referenced in database
        $referenced = News::where('video', 'like', "%$file%")->exists();
        echo "    Referenced in database: " . ($referenced ? 'Yes' : 'No') . "\n";
        
        // Check if this might be a manual upload
        $isManualFile = !str_contains($file, 'test_') && !str_contains($file, 'news_');
        if ($isManualFile && !$referenced) {
            echo "    ⚠️ POTENTIAL ORPHANED MANUAL UPLOAD\n";
        }
    }
    echo "\n";
}

echo "=== CHECKING RECENT LARAVEL LOGS ===\n\n";

// Check recent log entries for news creation
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    
    // Look for recent news-related log entries
    $newsLogs = array_filter($lines, function($line) {
        return str_contains($line, 'News creation') || 
               str_contains($line, 'uploaded successfully') || 
               str_contains($line, 'upload failed');
    });
    
    if (!empty($newsLogs)) {
        echo "Recent news-related log entries:\n";
        foreach (array_slice($newsLogs, -10) as $log) {
            echo "  " . trim($log) . "\n";
        }
    } else {
        echo "No recent news-related log entries found.\n";
    }
} else {
    echo "Laravel log file not found.\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
