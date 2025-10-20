<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "Testing simple announcement creation via HTTP simulation...\n";

try {
    // Simulate a POST request to the announcement store route
    $request = \Illuminate\Http\Request::create(
        '/superadmin/announcements',
        'POST',
        [
            'title' => 'Test Announcement via HTTP',
            'content' => 'This is a test announcement created via simulated HTTP request',
            'visibility_scope' => 'all',
            'is_published' => '1',
            'action' => 'save_and_publish',
            '_token' => csrf_token()
        ]
    );
    
    // Set up authentication (simulate logged in superadmin)
    $admin = \App\Models\Admin::where('role', 'superadmin')->first();
    if ($admin) {
        \Auth::guard('admin')->login($admin);
        echo "Authenticated as admin: " . $admin->username . " (Role: " . $admin->role . ")\n";
    } else {
        echo "No superadmin found in database\n";
        exit;
    }
    
    // Create controller instance and call store method
    $controller = new \App\Http\Controllers\AnnouncementController();
    $response = $controller->store($request);
    
    echo "Response type: " . get_class($response) . "\n";
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Redirect URL: " . $response->getTargetUrl() . "\n";
        
        $session = $response->getSession();
        if ($session && $session->has('success')) {
            echo "Success message: " . $session->get('success') . "\n";
        }
        if ($session && $session->has('error')) {
            echo "Error message: " . $session->get('error') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
