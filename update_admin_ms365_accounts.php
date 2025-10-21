<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Updating Admin MS365 Accounts ===\n";

// Update office admin to use MS365 account format
$officeAdmin = \App\Models\Admin::where('role', 'office_admin')->first();
if ($officeAdmin) {
    echo "Current Office Admin:\n";
    echo "ID: " . $officeAdmin->id . "\n";
    echo "Username: " . $officeAdmin->username . "\n";
    echo "Role: " . $officeAdmin->role . "\n";
    
    // The username is already an MS365 email format, so it's good
    if (strpos($officeAdmin->username, '@') !== false) {
        echo "✓ Office admin username is already in MS365 email format\n";
    } else {
        echo "✗ Office admin username needs to be updated to MS365 format\n";
        // You can update it here if needed
        // $officeAdmin->username = 'office.admin@mcclawis.edu.ph';
        // $officeAdmin->save();
    }
}

// Check for department admins
$departmentAdmins = \App\Models\Admin::where('role', 'department_admin')->get();
echo "\nDepartment Admins found: " . $departmentAdmins->count() . "\n";

foreach ($departmentAdmins as $admin) {
    echo "Department Admin ID: " . $admin->id . "\n";
    echo "Username: " . $admin->username . "\n";
    echo "Role: " . $admin->role . "\n";
    
    if (strpos($admin->username, '@') !== false) {
        echo "✓ Username is already in MS365 email format\n";
    } else {
        echo "✗ Username needs to be updated to MS365 format\n";
        // You can update it here if needed
        // $admin->username = 'dept.admin@mcclawis.edu.ph';
        // $admin->save();
    }
    echo "---\n";
}

echo "\nNote: Department admin and office admin should now use MS365 account field in the unified login form.\n";
echo "The authentication logic has been updated to use ms365_account field for these admin types.\n";
