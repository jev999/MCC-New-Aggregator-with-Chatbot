<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use App\Models\News;

echo "=== DEBUGGING MEDIA DISPLAY ISSUE ===\n\n";

echo "Latest Event:\n";
$event = Event::latest()->first();
if ($event) {
    echo "ID: " . $event->id . "\n";
    echo "Title: " . $event->title . "\n";
    echo "Image Paths (raw): " . var_export($event->getAttributes()['image_paths'] ?? null, true) . "\n";
    echo "Video Paths (raw): " . var_export($event->getAttributes()['video_paths'] ?? null, true) . "\n";
    echo "Image Paths (cast): " . json_encode($event->image_paths) . "\n";
    echo "Video Paths (cast): " . json_encode($event->video_paths) . "\n";
    echo "All Image URLs: " . json_encode($event->allImageUrls) . "\n";
    echo "All Video URLs: " . json_encode($event->allVideoUrls) . "\n";
    echo "Has Media: " . $event->hasMedia . "\n";
} else {
    echo "No events found\n";
}

echo "\n\nLatest News:\n";
$news = News::latest()->first();
if ($news) {
    echo "ID: " . $news->id . "\n";
    echo "Title: " . $news->title . "\n";
    echo "Image Paths (raw): " . var_export($news->getAttributes()['image_paths'] ?? null, true) . "\n";
    echo "Video Paths (raw): " . var_export($news->getAttributes()['video_paths'] ?? null, true) . "\n";
    echo "Image Paths (cast): " . json_encode($news->image_paths) . "\n";
    echo "Video Paths (cast): " . json_encode($news->video_paths) . "\n";
    echo "All Image URLs: " . json_encode($news->allImageUrls) . "\n";
    echo "All Video URLs: " . json_encode($news->allVideoUrls) . "\n";
    echo "Has Media: " . $news->hasMedia . "\n";
} else {
    echo "No news found\n";
}
