<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Comment Debug Script ===\n\n";

// Check if comments table exists and has data
try {
    $comments = \App\Models\Comment::latest()->take(10)->with('user')->get();
    echo "Total comments in database: " . \App\Models\Comment::count() . "\n\n";
    
    if ($comments->count() > 0) {
        echo "Recent comments:\n";
        foreach ($comments as $comment) {
            echo "ID: {$comment->id}\n";
            echo "User: {$comment->user->name} ({$comment->user->department})\n";
            echo "Content: " . substr($comment->content, 0, 100) . "...\n";
            echo "Type: {$comment->commentable_type}\n";
            echo "Target ID: {$comment->commentable_id}\n";
            echo "Created: {$comment->created_at}\n";
            echo "---\n";
        }
    } else {
        echo "No comments found in database.\n";
    }
    
    // Check specific content items
    echo "\nChecking content items:\n";
    $announcements = \App\Models\Announcement::where('is_published', true)->take(3)->get();
    foreach ($announcements as $announcement) {
        $commentCount = \App\Models\Comment::where('commentable_type', 'App\Models\Announcement')
            ->where('commentable_id', $announcement->id)->count();
        echo "Announcement '{$announcement->title}' has {$commentCount} comments\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
