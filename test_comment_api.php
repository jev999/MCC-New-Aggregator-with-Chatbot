<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Comment API Test ===\n\n";

// Test the comment API endpoint directly
$user = \App\Models\User::first();
if (!$user) {
    echo "User not found!\n";
    exit;
}

echo "Testing user: {$user->name} ({$user->department})\n\n";

// Get an announcement that has comments
$announcement = \App\Models\Announcement::find(89);
if (!$announcement) {
    echo "Announcement not found!\n";
    exit;
}

echo "Testing announcement: {$announcement->title}\n";
echo "Visibility scope: {$announcement->visibility_scope}\n";
echo "Target department: {$announcement->target_department}\n\n";

// Test visibility
$isVisible = $announcement->isVisibleToUser($user);
echo "Is visible to user: " . ($isVisible ? 'YES' : 'NO') . "\n\n";

// Manually test the comment query logic
echo "Testing comment retrieval logic:\n";

$commentsQuery = \App\Models\Comment::where('commentable_type', 'App\Models\Announcement')
    ->where('commentable_id', $announcement->id)
    ->whereNull('parent_id')
    ->with(['user' => function($query) {
        $query->select('id', 'first_name', 'middle_name', 'surname', 'role', 'department');
    }]);

// Apply the same logic as in CommentController
if ($announcement->visibility_scope === 'all' || 
    $announcement->visibility_scope === null || 
    $announcement->visibility_scope === '') {
    echo "Content is for all departments - showing all comments\n";
    $commentsQuery->whereHas('user', function($query) {
        $query->where('role', 'student')
              ->orWhere('role', 'like', '%admin%');
    });
} else {
    echo "Content is department specific - showing same department comments only\n";
    $commentsQuery->whereHas('user', function($query) use ($user) {
        $query->where('department', $user->department)
              ->orWhere('role', 'like', '%admin%');
    });
}

$comments = $commentsQuery->orderBy('created_at', 'desc')->get();

echo "Found " . $comments->count() . " comments:\n";
foreach ($comments as $comment) {
    echo "- ID: {$comment->id}, User: {$comment->user->name}, Dept: {$comment->user->department}\n";
    echo "  Content: " . substr($comment->content, 0, 50) . "...\n";
}
