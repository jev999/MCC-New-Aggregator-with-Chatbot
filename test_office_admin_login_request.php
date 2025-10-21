<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Office Admin Login Request ===\n";

// Simulate the exact request that would come from the frontend
$requestData = [
    'login_type' => 'office-admin',
    'ms365_account' => 'jev.bautro@mcclawis.edu.ph',
    'password' => 'admin123',
    '_token' => 'test-token'
];

echo "Simulating login request with data:\n";
foreach ($requestData as $key => $value) {
    echo "  $key: '$value'\n";
}

// Test the validation rules that would be applied
echo "\nTesting validation rules:\n";

$loginType = $requestData['login_type'];
echo "Login type: $loginType\n";

// Check if this matches our updated validation logic
if ($loginType === 'superadmin') {
    echo "✅ Would validate 'username' field (superadmin)\n";
} elseif (in_array($loginType, ['ms365', 'department-admin', 'office-admin'])) {
    echo "✅ Would validate 'ms365_account' field (office-admin)\n";
    
    // Check if ms365_account is provided
    if (isset($requestData['ms365_account']) && !empty($requestData['ms365_account'])) {
        echo "✅ ms365_account field is provided: '{$requestData['ms365_account']}'\n";
    } else {
        echo "❌ ms365_account field is missing or empty\n";
    }
} else {
    echo "❌ Unknown login type\n";
}

// Test the authentication logic
echo "\nTesting authentication logic:\n";

if ($loginType === 'office-admin') {
    $credentials = [
        'ms365_account' => $requestData['ms365_account'],
        'password' => $requestData['password']
    ];
    
    echo "Extracted credentials:\n";
    echo "  ms365_account: '{$credentials['ms365_account']}'\n";
    echo "  password: '{$credentials['password']}'\n";
    
    // Find admin by MS365 account
    $admin = \App\Models\Admin::all()->first(function ($admin) use ($credentials) {
        return $admin->username === $credentials['ms365_account'];
    });
    
    if ($admin) {
        echo "✅ Admin found: ID={$admin->id}, Role={$admin->role}\n";
        
        // Verify password
        if (\Hash::check($credentials['password'], $admin->password)) {
            echo "✅ Password verification successful\n";
            
            // Check role
            if ($admin->isOfficeAdmin()) {
                echo "✅ Role verification successful\n";
                echo "🎉 LOGIN SHOULD SUCCEED!\n";
                echo "\nExpected result: Redirect to office-admin.dashboard\n";
            } else {
                echo "❌ Role verification failed: not office admin\n";
            }
        } else {
            echo "❌ Password verification failed\n";
        }
    } else {
        echo "❌ Admin not found with MS365 account: '{$credentials['ms365_account']}'\n";
        
        // Debug: show what usernames exist
        echo "\nDebugging - Available admin usernames:\n";
        $allAdmins = \App\Models\Admin::all();
        foreach ($allAdmins as $a) {
            echo "  '{$a->username}' (role: {$a->role})\n";
        }
    }
}

// Test if there are any issues with the frontend form
echo "\n" . str_repeat("=", 50) . "\n";
echo "FRONTEND FORM CHECKLIST:\n";
echo "1. ✅ Login type 'office-admin' should show MS365 account field\n";
echo "2. ✅ MS365 account field should be required\n";
echo "3. ✅ Password field should be required\n";
echo "4. ✅ Form should submit to unified login endpoint\n";

echo "\nTo test manually:\n";
echo "1. Go to: http://127.0.0.1:8000/login\n";
echo "2. Select 'Office Admin' from dropdown\n";
echo "3. Enter MS365 Account: jev.bautro@mcclawis.edu.ph\n";
echo "4. Enter Password: admin123\n";
echo "5. Click Login\n";

echo "\nIf it still fails, check browser developer tools for:\n";
echo "- JavaScript errors\n";
echo "- Network requests being sent\n";
echo "- Form data being submitted\n";
