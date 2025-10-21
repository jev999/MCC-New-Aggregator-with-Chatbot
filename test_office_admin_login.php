<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Office Admin Login Test ===\n";

// Get the office admin
$admin = \App\Models\Admin::where('role', 'office_admin')->first();

if (!$admin) {
    echo "No office admin found!\n";
    exit;
}

echo "Office Admin Found:\n";
echo "ID: " . $admin->id . "\n";
echo "Username: " . $admin->username . "\n";
echo "Role: " . $admin->role . "\n";
echo "isOfficeAdmin(): " . ($admin->isOfficeAdmin() ? 'true' : 'false') . "\n";

// Test common passwords
$testPasswords = ['admin123', 'password', 'office123', 'admin', '123456'];

echo "\nTesting common passwords:\n";
foreach ($testPasswords as $password) {
    $isValid = \Hash::check($password, $admin->password);
    echo "Password '$password': " . ($isValid ? 'VALID' : 'invalid') . "\n";
    if ($isValid) {
        echo "*** FOUND WORKING PASSWORD: $password ***\n";
        break;
    }
}

echo "\nPassword hash: " . substr($admin->password, 0, 50) . "...\n";
