<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;

echo "=== CHECKING ALL NEWS ARTICLES ===\n\n";

// Get ALL news articles including unpublished
$allNews = News::orderBy('id')->get();

echo "Total news articles in database: " . $allNews->count() . "\n\n";

foreach ($allNews as $news) {
    echo "--- News ID: {$news->id} ---\n";
    echo "Title: {$news->title}\n";
    echo "Published: " . ($news->is_published ? 'Yes' : 'No') . "\n";
    echo "Created: {$news->created_at}\n";
    echo "Admin ID: {$news->admin_id}\n";
    echo "Image: " . ($news->image ?? 'NULL') . "\n";
    echo "Video: " . ($news->video ?? 'NULL') . "\n";
    echo "CSV: " . ($news->csv_file ?? 'NULL') . "\n";
    echo "\n";
}

// Check published vs unpublished
$publishedCount = News::where('is_published', true)->count();
$unpublishedCount = News::where('is_published', false)->count();

echo "Published: $publishedCount\n";
echo "Unpublished: $unpublishedCount\n";

// Check which ones have media
$withMedia = News::where(function($query) {
    $query->whereNotNull('image')
          ->orWhereNotNull('video')
          ->orWhereNotNull('csv_file');
})->count();

$withoutMedia = News::where(function($query) {
    $query->whereNull('image')
          ->whereNull('video')
          ->whereNull('csv_file');
})->count();

echo "With media: $withMedia\n";
echo "Without media: $withoutMedia\n";
