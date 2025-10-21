<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Office Admin Login Debug ===\n";

// Get the office admin details
$officeAdmin = \App\Models\Admin::where('role', 'office_admin')->first();

if (!$officeAdmin) {
    echo "❌ No office admin found in database!\n";
    exit;
}

echo "Office Admin in Database:\n";
echo "ID: " . $officeAdmin->id . "\n";
echo "Username: '" . $officeAdmin->username . "'\n";
echo "Role: '" . $officeAdmin->role . "'\n";
echo "Password Hash: " . substr($officeAdmin->password, 0, 30) . "...\n";
echo "isOfficeAdmin(): " . ($officeAdmin->isOfficeAdmin() ? 'true' : 'false') . "\n";

// Test what credentials you might be entering
$possibleCredentials = [
    $officeAdmin->username,  // Exact username from database
    'jev.bautro@mcclawis.edu.ph',  // In case there's a slight difference
    'office.admin@mcclawis.edu.ph',  // Common office admin email
    'admin@mcclawis.edu.ph'  // Generic admin email
];

echo "\nTesting possible MS365 account credentials:\n";
foreach ($possibleCredentials as $testAccount) {
    echo "\nTesting MS365 Account: '$testAccount'\n";
    
    // Simulate the exact lookup logic from UnifiedAuthController
    $foundAdmin = \App\Models\Admin::all()->first(function ($admin) use ($testAccount) {
        return $admin->username === $testAccount;
    });
    
    if ($foundAdmin) {
        echo "✅ MATCH FOUND!\n";
        echo "  Found Admin ID: " . $foundAdmin->id . "\n";
        echo "  Found Username: '" . $foundAdmin->username . "'\n";
        echo "  Found Role: '" . $foundAdmin->role . "'\n";
        
        // Test password with common passwords
        $testPasswords = ['admin123', 'password', 'office123', 'admin'];
        foreach ($testPasswords as $testPassword) {
            $passwordValid = \Hash::check($testPassword, $foundAdmin->password);
            if ($passwordValid) {
                echo "  ✅ PASSWORD MATCH: '$testPassword'\n";
                
                // Test role validation
                if ($foundAdmin->isOfficeAdmin()) {
                    echo "  ✅ ROLE VALIDATION: Office Admin\n";
                    echo "\n🎯 WORKING CREDENTIALS FOUND:\n";
                    echo "  Login Type: office-admin\n";
                    echo "  MS365 Account: '$testAccount'\n";
                    echo "  Password: '$testPassword'\n";
                    break 2;
                } else {
                    echo "  ❌ ROLE VALIDATION FAILED: Not office admin\n";
                }
            }
        }
    } else {
        echo "❌ No match found\n";
    }
}

// Debug: Show all admins for comparison
echo "\n" . str_repeat("=", 50) . "\n";
echo "ALL ADMINS IN DATABASE:\n";
$allAdmins = \App\Models\Admin::all();
foreach ($allAdmins as $admin) {
    echo "ID: {$admin->id} | Username: '{$admin->username}' | Role: '{$admin->role}'\n";
}

// Test the exact UnifiedAuthController logic
echo "\n" . str_repeat("=", 50) . "\n";
echo "TESTING UNIFIED CONTROLLER LOGIC:\n";

// Simulate request data
$testCredentials = [
    'ms365_account' => $officeAdmin->username,
    'password' => 'admin123'
];

echo "Simulating UnifiedAuthController office-admin case:\n";
echo "Credentials: ms365_account='{$testCredentials['ms365_account']}', password='{$testCredentials['password']}'\n";

// Find admin by MS365 account (exact logic from controller)
$admin = \App\Models\Admin::all()->first(function ($admin) use ($testCredentials) {
    return $admin->username === $testCredentials['ms365_account'];
});

if ($admin) {
    echo "✅ Admin found by MS365 account lookup\n";
    echo "Found Admin: ID={$admin->id}, Username='{$admin->username}', Role='{$admin->role}'\n";
    
    // Verify password
    if (\Hash::check($testCredentials['password'], $admin->password)) {
        echo "✅ Password verification successful\n";
        
        // Check role
        if ($admin->isOfficeAdmin()) {
            echo "✅ Role verification successful - is office admin\n";
            echo "🎉 AUTHENTICATION SHOULD SUCCEED!\n";
        } else {
            echo "❌ Role verification failed - not office admin (role: {$admin->role})\n";
        }
    } else {
        echo "❌ Password verification failed\n";
        echo "💡 Try resetting password with: \$admin->password = \\Hash::make('admin123'); \$admin->save();\n";
    }
} else {
    echo "❌ Admin NOT found by MS365 account lookup\n";
    echo "💡 The MS365 account '{$testCredentials['ms365_account']}' doesn't match any admin username\n";
}
