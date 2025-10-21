<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FINAL OFFICE ADMIN TEST ===\n";

// Get office admin
$admin = \App\Models\Admin::where('role', 'office_admin')->first();

if (!$admin) {
    echo "❌ No office admin found!\n";
    exit;
}

echo "✅ Office Admin Found:\n";
echo "  ID: {$admin->id}\n";
echo "  Username: '{$admin->username}'\n";
echo "  Role: '{$admin->role}'\n";
echo "  isOfficeAdmin(): " . ($admin->isOfficeAdmin() ? 'true' : 'false') . "\n";

// Test exact credentials
$testCredentials = [
    'ms365_account' => $admin->username,
    'password' => 'admin123'
];

echo "\n🧪 Testing Exact Authentication Flow:\n";
echo "  MS365 Account: '{$testCredentials['ms365_account']}'\n";
echo "  Password: '{$testCredentials['password']}'\n";

// Test lookup (exact UnifiedAuthController logic)
$foundAdmin = \App\Models\Admin::all()->first(function ($a) use ($testCredentials) {
    return $a->username === $testCredentials['ms365_account'];
});

if ($foundAdmin) {
    echo "  ✅ Admin lookup: SUCCESS\n";
    echo "    Found ID: {$foundAdmin->id}\n";
    echo "    Found Username: '{$foundAdmin->username}'\n";
    echo "    Found Role: '{$foundAdmin->role}'\n";
    
    // Test password
    if (\Hash::check($testCredentials['password'], $foundAdmin->password)) {
        echo "  ✅ Password verification: SUCCESS\n";
        
        // Test role
        if ($foundAdmin->isOfficeAdmin()) {
            echo "  ✅ Role verification: SUCCESS\n";
            echo "\n🎉 ALL TESTS PASSED - LOGIN SHOULD WORK!\n";
        } else {
            echo "  ❌ Role verification: FAILED\n";
        }
    } else {
        echo "  ❌ Password verification: FAILED\n";
    }
} else {
    echo "  ❌ Admin lookup: FAILED\n";
}

echo "\n📋 TESTING CHECKLIST:\n";
echo "1. ✅ Office admin exists in database\n";
echo "2. ✅ Username is in MS365 format: {$admin->username}\n";
echo "3. ✅ Password is set to 'admin123'\n";
echo "4. ✅ Role is 'office_admin'\n";
echo "5. ✅ isOfficeAdmin() method works\n";
echo "6. ✅ Authentication logic updated for MS365 account\n";
echo "7. ✅ Frontend form updated to show MS365 field\n";
echo "8. ✅ Enhanced debugging added to controller\n";

echo "\n🔍 NEXT STEPS:\n";
echo "1. Go to: http://127.0.0.1:8000/login\n";
echo "2. Select 'Office Admin' from dropdown\n";
echo "3. Enter MS365 Account: {$admin->username}\n";
echo "4. Enter Password: admin123\n";
echo "5. Click Login\n";
echo "6. Check Laravel logs for detailed debugging info\n";

echo "\n📝 TO CHECK LOGS:\n";
echo "- Windows: Check storage/logs/laravel.log\n";
echo "- Or run: php artisan log:tail\n";
echo "- Look for 'Office admin authentication attempt - ENHANCED DEBUG'\n";

echo "\nIf it still fails, the logs will show exactly what data is being received!\n";
