<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Office Admin Authentication Flow Test ===\n";

// Get the office admin
$admin = \App\Models\Admin::where('role', 'office_admin')->first();

if (!$admin) {
    echo "No office admin found!\n";
    exit;
}

echo "Office Admin Details:\n";
echo "ID: " . $admin->id . "\n";
echo "Username: " . $admin->username . "\n";
echo "Role: " . $admin->role . "\n";
echo "isOfficeAdmin(): " . ($admin->isOfficeAdmin() ? 'true' : 'false') . "\n";

// Test the authentication flow similar to UnifiedAuthController
$testUsername = $admin->username;
$testPassword = 'admin123';

echo "\nTesting authentication flow:\n";
echo "Test Username: $testUsername\n";
echo "Test Password: $testPassword\n";

// Step 1: Find admin by username (similar to UnifiedAuthController logic)
echo "\n1. Testing admin lookup by username:\n";
$foundAdmin = \App\Models\Admin::all()->first(function ($admin) use ($testUsername) {
    return $admin->username === $testUsername;
});

if ($foundAdmin) {
    echo "✓ Admin found by username lookup\n";
    echo "  Found ID: " . $foundAdmin->id . "\n";
    echo "  Found Username: " . $foundAdmin->username . "\n";
    echo "  Found Role: " . $foundAdmin->role . "\n";
} else {
    echo "✗ Admin NOT found by username lookup\n";
    echo "  This indicates a username matching issue\n";
    
    // Debug: Show all usernames for comparison
    echo "\nAll admin usernames in database:\n";
    $allAdmins = \App\Models\Admin::all();
    foreach ($allAdmins as $admin) {
        echo "  ID {$admin->id}: '{$admin->username}' (role: {$admin->role})\n";
    }
    exit;
}

// Step 2: Test password verification
echo "\n2. Testing password verification:\n";
$passwordValid = \Hash::check($testPassword, $foundAdmin->password);
echo "Password check result: " . ($passwordValid ? 'VALID' : 'INVALID') . "\n";

if (!$passwordValid) {
    echo "✗ Password verification failed\n";
    echo "  This indicates the password is not 'admin123'\n";
    exit;
}

// Step 3: Test role verification
echo "\n3. Testing role verification:\n";
$isOfficeAdmin = $foundAdmin->isOfficeAdmin();
echo "isOfficeAdmin() result: " . ($isOfficeAdmin ? 'true' : 'false') . "\n";

if (!$isOfficeAdmin) {
    echo "✗ Role verification failed\n";
    echo "  Expected role: office_admin\n";
    echo "  Actual role: " . $foundAdmin->role . "\n";
    exit;
}

echo "\n✓ ALL AUTHENTICATION CHECKS PASSED!\n";
echo "The office admin should be able to login successfully with:\n";
echo "Username: $testUsername\n";
echo "Password: $testPassword\n";
echo "Login Type: office-admin\n";

echo "\n=== Testing Auth Guard Login ===\n";
try {
    // Test manual login (similar to what UnifiedAuthController does)
    \Auth::guard('admin')->login($foundAdmin);
    echo "✓ Auth::guard('admin')->login() successful\n";
    
    // Check if authenticated
    $isAuthenticated = \Auth::guard('admin')->check();
    echo "Auth check result: " . ($isAuthenticated ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";
    
    // Get authenticated user
    $authUser = \Auth::guard('admin')->user();
    if ($authUser) {
        echo "Authenticated user ID: " . $authUser->id . "\n";
        echo "Authenticated user role: " . $authUser->role . "\n";
    }
    
    // Logout to clean up
    \Auth::guard('admin')->logout();
    echo "✓ Logout successful\n";
    
} catch (\Exception $e) {
    echo "✗ Auth guard login failed: " . $e->getMessage() . "\n";
}
