<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "Testing route access...\n";

try {
    // Test if the route exists
    $route = \Route::getRoutes()->getByName('superadmin.announcements.store');
    
    if ($route) {
        echo "SUCCESS: Route 'superadmin.announcements.store' exists\n";
        echo "URI: " . $route->uri() . "\n";
        echo "Methods: " . implode(', ', $route->methods()) . "\n";
        echo "Action: " . $route->getActionName() . "\n";
    } else {
        echo "ERROR: Route 'superadmin.announcements.store' not found\n";
    }
    
    // List all announcement routes
    echo "\nAll announcement routes:\n";
    $routes = \Route::getRoutes();
    foreach ($routes as $route) {
        if (strpos($route->getName(), 'announcements') !== false) {
            echo "- " . $route->getName() . " => " . $route->uri() . " [" . implode(', ', $route->methods()) . "]\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
