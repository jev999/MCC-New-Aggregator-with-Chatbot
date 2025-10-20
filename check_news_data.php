<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== NEWS DATA CHECK ===\n\n";

// Check all news
$allNews = News::all();
echo "Total news articles: " . $allNews->count() . "\n";

// Check published news
$publishedNews = News::where('is_published', true)->get();
echo "Published news articles: " . $publishedNews->count() . "\n\n";

// Check news with media
$newsWithMedia = News::where('is_published', true)
    ->where(function($query) {
        $query->whereNotNull('image')
              ->orWhereNotNull('video')
              ->orWhereNotNull('csv_file');
    })->get();

echo "Published news with media: " . $newsWithMedia->count() . "\n\n";

// Show details of each published news
foreach ($publishedNews as $news) {
    echo "--- News ID: {$news->id} ---\n";
    echo "Title: {$news->title}\n";
    echo "Published: " . ($news->is_published ? 'Yes' : 'No') . "\n";
    echo "Admin ID: " . ($news->admin_id ?? 'NULL') . "\n";
    echo "Image: " . ($news->image ?? 'NULL') . "\n";
    echo "Video: " . ($news->video ?? 'NULL') . "\n";
    echo "CSV: " . ($news->csv_file ?? 'NULL') . "\n";
    echo "Created: " . $news->created_at . "\n";
    
    // Check if files exist
    if ($news->image) {
        $imagePath = storage_path('app/public/' . $news->image);
        echo "Image file exists: " . (file_exists($imagePath) ? 'Yes' : 'No') . "\n";
        if (file_exists($imagePath)) {
            echo "Image size: " . formatBytes(filesize($imagePath)) . "\n";
        }
    }
    
    if ($news->video) {
        $videoPath = storage_path('app/public/' . $news->video);
        echo "Video file exists: " . (file_exists($videoPath) ? 'Yes' : 'No') . "\n";
        if (file_exists($videoPath)) {
            echo "Video size: " . formatBytes(filesize($videoPath)) . "\n";
        }
    }
    
    echo "\n";
}

echo "=== END CHECK ===\n";

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
