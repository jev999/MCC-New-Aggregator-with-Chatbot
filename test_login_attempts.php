<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LoginAttempt;

echo "=== Testing LoginAttempt Model ===\n\n";

try {
    // Test if we can query the table
    $count = LoginAttempt::count();
    echo "✅ LoginAttempt model works! Found {$count} records.\n";
    
    // Test creating a test record
    $testAttempt = LoginAttempt::create([
        'identifier' => 'test@example.com',
        'login_type' => 'ms365',
        'ip_address' => '127.0.0.1',
        'attempts' => 1,
        'last_attempt_at' => now(),
    ]);
    
    echo "✅ Successfully created test login attempt record (ID: {$testAttempt->id})\n";
    
    // Clean up test record
    $testAttempt->delete();
    echo "✅ Test record cleaned up\n";
    
    echo "\n🎉 The login_attempts table is working correctly!\n";
    echo "The original error should now be resolved.\n";
    
} catch (Exception $e) {
    echo "❌ Error testing LoginAttempt model:\n";
    echo $e->getMessage() . "\n";
}
