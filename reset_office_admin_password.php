<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Resetting Office Admin Password ===\n";

// Get the office admin
$admin = \App\Models\Admin::where('role', 'office_admin')->first();

if (!$admin) {
    echo "No office admin found!\n";
    exit;
}

echo "Office Admin Found:\n";
echo "Username: " . $admin->username . "\n";
echo "Current Role: " . $admin->role . "\n";

// Reset password to 'admin123'
$newPassword = 'admin123';
$admin->password = \Hash::make($newPassword);
$admin->save();

echo "\nPassword has been reset to: $newPassword\n";

// Verify the new password works
$isValid = \Hash::check($newPassword, $admin->password);
echo "Password verification test: " . ($isValid ? 'SUCCESS' : 'FAILED') . "\n";

echo "\nYou can now login with:\n";
echo "Username: " . $admin->username . "\n";
echo "Password: $newPassword\n";
echo "Login Type: office-admin\n";
