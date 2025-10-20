<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== ADDING MEDIA TO EXISTING NEWS ===\n\n";

// Get the "testinggg" article (ID 17)
$news = News::find(17);

if (!$news) {
    echo "❌ News article ID 17 not found\n";
    exit;
}

echo "Found news article: {$news->title}\n";

// Available source files
$announcementImages = glob(storage_path('app/public/announcement-images/*'));
$announcementVideos = glob(storage_path('app/public/announcement-videos/*'));

$newsImageDir = storage_path('app/public/news-images');
$newsVideoDir = storage_path('app/public/news-videos');

if (!empty($announcementImages)) {
    $sourceImage = $announcementImages[0]; // Use first available image
    $imageName = 'manual_news_' . $news->id . '_' . basename($sourceImage);
    $targetImage = $newsImageDir . '/' . $imageName;
    
    if (copy($sourceImage, $targetImage)) {
        $news->image = 'news-images/' . $imageName;
        echo "✅ Added image: $imageName\n";
    } else {
        echo "❌ Failed to copy image\n";
    }
}

if (!empty($announcementVideos)) {
    $sourceVideo = $announcementVideos[0]; // Use first available video
    $videoName = 'manual_news_' . $news->id . '_' . basename($sourceVideo);
    $targetVideo = $newsVideoDir . '/' . $videoName;
    
    if (copy($sourceVideo, $targetVideo)) {
        $news->video = 'news-videos/' . $videoName;
        echo "✅ Added video: $videoName\n";
    } else {
        echo "❌ Failed to copy video\n";
    }
}

// Save the changes
$news->save();
echo "✅ Updated news article in database\n\n";

// Verify the update
$updatedNews = News::find(17);
echo "=== VERIFICATION ===\n";
echo "Title: {$updatedNews->title}\n";
echo "Image: " . ($updatedNews->image ?? 'NULL') . "\n";
echo "Video: " . ($updatedNews->video ?? 'NULL') . "\n";

// Test file access
if ($updatedNews->image) {
    $imagePath = storage_path('app/public/' . $updatedNews->image);
    echo "Image file exists: " . (file_exists($imagePath) ? 'Yes' : 'No') . "\n";
}

if ($updatedNews->video) {
    $videoPath = storage_path('app/public/' . $updatedNews->video);
    echo "Video file exists: " . (file_exists($videoPath) ? 'Yes' : 'No') . "\n";
}

echo "\n=== COMPLETE ===\n";
echo "Now check the user dashboard to see if the manually created news shows media!\n";
