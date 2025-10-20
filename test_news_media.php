<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== NEWS MEDIA DIAGNOSTIC ===\n\n";

echo "1. Checking news with media...\n";

$newsWithMedia = News::where(function($query) {
    $query->whereNotNull('image')
          ->orWhereNotNull('video')
          ->orWhereNotNull('csv_file');
})->get();

echo "Found " . $newsWithMedia->count() . " news articles with media files\n\n";

foreach ($newsWithMedia as $news) {
    echo "--- News ID: {$news->id} ---\n";
    echo "Title: {$news->title}\n";
    echo "Published: " . ($news->is_published ? 'Yes' : 'No') . "\n";
    echo "Admin ID: {$news->admin_id}\n";
    
    // Check image
    if ($news->image) {
        echo "\n📷 IMAGE:\n";
        echo "  Database path: {$news->image}\n";
        
        $storagePath = storage_path('app/public/' . $news->image);
        $publicPath = public_path('storage/' . $news->image);
        $assetUrl = asset('storage/' . $news->image);
        
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
    if ($news->video) {
        echo "\n🎥 VIDEO:\n";
        echo "  Database path: {$news->video}\n";
        
        $storagePath = storage_path('app/public/' . $news->video);
        $publicPath = public_path('storage/' . $news->video);
        $assetUrl = asset('storage/' . $news->video);
        
        echo "  Storage path: $storagePath\n";
        echo "  Public path: $publicPath\n";
        echo "  Asset URL: $assetUrl\n";
        
        if (file_exists($storagePath)) {
            echo "  ✅ File exists in storage\n";
            echo "  Size: " . formatBytes(filesize($storagePath)) . "\n";
            
            // Check video format
            $extension = pathinfo($news->video, PATHINFO_EXTENSION);
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
    if ($news->csv_file) {
        echo "\n📄 CSV:\n";
        echo "  Database path: {$news->csv_file}\n";
        
        $storagePath = storage_path('app/public/' . $news->csv_file);
        $publicPath = public_path('storage/' . $news->csv_file);
        
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

echo "2. Checking news storage directories...\n";
$directories = [
    'news-images',
    'news-videos', 
    'news-csv'
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

echo "\n3. Checking published news for user dashboard...\n";
$publishedNews = News::where('is_published', true)->orderBy('created_at', 'desc')->limit(5)->get();
echo "Published news articles: " . $publishedNews->count() . "\n";

foreach ($publishedNews as $news) {
    echo "- {$news->title}";
    if ($news->image) echo " [IMAGE]";
    if ($news->video) echo " [VIDEO]";
    if ($news->csv_file) echo " [CSV]";
    echo "\n";
}

echo "\n=== NEWS MEDIA DIAGNOSTIC COMPLETE ===\n";

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
