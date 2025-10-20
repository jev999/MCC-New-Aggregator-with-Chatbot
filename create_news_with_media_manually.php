<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== CREATING NEWS WITH MEDIA (SIMULATING MANUAL UPLOAD) ===\n\n";

// Simulate what should happen when superadmin uploads files
$sourceImages = glob(storage_path('app/public/announcement-images/*'));
$sourceVideos = glob(storage_path('app/public/announcement-videos/*'));

if (empty($sourceImages) || empty($sourceVideos)) {
    echo "❌ No source files available for testing\n";
    exit;
}

// Create news articles that simulate successful manual uploads
$testNews = [
    [
        'title' => 'Manual Upload Test - Image Only',
        'content' => 'This news article simulates what should happen when a superadmin successfully uploads an image through the web interface.',
        'media_type' => 'image'
    ],
    [
        'title' => 'Manual Upload Test - Video Only', 
        'content' => 'This news article simulates what should happen when a superadmin successfully uploads a video through the web interface.',
        'media_type' => 'video'
    ],
    [
        'title' => 'Manual Upload Test - Both Media',
        'content' => 'This news article simulates what should happen when a superadmin successfully uploads both image and video through the web interface.',
        'media_type' => 'both'
    ]
];

$newsImageDir = storage_path('app/public/news-images');
$newsVideoDir = storage_path('app/public/news-videos');

foreach ($testNews as $index => $data) {
    echo "Creating: {$data['title']}\n";
    
    $news = new News();
    $news->title = $data['title'];
    $news->content = $data['content'];
    $news->is_published = true;
    $news->admin_id = 1;
    
    // Add media based on type
    if ($data['media_type'] === 'image' || $data['media_type'] === 'both') {
        $sourceImage = $sourceImages[$index % count($sourceImages)];
        $imageName = 'manual_upload_test_' . time() . '_' . $index . '_' . basename($sourceImage);
        $targetImage = $newsImageDir . '/' . $imageName;
        
        if (copy($sourceImage, $targetImage)) {
            $news->image = 'news-images/' . $imageName;
            echo "  ✅ Added image: $imageName\n";
        }
    }
    
    if ($data['media_type'] === 'video' || $data['media_type'] === 'both') {
        $sourceVideo = $sourceVideos[$index % count($sourceVideos)];
        $videoName = 'manual_upload_test_' . time() . '_' . $index . '_' . basename($sourceVideo);
        $targetVideo = $newsVideoDir . '/' . $videoName;
        
        if (copy($sourceVideo, $targetVideo)) {
            $news->video = 'news-videos/' . $videoName;
            echo "  ✅ Added video: $videoName\n";
        }
    }
    
    $news->save();
    echo "  ✅ Created news ID: {$news->id}\n\n";
    
    // Small delay to ensure unique timestamps
    sleep(1);
}

echo "=== VERIFICATION ===\n\n";

// Show all news articles
$allNews = News::orderBy('created_at', 'desc')->get();

echo "ALL NEWS ARTICLES:\n";
foreach ($allNews as $news) {
    echo "ID {$news->id}: {$news->title}\n";
    
    $mediaTypes = [];
    if ($news->image) {
        $imagePath = storage_path('app/public/' . $news->image);
        $imageExists = file_exists($imagePath);
        $mediaTypes[] = '📷 Image ' . ($imageExists ? '✅' : '❌');
    }
    if ($news->video) {
        $videoPath = storage_path('app/public/' . $news->video);
        $videoExists = file_exists($videoPath);
        $mediaTypes[] = '🎥 Video ' . ($videoExists ? '✅' : '❌');
    }
    if ($news->csv_file) {
        $csvPath = storage_path('app/public/' . $news->csv_file);
        $csvExists = file_exists($csvPath);
        $mediaTypes[] = '📄 CSV ' . ($csvExists ? '✅' : '❌');
    }
    
    if (empty($mediaTypes)) {
        echo "  ❌ No media\n";
    } else {
        echo "  " . implode(', ', $mediaTypes) . "\n";
    }
    
    echo "  Created: {$news->created_at}\n";
    echo "  Published: " . ($news->is_published ? 'Yes' : 'No') . "\n\n";
}

$totalNews = News::count();
$newsWithMedia = News::where(function($query) {
    $query->whereNotNull('image')
          ->orWhereNotNull('video')
          ->orWhereNotNull('csv_file');
})->count();

echo "SUMMARY:\n";
echo "Total news articles: $totalNews\n";
echo "Articles with media: $newsWithMedia\n";
echo "Success rate: " . round(($newsWithMedia / $totalNews) * 100, 1) . "%\n\n";

echo "=== NEXT STEPS ===\n";
echo "1. Check user dashboard to see if all news articles display properly\n";
echo "2. Try creating a news article through the web interface with files\n";
echo "3. Check browser console for JavaScript errors during upload\n";
echo "4. Monitor Laravel logs during manual upload attempts\n";

echo "\n=== COMPLETE ===\n";
