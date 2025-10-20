<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

echo "=== Comment API Endpoint Test ===\n\n";

// Create a mock request to test the endpoint
$request = \Illuminate\Http\Request::create('/user/content/announcement/89/comments', 'GET');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$request->headers->set('Accept', 'application/json');

// Mock authentication
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

echo "Testing as user: {$user->name} ({$user->department})\n";
echo "Endpoint: /user/content/announcement/89/comments\n\n";

try {
    $controller = new \App\Http\Controllers\CommentController();
    $response = $controller->getComments('announcement', 89);
    
    $content = $response->getContent();
    $data = json_decode($content, true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Data:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    if (isset($data['comments'])) {
        echo "\nFound " . count($data['comments']) . " comments\n";
        foreach ($data['comments'] as $comment) {
            echo "- {$comment['user_name']}: " . substr($comment['content'], 0, 50) . "...\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
