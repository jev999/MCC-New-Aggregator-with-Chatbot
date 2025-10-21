<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Office Admin Debug Information ===\n";

// Get office admin records
$officeAdmins = \App\Models\Admin::where('role', 'office_admin')->get();

echo "Total office admins found: " . $officeAdmins->count() . "\n\n";

foreach ($officeAdmins as $admin) {
    echo "Office Admin Details:\n";
    echo "ID: " . $admin->id . "\n";
    echo "Username: " . $admin->username . "\n";
    echo "Role: " . $admin->role . "\n";
    echo "Password hash (first 20 chars): " . substr($admin->password, 0, 20) . "\n";
    echo "isOfficeAdmin() result: " . ($admin->isOfficeAdmin() ? 'true' : 'false') . "\n";
    echo "Created: " . $admin->created_at . "\n";
    echo "Updated: " . $admin->updated_at . "\n";
    echo "---\n";
}

// Also check all admin roles
echo "\nAll admin roles in database:\n";
$allAdmins = \App\Models\Admin::all(['id', 'username', 'role']);
foreach ($allAdmins as $admin) {
    echo "ID: {$admin->id}, Username: {$admin->username}, Role: {$admin->role}\n";
}
