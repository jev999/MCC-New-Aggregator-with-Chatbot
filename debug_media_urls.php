<?php
/**
 * Debug script to test media URL generation
 * Run this from the command line: php debug_media_urls.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;

echo "=== Media URL Debug Test ===\n\n";

// Test Announcements
echo "Testing Announcements:\n";
$announcements = Announcement::where('is_published', true)
    ->whereNotNull('image_paths')
    ->orWhereNotNull('video_paths')
    ->orWhereNotNull('image_path')
    ->orWhereNotNull('video_path')
    ->limit(3)
    ->get();

foreach ($announcements as $announcement) {
    echo "Announcement ID: {$announcement->id}\n";
    echo "Title: {$announcement->title}\n";
    echo "Has Media: {$announcement->hasMedia}\n";
    echo "Media URL: " . ($announcement->mediaUrl ?? 'null') . "\n";
    echo "All Image URLs: " . json_encode($announcement->allImageUrls) . "\n";
    echo "All Video URLs: " . json_encode($announcement->allVideoUrls) . "\n";
    echo "---\n";
}

// Test Events
echo "\nTesting Events:\n";
$events = Event::where('is_published', true)
    ->whereNotNull('image_paths')
    ->orWhereNotNull('video_paths')
    ->orWhereNotNull('image')
    ->orWhereNotNull('video')
    ->limit(3)
    ->get();

foreach ($events as $event) {
    echo "Event ID: {$event->id}\n";
    echo "Title: {$event->title}\n";
    echo "Has Media: {$event->hasMedia}\n";
    echo "Media URL: " . ($event->mediaUrl ?? 'null') . "\n";
    echo "All Image URLs: " . json_encode($event->allImageUrls) . "\n";
    echo "All Video URLs: " . json_encode($event->allVideoUrls) . "\n";
    echo "---\n";
}

// Test News
echo "\nTesting News:\n";
$news = News::where('is_published', true)
    ->whereNotNull('image_paths')
    ->orWhereNotNull('video_paths')
    ->orWhereNotNull('image_path')
    ->orWhereNotNull('video_path')
    ->limit(3)
    ->get();

foreach ($news as $article) {
    echo "News ID: {$article->id}\n";
    echo "Title: {$article->title}\n";
    echo "Has Media: {$article->hasMedia}\n";
    echo "Media URL: " . ($article->mediaUrl ?? 'null') . "\n";
    echo "All Image URLs: " . json_encode($article->allImageUrls) . "\n";
    echo "All Video URLs: " . json_encode($article->allVideoUrls) . "\n";
    echo "---\n";
}

echo "\n=== Debug Complete ===\n";
