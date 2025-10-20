<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

echo "=== TESTING SUPERADMIN FILE UPLOAD SIMULATION ===\n\n";

// Simulate what happens when superadmin uploads files through the web interface
$sourceImages = glob(storage_path('app/public/announcement-images/*'));
$sourceVideos = glob(storage_path('app/public/announcement-videos/*'));

if (empty($sourceImages) || empty($sourceVideos)) {
    echo "❌ No source files available for testing\n";
    exit;
}

// Create a test file upload simulation
$sourceImage = $sourceImages[0];
$sourceVideo = $sourceVideos[0];

echo "Source files:\n";
echo "  Image: " . basename($sourceImage) . " (" . number_format(filesize($sourceImage) / 1024, 2) . " KB)\n";
echo "  Video: " . basename($sourceVideo) . " (" . number_format(filesize($sourceVideo) / (1024 * 1024), 2) . " MB)\n\n";

// Simulate the Laravel file upload process
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

// Simulate file upload with Laravel naming convention
$imageFileName = 'superadmin_test_' . time() . '_' . uniqid() . '.' . pathinfo($sourceImage, PATHINFO_EXTENSION);
$videoFileName = 'superadmin_test_' . time() . '_' . uniqid() . '.' . pathinfo($sourceVideo, PATHINFO_EXTENSION);

$imageTargetPath = $newsImageDir . '/' . $imageFileName;
$videoTargetPath = $newsVideoDir . '/' . $videoFileName;

echo "Simulating file upload...\n";

// Copy files to simulate upload
if (copy($sourceImage, $imageTargetPath)) {
    echo "✅ Image uploaded successfully: $imageFileName\n";
} else {
    echo "❌ Image upload failed\n";
    exit;
}

if (copy($sourceVideo, $videoTargetPath)) {
    echo "✅ Video uploaded successfully: $videoFileName\n";
} else {
    echo "❌ Video upload failed\n";
    exit;
}

// Create news article with uploaded files
$news = News::create([
    'title' => 'Superadmin Upload Test - ' . date('Y-m-d H:i:s'),
    'content' => 'This news article was created to test the superadmin file upload functionality. It should display both image and video properly on the user dashboard.',
    'image' => 'news-images/' . $imageFileName,
    'video' => 'news-videos/' . $videoFileName,
    'csv_file' => null,
    'is_published' => true,
    'admin_id' => 1
]);

echo "✅ News article created with ID: {$news->id}\n\n";

// Verify the files are accessible
$imageWebPath = asset('storage/news-images/' . $imageFileName);
$videoWebPath = asset('storage/news-videos/' . $videoFileName);

echo "Web accessibility test:\n";
echo "  Image URL: $imageWebPath\n";
echo "  Video URL: $videoWebPath\n";

// Test file existence
$imageExists = file_exists($imageTargetPath);
$videoExists = file_exists($videoTargetPath);

echo "  Image file exists: " . ($imageExists ? '✅ Yes' : '❌ No') . "\n";
echo "  Video file exists: " . ($videoExists ? '✅ Yes' : '❌ No') . "\n";

if ($imageExists) {
    echo "  Image size: " . number_format(filesize($imageTargetPath) / 1024, 2) . " KB\n";
}

if ($videoExists) {
    echo "  Video size: " . number_format(filesize($videoTargetPath) / (1024 * 1024), 2) . " MB\n";
}

echo "\n=== VERIFICATION ===\n\n";

// Check all news articles
$allNews = News::orderBy('created_at', 'desc')->get();

echo "ALL NEWS ARTICLES:\n";
foreach ($allNews as $article) {
    echo "ID {$article->id}: {$article->title}\n";
    
    $mediaCount = 0;
    if ($article->image) {
        $imagePath = storage_path('app/public/' . $article->image);
        $imageExists = file_exists($imagePath);
        echo "  📷 Image: " . ($imageExists ? '✅' : '❌') . " {$article->image}\n";
        $mediaCount++;
    }
    
    if ($article->video) {
        $videoPath = storage_path('app/public/' . $article->video);
        $videoExists = file_exists($videoPath);
        echo "  🎥 Video: " . ($videoExists ? '✅' : '❌') . " {$article->video}\n";
        $mediaCount++;
    }
    
    if ($article->csv_file) {
        $csvPath = storage_path('app/public/' . $article->csv_file);
        $csvExists = file_exists($csvPath);
        echo "  📄 CSV: " . ($csvExists ? '✅' : '❌') . " {$article->csv_file}\n";
        $mediaCount++;
    }
    
    if ($mediaCount === 0) {
        echo "  ❌ No media files\n";
    }
    
    echo "  Published: " . ($article->is_published ? 'Yes' : 'No') . "\n";
    echo "  Created: {$article->created_at}\n\n";
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
echo "1. Check user dashboard: http://127.0.0.1:8000/user/dashboard\n";
echo "2. Verify the new article displays with image and video\n";
echo "3. Test video playback functionality\n";
echo "4. Try creating a news article manually through superadmin panel\n\n";

echo "=== COMPLETE ===\n";
