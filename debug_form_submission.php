<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "Testing form submission debugging...\n";

// Check if there are any middleware issues
try {
    $route = \Route::getRoutes()->getByName('superadmin.announcements.store');
    
    if ($route) {
        echo "Route found: " . $route->uri() . "\n";
        echo "Middleware: " . implode(', ', $route->gatherMiddleware()) . "\n";
        
        // Check if auth:admin middleware exists
        $middlewares = $route->gatherMiddleware();
        if (in_array('auth:admin', $middlewares)) {
            echo "Auth middleware detected: auth:admin\n";
        }
        
        // Test if we can create a simple announcement via direct model call
        echo "\nTesting direct model creation...\n";
        
        $announcement = \App\Models\Announcement::create([
            'title' => 'Debug Test Announcement',
            'content' => 'Testing direct creation',
            'admin_id' => 1,
            'is_published' => true,
            'visibility_scope' => 'all'
        ]);
        
        echo "Direct creation successful! ID: " . $announcement->id . "\n";
        
    } else {
        echo "Route not found!\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
