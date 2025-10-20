<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== DEBUGGING NEWS MEDIA ISSUE ===\n\n";

// Get all news articles
$allNews = News::orderBy('id')->get();

echo "Total news articles: " . $allNews->count() . "\n\n";

foreach ($allNews as $news) {
    echo "--- News ID: {$news->id} ---\n";
    echo "Title: {$news->title}\n";
    echo "Published: " . ($news->is_published ? 'Yes' : 'No') . "\n";
    echo "Admin ID: {$news->admin_id}\n";
    echo "Created: {$news->created_at}\n";
    echo "Image: " . ($news->image ?? 'NULL') . "\n";
    echo "Video: " . ($news->video ?? 'NULL') . "\n";
    echo "CSV: " . ($news->csv_file ?? 'NULL') . "\n";
    
    // Check if this is a manually created article (older IDs)
    if ($news->id <= 13) {
        echo "Type: MANUALLY CREATED BY SUPERADMIN\n";
        
        // Check if files were uploaded but paths are wrong
        if (!$news->image && !$news->video && !$news->csv_file) {
            echo "❌ NO MEDIA FILES - Superadmin didn't upload any files\n";
        }
    } else {
        echo "Type: TEST ARTICLE (Generated)\n";
        
        // Verify test files exist
        if ($news->image) {
            $imagePath = storage_path('app/public/' . $news->image);
            echo "Image exists: " . (file_exists($imagePath) ? 'Yes' : 'No') . "\n";
        }
        
        if ($news->video) {
            $videoPath = storage_path('app/public/' . $news->video);
            echo "Video exists: " . (file_exists($videoPath) ? 'Yes' : 'No') . "\n";
        }
    }
    
    echo "\n";
}

echo "=== CHECKING SUPERADMIN UPLOAD PROCESS ===\n\n";

// Check if there are any files in news directories that aren't linked to articles
$newsImageDir = storage_path('app/public/news-images');
$newsVideoDir = storage_path('app/public/news-videos');

if (is_dir($newsImageDir)) {
    $imageFiles = array_diff(scandir($newsImageDir), ['.', '..']);
    echo "Files in news-images directory: " . count($imageFiles) . "\n";
    foreach ($imageFiles as $file) {
        echo "  - $file\n";
        
        // Check if this file is referenced in any news article
        $referenced = News::where('image', 'like', "%$file%")->exists();
        echo "    Referenced in database: " . ($referenced ? 'Yes' : 'No') . "\n";
    }
}

if (is_dir($newsVideoDir)) {
    $videoFiles = array_diff(scandir($newsVideoDir), ['.', '..']);
    echo "\nFiles in news-videos directory: " . count($videoFiles) . "\n";
    foreach ($videoFiles as $file) {
        echo "  - $file\n";
        
        // Check if this file is referenced in any news article
        $referenced = News::where('video', 'like', "%$file%")->exists();
        echo "    Referenced in database: " . ($referenced ? 'Yes' : 'No') . "\n";
    }
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
