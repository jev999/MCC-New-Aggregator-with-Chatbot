<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminFacultyController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdminProfileController;
use App\Http\Controllers\DepartmentAdminDashboardController;
use App\Http\Controllers\OfficeAdminController;
use App\Http\Controllers\OfficeAdminDashboardController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DepartmentAdminAuthController;
use App\Http\Controllers\OfficeAdminAuthController;
use App\Http\Controllers\SuperAdminAuthController;
use App\Http\Controllers\PublicContentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UnifiedAuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Auth\GmailAuthController;
use App\Http\Controllers\Auth\MS365AuthController;
use App\Http\Controllers\Auth\MS365OAuthController;
use Illuminate\Support\Facades\Artisan;

// Show welcome page at root
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Test chatbot page
Route::get('/test-chatbot', function () {
    return view('test-chatbot');
})->name('test.chatbot');

// Legal pages routes
Route::get('/terms-and-conditions', [App\Http\Controllers\LegalController::class, 'termsAndConditions'])->name('legal.terms');
Route::get('/privacy-policy', [App\Http\Controllers\LegalController::class, 'privacyPolicy'])->name('legal.privacy');
Route::get('/data-protection-notice', [App\Http\Controllers\LegalController::class, 'dataProtectionNotice'])->name('legal.data-protection');
Route::get('/cookie-policy', [App\Http\Controllers\LegalController::class, 'cookiePolicy'])->name('legal.cookies');

// Simple policy routes for registration forms
Route::get('/terms', function () {
    return view('policies.terms');
})->name('terms');
Route::get('/privacy', function () {
    return view('policies.privacy-policy');
})->name('privacy');


// Debug route to reset user password
Route::get('/reset-user-password/{email}/{newPassword}', function ($email, $newPassword) {
    $user = \App\Models\User::all()->first(function ($user) use ($email) {
        return $user->ms365_account === $email || $user->gmail_account === $email;
    });
    
    if ($user) {
        $user->password = \Hash::make($newPassword);
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
            'user_id' => $user->id,
            'email' => $email,
            'new_password' => $newPassword,
            'password_check' => \Hash::check($newPassword, $user->password)
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
            'searched_email' => $email
        ]);
    }
});

// Debug route for user credentials - test authentication
Route::get('/test-user-auth/{email}/{password}', function ($email, $password) {
    $users = \App\Models\User::all();
    $foundUser = null;
    $allEmails = [];
    
    foreach ($users as $user) {
        $allEmails[] = [
            'id' => $user->id,
            'ms365_account' => $user->ms365_account,
            'gmail_account' => $user->gmail_account,
            'matches_input' => ($user->ms365_account === $email || $user->gmail_account === $email)
        ];
        
        if ($user->ms365_account === $email || $user->gmail_account === $email) {
            $foundUser = $user;
        }
    }
    
    if ($foundUser) {
        return response()->json([
            'found' => true,
            'user_id' => $foundUser->id,
            'ms365_account' => $foundUser->ms365_account,
            'gmail_account' => $foundUser->gmail_account,
            'password_hash' => substr($foundUser->password, 0, 50) . '...',
            'password_check_provided' => \Hash::check($password, $foundUser->password),
            'auth_attempt_result' => auth()->attempt(['ms365_account' => $email, 'password' => $password]),
            'all_users_count' => count($allEmails)
        ]);
    } else {
        return response()->json([
            'found' => false,
            'searched_email' => $email,
            'all_users_count' => count($allEmails),
            'sample_emails' => array_slice($allEmails, 0, 3)
        ]);
    }
});

// Unified login routes (default login)

Route::get('/login', [UnifiedAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UnifiedAuthController::class, 'login'])->middleware(\App\Http\Middleware\LoginLockoutMiddleware::class)->name('unified.login');

// Password change routes
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [App\Http\Controllers\PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [App\Http\Controllers\PasswordChangeController::class, 'changePassword'])->name('password.change');
});

// Account switching routes
Route::post('/switch-account', [UnifiedAuthController::class, 'switchAccount'])->name('switch.account');
Route::post('/remove-account', [UnifiedAuthController::class, 'removeAccount'])->name('remove.account');

// Login attempt management routes (for debugging/admin purposes)
Route::post('/clear-all-login-attempts', [UnifiedAuthController::class, 'clearAllLoginAttempts'])->name('clear.all.login.attempts');
Route::post('/clear-login-attempts', [UnifiedAuthController::class, 'clearSpecificLoginAttempts'])->name('clear.login.attempts');
Route::get('/debug-lockout-status', [UnifiedAuthController::class, 'debugLockoutStatus'])->name('debug.lockout.status');
Route::post('/clear-current-lockout', [UnifiedAuthController::class, 'clearCurrentLockout'])->name('clear.current.lockout');

// Test lockout functionality for all admin types
Route::get('/test-lockout-system', function() {
    $output = "<h2>3-Minute Lockout System Test - All Admin Types</h2>";
    
    // Clear any existing lockout data first
    $sessionData = session()->all();
    $clearedKeys = [];
    foreach ($sessionData as $key => $value) {
        if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
            session()->forget($key);
            $clearedKeys[] = $key;
        }
    }
    
    $output .= "<h3>Cleared existing lockout data:</h3>";
    $output .= "<pre>" . json_encode($clearedKeys, JSON_PRETTY_PRINT) . "</pre>";
    
    // Create a test request for MS365 login
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'login_type' => 'ms365',
        'ms365_account' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);
    $request->setSession(session());
    
    $unifiedController = new \App\Http\Controllers\UnifiedAuthController();
    
    // Test the lockout methods directly
    $reflection = new \ReflectionClass($unifiedController);
    $getAccountIdentifier = $reflection->getMethod('getAccountIdentifier');
    $getAccountIdentifier->setAccessible(true);
    
    $getLoginAttemptsKey = $reflection->getMethod('getLoginAttemptsKey');
    $getLoginAttemptsKey->setAccessible(true);
    
    $getLockoutKey = $reflection->getMethod('getLockoutKey');
    $getLockoutKey->setAccessible(true);
    
    $incrementLoginAttempts = $reflection->getMethod('incrementLoginAttempts');
    $incrementLoginAttempts->setAccessible(true);
    
    $isLockedOut = $reflection->getMethod('isLockedOut');
    $isLockedOut->setAccessible(true);
    
    $getLockoutTimeRemaining = $reflection->getMethod('getLockoutTimeRemaining');
    $getLockoutTimeRemaining->setAccessible(true);
    
    $accountIdentifier = $getAccountIdentifier->invoke($unifiedController, $request);
    $attemptsKey = $getLoginAttemptsKey->invoke($unifiedController, $request);
    $lockoutKey = $getLockoutKey->invoke($unifiedController, $request);
    
    $output .= "<h3>Account Information:</h3>";
    $output .= "Account Identifier: <strong>" . $accountIdentifier . "</strong><br>";
    $output .= "Attempts Key: <strong>" . $attemptsKey . "</strong><br>";
    $output .= "Lockout Key: <strong>" . $lockoutKey . "</strong><br><br>";
    
    // Test 3 failed attempts
    for ($i = 1; $i <= 3; $i++) {
        $output .= "<h4>Attempt #{$i}:</h4>";
        
        $incrementLoginAttempts->invoke($unifiedController, $request);
        
        $attempts = session($attemptsKey, 0);
        $locked = $isLockedOut->invoke($unifiedController, $request);
        $timeRemaining = $getLockoutTimeRemaining->invoke($unifiedController, $request);
        $lockoutTime = session($lockoutKey);
        
        $output .= "Attempts in session: <strong>" . $attempts . "</strong><br>";
        $output .= "Is locked out: <strong>" . ($locked ? 'YES' : 'NO') . "</strong><br>";
        $output .= "Time remaining: <strong>" . $timeRemaining . " minutes</strong><br>";
        $output .= "Lockout time in session: <strong>" . ($lockoutTime ? $lockoutTime->toDateTimeString() : 'None') . "</strong><br>";
        $output .= "Current time: <strong>" . now()->toDateTimeString() . "</strong><br><br>";
    }
    
    // Test the lockout check after 3 attempts
    $output .= "<h3>Final Lockout Status:</h3>";
    $finalLocked = $isLockedOut->invoke($unifiedController, $request);
    $finalTimeRemaining = $getLockoutTimeRemaining->invoke($unifiedController, $request);
    
    $output .= "Final locked status: <strong>" . ($finalLocked ? 'LOCKED' : 'NOT LOCKED') . "</strong><br>";
    $output .= "Final time remaining: <strong>" . $finalTimeRemaining . " minutes</strong><br>";
    
    // Show all session data
    $output .= "<h3>All Session Data:</h3>";
    $allSessionData = session()->all();
    $lockoutSessionData = [];
    foreach ($allSessionData as $key => $value) {
        if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
            $lockoutSessionData[$key] = $value;
        }
    }
    $output .= "<pre>" . json_encode($lockoutSessionData, JSON_PRETTY_PRINT) . "</pre>";
    
    return $output;
});

// Test lockout for all admin types
Route::get('/test-admin-lockout', function() {
    $output = "<h2>Admin Lockout System Test - All Types</h2>";
    $output .= "<p>Testing 3-attempt lockout for all admin types in the unified login form.</p>";
    
    $unifiedController = new \App\Http\Controllers\UnifiedAuthController();
    $reflection = new \ReflectionClass($unifiedController);
    $getAccountIdentifier = $reflection->getMethod('getAccountIdentifier');
    $getAccountIdentifier->setAccessible(true);
    
    // Test data for each admin type
    $adminTypes = [
        'superadmin' => [
            'login_type' => 'superadmin',
            'username' => 'superadmin',
            'password' => 'wrongpassword',
            'field' => 'username'
        ],
        'department-admin' => [
            'login_type' => 'department-admin', 
            'ms365_account' => 'dept.admin@mcc-nac.edu.ph',
            'password' => 'wrongpassword',
            'field' => 'ms365_account'
        ],
        'office-admin' => [
            'login_type' => 'office-admin',
            'ms365_account' => 'office.admin@mcc-nac.edu.ph', 
            'password' => 'wrongpassword',
            'field' => 'ms365_account'
        ]
    ];
    
    foreach ($adminTypes as $type => $data) {
        $output .= "<h3>Testing {$type} Lockout:</h3>";
        
        // Create test request
        $request = new \Illuminate\Http\Request();
        $request->merge($data);
        $request->setSession(session());
        
        // Get account identifier
        $accountIdentifier = $getAccountIdentifier->invoke($unifiedController, $request);
        $output .= "<strong>Account Identifier:</strong> {$accountIdentifier}<br>";
        $output .= "<strong>Login Field:</strong> {$data['field']} = {$data[$data['field']]}<br>";
        $output .= "<strong>Expected Lockout:</strong> After 3 failed attempts, account locked for 3 minutes<br>";
        $output .= "<strong>Frontend Field:</strong> " . ($data['field'] === 'username' ? 'Username field' : 'MS365 Account field') . "<br><br>";
    }
    
    $output .= "<h3>Manual Testing Instructions:</h3>";
    $output .= "<ol>";
    $output .= "<li><strong>Superadmin Test:</strong><br>";
    $output .= "   - Go to <a href='/login' target='_blank'>Login Page</a><br>";
    $output .= "   - Select 'Super Admin' login type<br>";
    $output .= "   - Enter username: 'superadmin' and wrong password (e.g., 'wrongpass')<br>";
    $output .= "   - <strong>After 1st attempt:</strong> Should see warning '2 login attempt(s) remaining'<br>";
    $output .= "   - <strong>After 2nd attempt:</strong> Should see warning '1 login attempt(s) remaining'<br>";
    $output .= "   - <strong>After 3rd attempt:</strong> Should see lockout message with countdown timer</li><br>";
    
    $output .= "<li><strong>Department Admin Test:</strong><br>";
    $output .= "   - Go to <a href='/login' target='_blank'>Login Page</a><br>";
    $output .= "   - Select 'Department Admin' login type<br>";
    $output .= "   - Enter MS365 account: 'dept.admin@mcc-nac.edu.ph' and wrong password<br>";
    $output .= "   - <strong>After 1st attempt:</strong> Should see warning '2 login attempt(s) remaining'<br>";
    $output .= "   - <strong>After 2nd attempt:</strong> Should see warning '1 login attempt(s) remaining'<br>";
    $output .= "   - <strong>After 3rd attempt:</strong> Should see lockout message with countdown timer</li><br>";
    
    $output .= "<li><strong>Office Admin Test:</strong><br>";
    $output .= "   - Go to <a href='/login' target='_blank'>Login Page</a><br>";
    $output .= "   - Select 'Office Admin' login type<br>";
    $output .= "   - Enter MS365 account: 'office.admin@mcc-nac.edu.ph' and wrong password<br>";
    $output .= "   - <strong>After 1st attempt:</strong> Should see warning '2 login attempt(s) remaining'<br>";
    $output .= "   - <strong>After 2nd attempt:</strong> Should see warning '1 login attempt(s) remaining'<br>";
    $output .= "   - <strong>After 3rd attempt:</strong> Should see lockout message with countdown timer</li>";
    $output .= "</ol>";
    
    $output .= "<h3>Expected Behavior:</h3>";
    $output .= "<ul>";
    $output .= "<li>✅ Each admin type gets separate lockout tracking</li>";
    $output .= "<li>✅ Form disables and grays out during lockout</li>";
    $output .= "<li>✅ Real-time countdown shows MM:SS format</li>";
    $output .= "<li>✅ Lockout message shows admin type (e.g., 'Your Super Admin account is locked...')</li>";
    $output .= "<li>✅ Form automatically re-enables after 3 minutes</li>";
    $output .= "<li>✅ Success message appears when unlocked</li>";
    $output .= "</ul>";
    
    return $output;
});

// Simple lockout test route
Route::get('/test-simple-lockout', function() {
    return '
    <h2>Simple Lockout Test</h2>
    <p>This will test the complete lockout flow:</p>
    <ol>
        <li>Go to <a href="/login" target="_blank">Login Page</a></li>
        <li>Select any login type</li>
        <li>Enter wrong credentials 3 times in a row</li>
        <li>Form should be locked for 3 minutes</li>
        <li>After 3 minutes, form should unlock automatically</li>
    </ol>
    
    <h3>Debug Tools:</h3>
    <ul>
        <li><a href="/debug-lockout-status" target="_blank">Check Lockout Status</a></li>
        <li><a href="/debug-superadmin-attempts" target="_blank">Debug Superadmin Attempts</a></li>
        <li><a href="/debug-all-attempts" target="_blank">Debug All Login Types Attempts</a></li>
        <li><a href="/test-lockout-system" target="_blank">Detailed Lockout Test</a></li>
        <li><a href="/test-admin-lockout" target="_blank">Admin Lockout Test</a></li>
        <li><a href="/clear-all-lockouts" target="_blank">Clear All Lockouts</a></li>
    </ul>
    
    <h3>Expected Behavior:</h3>
    <ul>
        <li><strong>Attempt 1-2:</strong> Shows warning with remaining attempts</li>
        <li><strong>Attempt 3:</strong> Form becomes disabled with lockout message</li>
        <li><strong>During Lockout:</strong> Form completely unusable, countdown shows remaining time</li>
        <li><strong>After 3 Minutes:</strong> Form re-enables, success message appears</li>
    </ul>
    ';
});

// Test lockout enforcement
Route::get('/test-lockout-enforcement', function() {
    $output = "<h2>Lockout Enforcement Test</h2>";
    
    // Create a test lockout
    $testRequest = new \Illuminate\Http\Request();
    $testRequest->merge([
        'login_type' => 'ms365',
        'ms365_account' => 'test@lockout.com',
        'password' => 'wrong'
    ]);
    $testRequest->setSession(session());
    
    $unifiedController = new \App\Http\Controllers\UnifiedAuthController();
    $reflection = new \ReflectionClass($unifiedController);
    
    $getLockoutKey = $reflection->getMethod('getLockoutKey');
    $getLockoutKey->setAccessible(true);
    
    $getAccountIdentifier = $reflection->getMethod('getAccountIdentifier');
    $getAccountIdentifier->setAccessible(true);
    
    $lockoutKey = $getLockoutKey->invoke($unifiedController, $testRequest);
    $accountId = $getAccountIdentifier->invoke($unifiedController, $testRequest);
    
    // Set a lockout manually
    $lockoutTime = now()->addMinutes(3);
    session([$lockoutKey => $lockoutTime]);
    
    $output .= "<h3>Lockout Created:</h3>";
    $output .= "Account: <strong>" . $accountId . "</strong><br>";
    $output .= "Lockout Key: <strong>" . $lockoutKey . "</strong><br>";
    $output .= "Lockout Until: <strong>" . $lockoutTime->toDateTimeString() . "</strong><br>";
    $output .= "Current Time: <strong>" . now()->toDateTimeString() . "</strong><br><br>";
    
    // Test if lockout is enforced
    try {
        $result = $unifiedController->login($testRequest);
        
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            $errors = session()->get('errors');
            if ($errors && $errors->has('account_lockout')) {
                $output .= "<h3 style=\"color: green;\">✅ LOCKOUT WORKING!</h3>";
                $output .= "Error message: <strong>" . $errors->first('account_lockout') . "</strong><br>";
            } else {
                $output .= "<h3 style=\"color: red;\">❌ LOCKOUT NOT WORKING</h3>";
                $output .= "No lockout error found in session<br>";
            }
        } else {
            $output .= "<h3 style=\"color: red;\">❌ UNEXPECTED RESULT</h3>";
            $output .= "Result type: " . get_class($result) . "<br>";
        }
    } catch (\Exception $e) {
        $output .= "<h3 style=\"color: red;\">❌ ERROR DURING TEST</h3>";
        $output .= "Error: " . $e->getMessage() . "<br>";
    }
    
    // Show session data
    $output .= "<h3>Session Data:</h3>";
    $sessionData = session()->all();
    $lockoutData = [];
    foreach ($sessionData as $key => $value) {
        if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
            $lockoutData[$key] = $value;
        }
    }
    $output .= "<pre>" . json_encode($lockoutData, JSON_PRETTY_PRINT) . "</pre>";
    
    return $output;
});

// Test timer accuracy
Route::get('/test-timer-accuracy', function() {
    // Create a test lockout with exact timing
    $lockoutTime = now()->addMinutes(3);
    session(['test_lockout_time' => $lockoutTime]);
    
    $output = "<h2>Timer Accuracy Test</h2>";
    $output .= "<p>Lockout created at: <strong>" . now()->toDateTimeString() . "</strong></p>";
    $output .= "<p>Lockout expires at: <strong>" . $lockoutTime->toDateTimeString() . "</strong></p>";
    
    // Calculate remaining time in different ways
    $remainingMinutes = $lockoutTime->diffInMinutes(now());
    $remainingSeconds = $lockoutTime->diffInSeconds(now());
    
    $output .= "<p>Remaining minutes (diffInMinutes): <strong>" . $remainingMinutes . "</strong></p>";
    $output .= "<p>Remaining seconds (diffInSeconds): <strong>" . $remainingSeconds . "</strong></p>";
    $output .= "<p>Remaining seconds converted to MM:SS: <strong>" . floor($remainingSeconds / 60) . ":" . str_pad($remainingSeconds % 60, 2, '0', STR_PAD_LEFT) . "</strong></p>";
    
    // Test the countdown in real-time
    $output .= "
    <h3>Real-time Countdown Test:</h3>
    <div id='countdown' style='font-size: 24px; font-weight: bold; color: red;'></div>
    
    <script>
    let remainingSeconds = " . $remainingSeconds . ";
    
    console.log('Timer test started with', remainingSeconds, 'seconds remaining');
    
    const countdownElement = document.getElementById('countdown');
    
    function updateCountdown() {
        if (remainingSeconds <= 0) {
            countdownElement.innerHTML = '⏰ TIMER EXPIRED!';
            countdownElement.style.color = 'green';
            return;
        }
        
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        const timeText = minutes + ':' + seconds.toString().padStart(2, '0');
        
        countdownElement.innerHTML = '⏱️ ' + timeText + ' remaining';
        remainingSeconds--;
    }
    
    // Update immediately
    updateCountdown();
    
    // Update every second
    const timer = setInterval(() => {
        updateCountdown();
        if (remainingSeconds < 0) {
            clearInterval(timer);
        }
    }, 1000);
    </script>
    ";
    
    return $output;
});

// Temporary debug route to clear superadmin login attempts
Route::get('/debug-clear-superadmin-attempts', function() {
    $sessionData = session()->all();
    $keysToForget = [];
    $foundKeys = [];
    
    foreach ($sessionData as $key => $value) {
        if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
            $keysToForget[] = $key;
            $foundKeys[] = $key;
        }
    }
    
    if (!empty($keysToForget)) {
        session()->forget($keysToForget);
    }
    
    return response()->json([
        'success' => true,
        'message' => 'All login attempts and lockouts cleared',
        'cleared_keys' => $foundKeys,
        'total_cleared' => count($keysToForget),
        'instructions' => 'You can now try logging in as superadmin again. This route will be removed after testing.'
    ]);
})->name('debug.clear.superadmin.attempts');

// Debug route to check superadmin credentials and permissions
Route::get('/debug-superadmin-auth', function() {
    $admin = \App\Models\Admin::where('username', 'superadmin')->first();
    
    if (!$admin) {
        return response()->json([
            'error' => 'Superadmin not found',
            'suggestion' => 'Run the SuperAdminSeeder: php artisan db:seed --class=SuperAdminSeeder'
        ]);
    }
    
    $testPassword = 'password123';
    $passwordCheck = \Hash::check($testPassword, $admin->password);
    
    // Check permissions
    $hasRole = $admin->hasRole('superadmin');
    $hasPermission = $admin->can('view-superadmin-dashboard');
    $allRoles = $admin->getRoleNames();
    $allPermissions = $admin->getAllPermissions()->pluck('name');
    
    return response()->json([
        'admin_found' => true,
        'username' => $admin->username,
        'role' => $admin->role,
        'is_superadmin' => $admin->isSuperAdmin(),
        'password_hash' => substr($admin->password, 0, 20) . '...',
        'test_password' => $testPassword,
        'password_matches' => $passwordCheck,
        'auth_guard' => 'admin',
        'current_auth_status' => auth('admin')->check(),
        'current_user' => auth('admin')->user() ? auth('admin')->user()->username : 'none',
        'has_superadmin_role' => $hasRole,
        'has_dashboard_permission' => $hasPermission,
        'all_roles' => $allRoles,
        'permission_count' => $allPermissions->count(),
        'sample_permissions' => $allPermissions->take(10)
    ]);
})->name('debug.superadmin.auth');

// Debug route to fix superadmin password
Route::get('/fix-superadmin-password', function() {
    $admin = \App\Models\Admin::where('username', 'superadmin')->first();
    
    if (!$admin) {
        return response()->json(['error' => 'Superadmin not found']);
    }
    
    $newPassword = 'password123';
    $admin->password = \Hash::make($newPassword);
    $admin->save();
    
    // Verify the password works
    $passwordCheck = \Hash::check($newPassword, $admin->password);
    
    return response()->json([
        'success' => true,
        'message' => 'Superadmin password updated',
        'username' => $admin->username,
        'new_password' => $newPassword,
        'password_verification' => $passwordCheck,
        'password_hash' => substr($admin->password, 0, 30) . '...'
    ]);
})->name('fix.superadmin.password');

// Debug route to check all session data and clear specific lockouts
Route::get('/debug-session-lockouts', function() {
    $sessionData = session()->all();
    $lockoutKeys = [];
    $attemptKeys = [];
    
    foreach ($sessionData as $key => $value) {
        if (strpos($key, 'lockout_time_') === 0) {
            $lockoutKeys[$key] = $value;
        }
        if (strpos($key, 'login_attempts_') === 0) {
            $attemptKeys[$key] = $value;
        }
    }
    
    return response()->json([
        'lockout_keys' => $lockoutKeys,
        'attempt_keys' => $attemptKeys,
        'total_lockouts' => count($lockoutKeys),
        'total_attempts' => count($attemptKeys)
    ]);
})->name('debug.session.lockouts');

// Route to clear specific user lockout by username
Route::get('/clear-user-lockout/{username}', function($username) {
    // Generate possible account identifiers for this username
    $loginTypes = ['superadmin', 'department-admin', 'office-admin', 'user', 'ms365'];
    $clearedKeys = [];
    
    foreach ($loginTypes as $loginType) {
        $identifier = $loginType . '_' . $username;
        $attemptsKey = 'login_attempts_' . md5($identifier);
        $lockoutKey = 'lockout_time_' . md5($identifier);
        
        if (session()->has($attemptsKey)) {
            session()->forget($attemptsKey);
            $clearedKeys[] = $attemptsKey;
        }
        
        if (session()->has($lockoutKey)) {
            session()->forget($lockoutKey);
            $clearedKeys[] = $lockoutKey;
        }
    }
    
    // Also try direct username without login type prefix
    $directAttemptsKey = 'login_attempts_' . md5($username);
    $directLockoutKey = 'lockout_time_' . md5($username);
    
    if (session()->has($directAttemptsKey)) {
        session()->forget($directAttemptsKey);
        $clearedKeys[] = $directAttemptsKey;
    }
    
    if (session()->has($directLockoutKey)) {
        session()->forget($directLockoutKey);
        $clearedKeys[] = $directLockoutKey;
    }
    
    return response()->json([
        'success' => true,
        'message' => "Cleared lockouts for username: {$username}",
        'username' => $username,
        'cleared_keys' => $clearedKeys,
        'total_cleared' => count($clearedKeys)
    ]);
})->name('clear.user.lockout');

// Route to completely clear all login-related session data and cache
Route::get('/force-clear-all-lockouts', function() {
    // Clear all session data
    $sessionData = session()->all();
    $clearedSessionKeys = [];
    
    foreach ($sessionData as $key => $value) {
        if (strpos($key, 'login_attempts_') === 0 || 
            strpos($key, 'lockout_time_') === 0 ||
            strpos($key, 'authenticated_accounts') === 0) {
            session()->forget($key);
            $clearedSessionKeys[] = $key;
        }
    }
    
    // Clear Laravel cache
    try {
        \Artisan::call('cache:clear');
        $cacheCleared = true;
    } catch (\Exception $e) {
        $cacheCleared = false;
    }
    
    // Regenerate session
    session()->regenerate(true);
    
    return response()->json([
        'success' => true,
        'message' => 'All login lockouts and session data cleared',
        'cleared_session_keys' => $clearedSessionKeys,
        'total_session_cleared' => count($clearedSessionKeys),
        'cache_cleared' => $cacheCleared,
        'session_regenerated' => true,
        'instructions' => 'All lockouts should now be removed from the login form'
    ]);
})->name('force.clear.all.lockouts');

// Route to check what locked accounts are currently being shown
Route::get('/check-locked-accounts', function() {
    $unifiedController = new \App\Http\Controllers\UnifiedAuthController();
    
    // Use reflection to access the private method
    $reflection = new \ReflectionClass($unifiedController);
    $method = $reflection->getMethod('getLockedAccounts');
    $method->setAccessible(true);
    
    $lockedAccounts = $method->invoke($unifiedController);
    
    return response()->json([
        'locked_accounts' => $lockedAccounts,
        'total_locked' => count($lockedAccounts),
        'message' => count($lockedAccounts) > 0 ? 'There are locked accounts' : 'No locked accounts found'
    ]);
})->name('check.locked.accounts');

// Route to completely flush all session data and force clear everything
Route::get('/nuclear-clear-lockouts', function() {
    // Get all session keys
    $allSessionKeys = array_keys(session()->all());
    
    // Flush entire session
    session()->flush();
    
    // Clear all caches
    try {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        $cachesCleared = true;
    } catch (\Exception $e) {
        $cachesCleared = false;
    }
    
    // Start a completely new session
    session()->regenerate(true);
    
    // Also clear any potential file-based session storage
    try {
        $sessionPath = storage_path('framework/sessions');
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        $sessionFilesCleared = true;
    } catch (\Exception $e) {
        $sessionFilesCleared = false;
    }
    
    return response()->json([
        'success' => true,
        'message' => 'NUCLEAR OPTION: All session data and caches completely cleared',
        'session_keys_cleared' => count($allSessionKeys),
        'caches_cleared' => $cachesCleared,
        'session_files_cleared' => $sessionFilesCleared,
        'new_session_started' => true,
        'instructions' => 'All lockouts should now be completely removed. Try refreshing the login page.'
    ]);
})->name('nuclear.clear.lockouts');

// Route to test the login form without any session data
Route::get('/test-clean-login', function() {
    // Completely fresh request to the login form
    $request = new \Illuminate\Http\Request();
    $unifiedController = new \App\Http\Controllers\UnifiedAuthController();
    
    try {
        $response = $unifiedController->showLoginForm($request);
        
        // Check what data is being passed to the view
        $viewData = $response->getData();
        
        return response()->json([
            'success' => true,
            'view_data' => [
                'locked_accounts' => $viewData['lockedAccounts'] ?? 'not_set',
                'authenticated_accounts' => $viewData['authenticatedAccounts'] ?? 'not_set',
                'preselected_type' => $viewData['preselectedType'] ?? 'not_set'
            ],
            'locked_accounts_count' => is_array($viewData['lockedAccounts'] ?? []) ? count($viewData['lockedAccounts']) : 0,
            'message' => 'Login form data retrieved successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test.clean.login');

// Quick login route for testing (REMOVE IN PRODUCTION)
Route::get('/quick-superadmin-login', function() {
    $admin = \App\Models\Admin::where('username', 'superadmin')->first();
    
    if (!$admin) {
        return response()->json(['error' => 'Superadmin not found']);
    }
    
    // Log in the superadmin
    \Auth::guard('admin')->login($admin);
    
    return response()->json([
        'success' => true,
        'message' => 'Superadmin logged in successfully',
        'redirect_url' => route('superadmin.dashboard'),
        'auth_status' => \Auth::guard('admin')->check(),
        'user' => \Auth::guard('admin')->user()->username
    ]);
})->name('quick.superadmin.login');

// Debug route to check all admins in the database
Route::get('/debug-all-admins', function() {
    $admins = \App\Models\Admin::all();
    
    $adminData = $admins->map(function($admin) {
        return [
            'id' => $admin->id,
            'username' => $admin->username,
            'role' => $admin->role,
            'password_hash' => substr($admin->password, 0, 20) . '...',
            'password_test' => \Hash::check('password123', $admin->password),
            'has_superadmin_role' => $admin->hasRole('superadmin'),
            'has_department_role' => $admin->hasRole('department_admin'),
            'all_roles' => $admin->getRoleNames(),
            'created_at' => $admin->created_at,
            'updated_at' => $admin->updated_at
        ];
    });
    
    return response()->json([
        'total_admins' => $admins->count(),
        'admins' => $adminData,
        'superadmin_count' => $admins->where('role', 'superadmin')->count(),
        'department_admin_count' => $admins->where('role', 'department_admin')->count()
    ]);
})->name('debug.all.admins');

// Test route to simulate creating a department admin and check superadmin
Route::get('/test-create-department-admin', function() {
    // First, check superadmin before creating department admin
    $superadmin = \App\Models\Admin::where('username', 'superadmin')->first();
    $beforeData = [
        'password_hash' => substr($superadmin->password, 0, 20) . '...',
        'password_test' => \Hash::check('password123', $superadmin->password),
        'has_superadmin_role' => $superadmin->hasRole('superadmin'),
        'all_roles' => $superadmin->getRoleNames()
    ];
    
    // Create a test department admin
    $deptAdmin = \App\Models\Admin::create([
        'username' => 'test_dept_admin',
        'password' => \Hash::make('password123'),
        'role' => 'department_admin',
        'department' => 'BSIT',
    ]);
    
    // Assign role to the new department admin
    $deptAdmin->assignRole('department_admin');
    
    // Check superadmin after creating department admin
    $superadmin->refresh(); // Reload from database
    $afterData = [
        'password_hash' => substr($superadmin->password, 0, 20) . '...',
        'password_test' => \Hash::check('password123', $superadmin->password),
        'has_superadmin_role' => $superadmin->hasRole('superadmin'),
        'all_roles' => $superadmin->getRoleNames()
    ];
    
    return response()->json([
        'success' => true,
        'message' => 'Department admin created and tested',
        'department_admin_created' => [
            'id' => $deptAdmin->id,
            'username' => $deptAdmin->username,
            'role' => $deptAdmin->role,
            'department' => $deptAdmin->department
        ],
        'superadmin_before' => $beforeData,
        'superadmin_after' => $afterData,
        'password_changed' => $beforeData['password_hash'] !== $afterData['password_hash'],
        'roles_changed' => $beforeData['all_roles'] !== $afterData['all_roles']
    ]);
})->name('test.create.department.admin');

// Route to fix superadmin credentials after department admin creation
Route::get('/fix-superadmin-after-dept-admin', function() {
    // Run the improved SuperAdminSeeder
    \Artisan::call('db:seed', ['--class' => 'SuperAdminSeeder']);
    
    // Run the RolePermissionSeeder to ensure roles are assigned
    \Artisan::call('db:seed', ['--class' => 'RolePermissionSeeder']);
    
    // Check the final state
    $superadmin = \App\Models\Admin::where('username', 'superadmin')->first();
    
    return response()->json([
        'success' => true,
        'message' => 'Superadmin fixed after department admin creation',
        'superadmin_status' => [
            'exists' => $superadmin ? true : false,
            'username' => $superadmin ? $superadmin->username : 'not found',
            'role' => $superadmin ? $superadmin->role : 'not found',
            'password_test' => $superadmin ? \Hash::check('password123', $superadmin->password) : false,
            'has_superadmin_role' => $superadmin ? $superadmin->hasRole('superadmin') : false,
            'permission_count' => $superadmin ? $superadmin->getAllPermissions()->count() : 0
        ],
        'seeder_output' => \Artisan::output()
    ]);
})->name('fix.superadmin.after.dept.admin');

// Debug route to test logout and re-login process
Route::get('/debug-logout-login-cycle', function() {
    // First, ensure superadmin is logged in
    $admin = \App\Models\Admin::where('username', 'superadmin')->first();
    \Auth::guard('admin')->login($admin);
    
    $beforeLogout = [
        'auth_status' => \Auth::guard('admin')->check(),
        'user' => \Auth::guard('admin')->user() ? \Auth::guard('admin')->user()->username : 'none',
        'session_keys' => array_keys(session()->all())
    ];
    
    // Simulate logout
    \Auth::guard('admin')->logout();
    session()->invalidate();
    session()->regenerateToken();
    
    $afterLogout = [
        'auth_status' => \Auth::guard('admin')->check(),
        'user' => \Auth::guard('admin')->user() ? \Auth::guard('admin')->user()->username : 'none',
        'session_keys' => array_keys(session()->all())
    ];
    
    // Try to login again
    $credentials = ['username' => 'superadmin', 'password' => 'password123'];
    $loginAttempt = \Auth::guard('admin')->attempt($credentials);
    
    $afterReLogin = [
        'auth_status' => \Auth::guard('admin')->check(),
        'user' => \Auth::guard('admin')->user() ? \Auth::guard('admin')->user()->username : 'none',
        'login_attempt_result' => $loginAttempt,
        'password_check' => \Hash::check('password123', $admin->password)
    ];
    
    return response()->json([
        'test_completed' => true,
        'before_logout' => $beforeLogout,
        'after_logout' => $afterLogout,
        'after_re_login' => $afterReLogin,
        'admin_data' => [
            'id' => $admin->id,
            'username' => $admin->username,
            'role' => $admin->role,
            'password_hash' => substr($admin->password, 0, 20) . '...'
        ]
    ]);
})->name('debug.logout.login.cycle');

// Test route to verify logout fix
Route::get('/test-logout-fix', function() {
    // Login as superadmin first
    $admin = \App\Models\Admin::where('username', 'superadmin')->first();
    \Auth::guard('admin')->login($admin);
    
    $beforeLogout = [
        'auth_status' => \Auth::guard('admin')->check(),
        'user' => \Auth::guard('admin')->user()->username,
        'session_id' => session()->getId()
    ];
    
    // Use the actual UnifiedAuthController logout method
    $request = new \Illuminate\Http\Request();
    $request->setSession(session());
    
    $unifiedController = new \App\Http\Controllers\UnifiedAuthController();
    $logoutResult = $unifiedController->logout($request);
    
    $afterLogout = [
        'auth_status' => \Auth::guard('admin')->check(),
        'user' => \Auth::guard('admin')->user() ? \Auth::guard('admin')->user()->username : 'none',
        'session_id' => session()->getId(),
        'logout_result_type' => get_class($logoutResult)
    ];
    
    // Now try to login again using UnifiedAuthController
    $loginRequest = new \Illuminate\Http\Request();
    $loginRequest->merge([
        'login_type' => 'superadmin',
        'username' => 'superadmin',
        'password' => 'password123'
    ]);
    $loginRequest->setSession(session());
    
    $loginResult = $unifiedController->login($loginRequest);
    
    $afterReLogin = [
        'auth_status' => \Auth::guard('admin')->check(),
        'user' => \Auth::guard('admin')->user() ? \Auth::guard('admin')->user()->username : 'none',
        'login_result_type' => get_class($loginResult),
        'is_redirect' => $loginResult instanceof \Illuminate\Http\RedirectResponse,
        'redirect_url' => $loginResult instanceof \Illuminate\Http\RedirectResponse ? $loginResult->getTargetUrl() : 'not_redirect'
    ];
    
    return response()->json([
        'test_name' => 'Logout Fix Test',
        'before_logout' => $beforeLogout,
        'after_logout' => $afterLogout,
        'after_re_login' => $afterReLogin,
        'success' => $afterReLogin['auth_status'] && strpos($afterReLogin['redirect_url'], 'superadmin/dashboard') !== false
    ]);
})->name('test.logout.fix');

// Emergency route to restore superadmin roles and permissions
Route::get('/emergency-restore-superadmin', function() {
    try {
        // First, check current superadmin status
        $superadmin = \App\Models\Admin::where('username', 'superadmin')->first();
        
        if (!$superadmin) {
            return response()->json([
                'error' => 'Superadmin user not found',
                'action' => 'Run SuperAdminSeeder first'
            ]);
        }
        
        $beforeRestore = [
            'username' => $superadmin->username,
            'role' => $superadmin->role,
            'has_superadmin_role' => $superadmin->hasRole('superadmin'),
            'permission_count' => $superadmin->getAllPermissions()->count(),
            'all_roles' => $superadmin->getRoleNames()->toArray()
        ];
        
        // Clear all existing roles for this admin
        $superadmin->syncRoles([]);
        
        // Ensure the superadmin role exists
        $superadminRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'admin'
        ]);
        
        // Assign superadmin role
        $superadmin->assignRole('superadmin');
        
        // Run the RolePermissionSeeder to ensure all permissions are assigned
        \Artisan::call('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        // Refresh the model to get updated data
        $superadmin->refresh();
        
        $afterRestore = [
            'username' => $superadmin->username,
            'role' => $superadmin->role,
            'has_superadmin_role' => $superadmin->hasRole('superadmin'),
            'permission_count' => $superadmin->getAllPermissions()->count(),
            'all_roles' => $superadmin->getRoleNames()->toArray(),
            'has_dashboard_permission' => $superadmin->can('view-superadmin-dashboard')
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Superadmin roles and permissions restored successfully!',
            'before_restore' => $beforeRestore,
            'after_restore' => $afterRestore,
            'seeder_output' => \Artisan::output(),
            'instructions' => [
                '1. Try accessing the superadmin dashboard now',
                '2. If still getting 403, clear browser cache and try again',
                '3. Make sure you are logged in as superadmin'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('emergency.restore.superadmin');

// Verification route to check superadmin access
Route::get('/verify-superadmin-access', function() {
    $currentUser = \Auth::guard('admin')->user();
    
    if (!$currentUser) {
        return response()->json([
            'authenticated' => false,
            'message' => 'Not logged in',
            'action' => 'Please log in first'
        ]);
    }
    
    return response()->json([
        'authenticated' => true,
        'user' => [
            'id' => $currentUser->id,
            'username' => $currentUser->username,
            'role' => $currentUser->role
        ],
        'permissions' => [
            'has_superadmin_role' => $currentUser->hasRole('superadmin'),
            'has_dashboard_permission' => $currentUser->can('view-superadmin-dashboard'),
            'total_permissions' => $currentUser->getAllPermissions()->count()
        ],
        'dashboard_access' => [
            'should_work' => $currentUser->hasRole('superadmin') && $currentUser->can('view-superadmin-dashboard'),
            'dashboard_url' => route('superadmin.dashboard')
        ],
        'message' => 'Superadmin access verification complete'
    ]);
})->name('verify.superadmin.access');

// Emergency route to fix user roles and permissions
Route::get('/emergency-fix-user-roles', function() {
    try {
        // First, ensure roles and permissions exist
        \Artisan::call('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        // Get all users
        $users = \App\Models\User::all();
        $fixedUsers = [];
        $errors = [];
        
        foreach ($users as $user) {
            try {
                // Clear existing roles first
                $user->syncRoles([]);
                
                // Assign role based on user's role field
                if ($user->role === 'student') {
                    $user->assignRole('student');
                } elseif ($user->role === 'faculty') {
                    $user->assignRole('faculty');
                } else {
                    // Default to student role
                    $user->assignRole('student');
                }
                
                $fixedUsers[] = [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'role' => $user->role,
                    'assigned_roles' => $user->getRoleNames(),
                    'has_dashboard_permission' => $user->can('view-user-dashboard'),
                    'permission_count' => $user->getAllPermissions()->count()
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'User roles and permissions fixed successfully!',
            'total_users' => $users->count(),
            'fixed_users' => $fixedUsers,
            'errors' => $errors,
            'seeder_output' => \Artisan::output(),
            'instructions' => [
                '1. All users should now have proper roles and permissions',
                '2. Try accessing the user dashboard now',
                '3. New users will automatically get roles assigned'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('emergency.fix.user.roles');

// Test route to verify automatic role assignment for new users
Route::get('/test-new-user-role-assignment', function() {
    try {
        // Create a test user
        $testUser = \App\Models\User::create([
            'first_name' => 'Test',
            'middle_name' => 'Auto',
            'surname' => 'User',
            'ms365_account' => 'test.auto.user@example.com',
            'role' => 'student',
            'department' => 'BSIT',
            'year_level' => '1st Year',
            'password' => \Hash::make('password123'),
            'email_verified_at' => now()
        ]);
        
        // Check if roles were automatically assigned
        $userInfo = [
            'id' => $testUser->id,
            'name' => $testUser->full_name,
            'role' => $testUser->role,
            'assigned_roles' => $testUser->getRoleNames(),
            'has_dashboard_permission' => $testUser->can('view-user-dashboard'),
            'permission_count' => $testUser->getAllPermissions()->count(),
            'all_permissions' => $testUser->getAllPermissions()->pluck('name')->take(10)
        ];
        
        // Clean up - delete the test user
        $testUser->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Automatic role assignment test completed successfully!',
            'test_user_info' => $userInfo,
            'automatic_assignment_working' => $userInfo['has_dashboard_permission'] && count($userInfo['assigned_roles']) > 0,
            'conclusion' => $userInfo['has_dashboard_permission'] ? 
                'New users will automatically get proper roles and dashboard access!' : 
                'There might still be an issue with automatic role assignment.'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test.new.user.role.assignment');

// Debug route to check profile picture issues
Route::get('/debug-profile-picture', function() {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    // Check storage directory
    $storagePath = storage_path('app/public/profile_pictures');
    $publicPath = public_path('storage/profile_pictures');
    
    return response()->json([
        'user_info' => [
            'id' => $user->id,
            'name' => $user->full_name,
            'profile_picture_field' => $user->profile_picture,
            'has_profile_picture' => $user->hasProfilePicture,
            'profile_picture_url' => $user->profilePictureUrl,
        ],
        'storage_info' => [
            'storage_path_exists' => is_dir($storagePath),
            'public_path_exists' => is_dir($publicPath),
            'storage_path' => $storagePath,
            'public_path' => $publicPath,
            'storage_link_exists' => is_link(public_path('storage')),
        ],
        'files_in_storage' => is_dir($storagePath) ? scandir($storagePath) : 'Directory does not exist',
        'files_in_public' => is_dir($publicPath) ? scandir($publicPath) : 'Directory does not exist',
    ]);
})->middleware('auth')->name('debug.profile.picture');

// Fix profile picture storage issues
Route::get('/fix-profile-picture-storage', function() {
    try {
        // Create necessary directories
        $storagePath = storage_path('app/public/profile_pictures');
        $publicPath = public_path('storage');
        
        // Create storage directory if it doesn't exist
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        // Ensure storage link exists
        if (!is_link($publicPath)) {
            \Artisan::call('storage:link');
        }
        
        // Set proper permissions
        chmod($storagePath, 0755);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile picture storage fixed successfully!',
            'actions_taken' => [
                'created_storage_directory' => is_dir($storagePath),
                'storage_link_created' => is_link($publicPath),
                'permissions_set' => '0755',
            ],
            'storage_path' => $storagePath,
            'public_link' => $publicPath,
            'test_url' => url('/debug-profile-picture'),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('fix.profile.picture.storage');

// Test profile picture functionality
Route::get('/test-profile-picture-functionality', function() {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    // Create a test image file
    $testImagePath = storage_path('app/public/profile_pictures/test_image.png');
    
    // Create a simple 100x100 PNG image for testing
    $image = imagecreate(100, 100);
    $backgroundColor = imagecolorallocate($image, 255, 255, 255); // White background
    $textColor = imagecolorallocate($image, 0, 0, 0); // Black text
    imagestring($image, 5, 30, 40, 'TEST', $textColor);
    
    // Ensure directory exists
    $directory = dirname($testImagePath);
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    imagepng($image, $testImagePath);
    imagedestroy($image);
    
    // Test if the image can be accessed via URL
    $testUrl = asset('storage/profile_pictures/test_image.png');
    
    return response()->json([
        'success' => true,
        'message' => 'Profile picture functionality test completed',
        'test_results' => [
            'user_authenticated' => true,
            'user_id' => $user->id,
            'current_profile_picture' => $user->profile_picture,
            'current_profile_url' => $user->profilePictureUrl,
            'has_profile_picture' => $user->hasProfilePicture,
            'test_image_created' => file_exists($testImagePath),
            'test_image_url' => $testUrl,
            'test_image_size' => file_exists($testImagePath) ? filesize($testImagePath) : 0,
            'storage_directory_writable' => is_writable(dirname($testImagePath)),
        ],
        'instructions' => [
            '1. Try uploading a profile picture through the dashboard',
            '2. Check the browser console for any JavaScript errors',
            '3. Check if the image appears after upload',
            '4. Test image URL: ' . $testUrl
        ]
    ]);
})->middleware('auth')->name('test.profile.picture.functionality');

// Gmail Authentication Routes
Route::get('/signup', [UnifiedAuthController::class, 'showSignupForm'])->name('gmail.signup');
Route::post('/signup', [UnifiedAuthController::class, 'sendRegistrationLink'])->name('gmail.signup.send');
Route::get('/register', [UnifiedAuthController::class, 'showRegistrationForm'])->name('gmail.register.form');
Route::post('/register', [UnifiedAuthController::class, 'completeRegistration'])->name('gmail.register.complete');

// MS365 Authentication Routes
Route::get('/ms365/signup', [MS365OAuthController::class, 'showSignupForm'])->name('ms365.signup');
Route::post('/ms365/signup', [MS365OAuthController::class, 'sendSignupLink'])->name('ms365.signup.send');
Route::get('/ms365/register', [MS365OAuthController::class, 'showRegisterForm'])->name('ms365.register.form');
Route::post('/ms365/register', [MS365OAuthController::class, 'handleRegister'])->name('ms365.register.complete');

// MS365 OAuth2 Routes
Route::get('/auth/ms365/redirect', [MS365OAuthController::class, 'redirectToProvider'])->name('ms365.oauth.redirect');
Route::get('/auth/ms365/callback', [MS365OAuthController::class, 'handleProviderCallback'])->name('ms365.oauth.callback');
Route::get('/register/{token}', [MS365OAuthController::class, 'showRegisterForm'])->name('ms365.register.form.token');
Route::post('/register', [MS365OAuthController::class, 'handleRegister'])->name('ms365.register.complete.token');

Route::post('/logout', [UnifiedAuthController::class, 'logout'])->name('logout');


// Test route for image debugging (remove in production)
Route::get('/test-images', function () {
    return view('test-images');
})->name('test.images');

// Test route for chatbot comparison (remove in production)
Route::get('/test-chatbots', function () {
    return view('test-chatbots');
})->name('test.chatbots');

// Public content routes (no authentication required)
Route::get('/announcements', [PublicContentController::class, 'announcements'])->name('public.announcements.index');
Route::get('/announcements/{announcement}', [PublicContentController::class, 'showAnnouncement'])->name('public.announcements.show');
Route::get('/events', [PublicContentController::class, 'events'])->name('public.events.index');
Route::get('/events/{event}', [PublicContentController::class, 'showEvent'])->name('public.events.show');
Route::get('/news', [PublicContentController::class, 'news'])->name('public.news.index');
Route::get('/news/{news}', [PublicContentController::class, 'showNews'])->name('public.news.show');






// Admin Routes
Route::prefix('admin')->group(function () {
    // Auth routes
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('admin.register');
    Route::post('register', [AdminAuthController::class, 'register']);
    
    // Admin registration from email link (signed routes for security)
    Route::get('register-form', [SuperAdminController::class, 'showAdminRegistrationForm'])->name('admin.register.form')->middleware('signed');
    Route::post('register-complete', [SuperAdminController::class, 'completeAdminRegistration'])->name('admin.register.complete');
    
    // Debug routes for department admin registration troubleshooting
    Route::get('debug-registration/{token}', function($token) {
        $cachedData = \Cache::get('admin_registration_' . $token);
        return response()->json([
            'token' => $token,
            'cached_data' => $cachedData,
            'cache_exists' => $cachedData !== null,
            'current_timestamp' => now()->timestamp,
            'cache_key' => 'admin_registration_' . $token,
            'used_status' => isset($cachedData['used']) ? $cachedData['used'] : 'not_set',
            'viewed_status' => isset($cachedData['viewed']) ? $cachedData['viewed'] : 'not_set',
            'token_age_seconds' => $cachedData ? (now()->timestamp - $cachedData['timestamp']) : 'no_cache',
            'is_expired' => $cachedData ? ((now()->timestamp - $cachedData['timestamp']) > 1800) : 'no_cache'
        ]);
    })->name('admin.debug-registration');

    Route::get('clear-registration/{token}', function($token) {
        $cachedData = \Cache::get('admin_registration_' . $token);
        \Cache::forget('admin_registration_' . $token);
        
        // Also clear any potential corrupted cache variations
        \Cache::forget('admin_registration_' . strtolower($token));
        \Cache::forget('admin_registration_' . strtoupper($token));
        
        return response()->json([
            'message' => 'Department admin registration cache cleared',
            'token' => $token,
            'previous_data' => $cachedData,
            'cleared_at' => now()->toDateTimeString()
        ]);
    })->name('admin.clear-registration');

    Route::get('clear-all-registrations', function() {
        // Clear all admin registration caches (emergency cleanup)
        $cleared = 0;
        $cacheKeys = \Cache::getRedis()->keys('*admin_registration_*');
        foreach ($cacheKeys as $key) {
            \Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
            $cleared++;
        }
        return response()->json([
            'message' => 'All department admin registration caches cleared',
            'cleared_count' => $cleared
        ]);
    })->name('admin.clear-all-registrations');
    
    // Protected routes - Only department admins can access these
    Route::middleware(['auth:admin', 'can:view-admin-dashboard'])->group(function () {
        Route::get('dashboard', [DepartmentAdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        
        // Content management routes (to be implemented)
        // Announcements CRUD
        Route::resource('announcements', AnnouncementController::class);

        // Events CRUD  
        Route::resource('events', EventController::class);

        // News CRUD
        Route::resource('news', NewsController::class);
        
        // Faculty management routes
        Route::get('faculty', [AdminFacultyController::class, 'index'])->name('admin.faculty.index');
        Route::get('faculty/{faculty}/edit', [AdminFacultyController::class, 'edit'])->name('admin.faculty.edit');
        Route::put('faculty/{faculty}', [AdminFacultyController::class, 'update'])->name('admin.faculty.update');
        Route::delete('faculty/{faculty}', [AdminFacultyController::class, 'destroy'])->name('admin.faculty.delete');
        
        // Student management routes
        Route::get('students', [AdminStudentController::class, 'index'])->name('admin.students');
        Route::get('students/{student}/edit', [AdminStudentController::class, 'edit'])->name('admin.students.edit');
        Route::put('students/{student}', [AdminStudentController::class, 'update'])->name('admin.students.update');
        Route::delete('students/{student}', [AdminStudentController::class, 'destroy'])->name('admin.students.delete');
    });
});

// SuperAdmin Routes
Route::prefix('superadmin')->group(function () {
    // Redirect to unified login form - no dedicated superadmin login
    Route::get('login', function() {
        return redirect()->route('login')->with('info', 'Please use the unified login form and select "Super Admin" as login type.');
    })->name('superadmin.login');
    
    // Only logout route needed
    Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('superadmin.logout');

    // Protected SuperAdmin routes
    Route::middleware([\App\Http\Middleware\SuperAdminAuth::class])->group(function () {
        Route::get('dashboard', [SuperAdminDashboardController::class, 'index'])->name('superadmin.dashboard');
        
        // Admin access logs route
        Route::get('admin-access', [App\Http\Controllers\AdminAccessController::class, 'index'])->name('superadmin.admin-access');
        Route::delete('admin-access/{id}', [App\Http\Controllers\AdminAccessController::class, 'destroy'])->name('superadmin.admin-access.delete');

        // Admin management routes
        Route::resource('admins', SuperAdminController::class, [
            'as' => 'superadmin'
        ]);

        // Department Admin management routes
        Route::get('department-admins/create', [SuperAdminController::class, 'createDepartmentAdmin'])->name('superadmin.department-admins.create');
        Route::post('department-admins', [SuperAdminController::class, 'storeDepartmentAdmin'])->name('superadmin.department-admins.store');
        Route::get('department-admins', [SuperAdminController::class, 'departmentAdmins'])->name('superadmin.department-admins.index');

        // Office Admin management routes
        Route::resource('office-admins', OfficeAdminController::class, [
            'as' => 'superadmin'
        ]);

        // Content management routes (inherited from admin)
        Route::resource('announcements', AnnouncementController::class, [
            'as' => 'superadmin'
        ]);
        
        // Modal routes for announcements
        Route::get('announcements/{announcement}/modal-show', [AnnouncementController::class, 'showModal'])->name('superadmin.announcements.modal-show');
        Route::get('announcements/{announcement}/modal-edit', [AnnouncementController::class, 'editModal'])->name('superadmin.announcements.modal-edit');
        
        Route::resource('events', EventController::class, [
            'as' => 'superadmin'
        ]);
        Route::resource('news', NewsController::class, [
            'as' => 'superadmin'
        ]);
        Route::get('news/{news}/show-data', [NewsController::class, 'showData'])->name('superadmin.news.show-data');

        // Faculty management routes
        Route::get('faculty', [AdminFacultyController::class, 'index'])->name('superadmin.faculty.index');
        Route::get('faculty/create', [AdminFacultyController::class, 'create'])->name('superadmin.faculty.create');
        Route::post('faculty', [AdminFacultyController::class, 'store'])->name('superadmin.faculty.store');
        Route::get('faculty/{faculty}', [AdminFacultyController::class, 'show'])->name('superadmin.faculty.show');
        Route::get('faculty/{faculty}/edit', [AdminFacultyController::class, 'edit'])->name('superadmin.faculty.edit');
        Route::put('faculty/{faculty}', [AdminFacultyController::class, 'update'])->name('superadmin.faculty.update');
        Route::delete('faculty/{faculty}', [AdminFacultyController::class, 'destroy'])->name('superadmin.faculty.destroy');

        // Student management routes
        Route::get('students', [AdminStudentController::class, 'index'])->name('superadmin.students.index');
        Route::get('students/create', [AdminStudentController::class, 'create'])->name('superadmin.students.create');
        Route::post('students', [AdminStudentController::class, 'store'])->name('superadmin.students.store');
        Route::get('students/{student}', [AdminStudentController::class, 'show'])->name('superadmin.students.show');
        Route::get('students/{student}/edit', [AdminStudentController::class, 'edit'])->name('superadmin.students.edit');
        Route::put('students/{student}', [AdminStudentController::class, 'update'])->name('superadmin.students.update');
        Route::delete('students/{student}', [AdminStudentController::class, 'destroy'])->name('superadmin.students.destroy');

        // Admin profile picture management routes
        Route::post('admins/{admin}/upload-picture', [SuperAdminProfileController::class, 'uploadProfilePicture'])->name('superadmin.admins.upload-picture');
        Route::delete('admins/{admin}/remove-picture', [SuperAdminProfileController::class, 'removeProfilePicture'])->name('superadmin.admins.remove-picture');
        
        // Office Admin profile picture management routes
        Route::post('office-admins/{admin}/upload-picture', [SuperAdminProfileController::class, 'uploadProfilePicture'])->name('superadmin.office-admins.upload-picture');
        Route::delete('office-admins/{admin}/remove-picture', [SuperAdminProfileController::class, 'removeProfilePicture'])->name('superadmin.office-admins.remove-picture');
    });
});

// Department Admin Routes
Route::prefix('department-admin')->group(function () {
    // Auth routes (dedicated department admin auth)
    Route::get('login', [DepartmentAdminAuthController::class, 'showLoginForm'])->name('department-admin.login');
    Route::post('login', [DepartmentAdminAuthController::class, 'login']);
    Route::post('logout', [DepartmentAdminAuthController::class, 'logout'])->name('department-admin.logout');

    // Debug route for department admin authentication
    Route::get('debug-auth', function() {
        return response()->json([
            'authenticated' => Auth::guard('admin')->check(),
            'user' => Auth::guard('admin')->user(),
            'is_department_admin' => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->isDepartmentAdmin() : false,
            'role' => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->role : null,
            'timestamp' => now()
        ]);
    })->name('department-admin.debug-auth');

    // Protected Department Admin routes
    Route::middleware([\App\Http\Middleware\DepartmentAdminAuth::class])->group(function () {
        Route::get('dashboard', [DepartmentAdminDashboardController::class, 'index'])->name('department-admin.dashboard');

        // Content management routes (limited to department admin's content)
        Route::resource('announcements', AnnouncementController::class, [
            'as' => 'department-admin'
        ]);
        Route::resource('events', EventController::class, [
            'as' => 'department-admin'
        ]);
        Route::resource('news', NewsController::class, [
            'as' => 'department-admin'
        ]);
    });
});

// Office Admin Routes
Route::prefix('office-admin')->name('office-admin.')->group(function () {
    Route::get('login', [OfficeAdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [OfficeAdminAuthController::class, 'login']);

    // Office admin registration from email link (signed routes for security)
    Route::get('register-form', [OfficeAdminController::class, 'showOfficeAdminRegistrationForm'])->name('register.form')->middleware('signed');
    Route::post('register-complete', [OfficeAdminController::class, 'completeOfficeAdminRegistration'])->name('register.complete');

    // Debug route to test authentication
    Route::get('debug-auth', function() {
        $admin = Auth::guard('admin')->user();
        return response()->json([
            'authenticated' => Auth::guard('admin')->check(),
            'admin' => $admin ? [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role,
                'isOfficeAdmin' => $admin->isOfficeAdmin()
            ] : null
        ]);
    })->name('debug-auth');

    // Debug routes for registration troubleshooting
    Route::get('debug-registration/{token}', function($token) {
        $cachedData = \Cache::get('office_admin_registration_' . $token);
        return response()->json([
            'token' => $token,
            'cached_data' => $cachedData,
            'cache_exists' => $cachedData !== null,
            'timestamp' => now()->timestamp,
            'cache_key' => 'office_admin_registration_' . $token
        ]);
    })->name('debug-registration');

    Route::get('clear-registration/{token}', function($token) {
        $cachedData = \Cache::get('office_admin_registration_' . $token);
        \Cache::forget('office_admin_registration_' . $token);
        return response()->json([
            'message' => 'Registration cache cleared',
            'token' => $token,
            'previous_data' => $cachedData,
            'cleared_at' => now()->timestamp
        ]);
    })->name('clear-registration');

    // Protected Office Admin routes
    Route::middleware([\App\Http\Middleware\OfficeAdminAuth::class])->group(function () {
        Route::get('dashboard', [OfficeAdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [OfficeAdminAuthController::class, 'logout'])->name('logout');

        // Content management routes (limited to office admin's content)
        Route::resource('announcements', AnnouncementController::class);
        Route::resource('events', EventController::class);
        Route::resource('news', NewsController::class);
    });
});

// User Routes
Route::prefix('user')->group(function () {
    // Auth routes
    Route::get('login', [UserAuthController::class, 'showLoginForm'])->name('user.login');
    Route::post('login', [UserAuthController::class, 'login']);
    Route::get('register', [UserAuthController::class, 'showRegisterForm'])->name('user.register');
    Route::post('register', [UserAuthController::class, 'register']);
    
    // Protected routes
    Route::middleware(['auth', 'password.expiration', 'can:view-user-dashboard'])->group(function () {
        Route::get('dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
        Route::post('logout', [UserAuthController::class, 'logout'])->name('user.logout');

        // Notification routes
        Route::get('notifications', [NotificationController::class, 'index'])->name('user.notifications.index');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('user.notifications.read');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('user.notifications.mark-all-read');
        Route::get('notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('user.notifications.unread-count');
        Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('user.notifications.destroy');

        // Content routes for notifications
        Route::get('content/announcement/{id}', [UserDashboardController::class, 'getAnnouncement'])->name('user.content.announcement');
        Route::get('content/event/{id}', [UserDashboardController::class, 'getEvent'])->name('user.content.event');
        Route::get('content/news/{id}', [UserDashboardController::class, 'getNews'])->name('user.content.news');

        // Comment routes
        Route::get('content/{type}/{id}/comments', [CommentController::class, 'getComments'])->name('comments.get');
        Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
        Route::put('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        // Profile routes
        Route::post('profile/update', [\App\Http\Controllers\UserProfileController::class, 'updateProfile'])->name('user.profile.update');
        Route::post('profile/upload-picture', [\App\Http\Controllers\UserProfileController::class, 'uploadProfilePicture'])->name('user.profile.upload-picture');
        Route::delete('profile/remove-picture', [\App\Http\Controllers\UserProfileController::class, 'removeProfilePicture'])->name('user.profile.remove-picture');

        // Test route for comment functionality
        Route::get('comments/test', function() {
            $user = auth()->user();
            $announcement = \App\Models\Announcement::find(60);

            return response()->json([
                'success' => true,
                'message' => 'Comment routes are working',
                'user' => $user ? $user->name : 'Not authenticated',
                'user_id' => $user ? $user->id : null,
                'announcement_exists' => $announcement ? true : false,
                'announcement_published' => $announcement ? $announcement->is_published : null,
                'csrf_token' => csrf_token(),
                'can_comment' => $user && $announcement && $announcement->is_published && $announcement->isVisibleToUser($user)
            ]);
        });

        // Debug route to check comment isolation
        Route::get('comments/debug/{type}/{id}', function($type, $id) {
            $user = auth()->user();
            $commentableModel = null;
            
            switch ($type) {
                case 'announcement':
                    $commentableModel = \App\Models\Announcement::find($id);
                    break;
                case 'event':
                    $commentableModel = \App\Models\Event::find($id);
                    break;
                case 'news':
                    $commentableModel = \App\Models\News::find($id);
                    break;
            }

            if (!$commentableModel) {
                return response()->json(['error' => 'Content not found'], 404);
            }

            $comments = \App\Models\Comment::where('commentable_type', get_class($commentableModel))
                ->where('commentable_id', $commentableModel->id)
                ->get();

            return response()->json([
                'content_type' => $type,
                'content_id' => $id,
                'model_class' => get_class($commentableModel),
                'model_id' => $commentableModel->id,
                'comments_count' => $comments->count(),
                'comments' => $comments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'user_id' => $comment->user_id,
                        'commentable_type' => $comment->commentable_type,
                        'commentable_id' => $comment->commentable_id,
                        'parent_id' => $comment->parent_id,
                        'created_at' => $comment->created_at
                    ];
                })
            ]);
        });
    });
    // Test route for DeepSeek API
Route::get('/test-deepseek', function () {
    // Check if API key exists in environment
    $apiKey = env('DEEPSEEK_API_KEY');
    
    // Debug information
    $debugInfo = [
        'api_key_exists' => !empty($apiKey),
        'api_key_length' => $apiKey ? strlen($apiKey) : 0,
        'api_key_prefix' => $apiKey ? substr($apiKey, 0, 10) . '...' : 'Not found',
        'env_file_exists' => file_exists(base_path('.env')),
        'config_cached' => file_exists(base_path('bootstrap/cache/config.php'))
    ];
    if (!$apiKey) {
        return response()->json([
            'error' => 'API key not configured',
            'debug' => $debugInfo,
            'instructions' => [
                '1. Make sure DEEPSEEK_API_KEY is in your .env file',
                '2. Run: php artisan config:clear',
                '3. Run: php artisan cache:clear',
                '4. Restart your server'
            ]
        ]);
         }
    
    try {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.deepseek.com/v1/chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello, can you respond with "API connection successful"?']
            ],
            'max_tokens' => 50,
            'temperature' => 0.7
        ]);
        
        return response()->json([
            'status' => $response->status(),
            'success' => $response->successful(),
            'debug' => $debugInfo,
            'response' => $response->json(),
            'raw_response' => $response->body()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'debug' => $debugInfo,
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test.deepseek');
// Chatbot API route (accessible from welcome page and dashboard)
Route::post('/api/chatbot', [ChatbotController::class, 'chat'])->name('api.chatbot');

// Gemini Chatbot API routes
Route::post('/api/gemini-chatbot', [App\Http\Controllers\GeminiChatbotController::class, 'chat'])->name('api.gemini.chatbot');
Route::get('/api/gemini-test', [App\Http\Controllers\GeminiChatbotController::class, 'testConnection'])->name('api.gemini.test');
Route::get('/api/gemini-faq', [App\Http\Controllers\GeminiChatbotController::class, 'getFaqContent'])->name('api.gemini.faq');
Route::put('/user/update-settings', [UserAuthController::class, 'updateSettings'])->name('user.update-settings');

});

// Test route for department visibility
Route::get('/test-department-counts', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }

    $userDepartment = $user->department;

    $totalAnnouncements = App\Models\Announcement::where('is_published', true)
        ->visibleToDepartment($userDepartment)
        ->count();
    $totalEvents = App\Models\Event::where('is_published', true)
        ->visibleToDepartment($userDepartment)
        ->count();
    $totalNews = App\Models\News::where('is_published', true)
        ->visibleToDepartment($userDepartment)
        ->count();

    $allAnnouncements = App\Models\Announcement::where('is_published', true)->count();
    $allEvents = App\Models\Event::where('is_published', true)->count();
    $allNews = App\Models\News::where('is_published', true)->count();

    return response()->json([
        'user_department' => $userDepartment,
        'visible_to_user' => [
            'announcements' => $totalAnnouncements,
            'events' => $totalEvents,
            'news' => $totalNews
        ],
        'total_published' => [
            'announcements' => $allAnnouncements,
            'events' => $allEvents,
            'news' => $allNews
        ]
    ]);
})->middleware('auth');

// Test route for all departments visibility
Route::get('/test-all-departments', function () {
    $departments = ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'];
    $results = [];

    foreach ($departments as $dept) {
        $results[$dept] = [
            'announcements' => App\Models\Announcement::where('is_published', true)
                ->visibleToDepartment($dept)
                ->count(),
            'events' => App\Models\Event::where('is_published', true)
                ->visibleToDepartment($dept)
                ->count(),
            'news' => App\Models\News::where('is_published', true)
                ->visibleToDepartment($dept)
                ->count(),
        ];
    }

    $totalCounts = [
        'announcements' => App\Models\Announcement::where('is_published', true)->count(),
        'events' => App\Models\Event::where('is_published', true)->count(),
        'news' => App\Models\News::where('is_published', true)->count(),
    ];

    return response()->json([
        'department_visibility' => $results,
        'total_published' => $totalCounts,
        'admins' => App\Models\Admin::where('role', 'department_admin')->get(['username', 'department'])
    ]);
});

// Test route for student profiles
Route::get('/test-student-profiles', function () {
    $students = App\Models\User::where('role', 'student')
        ->whereNotNull('department')
        ->get(['first_name', 'surname', 'department', 'year_level', 'ms365_account']);

    return response()->json([
        'students' => $students,
        'total_students' => $students->count(),
        'departments' => $students->groupBy('department')->map(function($group) {
            return $group->count();
        })
    ]);
});

// Test route for MS365 OAuth2 system
Route::get('/test-ms365-oauth', function () {
    $ms365Accounts = App\Models\Ms365Account::all(['display_name', 'user_principal_name', 'first_name', 'last_name']);
    
    return response()->json([
        'ms365_accounts' => $ms365Accounts,
        'total_accounts' => $ms365Accounts->count(),
        'system_status' => [
            'socialite_installed' => class_exists('Laravel\Socialite\Facades\Socialite'),
            'microsoft_provider_installed' => class_exists('SocialiteProviders\Microsoft\MicrosoftExtendSocialite'),
            'ms365_oauth_controller_exists' => class_exists('App\Http\Controllers\Auth\MS365OAuthController'),
            'ms365_account_model_exists' => class_exists('App\Models\Ms365Account'),
            'microsoft_graph_service_exists' => class_exists('App\Services\MicrosoftGraphService'),
        ]
    ]);
});

// Password Reset Routes
Route::get('/forgot-password', [UnifiedAuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [UnifiedAuthController::class, 'sendPasswordResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [UnifiedAuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [UnifiedAuthController::class, 'resetPassword'])->name('password.update');

// Laravel's default auth routes (excluding login since we have custom unified login)
Auth::routes(['login' => false, 'reset' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Debug route to analyze admins table
Route::get('/debug-admins', function () {
    $admins = \App\Models\Admin::all();
    $output = "<h2>Analyzing Admins Table</h2>";
    $output .= "Total admins: " . $admins->count() . "<br><br>";
    
    if ($admins->count() > 0) {
        foreach ($admins as $admin) {
            $output .= "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            $output .= "<strong>Admin ID:</strong> " . $admin->id . "<br>";
            $output .= "<strong>Username:</strong> " . ($admin->username ?? 'null') . "<br>";
            $output .= "<strong>Role:</strong> " . ($admin->role ?? 'null') . "<br>";
            $output .= "<strong>Password Hash:</strong> " . substr($admin->password ?? 'null', 0, 50) . "...<br>";
            $output .= "<strong>Created:</strong> " . ($admin->created_at ?? 'null') . "<br>";
            $output .= "<strong>Updated:</strong> " . ($admin->updated_at ?? 'null') . "<br>";
            
            // Test role methods
            $output .= "<strong>isSuperAdmin():</strong> " . ($admin->isSuperAdmin() ? 'true' : 'false') . "<br>";
            $output .= "<strong>isDepartmentAdmin():</strong> " . ($admin->isDepartmentAdmin() ? 'true' : 'false') . "<br>";
            $output .= "<strong>isOfficeAdmin():</strong> " . ($admin->isOfficeAdmin() ? 'true' : 'false') . "<br>";
            $output .= "</div>";
        }
    }
    
    return $output;
});

// Debug route to test admin authentication
Route::get('/debug-admin-auth/{username}/{password}', function ($username, $password) {
    $output = "<h2>Testing Admin Authentication</h2>";
    $output .= "Testing username: <strong>" . $username . "</strong><br>";
    $output .= "Testing password: <strong>" . $password . "</strong><br><br>";
    
    // Test the same logic as UnifiedAuthController
    $admin = \App\Models\Admin::all()->first(function ($admin) use ($username) {
        return $admin->username === $username;
    });
    
    if ($admin) {
        $output .= "<div style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50;'>";
        $output .= "<strong>✓ Admin Found!</strong><br>";
        $output .= "<strong>Admin ID:</strong> " . $admin->id . "<br>";
        $output .= "<strong>Username:</strong> " . $admin->username . "<br>";
        $output .= "<strong>Role:</strong> " . $admin->role . "<br>";
        $output .= "<strong>Password Hash:</strong> " . substr($admin->password, 0, 50) . "...<br>";
        
        // Test password verification
        $passwordCheck = \Hash::check($password, $admin->password);
        $output .= "<strong>Password Check:</strong> " . ($passwordCheck ? '✓ PASS' : '✗ FAIL') . "<br>";
        
        // Test role methods
        $output .= "<strong>isSuperAdmin():</strong> " . ($admin->isSuperAdmin() ? 'true' : 'false') . "<br>";
        $output .= "<strong>isDepartmentAdmin():</strong> " . ($admin->isDepartmentAdmin() ? 'true' : 'false') . "<br>";
        $output .= "<strong>isOfficeAdmin():</strong> " . ($admin->isOfficeAdmin() ? 'true' : 'false') . "<br>";
        $output .= "</div>";
        
        if (!$passwordCheck) {
            $output .= "<br><div style='background: #ffe8e8; padding: 10px; border: 1px solid #f44336;'>";
            $output .= "<strong>Password Issue Detected!</strong><br>";
            $output .= "The password hash might be corrupted or the password is incorrect.<br>";
            $output .= "Current hash: " . $admin->password . "<br>";
            $output .= "</div>";
        }
    } else {
        $output .= "<div style='background: #ffe8e8; padding: 10px; border: 1px solid #f44336;'>";
        $output .= "<strong>✗ Admin NOT Found!</strong><br>";
        $output .= "No admin found with username: " . $username . "<br>";
        $output .= "</div>";
        
        // Show all available usernames for debugging
        $allAdmins = \App\Models\Admin::all();
        $output .= "<br><strong>Available usernames in database:</strong><br>";
        foreach ($allAdmins as $a) {
            $output .= "- " . $a->username . " (Role: " . $a->role . ")<br>";
        }
    }
    
    return $output;
});

// Debug route to fix admin password
Route::get('/debug-fix-admin-password/{username}/{newPassword}', function ($username, $newPassword) {
    $admin = \App\Models\Admin::all()->first(function ($admin) use ($username) {
        return $admin->username === $username;
    });
    
    if ($admin) {
        $oldHash = $admin->password;
        $admin->password = \Hash::make($newPassword);
        $admin->save();
        
        $output = "<h2>Admin Password Updated</h2>";
        $output .= "<strong>Username:</strong> " . $admin->username . "<br>";
        $output .= "<strong>Role:</strong> " . $admin->role . "<br>";
        $output .= "<strong>Old Hash:</strong> " . substr($oldHash, 0, 50) . "...<br>";
        $output .= "<strong>New Hash:</strong> " . substr($admin->password, 0, 50) . "...<br>";
        $output .= "<strong>New Password:</strong> " . $newPassword . "<br>";
        
        // Test the new password
        $passwordCheck = \Hash::check($newPassword, $admin->password);
        $output .= "<strong>Password Verification:</strong> " . ($passwordCheck ? '✓ PASS' : '✗ FAIL') . "<br>";
        
        return $output;
    } else {
        return "<h2>Admin Not Found</h2>Username: " . $username . " not found in database.";
    }
});

// Quick fix route for admin passwords based on phpMyAdmin data
Route::get('/quick-fix-admin-passwords', function () {
    $output = "<h2>Quick Admin Password Fix</h2>";
    
    // Get all admins first to see what we're working with
    $allAdmins = \App\Models\Admin::all();
    $output .= "<h3>Current Admins in Database:</h3>";
    foreach ($allAdmins as $admin) {
        $output .= "<p>ID: {$admin->id}, Username: '{$admin->username}', Role: '{$admin->role}'</p>";
    }
    
    // Fix superadmin (ID 61)
    $superadmin = \App\Models\Admin::find(61);
    if ($superadmin) {
        $oldHash = $superadmin->password;
        $superadmin->password = \Hash::make('admin123');
        $superadmin->save();
        $output .= "<p>✓ Superadmin (ID: 61) password reset to: <strong>admin123</strong></p>";
        $output .= "<p>Old hash: " . substr($oldHash, 0, 30) . "...</p>";
        $output .= "<p>New hash: " . substr($superadmin->password, 0, 30) . "...</p>";
    }
    
    // Fix office admin (ID 62) 
    $officeAdmin = \App\Models\Admin::find(62);
    if ($officeAdmin) {
        $oldHash = $officeAdmin->password;
        $officeAdmin->password = \Hash::make('office123');
        $officeAdmin->save();
        $output .= "<p>✓ Office Admin (ID: 62, Username: '" . $officeAdmin->username . "') password reset to: <strong>office123</strong></p>";
        $output .= "<p>Old hash: " . substr($oldHash, 0, 30) . "...</p>";
        $output .= "<p>New hash: " . substr($officeAdmin->password, 0, 30) . "...</p>";
    }
    
    $output .= "<br><h3>Test these credentials:</h3>";
    $output .= "<p><strong>Superadmin:</strong><br>Username: superadmin<br>Password: admin123</p>";
    $output .= "<p><strong>Office Admin:</strong><br>Username: " . ($officeAdmin ? $officeAdmin->username : 'po.bautro@mccalumni.edu.ph') . "<br>Password: office123</p>";
    
    // Test password verification immediately
    if ($officeAdmin) {
        $testPassword = \Hash::check('office123', $officeAdmin->password);
        $output .= "<br><h3>Password Verification Test:</h3>";
        $output .= "<p>Office Admin Password Check: " . ($testPassword ? '✓ PASS' : '✗ FAIL') . "</p>";
    }
    
    return $output;
});

// Test office admin authentication logic specifically
Route::get('/test-office-admin-auth', function () {
    $output = "<h2>Testing Office Admin Authentication Logic</h2>";
    
    // Get the office admin from database
    $officeAdmin = \App\Models\Admin::find(62);
    if (!$officeAdmin) {
        return "<p>❌ Office Admin (ID 62) not found in database</p>";
    }
    
    $output .= "<h3>Office Admin Details:</h3>";
    $output .= "<p><strong>ID:</strong> {$officeAdmin->id}</p>";
    $output .= "<p><strong>Username:</strong> '{$officeAdmin->username}'</p>";
    $output .= "<p><strong>Role:</strong> '{$officeAdmin->role}'</p>";
    $output .= "<p><strong>Password Hash:</strong> " . substr($officeAdmin->password, 0, 50) . "...</p>";
    
    // Test role methods
    $output .= "<h3>Role Method Tests:</h3>";
    $output .= "<p><strong>isOfficeAdmin():</strong> " . ($officeAdmin->isOfficeAdmin() ? '✅ TRUE' : '❌ FALSE') . "</p>";
    $output .= "<p><strong>isSuperAdmin():</strong> " . ($officeAdmin->isSuperAdmin() ? '✅ TRUE' : '❌ FALSE') . "</p>";
    $output .= "<p><strong>isDepartmentAdmin():</strong> " . ($officeAdmin->isDepartmentAdmin() ? '✅ TRUE' : '❌ FALSE') . "</p>";
    
    // Test password with known password
    $testPassword = 'office123';
    $passwordCheck = \Hash::check($testPassword, $officeAdmin->password);
    $output .= "<h3>Password Test:</h3>";
    $output .= "<p><strong>Testing password 'office123':</strong> " . ($passwordCheck ? '✅ PASS' : '❌ FAIL') . "</p>";
    
    // Test the exact lookup logic used in UnifiedAuthController
    $testUsername = $officeAdmin->username;
    $foundAdmin = \App\Models\Admin::all()->first(function ($admin) use ($testUsername) {
        return $admin->username === $testUsername;
    });
    
    $output .= "<h3>Admin Lookup Test:</h3>";
    $output .= "<p><strong>Looking for username:</strong> '{$testUsername}'</p>";
    $output .= "<p><strong>Admin found:</strong> " . ($foundAdmin ? '✅ YES (ID: ' . $foundAdmin->id . ')' : '❌ NO') . "</p>";
    
    if ($foundAdmin && $passwordCheck && $foundAdmin->isOfficeAdmin()) {
        $output .= "<br><div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        $output .= "<h3>✅ Authentication Should Work!</h3>";
        $output .= "<p>All tests passed. The office admin should be able to login with:</p>";
        $output .= "<p><strong>Username:</strong> {$testUsername}</p>";
        $output .= "<p><strong>Password:</strong> office123</p>";
        $output .= "</div>";
    } else {
        $output .= "<br><div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        $output .= "<h3>❌ Authentication Issues Detected!</h3>";
        $output .= "<p>Issues found:</p>";
        if (!$foundAdmin) $output .= "<p>- Admin lookup failed</p>";
        if (!$passwordCheck) $output .= "<p>- Password verification failed</p>";
        if ($foundAdmin && !$foundAdmin->isOfficeAdmin()) $output .= "<p>- Role check failed</p>";
        $output .= "</div>";
    }
    
    return $output;
});

// Debug all login types attempts
Route::get('/debug-all-attempts', function() {
    $output = "<h2>Debug All Login Types Attempts</h2>";
    
    $unifiedController = new \App\Http\Controllers\UnifiedAuthController();
    $reflection = new \ReflectionClass($unifiedController);
    
    $getAccountIdentifier = $reflection->getMethod('getAccountIdentifier');
    $getAccountIdentifier->setAccessible(true);
    
    $getLoginAttemptsKey = $reflection->getMethod('getLoginAttemptsKey');
    $getLoginAttemptsKey->setAccessible(true);
    
    $getRemainingAttempts = $reflection->getMethod('getRemainingAttempts');
    $getRemainingAttempts->setAccessible(true);
    
    // Test data for each login type
    $loginTypes = [
        'ms365' => [
            'login_type' => 'ms365',
            'ms365_account' => 'student@mcc-nac.edu.ph',
            'password' => 'wrongpassword',
            'display_name' => 'MS365 Student/Faculty'
        ],
        'user' => [
            'login_type' => 'user',
            'gmail_account' => 'student@gmail.com',
            'password' => 'wrongpassword',
            'display_name' => 'Gmail User'
        ],
        'superadmin' => [
            'login_type' => 'superadmin',
            'username' => 'superadmin',
            'password' => 'wrongpassword',
            'display_name' => 'Super Admin'
        ],
        'department-admin' => [
            'login_type' => 'department-admin',
            'ms365_account' => 'dept.admin@mcc-nac.edu.ph',
            'password' => 'wrongpassword',
            'display_name' => 'Department Admin'
        ],
        'office-admin' => [
            'login_type' => 'office-admin',
            'ms365_account' => 'office.admin@mcc-nac.edu.ph',
            'password' => 'wrongpassword',
            'display_name' => 'Office Admin'
        ]
    ];
    
    $output .= "<h3>Attempts Status for All Login Types:</h3>";
    $output .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    $output .= "<tr><th>Login Type</th><th>Account Identifier</th><th>Current Attempts</th><th>Remaining Attempts</th><th>Should Show Warning</th></tr>";
    
    foreach ($loginTypes as $type => $data) {
        $request = new \Illuminate\Http\Request();
        $request->merge($data);
        $request->setSession(session());
        
        $accountIdentifier = $getAccountIdentifier->invoke($unifiedController, $request);
        $attemptsKey = $getLoginAttemptsKey->invoke($unifiedController, $request);
        $remainingAttempts = $getRemainingAttempts->invoke($unifiedController, $request);
        $currentAttempts = session($attemptsKey, 0);
        $shouldShowWarning = ($remainingAttempts > 0 && $remainingAttempts < 3) ? 'YES' : 'NO';
        
        $output .= "<tr>";
        $output .= "<td><strong>{$data['display_name']}</strong></td>";
        $output .= "<td>{$accountIdentifier}</td>";
        $output .= "<td>{$currentAttempts}</td>";
        $output .= "<td>{$remainingAttempts}</td>";
        $output .= "<td>" . ($shouldShowWarning === 'YES' ? '<span style="color: green; font-weight: bold;">YES</span>' : '<span style="color: red;">NO</span>') . "</td>";
        $output .= "</tr>";
    }
    
    $output .= "</table><br>";
    
    $output .= "<h3>Session Data (Lockout Related):</h3>";
    $sessionData = session()->all();
    $lockoutData = [];
    foreach ($sessionData as $key => $value) {
        if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
            $lockoutData[$key] = $value;
        }
    }
    $output .= "<pre>" . json_encode($lockoutData, JSON_PRETTY_PRINT) . "</pre>";
    
    $output .= "<h3>Testing Instructions:</h3>";
    $output .= "<ol>";
    foreach ($loginTypes as $type => $data) {
        $output .= "<li><strong>{$data['display_name']}:</strong><br>";
        $output .= "   - Go to <a href='/login' target='_blank'>Login Page</a><br>";
        $output .= "   - Select '{$data['display_name']}' login type<br>";
        $fieldName = isset($data['username']) ? 'username' : (isset($data['ms365_account']) ? 'MS365 account' : 'Gmail account');
        $fieldValue = $data['username'] ?? $data['ms365_account'] ?? $data['gmail_account'];
        $output .= "   - Enter {$fieldName}: '{$fieldValue}' and wrong password<br>";
        $output .= "   - <strong>After 1st attempt:</strong> Should see warning '2 login attempt(s) remaining'<br>";
        $output .= "   - <strong>After 2nd attempt:</strong> Should see warning '1 login attempt(s) remaining'<br>";
        $output .= "   - <strong>After 3rd attempt:</strong> Should see lockout message with countdown timer</li><br>";
    }
    $output .= "</ol>";
    
    $output .= "<p><a href='/login'>Go to Login Page</a> | <a href='/clear-all-lockouts'>Clear All Lockouts</a></p>";
    
    return $output;
});

// Clear all lockouts for testing
Route::get('/clear-all-lockouts', function() {
    $sessionData = session()->all();
    $clearedKeys = [];
    
    foreach ($sessionData as $key => $value) {
        if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
            session()->forget($key);
            $clearedKeys[] = $key;
        }
    }
    
    $output = "<h2>All Lockouts Cleared</h2>";
    $output .= "<p>Cleared " . count($clearedKeys) . " lockout-related session keys:</p>";
    $output .= "<ul>";
    foreach ($clearedKeys as $key) {
        $output .= "<li>{$key}</li>";
    }
    $output .= "</ul>";
    $output .= "<p><a href='/login'>Go to Login Page</a> | <a href='/test-admin-lockout'>Test Admin Lockouts</a></p>";
    
    return $output;
});

