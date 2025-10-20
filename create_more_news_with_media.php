<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== CREATING MORE NEWS WITH MEDIA ===\n\n";

// Available source files
$announcementImages = glob(storage_path('app/public/announcement-images/*'));
$announcementVideos = glob(storage_path('app/public/announcement-videos/*'));

$newsImageDir = storage_path('app/public/news-images');
$newsVideoDir = storage_path('app/public/news-videos');

// Create news articles with different media combinations
$newsData = [
    [
        'title' => 'Campus Technology Update',
        'content' => 'We are excited to announce new technology upgrades across the campus. These improvements will enhance the learning experience for all students and provide better access to digital resources.',
        'media_type' => 'image'
    ],
    [
        'title' => 'Student Achievement Recognition',
        'content' => 'Congratulations to our outstanding students who have achieved excellence in their academic pursuits. Their dedication and hard work continue to inspire the entire campus community.',
        'media_type' => 'video'
    ],
    [
        'title' => 'Upcoming Academic Events',
        'content' => 'Mark your calendars for the exciting academic events coming up this semester. From seminars to workshops, there are many opportunities for learning and growth.',
        'media_type' => 'both'
    ],
    [
        'title' => 'Library Services Enhancement',
        'content' => 'Our library services have been expanded to better serve the academic needs of our students and faculty. New resources and extended hours are now available.',
        'media_type' => 'image'
    ]
];

foreach ($newsData as $index => $data) {
    echo "Creating: {$data['title']}\n";
    
    $news = new News();
    $news->title = $data['title'];
    $news->content = $data['content'];
    $news->is_published = true;
    $news->admin_id = 1;
    
    // Add media based on type
    if ($data['media_type'] === 'image' || $data['media_type'] === 'both') {
        if (!empty($announcementImages) && isset($announcementImages[$index % count($announcementImages)])) {
            $sourceImage = $announcementImages[$index % count($announcementImages)];
            $imageName = 'news_' . time() . '_' . $index . '_' . basename($sourceImage);
            $targetImage = $newsImageDir . '/' . $imageName;
            
            if (copy($sourceImage, $targetImage)) {
                $news->image = 'news-images/' . $imageName;
                echo "  ✅ Added image: $imageName\n";
            }
        }
    }
    
    if ($data['media_type'] === 'video' || $data['media_type'] === 'both') {
        if (!empty($announcementVideos) && isset($announcementVideos[$index % count($announcementVideos)])) {
            $sourceVideo = $announcementVideos[$index % count($announcementVideos)];
            $videoName = 'news_' . time() . '_' . $index . '_' . basename($sourceVideo);
            $targetVideo = $newsVideoDir . '/' . $videoName;
            
            if (copy($sourceVideo, $targetVideo)) {
                $news->video = 'news-videos/' . $videoName;
                echo "  ✅ Added video: $videoName\n";
            }
        }
    }
    
    $news->save();
    echo "  ✅ Created news ID: {$news->id}\n\n";
}

echo "=== FINAL STATUS ===\n\n";

// Show all news articles
$allNews = News::orderBy('created_at', 'desc')->get();

foreach ($allNews as $news) {
    echo "ID {$news->id}: {$news->title}\n";
    
    $mediaTypes = [];
    if ($news->image) $mediaTypes[] = '📷 Image';
    if ($news->video) $mediaTypes[] = '🎥 Video';
    if ($news->csv_file) $mediaTypes[] = '📄 CSV';
    
    if (empty($mediaTypes)) {
        echo "  ❌ No media\n";
    } else {
        echo "  ✅ " . implode(', ', $mediaTypes) . "\n";
    }
}

$totalNews = News::count();
$newsWithMedia = News::where(function($query) {
    $query->whereNotNull('image')
          ->orWhereNotNull('video')
          ->orWhereNotNull('csv_file');
})->count();

echo "\nTotal news articles: $totalNews\n";
echo "Articles with media: $newsWithMedia\n";
echo "Success rate: " . round(($newsWithMedia / $totalNews) * 100, 1) . "%\n";

echo "\n=== COMPLETE ===\n";
echo "Check the user dashboard to see all news articles with media!\n";
