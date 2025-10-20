<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== FIXING NEWS MEDIA ISSUE ===\n\n";

// Get manually created news articles (those without media)
$manualNews = News::whereNull('image')
    ->whereNull('video')
    ->whereNull('csv_file')
    ->where('id', '<=', 13) // Manually created ones
    ->get();

echo "Found " . $manualNews->count() . " manually created news articles without media\n\n";

// Available source files
$announcementImages = glob(storage_path('app/public/announcement-images/*'));
$announcementVideos = glob(storage_path('app/public/announcement-videos/*'));

$newsImageDir = storage_path('app/public/news-images');
$newsVideoDir = storage_path('app/public/news-videos');

// Ensure directories exist
if (!is_dir($newsImageDir)) {
    mkdir($newsImageDir, 0755, true);
    echo "✅ Created news-images directory\n";
}

if (!is_dir($newsVideoDir)) {
    mkdir($newsVideoDir, 0755, true);
    echo "✅ Created news-videos directory\n";
}

foreach ($manualNews as $index => $news) {
    echo "--- Processing News ID: {$news->id} ---\n";
    echo "Title: {$news->title}\n";
    
    $updated = false;
    
    // Add an image if available
    if (!empty($announcementImages) && $index < count($announcementImages)) {
        $sourceImage = $announcementImages[$index];
        $imageName = 'news_' . $news->id . '_' . basename($sourceImage);
        $targetImage = $newsImageDir . '/' . $imageName;
        
        if (copy($sourceImage, $targetImage)) {
            $news->image = 'news-images/' . $imageName;
            echo "✅ Added image: $imageName\n";
            $updated = true;
        } else {
            echo "❌ Failed to copy image\n";
        }
    }
    
    // Add a video if available
    if (!empty($announcementVideos) && $index < count($announcementVideos)) {
        $sourceVideo = $announcementVideos[$index];
        $videoName = 'news_' . $news->id . '_' . basename($sourceVideo);
        $targetVideo = $newsVideoDir . '/' . $videoName;
        
        if (copy($sourceVideo, $targetVideo)) {
            $news->video = 'news-videos/' . $videoName;
            echo "✅ Added video: $videoName\n";
            $updated = true;
        } else {
            echo "❌ Failed to copy video\n";
        }
    }
    
    if ($updated) {
        $news->save();
        echo "✅ Updated news article in database\n";
    } else {
        echo "⚠️ No media files added\n";
    }
    
    echo "\n";
}

echo "=== VERIFYING RESULTS ===\n\n";

// Check all news articles now
$allNews = News::orderBy('id')->get();

foreach ($allNews as $news) {
    echo "News ID {$news->id}: ";
    
    $mediaCount = 0;
    if ($news->image) {
        echo "📷 Image ";
        $mediaCount++;
    }
    if ($news->video) {
        echo "🎥 Video ";
        $mediaCount++;
    }
    if ($news->csv_file) {
        echo "📄 CSV ";
        $mediaCount++;
    }
    
    if ($mediaCount === 0) {
        echo "❌ No media";
    } else {
        echo "✅ {$mediaCount} media file(s)";
    }
    
    echo " | Published: " . ($news->is_published ? 'Yes' : 'No') . "\n";
}

echo "\n=== TESTING FILE ACCESS ===\n\n";

// Test that all media files are accessible
$newsWithMedia = News::where(function($query) {
    $query->whereNotNull('image')
          ->orWhereNotNull('video')
          ->orWhereNotNull('csv_file');
})->get();

foreach ($newsWithMedia as $news) {
    echo "Testing News ID {$news->id}:\n";
    
    if ($news->image) {
        $imagePath = storage_path('app/public/' . $news->image);
        $imageExists = file_exists($imagePath);
        echo "  Image: " . ($imageExists ? '✅ Exists' : '❌ Missing') . " - {$news->image}\n";
    }
    
    if ($news->video) {
        $videoPath = storage_path('app/public/' . $news->video);
        $videoExists = file_exists($videoPath);
        echo "  Video: " . ($videoExists ? '✅ Exists' : '❌ Missing') . " - {$news->video}\n";
    }
    
    if ($news->csv_file) {
        $csvPath = storage_path('app/public/' . $news->csv_file);
        $csvExists = file_exists($csvPath);
        echo "  CSV: " . ($csvExists ? '✅ Exists' : '❌ Missing') . " - {$news->csv_file}\n";
    }
    
    echo "\n";
}

echo "=== FIX COMPLETE ===\n";
echo "Now test the user dashboard to see if news media displays properly!\n";
