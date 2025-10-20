<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use App\Models\News;
use App\Models\User;

echo "=== DEBUGGING USER DASHBOARD DATA ===\n\n";

// Get a test user
$user = User::first();
if (!$user) {
    echo "No users found in database\n";
    exit;
}

echo "Testing with user: " . $user->name . " (Department: " . $user->department . ")\n\n";

// Test events query (same as UserDashboardController)
echo "=== EVENTS QUERY ===\n";
$events = Event::where('is_published', true)
    ->visibleToUser($user)
    ->with('admin')
    ->where(function($query) {
        $query->where('event_date', '>=', now()->subDays(90))
              ->orWhereNull('event_date');
    })
    ->orderByRaw('CASE WHEN event_date IS NULL THEN 1 ELSE 0 END')
    ->orderBy('event_date', 'asc')
    ->get();

echo "Found " . $events->count() . " events\n";
foreach ($events as $event) {
    echo "Event ID: " . $event->id . "\n";
    echo "  Title: " . $event->title . "\n";
    echo "  Has Media: " . $event->hasMedia . "\n";
    echo "  All Image URLs: " . json_encode($event->allImageUrls) . "\n";
    echo "  All Video URLs: " . json_encode($event->allVideoUrls) . "\n";
    echo "  Published: " . ($event->is_published ? 'Yes' : 'No') . "\n";
    echo "  Visibility: " . $event->visibility_scope . "\n";
    echo "  Target Dept: " . $event->target_department . "\n\n";
}

// Test news query (same as UserDashboardController)
echo "=== NEWS QUERY ===\n";
$news = News::where('is_published', true)
    ->visibleToUser($user)
    ->with('admin')
    ->latest()
    ->get();

echo "Found " . $news->count() . " news items\n";
foreach ($news as $newsItem) {
    echo "News ID: " . $newsItem->id . "\n";
    echo "  Title: " . $newsItem->title . "\n";
    echo "  Has Media: " . $newsItem->hasMedia . "\n";
    echo "  All Image URLs: " . json_encode($newsItem->allImageUrls) . "\n";
    echo "  All Video URLs: " . json_encode($newsItem->allVideoUrls) . "\n";
    echo "  Published: " . ($newsItem->is_published ? 'Yes' : 'No') . "\n";
    echo "  Visibility: " . $newsItem->visibility_scope . "\n";
    echo "  Target Dept: " . $newsItem->target_department . "\n\n";
}
