<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;

// Find an event with media (based on the logs, event ID 59 has images)
$event = Event::find(59);

if (!$event) {
    echo "Event not found!\n";
    exit;
}

echo "=== Event Media Debug ===\n";
echo "Event ID: {$event->id}\n";
echo "Event Title: {$event->title}\n\n";

echo "Raw Database Values:\n";
echo "image: " . ($event->image ?? 'null') . "\n";
echo "video: " . ($event->video ?? 'null') . "\n";
echo "image_paths: " . ($event->image_paths ? json_encode($event->image_paths) : 'null') . "\n";
echo "video_paths: " . ($event->video_paths ? json_encode($event->video_paths) : 'null') . "\n\n";

echo "Accessor Methods:\n";
try {
    $allImagePaths = $event->allImagePaths;
    echo "allImagePaths: " . json_encode($allImagePaths) . "\n";
    echo "allImagePaths count: " . (is_array($allImagePaths) ? count($allImagePaths) : 'not array') . "\n";
} catch (Exception $e) {
    echo "Error getting allImagePaths: " . $e->getMessage() . "\n";
}

try {
    $allVideoPaths = $event->allVideoPaths;
    echo "allVideoPaths: " . json_encode($allVideoPaths) . "\n";
    echo "allVideoPaths count: " . (is_array($allVideoPaths) ? count($allVideoPaths) : 'not array') . "\n";
} catch (Exception $e) {
    echo "Error getting allVideoPaths: " . $e->getMessage() . "\n";
}

echo "\nBlade Template Conditions:\n";
echo "allImagePaths exists: " . ($event->allImagePaths ? 'true' : 'false') . "\n";
echo "count(allImagePaths) > 0: " . (count($event->allImagePaths ?? []) > 0 ? 'true' : 'false') . "\n";
echo "allVideoPaths exists: " . ($event->allVideoPaths ? 'true' : 'false') . "\n";
echo "count(allVideoPaths) > 0: " . (count($event->allVideoPaths ?? []) > 0 ? 'true' : 'false') . "\n";

echo "\nCheckbox Generation Test:\n";
if ($event->allImagePaths && count($event->allImagePaths) > 0) {
    echo "Image checkboxes would be generated:\n";
    foreach ($event->allImagePaths as $index => $imagePath) {
        echo "  Checkbox: name='remove_images[]' value='{$index}' id='remove_image_{$index}'\n";
    }
} else {
    echo "No image checkboxes would be generated\n";
}

if ($event->allVideoPaths && count($event->allVideoPaths) > 0) {
    echo "Video checkboxes would be generated:\n";
    foreach ($event->allVideoPaths as $index => $videoPath) {
        echo "  Checkbox: name='remove_videos[]' value='{$index}' id='remove_video_{$index}'\n";
    }
} else {
    echo "No video checkboxes would be generated\n";
}

echo "\n=== End Debug ===\n";
