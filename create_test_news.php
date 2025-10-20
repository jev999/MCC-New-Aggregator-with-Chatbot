<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== CREATING TEST NEWS WITH MEDIA ===\n\n";

// Check if we have any announcement images we can copy for testing
$announcementImagePath = storage_path('app/public/announcement-images');
$newsImagePath = storage_path('app/public/news-images');

if (is_dir($announcementImagePath)) {
    $announcementImages = glob($announcementImagePath . '/*');
    if (!empty($announcementImages)) {
        $sourceImage = $announcementImages[0];
        $imageName = 'test_' . basename($sourceImage);
        $targetImage = $newsImagePath . '/' . $imageName;
        
        if (copy($sourceImage, $targetImage)) {
            echo "✅ Copied test image: $imageName\n";
            
            // Create news article with image
            $news = News::create([
                'title' => 'Test News Article with Image',
                'content' => 'This is a test news article created to verify that images display properly on the user dashboard. The image should be visible and clear.',
                'image' => 'news-images/' . $imageName,
                'video' => null,
                'csv_file' => null,
                'is_published' => true,
                'admin_id' => 1 // Assuming superadmin ID is 1
            ]);
            
            echo "✅ Created test news article with ID: {$news->id}\n";
        } else {
            echo "❌ Failed to copy test image\n";
        }
    } else {
        echo "❌ No announcement images found to copy\n";
    }
} else {
    echo "❌ Announcement images directory not found\n";
}

// Check if we have any announcement videos we can copy for testing
$announcementVideoPath = storage_path('app/public/announcement-videos');
$newsVideoPath = storage_path('app/public/news-videos');

if (is_dir($announcementVideoPath)) {
    $announcementVideos = glob($announcementVideoPath . '/*');
    if (!empty($announcementVideos)) {
        $sourceVideo = $announcementVideos[0];
        $videoName = 'test_' . basename($sourceVideo);
        $targetVideo = $newsVideoPath . '/' . $videoName;
        
        if (copy($sourceVideo, $targetVideo)) {
            echo "✅ Copied test video: $videoName\n";
            
            // Create news article with video
            $news = News::create([
                'title' => 'Test News Article with Video',
                'content' => 'This is a test news article created to verify that videos display and play properly on the user dashboard. The video should be playable with controls.',
                'image' => null,
                'video' => 'news-videos/' . $videoName,
                'csv_file' => null,
                'is_published' => true,
                'admin_id' => 1 // Assuming superadmin ID is 1
            ]);
            
            echo "✅ Created test news article with ID: {$news->id}\n";
        } else {
            echo "❌ Failed to copy test video\n";
        }
    } else {
        echo "❌ No announcement videos found to copy\n";
    }
} else {
    echo "❌ Announcement videos directory not found\n";
}

// Create a news article with both image and video
if (!empty($announcementImages) && !empty($announcementVideos)) {
    $sourceImage2 = $announcementImages[0];
    $imageName2 = 'test_combined_' . basename($sourceImage2);
    $targetImage2 = $newsImagePath . '/' . $imageName2;
    
    $sourceVideo2 = $announcementVideos[0];
    $videoName2 = 'test_combined_' . basename($sourceVideo2);
    $targetVideo2 = $newsVideoPath . '/' . $videoName2;
    
    if (copy($sourceImage2, $targetImage2) && copy($sourceVideo2, $targetVideo2)) {
        echo "✅ Copied combined media files\n";
        
        $news = News::create([
            'title' => 'Test News Article with Image and Video',
            'content' => 'This is a comprehensive test news article with both image and video to verify that all media types display properly on the user dashboard.',
            'image' => 'news-images/' . $imageName2,
            'video' => 'news-videos/' . $videoName2,
            'csv_file' => null,
            'is_published' => true,
            'admin_id' => 1
        ]);
        
        echo "✅ Created combined media news article with ID: {$news->id}\n";
    }
}

echo "\n=== TEST NEWS CREATION COMPLETE ===\n";

// Show current published news count
$publishedCount = News::where('is_published', true)->count();
echo "Total published news articles: $publishedCount\n";

$withMediaCount = News::where('is_published', true)
    ->where(function($query) {
        $query->whereNotNull('image')
              ->orWhereNotNull('video')
              ->orWhereNotNull('csv_file');
    })->count();
echo "Published news with media: $withMediaCount\n";
