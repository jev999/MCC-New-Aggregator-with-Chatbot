<?php
/**
 * Media URL Test for mcc-nac.com
 * Run this to test if media URLs are working correctly
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;

echo "=== MCC-NAC Media URL Test ===\n\n";

// Test configuration
echo "Current APP_URL: " . config('app.url') . "\n";
echo "Expected: https://mcc-nac.com\n\n";

// Test Storage URL generation
echo "Testing Storage URL generation:\n";
$testPath = 'announcement-images/test.jpg';
$storageUrl = \Storage::disk('public')->url($testPath);
echo "Storage::disk('public')->url('$testPath'): $storageUrl\n";
echo "Should start with: https://mcc-nac.com/storage/\n\n";

// Test recent announcements with media
echo "Testing recent announcements with media:\n";
$announcements = Announcement::where('is_published', true)
    ->where(function($query) {
        $query->whereNotNull('image_paths')
              ->orWhereNotNull('video_paths')
              ->orWhereNotNull('image_path')
              ->orWhereNotNull('video_path');
    })
    ->latest()
    ->limit(3)
    ->get();

if ($announcements->count() > 0) {
    foreach ($announcements as $announcement) {
        echo "Announcement: {$announcement->title}\n";
        echo "  Has Media: {$announcement->hasMedia}\n";
        
        if ($announcement->mediaUrl) {
            echo "  Media URL: {$announcement->mediaUrl}\n";
            
            // Check if URL starts with correct domain
            if (strpos($announcement->mediaUrl, 'https://mcc-nac.com') === 0) {
                echo "  ✅ URL format correct\n";
            } else {
                echo "  ❌ URL format incorrect - should start with https://mcc-nac.com\n";
            }
        }
        
        if (!empty($announcement->allImageUrls)) {
            echo "  Image URLs:\n";
            foreach ($announcement->allImageUrls as $index => $url) {
                echo "    " . ($index + 1) . ". $url\n";
            }
        }
        
        if (!empty($announcement->allVideoUrls)) {
            echo "  Video URLs:\n";
            foreach ($announcement->allVideoUrls as $index => $url) {
                echo "    " . ($index + 1) . ". $url\n";
            }
        }
        
        echo "  ---\n";
    }
} else {
    echo "No announcements with media found.\n";
    echo "Create a test announcement with images/videos to test.\n";
}

// Check storage directories
echo "\nChecking storage directories:\n";
$directories = ['announcement-images', 'announcement-videos', 'event-images', 'event-videos'];

foreach ($directories as $dir) {
    if (\Storage::disk('public')->exists($dir)) {
        $files = \Storage::disk('public')->files($dir);
        echo "$dir: " . count($files) . " files\n";
        
        if (count($files) > 0) {
            $sampleFile = $files[0];
            $url = \Storage::disk('public')->url($sampleFile);
            echo "  Sample URL: $url\n";
        }
    } else {
        echo "$dir: Directory doesn't exist\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "If URLs don't start with https://mcc-nac.com, run:\n";
echo "1. php fix_production_urls.php\n";
echo "2. php artisan config:clear\n";
echo "3. php artisan cache:clear\n";
