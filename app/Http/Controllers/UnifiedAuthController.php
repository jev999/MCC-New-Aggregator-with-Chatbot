<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\GmailAuthController;
use App\Http\Controllers\Auth\MS365AuthController;
use App\Http\Controllers\SuperAdminAuthController;
use App\Http\Controllers\DepartmentAdminAuthController;
use App\Http\Controllers\OfficeAdminAuthController;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Admin;
use App\Models\AdminAccessLog;
use App\Services\GeolocationService;
use Carbon\Carbon;
class UnifiedAuthController extends Controller
{
    protected $securityService;
    protected $geolocationService;
    
    public function __construct()
    {
        // Initialize security service if available, otherwise use fallback methods
        if (class_exists('\App\Services\SecurityService')) {
            $this->securityService = app('\App\Services\SecurityService');
        }
        $this->geolocationService = new GeolocationService();
    }

    /**
     * Get geolocation data for logging
     */
    protected function getGeolocationData($ip)
    {
        return $this->geolocationService->getLocationFromIp($ip);
    }

    /**
     * Validate reCAPTCHA response
     */
    private function validateRecaptcha(Request $request)
    {
        $recaptchaResponse = $request->input('g-recaptcha-response');
        
        if (!$recaptchaResponse) {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $recaptchaResponse,
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();
        
        return isset($result['success']) && $result['success'] === true;
    }
    
    /**
     * Show the unified login form
     */
    public function showLoginForm(Request $request)
    {
        $loginType = $request->query('type', 'ms365'); // Default to ms365 if no type specified
        
        // Validate the login type
        $validTypes = ['ms365', 'user', 'superadmin', 'department-admin', 'office-admin'];
        if (!in_array($loginType, $validTypes)) {
            $loginType = 'ms365';
        }
        
        // Get locked accounts information
        $lockedAccounts = $this->getLockedAccounts();
        $authenticatedAccounts = $this->getCurrentAuthenticatedAccounts();
        
        // Get attempts left for warning display
        $attemptsLeft = $this->getRemainingAttempts($request);
        
        return view('auth.unified-login', [
            'title' => 'Login - MCC News Aggregator',
            'preselectedType' => $loginType,
            'lockedAccounts' => $lockedAccounts,
            'authenticatedAccounts' => $authenticatedAccounts,
            'attemptsLeft' => $attemptsLeft
        ]);
    }

    /**
     * Handle unified login based on login type
     */
    public function login(Request $request)
    {
        // Check rate limiting
        if ($this->securityService) {
            $rateLimitCheck = $this->securityService->checkRateLimit($request, 'login');
            if (!$rateLimitCheck['allowed']) {
                $this->securityService->logSecurityEvent('rate_limit_exceeded', [
                    'ip' => $request->ip(),
                    'endpoint' => 'login'
                ]);
                return back()->withErrors([
                    'rate_limit' => 'Too many login attempts. Please try again in ' . ceil($rateLimitCheck['retry_after'] / 60) . ' minutes.'
                ]);
            }
        }

        // Check if specific account is locked out - ENHANCED CHECK
        $lockoutKey = $this->getLockoutKey($request);
        $lockoutTime = session($lockoutKey);
        $accountIdentifier = $this->getAccountIdentifier($request);
        
        // Enhanced lockout check with detailed logging
        if ($lockoutTime) {
            try {
                $lockoutTime = is_string($lockoutTime) ? \Carbon\Carbon::parse($lockoutTime) : $lockoutTime;
                
                if ($lockoutTime instanceof \Carbon\Carbon && now()->lessThan($lockoutTime)) {
                    $timeRemaining = $this->getLockoutTimeRemaining($request);
                    
                    \Log::warning('LOCKOUT ENFORCED - Account access blocked', [
                        'account_identifier' => $accountIdentifier,
                        'lockout_time_remaining' => $timeRemaining,
                        'lockout_key' => $lockoutKey,
                        'session_lockout_time' => $lockoutTime->toDateTimeString(),
                        'current_time' => now()->toDateTimeString(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                    
                    // Get remaining seconds for accurate frontend countdown
                    $remainingSeconds = $this->getLockoutTimeRemainingSeconds($request);
                    
                    // Generate user-friendly lockout message based on login type
                    $loginTypeText = $this->getLoginTypeDisplayName($request->login_type);
                    $lockoutMessage = "Your {$loginTypeText} account is temporarily locked due to too many failed login attempts. Please wait {$timeRemaining} minute" . ($timeRemaining != 1 ? 's' : '') . " before trying again.";
                    
                    // Force return lockout error - NO FURTHER PROCESSING
                    return back()->withErrors([
                        'account_lockout' => $lockoutMessage
                    ])->with('lockout_time', $timeRemaining)
                      ->with('lockout_seconds', $remainingSeconds)
                      ->with('locked_account', $accountIdentifier);
                }
            } catch (\Exception $e) {
                \Log::error('Lockout time parsing error', [
                    'error' => $e->getMessage(),
                    'lockout_time' => $lockoutTime
                ]);
            }
        }

        // Enhanced security validation
        $this->validateSecureInput($request);
        
        
        // Sanitize input data
        $this->sanitizeInputData($request);

        $secureRules = $this->getSecureValidationRules();
        $secureMessages = $this->getSecureValidationMessages();

        // Validate basic fields first - conditional validation based on login type
        $loginType = $request->login_type;
        
        $validationRules = [
            'login_type' => 'required|in:user,ms365,superadmin,department-admin,office-admin',
            'password' => $secureRules['password'],
        ];
        
        // Add conditional validation based on login type
        if ($loginType === 'superadmin') {
            $validationRules['username'] = array_merge(['required'], $secureRules['username']);
        } elseif (in_array($loginType, ['ms365', 'department-admin', 'office-admin'])) {
            $validationRules['ms365_account'] = array_merge(['required'], $secureRules['ms365_account']);
        } elseif ($loginType === 'user') {
            $validationRules['gmail_account'] = array_merge(['required'], $secureRules['gmail_account']);
        }
        
        $request->validate($validationRules, $secureMessages);

        // Store current auth status before login attempt
        $wasAuthenticated = ($loginType === 'superadmin' || $loginType === 'department-admin' || $loginType === 'office-admin') 
            ? auth('admin')->check() 
            : auth()->check();
        
        // Route to appropriate controller based on login type
        $result = null;
        $loginSuccessful = false;
        
        switch ($loginType) {
            case 'ms365':
                // Handle MS365 authentication with manual lookup (similar to admin authentication)
                $credentials = $request->only('ms365_account', 'password');
                
                // Enhanced debug logging for MS365 authentication
                \Log::info('MS365 authentication attempt - ENHANCED DEBUG', [
                    'all_request_data' => $request->all(),
                    'extracted_credentials' => $credentials,
                    'ms365_account' => $credentials['ms365_account'] ?? 'NOT_PROVIDED',
                    'password_provided' => !empty($credentials['password']),
                    'password_length' => isset($credentials['password']) ? strlen($credentials['password']) : 0,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                // Debug: Show all users for troubleshooting
                $allUsers = User::all();
                \Log::info('All users in database for MS365 auth', [
                    'total_users' => $allUsers->count(),
                    'users' => $allUsers->map(function($user) {
                        return [
                            'id' => $user->id,
                            'ms365_account' => $user->ms365_account,
                            'gmail_account' => $user->gmail_account,
                            'role' => $user->role,
                            'password_hash_start' => substr($user->password ?? 'null', 0, 20)
                        ];
                    })->toArray()
                ]);
                
                // Find user by ms365_account - handle potential encryption issues
                $user = User::all()->first(function ($user) use ($credentials) {
                    return $user->ms365_account === $credentials['ms365_account'];
                });
                
                if ($user) {
                    \Log::info('User found for MS365 authentication', [
                        'user_id' => $user->id,
                        'ms365_account' => $user->ms365_account,
                        'role' => $user->role,
                        'password_check' => Hash::check($credentials['password'], $user->password)
                    ]);
                    
                    // Verify password
                    if (Hash::check($credentials['password'], $user->password)) {
                        // Successful MS365 login - manually log in the user
                        auth()->login($user, $request->filled('remember'));
                        $request->session()->regenerate();
                        $result = redirect()->route('user.dashboard')->with('login_success', true);
                        $loginSuccessful = true;
                        
                        \Log::info('MS365 login successful', [
                            'user_id' => $user->id,
                            'ms365_account' => $user->ms365_account
                        ]);
                    } else {
                        // Password verification failed
                        \Log::warning('MS365 password verification failed', [
                            'ms365_account' => $credentials['ms365_account'],
                            'user_id' => $user->id
                        ]);
                        
                        $result = back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                                    ->withInput($request->only('ms365_account', 'login_type'));
                        $loginSuccessful = false;
                    }
                } else {
                    // User not found - Enhanced debugging
                    \Log::warning('MS365 user not found - ENHANCED DEBUG', [
                        'ms365_account' => $credentials['ms365_account'] ?? 'NULL',
                        'all_user_ms365_accounts' => User::all()->pluck('ms365_account')->toArray(),
                        'total_users_checked' => User::all()->count(),
                        'search_criteria' => 'exact_match_on_ms365_account_field'
                    ]);
                    
                    $result = back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                                ->withInput($request->only('ms365_account', 'login_type'));
                    $loginSuccessful = false;
                }
                break;

            case 'user':
                // Handle user authentication with manual lookup (similar to admin authentication)
                $credentials = $request->only('gmail_account', 'password');
                
                // Debug logging for user authentication
                \Log::info('User authentication attempt', [
                    'gmail_account' => $credentials['gmail_account'],
                    'password_provided' => !empty($credentials['password']),
                    'ip' => $request->ip()
                ]);
                
                // Find user by gmail_account - handle potential encryption issues
                $user = User::all()->first(function ($user) use ($credentials) {
                    return $user->gmail_account === $credentials['gmail_account'];
                });
                
                if ($user) {
                    \Log::info('User found for authentication', [
                        'user_id' => $user->id,
                        'gmail_account' => $user->gmail_account,
                        'role' => $user->role,
                        'password_check' => Hash::check($credentials['password'], $user->password)
                    ]);
                    
                    // Verify password
                    if (Hash::check($credentials['password'], $user->password)) {
                        // Successful user login - manually log in the user
                        auth()->login($user, $request->filled('remember'));
                        $request->session()->regenerate();
                        $result = redirect()->route('user.dashboard')->with('login_success', true);
                        $loginSuccessful = true;
                        
                        \Log::info('User login successful', [
                            'user_id' => $user->id,
                            'gmail_account' => $user->gmail_account
                        ]);
                    } else {
                        // Password verification failed
                        \Log::warning('User password verification failed', [
                            'gmail_account' => $credentials['gmail_account'],
                            'user_id' => $user->id
                        ]);
                        
                        $result = back()->withErrors(['gmail_account' => 'The provided credentials do not match our records.'])
                                    ->withInput($request->only('gmail_account', 'login_type'));
                        $loginSuccessful = false;
                    }
                } else {
                    // User not found
                    \Log::warning('User not found', [
                        'gmail_account' => $credentials['gmail_account']
                    ]);
                    
                    $result = back()->withErrors(['gmail_account' => 'The provided credentials do not match our records.'])
                                ->withInput($request->only('gmail_account', 'login_type'));
                    $loginSuccessful = false;
                }
                break;

            case 'superadmin':
                // Handle superadmin authentication with manual lookup (similar to other admin types)
                $credentials = $request->only('username', 'password');
                
                // Debug logging for superadmin authentication
                \Log::info('Superadmin authentication attempt', [
                    'username' => $credentials['username'],
                    'password_provided' => !empty($credentials['password']),
                    'ip' => $request->ip()
                ]);
                
                // Find admin by username - handle potential encryption issues
                $admin = Admin::all()->first(function ($admin) use ($credentials) {
                    return $admin->username === $credentials['username'];
                });
                
                if ($admin) {
                    \Log::info('Admin found for authentication', [
                        'admin_id' => $admin->id,
                        'username' => $admin->username,
                        'role' => $admin->role,
                        'is_superadmin' => $admin->isSuperAdmin(),
                        'password_check' => Hash::check($credentials['password'], $admin->password)
                    ]);
                    
                    // Verify password and role
                    if (Hash::check($credentials['password'], $admin->password)) {
                        // Check if the user is specifically a super admin
                        if (!$admin->isSuperAdmin()) {
                            \Log::warning('Non-superadmin tried to login as superadmin', [
                                'admin_id' => $admin->id,
                                'actual_role' => $admin->role
                            ]);
                            
                            // Provide specific error messages based on admin type
                            if ($admin->isDepartmentAdmin()) {
                                $result = back()->withErrors(['username' => 'Department admins should use the department admin login.'])
                                            ->withInput($request->only('username', 'login_type'));
                            } elseif ($admin->isOfficeAdmin()) {
                                $result = back()->withErrors(['username' => 'Office admins should use the office admin login.'])
                                            ->withInput($request->only('username', 'login_type'));
                            } else {
                                $result = back()->withErrors(['username' => 'You do not have super admin privileges.'])
                                            ->withInput($request->only('username', 'login_type'));
                            }
                            
                            // Add attempts warning for role validation errors
                            $attemptsLeft = $this->getRemainingAttempts($request);
                            if ($attemptsLeft > 0) {
                                // Set session variable for consistent access in blade template
                                session(['attempts_left' => $attemptsLeft]);
                                $result->with('attempts_left', $attemptsLeft);

                                \Log::info('Attempts warning added to role validation error', [
                                    'attempts_left' => $attemptsLeft,
                                    'account_identifier' => $this->getAccountIdentifier($request),
                                    'response_type' => get_class($result)
                                ]);
                            }
                            $loginSuccessful = false;
                        } else {
                            // Successful super admin login - manually log in the user
                            Auth::guard('admin')->login($admin);
                            $request->session()->regenerate();
                            
                            // Log admin access with geolocation
                            $geoData = $this->getGeolocationData($request->ip());
                            AdminAccessLog::create([
                                'admin_id' => $admin->id,
                                'role' => $admin->role,
                                'status' => 'success',
                                'ip_address' => $request->ip(),
                                'latitude' => $geoData['latitude'] ?? null,
                                'longitude' => $geoData['longitude'] ?? null,
                                'location_details' => $geoData['location_details'] ?? null,
                                'time_in' => Carbon::now(),
                            ]);
                            
                            $result = redirect()->route('superadmin.dashboard')->with('login_success', true);
                            $loginSuccessful = true;
                            
                            \Log::info('Superadmin login successful', [
                                'admin_id' => $admin->id,
                                'username' => $admin->username
                            ]);
                        }
                    } else {
                        // Password verification failed
                        \Log::warning('Superadmin password verification failed', [
                            'username' => $credentials['username'],
                            'admin_id' => $admin->id
                        ]);
                        
                        // Log failed login attempt with geolocation
                        $geoData = $this->getGeolocationData($request->ip());
                        AdminAccessLog::create([
                            'admin_id' => null,
                            'role' => 'superadmin',
                            'status' => 'failed',
                            'username_attempted' => $credentials['username'],
                            'ip_address' => $request->ip(),
                            'latitude' => $geoData['latitude'] ?? null,
                            'longitude' => $geoData['longitude'] ?? null,
                            'location_details' => $geoData['location_details'] ?? null,
                            'time_in' => null,
                        ]);
                        
                        $result = back()->withErrors(['username' => 'The provided credentials do not match our records.'])
                                    ->withInput($request->only('username', 'login_type'));
                        $loginSuccessful = false;
                    }
                } else {
                    // Admin not found
                    \Log::warning('Superadmin not found', [
                        'username' => $credentials['username']
                    ]);
                    
                    // Log failed login attempt with geolocation
                    $geoData = $this->getGeolocationData($request->ip());
                    AdminAccessLog::create([
                        'admin_id' => null,
                        'role' => 'superadmin',
                        'status' => 'failed',
                        'username_attempted' => $credentials['username'],
                        'ip_address' => $request->ip(),
                        'latitude' => $geoData['latitude'] ?? null,
                        'longitude' => $geoData['longitude'] ?? null,
                        'location_details' => $geoData['location_details'] ?? null,
                        'time_in' => null,
                    ]);
                    
                    $result = back()->withErrors(['username' => 'The provided credentials do not match our records.'])
                                ->withInput($request->only('username', 'login_type'));
                    $loginSuccessful = false;
                }
                break;

            case 'department-admin':
                // Handle department admin authentication with MS365 account lookup
                $credentials = $request->only('ms365_account', 'password');
                
                // Debug logging for department admin authentication
                \Log::info('Department admin authentication attempt', [
                    'ms365_account' => $credentials['ms365_account'],
                    'password_provided' => !empty($credentials['password']),
                    'ip' => $request->ip()
                ]);
                
                // Find admin by MS365 account - handle potential encryption issues
                $admin = Admin::all()->first(function ($admin) use ($credentials) {
                    return $admin->username === $credentials['ms365_account'];
                });
                
                if ($admin) {
                    \Log::info('Admin found for department admin authentication', [
                        'admin_id' => $admin->id,
                        'username' => $admin->username,
                        'role' => $admin->role,
                        'is_department_admin' => $admin->isDepartmentAdmin(),
                        'password_check' => Hash::check($credentials['password'], $admin->password)
                    ]);
                    
                    // Verify password and role
                    if (Hash::check($credentials['password'], $admin->password)) {
                        // Check if the user is specifically a department admin
                        if (!$admin->isDepartmentAdmin()) {
                            \Log::warning('Non-department-admin tried to login as department admin', [
                                'admin_id' => $admin->id,
                                'actual_role' => $admin->role
                            ]);
                            
                            // Provide specific error messages based on admin type
                            if ($admin->isSuperAdmin()) {
                                $result = back()->withErrors(['ms365_account' => 'Super admins should use the super admin login.'])
                                            ->withInput($request->only('ms365_account', 'login_type'));
                            } elseif ($admin->isOfficeAdmin()) {
                                $result = back()->withErrors(['ms365_account' => 'Office admins should use the office admin login.'])
                                            ->withInput($request->only('ms365_account', 'login_type'));
                            } else {
                                $result = back()->withErrors(['ms365_account' => 'You do not have department admin privileges.'])
                                            ->withInput($request->only('ms365_account', 'login_type'));
                            }
                            
                            // Add attempts warning for role validation errors
                            $attemptsLeft = $this->getRemainingAttempts($request);
                            if ($attemptsLeft > 0) {
                                // Set session variable for consistent access in blade template
                                session(['attempts_left' => $attemptsLeft]);
                                $result->with('attempts_left', $attemptsLeft);

                                \Log::info('Attempts warning added to role validation error', [
                                    'attempts_left' => $attemptsLeft,
                                    'account_identifier' => $this->getAccountIdentifier($request),
                                    'response_type' => get_class($result)
                                ]);
                            }
                            $loginSuccessful = false;
                        } else {
                            // Successful department admin login - manually log in the user
                            Auth::guard('admin')->login($admin);
                            $request->session()->regenerate();
                            
                            // Log admin access with geolocation
                            $geoData = $this->getGeolocationData($request->ip());
                            AdminAccessLog::create([
                                'admin_id' => $admin->id,
                                'role' => $admin->role,
                                'status' => 'success',
                                'ip_address' => $request->ip(),
                                'latitude' => $geoData['latitude'] ?? null,
                                'longitude' => $geoData['longitude'] ?? null,
                                'location_details' => $geoData['location_details'] ?? null,
                                'time_in' => Carbon::now(),
                            ]);
                            
                            $result = redirect()->route('department-admin.dashboard')->with('login_success', true);
                            $loginSuccessful = true;
                            
                            \Log::info('Department admin login successful', [
                                'admin_id' => $admin->id,
                                'username' => $admin->username
                            ]);
                        }
                    } else {
                        // Password verification failed
                        \Log::warning('Department admin password verification failed', [
                            'ms365_account' => $credentials['ms365_account'],
                            'admin_id' => $admin->id
                        ]);
                        
                        // Log failed login attempt with geolocation
                        $geoData = $this->getGeolocationData($request->ip());
                        AdminAccessLog::create([
                            'admin_id' => null,
                            'role' => 'department_admin',
                            'status' => 'failed',
                            'username_attempted' => $credentials['ms365_account'],
                            'ip_address' => $request->ip(),
                            'latitude' => $geoData['latitude'] ?? null,
                            'longitude' => $geoData['longitude'] ?? null,
                            'location_details' => $geoData['location_details'] ?? null,
                            'time_in' => null,
                        ]);
                        
                        $result = back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                                    ->withInput($request->only('ms365_account', 'login_type'));
                        $loginSuccessful = false;
                    }
                } else {
                    // Admin not found
                    \Log::warning('Department admin not found', [
                        'ms365_account' => $credentials['ms365_account']
                    ]);
                    
                    // Log failed login attempt with geolocation
                    $geoData = $this->getGeolocationData($request->ip());
                    AdminAccessLog::create([
                        'admin_id' => null,
                        'role' => 'department_admin',
                        'status' => 'failed',
                        'username_attempted' => $credentials['ms365_account'],
                        'ip_address' => $request->ip(),
                        'latitude' => $geoData['latitude'] ?? null,
                        'longitude' => $geoData['longitude'] ?? null,
                        'location_details' => $geoData['location_details'] ?? null,
                        'time_in' => null,
                    ]);
                    
                    $result = back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                                ->withInput($request->only('ms365_account', 'login_type'));
                    $loginSuccessful = false;
                }
                break;

            case 'office-admin':
                // Handle office admin authentication with MS365 account lookup
                $credentials = $request->only('ms365_account', 'password');
                
                // Enhanced debug logging for office admin authentication
                \Log::info('Office admin authentication attempt - ENHANCED DEBUG', [
                    'all_request_data' => $request->all(),
                    'extracted_credentials' => $credentials,
                    'ms365_account' => $credentials['ms365_account'] ?? 'NOT_PROVIDED',
                    'password_provided' => !empty($credentials['password']),
                    'password_length' => isset($credentials['password']) ? strlen($credentials['password']) : 0,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                // Debug: Show all admins for troubleshooting
                $allAdmins = Admin::all();
                \Log::info('All admins in database', [
                    'total_admins' => $allAdmins->count(),
                    'admins' => $allAdmins->map(function($admin) {
                        return [
                            'id' => $admin->id,
                            'username' => $admin->username,
                            'role' => $admin->role,
                            'password_hash_start' => substr($admin->password ?? 'null', 0, 20)
                        ];
                    })->toArray()
                ]);
                
                // Find admin by MS365 account - handle potential encryption issues
                \Log::info('Searching for office admin with MS365 account', [
                    'searching_for' => $credentials['ms365_account'] ?? 'NULL',
                    'search_method' => 'closure_based_lookup'
                ]);
                
                $admin = Admin::all()->first(function ($admin) use ($credentials) {
                    $match = $admin->username === $credentials['ms365_account'];
                    \Log::info('Office admin comparison', [
                        'admin_id' => $admin->id,
                        'admin_username' => $admin->username,
                        'admin_role' => $admin->role,
                        'searching_for' => $credentials['ms365_account'] ?? 'NULL',
                        'match' => $match ? 'YES' : 'NO'
                    ]);
                    return $match;
                });
                
                if ($admin) {
                    \Log::info('Admin found for office admin authentication', [
                        'admin_id' => $admin->id,
                        'username' => $admin->username,
                        'role' => $admin->role,
                        'is_office_admin' => $admin->isOfficeAdmin(),
                        'password_check' => Hash::check($credentials['password'], $admin->password)
                    ]);
                    
                    // Verify password and role
                    if (Hash::check($credentials['password'], $admin->password)) {
                        // Check if the user is specifically an office admin
                        if (!$admin->isOfficeAdmin()) {
                            \Log::warning('Non-office-admin tried to login as office admin', [
                                'admin_id' => $admin->id,
                                'actual_role' => $admin->role
                            ]);
                            
                            // Provide specific error messages based on admin type
                            if ($admin->isSuperAdmin()) {
                                $result = back()->withErrors(['ms365_account' => 'Super admins should use the super admin login.'])
                                            ->withInput($request->only('ms365_account', 'login_type'));
                            } elseif ($admin->isDepartmentAdmin()) {
                                $result = back()->withErrors(['ms365_account' => 'Department admins should use the department admin login.'])
                                            ->withInput($request->only('ms365_account', 'login_type'));
                            } else {
                                $result = back()->withErrors(['ms365_account' => 'You do not have office admin privileges.'])
                                            ->withInput($request->only('ms365_account', 'login_type'));
                            }
                            
                            // Add attempts warning for role validation errors
                            $attemptsLeft = $this->getRemainingAttempts($request);
                            if ($attemptsLeft > 0) {
                                // Set session variable for consistent access in blade template
                                session(['attempts_left' => $attemptsLeft]);
                                $result->with('attempts_left', $attemptsLeft);

                                \Log::info('Attempts warning added to role validation error', [
                                    'attempts_left' => $attemptsLeft,
                                    'account_identifier' => $this->getAccountIdentifier($request),
                                    'response_type' => get_class($result)
                                ]);
                            }
                            $loginSuccessful = false;
                        } else {
                            // Successful office admin login - manually log in the user
                            Auth::guard('admin')->login($admin);
                            $request->session()->regenerate();
                            
                            // Log admin access with geolocation
                            $geoData = $this->getGeolocationData($request->ip());
                            AdminAccessLog::create([
                                'admin_id' => $admin->id,
                                'role' => $admin->role,
                                'status' => 'success',
                                'ip_address' => $request->ip(),
                                'latitude' => $geoData['latitude'] ?? null,
                                'longitude' => $geoData['longitude'] ?? null,
                                'location_details' => $geoData['location_details'] ?? null,
                                'time_in' => Carbon::now(),
                            ]);
                            
                            $result = redirect()->route('office-admin.dashboard')->with('login_success', true);
                            $loginSuccessful = true;
                            
                            \Log::info('Office admin login successful', [
                                'admin_id' => $admin->id,
                                'username' => $admin->username
                            ]);
                        }
                    } else {
                        // Password verification failed
                        \Log::warning('Office admin password verification failed', [
                            'ms365_account' => $credentials['ms365_account'],
                            'admin_id' => $admin->id
                        ]);
                        
                        // Log failed login attempt with geolocation
                        $geoData = $this->getGeolocationData($request->ip());
                        AdminAccessLog::create([
                            'admin_id' => null,
                            'role' => 'office_admin',
                            'status' => 'failed',
                            'username_attempted' => $credentials['ms365_account'],
                            'ip_address' => $request->ip(),
                            'latitude' => $geoData['latitude'] ?? null,
                            'longitude' => $geoData['longitude'] ?? null,
                            'location_details' => $geoData['location_details'] ?? null,
                            'time_in' => null,
                        ]);
                        
                        $result = back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                                    ->withInput($request->only('ms365_account', 'login_type'));
                        $loginSuccessful = false;
                    }
                } else {
                    // Admin not found
                    \Log::warning('Office admin not found - ENHANCED DEBUG', [
                        'ms365_account' => $credentials['ms365_account'] ?? 'NULL',
                        'all_admin_usernames' => Admin::all()->pluck('username')->toArray(),
                        'total_admins_checked' => Admin::all()->count(),
                        'search_criteria' => 'exact_match_on_username_field'
                    ]);
                    
                    // Log failed login attempt with geolocation
                    $geoData = $this->getGeolocationData($request->ip());
                    AdminAccessLog::create([
                        'admin_id' => null,
                        'role' => 'office_admin',
                        'status' => 'failed',
                        'username_attempted' => $credentials['ms365_account'] ?? 'NULL',
                        'ip_address' => $request->ip(),
                        'latitude' => $geoData['latitude'] ?? null,
                        'longitude' => $geoData['longitude'] ?? null,
                        'location_details' => $geoData['location_details'] ?? null,
                        'time_in' => null,
                    ]);
                    
                    $result = back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                                ->withInput($request->only('ms365_account', 'login_type'));
                    $loginSuccessful = false;
                }
                break;

            default:
                return back()->withErrors(['login_type' => 'Invalid login type selected.']);
        }

        // Handle login attempt tracking and account switching
        $currentlyAuthenticated = $this->getCurrentAuthenticatedAccounts();
        
        if ($loginSuccessful) {
            // Login successful, clear attempts and store account info
            $this->clearLoginAttempts($request);
            $this->storeAccountSession($request, $loginType);
        } elseif (!$loginSuccessful) {
            // Login failed, increment attempt counter regardless of other authenticated accounts
            $this->incrementLoginAttempts($request);
            
            \Log::info('Login attempt failed, incrementing counter', [
                'account_identifier' => $this->getAccountIdentifier($request),
                'attempts_after_increment' => session($this->getLoginAttemptsKey($request), 0),
                'is_locked_out' => $this->isLockedOut($request),
                'lockout_time_remaining' => $this->getLockoutTimeRemaining($request)
            ]);
            
            // Add remaining attempts info to the error response
            $attemptsLeft = $this->getRemainingAttempts($request);
            if ($attemptsLeft > 0) {
                // Set session variable for consistent access in blade template
                session(['attempts_left' => $attemptsLeft]);
                $result->with('attempts_left', $attemptsLeft);

                \Log::info('Attempts warning added to response', [
                    'attempts_left' => $attemptsLeft,
                    'account_identifier' => $this->getAccountIdentifier($request),
                    'response_type' => get_class($result)
                ]);
            }
        }

        return $result;
    }
    
    
    /**
     * Sanitize all input data
     */
    private function sanitizeInputData(Request $request)
    {
        $allInput = $request->all();
        $sanitized = [];
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                if ($this->securityService) {
                    $sanitized[$key] = $this->securityService->sanitizeInput($value);
                } else {
                    // Fallback sanitization
                    $sanitized[$key] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
                }
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        $request->merge($sanitized);
    }

    public function showSignupForm(Request $request)
    {
        $type = $request->route()->getName() === 'ms365.signup' ? 'ms365' : 'gmail';
        return view('auth.' . $type . '-signup');
    }

    public function sendRegistrationLink(Request $request)
    {
        // Enhanced security validation
        $this->validateSecureInput($request);
        
        $type = $request->route()->getName() === 'ms365.signup.send' ? 'ms365' : 'gmail';
        $secureRules = $this->getSecureValidationRules();
        $secureMessages = $this->getSecureValidationMessages();
        
        $emailField = $type . '_account';
        $request->validate([
            $emailField => array_merge($secureRules[$emailField], [
                'required',
                'unique:users,' . $emailField,
            ]),
        ], $secureMessages);

        $email = $request->{$type . '_account'};

        $registrationUrl = URL::temporarySignedRoute(
            $type . '.register.form',
            now()->addMinutes(30),
            ['email' => $email]
        );

        try {
            Mail::send('emails.' . $type . '-registration', [
                'email' => $email,
                'registrationUrl' => $registrationUrl
            ], function ($message) use ($email) {
                $message->to($email)
                       ->subject('Complete Your Registration - MCC News Aggregator');
            });

            return back()->with('status', 'Registration link sent to your email.');
        } catch (\Exception $e) {
            \Log::error($type . ' registration email failed: ' . $e->getMessage());
            return back()->withErrors('Failed to send registration email. Please try again.');
        }
    }

    public function showRegistrationForm(Request $request)
    {
        $type = $request->route()->getName() === 'ms365.register.form' ? 'ms365' : 'gmail';

        if (!$request->hasValidSignature()) {
            abort(401, 'This link has expired or is invalid.');
        }

        $email = $request->email;

        if (User::where($type . '_account', $email)->exists()) {
            return redirect()->route('login')->withErrors('This email address is already registered.');
        }

        return view('auth.' . $type . '-register', [
            'email' => $email
        ]);
    }

    public function completeRegistration(Request $request)
    {
        // Enhanced security validation
        $this->validateSecureInput($request);
        
        $type = $request->route()->getName() === 'ms365.register.complete' ? 'ms365' : 'gmail';
        $secureRules = $this->getSecureValidationRules();
        $secureMessages = $this->getSecureValidationMessages();

        $emailField = $type . '_account';
        $validator = \Validator::make($request->all(), [
            'email' => array_merge($secureRules[$emailField], [
                'required',
                'unique:users,' . $emailField,
            ]),
            'first_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\' ]+$/u',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in first name.');
                    }
                },
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[\pL\' ]+$/u',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in middle name.');
                    }
                },
            ],
            'surname' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\' ]+$/u',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in surname.');
                    }
                },
            ],
            'role' => 'required|in:student,faculty',
            'department' => 'required_if:role,student,faculty|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management',
            'year_level' => 'required_if:role,student|in:1st Year,2nd Year,3rd Year,4th Year',
            'password' => array_merge($secureRules['password'], [
                'required',
                'min:8',
                'confirmed',
                new StrongPassword(),
            ]),
        ], array_merge($secureMessages, [
            'first_name.regex' => 'First name should only contain letters, spaces, and apostrophes',
            'middle_name.regex' => 'Middle name should only contain letters, spaces, and apostrophes',
            'surname.regex' => 'Surname should only contain letters, spaces, and apostrophes',
            'department.required_if' => 'Department is required for your selected role',
            'year_level.required_if' => 'Year level is required for students',
        ]));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $userData = [
                $type . '_account' => $request->email,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'surname' => $request->surname,
                'role' => $request->role,
                'department' => $request->department,
                'password' => \Hash::make($request->password),
                'email_verified_at' => now(),
            ];

            // Only set year_level for students
            if ($request->role === 'student') {
                $userData['year_level'] = $request->year_level;
            }

            $user = User::create($userData);

            auth()->login($user);

            return redirect()->route('user.dashboard')->with('status', 'Registration successful! Welcome to MCC News Aggregator.');
        } catch (\Exception $e) {
            \Log::error($type . ' registration failed: ' . $e->getMessage());
            return back()->withErrors('Registration failed. Please try again.')->withInput();
        }
    }

    /**
     * Handle unified logout
     */
    public function logout(Request $request)
    {
        // Determine which guard to logout from
        $adminUser = Auth::guard('admin')->user();
        $webUser = Auth::guard('web')->user();
        
        // Log the logout event for security monitoring
        \Log::info('User logout', [
            'admin_user_id' => $adminUser ? $adminUser->id : null,
            'web_user_id' => $webUser ? $webUser->id : null,
            'admin_username' => $adminUser ? $adminUser->username : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
        
        // Logout from appropriate guard
        if ($adminUser) {
            Auth::guard('admin')->logout();
        }
        if ($webUser) {
            Auth::guard('web')->logout();
        }
        
        // Clear authenticated accounts session data
        $request->session()->forget('authenticated_accounts');
        
        // Clear login attempt data but preserve other session data
        $sessionData = $request->session()->all();
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
                $request->session()->forget($key);
            }
        }
        
        // Regenerate session ID for security but don't flush everything
        $request->session()->regenerate(true);
        
        return redirect()->route('login')
                        ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(Request $request)
    {
        // Enhanced security validation
        $this->validateSecureInput($request);
        
        $secureRules = $this->getSecureValidationRules();
        $secureMessages = $this->getSecureValidationMessages();
        
        $request->validate([
            'ms365_account' => array_merge($secureRules['ms365_account'], [
                'required',
            ]),
        ], array_merge($secureMessages, [
            'ms365_account.required' => 'MS365 email address is required',
        ]));

        // Check if user exists - since ms365_account is encrypted, we need to check all users
        $email = $request->ms365_account;
        $user = User::all()->first(function ($user) use ($email) {
            return $user->ms365_account === $email;
        });
        
        if (!$user) {
            return back()->withErrors([
                'ms365_account' => 'This account is not registered. Please sign up first.'
            ])->with('show_signup', true);
        }

        // Generate reset token
        $token = Str::random(64);
        
        // Store token in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->ms365_account],
            [
                'email' => $request->ms365_account,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send reset email
        try {
            $resetUrl = route('password.reset', ['token' => $token]) . '?email=' . urlencode($request->ms365_account);
            
            Mail::send('emails.password-reset', [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'token' => $token
            ], function ($message) use ($request) {
                $message->to($request->ms365_account)
                       ->subject('Password Reset Request - MCC News Aggregator');
            });

            return back()->with('status', 'Password reset link has been sent to your MS365 email account.');
        } catch (\Exception $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());
            return back()->withErrors('Failed to send password reset email. Please try again.');
        }
    }

    /**
     * Show the reset password form
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('password.request')
                           ->withErrors(['email' => 'Invalid reset link.']);
        }

        // Verify token exists and is valid
        $resetRecord = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return redirect()->route('password.request')
                           ->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_resets')->where('email', $email)->delete();
            return redirect()->route('password.request')
                           ->withErrors(['email' => 'Reset link has expired. Please request a new one.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Reset the password
     */
    public function resetPassword(Request $request)
    {
        // Enhanced security validation
        $this->validateSecureInput($request);
        
        $secureRules = $this->getSecureValidationRules();
        $secureMessages = $this->getSecureValidationMessages();
        
        $request->validate([
            'token' => 'required|string|max:255',
            'email' => array_merge($secureRules['ms365_account'], ['required']),
            'password' => array_merge($secureRules['password'], [
                'required',
                'min:8',
                'confirmed',
                new StrongPassword(),
            ]),
        ], $secureMessages);

        // Verify token
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        // Check if token is expired
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Reset link has expired.']);
        }

        // Find user and update password - since ms365_account is encrypted, we need to check all users
        $email = $request->email;
        $user = User::all()->first(function ($user) use ($email) {
            return $user->ms365_account === $email;
        });
        
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete the reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')
                        ->with('success', 'Your password has been successfully reset. You can now log in with your new password.');
    }

    /**
     * Get the login attempts session key for specific account
     */
    private function getLoginAttemptsKey(Request $request)
    {
        $identifier = $this->getAccountIdentifier($request);
        return 'login_attempts_' . md5($identifier);
    }

    /**
     * Get the lockout session key for specific account
     */
    private function getLockoutKey(Request $request)
    {
        $identifier = $this->getAccountIdentifier($request);
        return 'lockout_time_' . md5($identifier);
    }

    /**
     * Get unique account identifier based on login type
     */
    private function getAccountIdentifier(Request $request)
    {
        $loginType = $request->login_type;
        
        switch ($loginType) {
            case 'ms365':
                return $loginType . '_' . ($request->ms365_account ?? 'unknown');
            case 'user':
                return $loginType . '_' . ($request->gmail_account ?? 'unknown');
            case 'superadmin':
                return $loginType . '_' . ($request->username ?? 'unknown');
            case 'department-admin':
            case 'office-admin':
                // Department and office admins now use MS365 accounts
                return $loginType . '_' . ($request->ms365_account ?? 'unknown');
            default:
                return 'unknown_' . $request->ip();
        }
    }

    /**
     * Increment login attempts
     */
    private function incrementLoginAttempts(Request $request)
    {
        $key = $this->getLoginAttemptsKey($request);
        $attempts = session($key, 0) + 1;
        session([$key => $attempts]);

        \Log::info('Login attempts incremented', [
            'session_key' => $key,
            'attempts' => $attempts,
            'account_identifier' => $this->getAccountIdentifier($request)
        ]);

        // If max attempts reached, set lockout time
        if ($attempts >= 3) {
            $lockoutKey = $this->getLockoutKey($request);
            $lockoutTime = now()->addMinutes(3);
            session([$lockoutKey => $lockoutTime]);
            
            \Log::warning('Account locked out after 3 attempts', [
                'lockout_key' => $lockoutKey,
                'lockout_time' => $lockoutTime->toDateTimeString(),
                'account_identifier' => $this->getAccountIdentifier($request)
            ]);
        }
    }

    /**
     * Clear login attempts
     */
    private function clearLoginAttempts(Request $request)
    {
        $attemptsKey = $this->getLoginAttemptsKey($request);
        $lockoutKey = $this->getLockoutKey($request);
        
        session()->forget([$attemptsKey, $lockoutKey]);
    }

    /**
     * Check if user is locked out
     */
    private function isLockedOut(Request $request)
    {
        $lockoutKey = $this->getLockoutKey($request);
        $lockoutTime = session($lockoutKey);
        
        if (!$lockoutTime) {
            return false;
        }
        
        try {
            // Ensure we have a valid Carbon instance
            $lockoutTime = is_string($lockoutTime) ? \Carbon\Carbon::parse($lockoutTime) : $lockoutTime;
            
            // Skip if not a valid Carbon instance
            if (!$lockoutTime instanceof \Carbon\Carbon) {
                return false;
            }
            
            // Check if lockout time has passed
            if (now()->greaterThan($lockoutTime)) {
                // Lockout expired, clear it
                $this->clearLoginAttempts($request);
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            // If there's an error parsing the date, assume not locked
            return false;
        }
    }

    /**
     * Get remaining lockout time in minutes
     */
    private function getLockoutTimeRemaining(Request $request)
    {
        $lockoutKey = $this->getLockoutKey($request);
        $lockoutTime = session($lockoutKey);
        
        if (!$lockoutTime) {
            return 0;
        }
        
        try {
            // Ensure we have a valid Carbon instance
            $lockoutTime = is_string($lockoutTime) ? \Carbon\Carbon::parse($lockoutTime) : $lockoutTime;
            
            // Skip if not a valid Carbon instance
            if (!$lockoutTime instanceof \Carbon\Carbon) {
                return 0;
            }
            
            // Calculate remaining time more precisely
            if (now()->greaterThan($lockoutTime)) {
                return 0; // Lockout has expired
            }
            
            // Use seconds for more precision, then convert to minutes
            $remainingSeconds = $lockoutTime->diffInSeconds(now());
            $remainingMinutes = ceil($remainingSeconds / 60);
            
            \Log::info('Lockout time calculation', [
                'lockout_time' => $lockoutTime->toDateTimeString(),
                'current_time' => now()->toDateTimeString(),
                'remaining_seconds' => $remainingSeconds,
                'remaining_minutes' => $remainingMinutes
            ]);
            
            return max(0, $remainingMinutes);
        } catch (\Exception $e) {
            // If there's an error parsing the date, return 0
            return 0;
        }
    }

    /**
     * Get remaining lockout time in seconds (for more accurate frontend countdown)
     */
    private function getLockoutTimeRemainingSeconds(Request $request)
    {
        $lockoutKey = $this->getLockoutKey($request);
        $lockoutTime = session($lockoutKey);
        
        if (!$lockoutTime) {
            return 0;
        }
        
        try {
            $lockoutTime = is_string($lockoutTime) ? \Carbon\Carbon::parse($lockoutTime) : $lockoutTime;
            
            if (!$lockoutTime instanceof \Carbon\Carbon) {
                return 0;
            }
            
            if (now()->greaterThan($lockoutTime)) {
                return 0;
            }
            
            return max(0, $lockoutTime->diffInSeconds(now()));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get remaining login attempts
     */
    private function getRemainingAttempts(Request $request)
    {
        $key = $this->getLoginAttemptsKey($request);
        $attempts = session($key, 0);
        return max(0, 3 - $attempts);
    }

    /**
     * Store account session information
     */
    private function storeAccountSession(Request $request, $loginType)
    {
        $accounts = session('authenticated_accounts', []);
        
        $accountInfo = [
            'type' => $loginType,
            'user_id' => auth()->id(),
            'name' => auth()->user()->name ?? auth()->user()->username ?? 'Unknown',
            'email' => $this->getUserEmail(auth()->user(), $loginType),
            'logged_in_at' => now()->toDateTimeString(),
        ];
        
        // Remove existing account of same type to prevent duplicates
        $accounts = array_filter($accounts, function($account) use ($loginType) {
            return $account['type'] !== $loginType;
        });
        
        $accounts[] = $accountInfo;
        session(['authenticated_accounts' => $accounts]);
    }

    /**
     * Get current authenticated accounts
     */
    private function getCurrentAuthenticatedAccounts()
    {
        return session('authenticated_accounts', []);
    }

    /**
     * Get user email based on account type
     */
    private function getUserEmail($user, $loginType)
    {
        if (!$user) return 'Unknown';
        
        switch ($loginType) {
            case 'ms365':
                return $user->ms365_account ?? $user->email ?? 'Unknown';
            case 'user':
                return $user->gmail_account ?? $user->email ?? 'Unknown';
            case 'superadmin':
            case 'department-admin':
            case 'office-admin':
                return $user->username ?? 'Unknown';
            default:
                return $user->email ?? $user->username ?? 'Unknown';
        }
    }

    /**
     * Get display name for login type
     */
    private function getLoginTypeDisplayName($loginType)
    {
        switch ($loginType) {
            case 'ms365':
                return 'MS365';
            case 'user':
                return 'Gmail';
            case 'superadmin':
                return 'Super Admin';
            case 'department-admin':
                return 'Department Admin';
            case 'office-admin':
                return 'Office Admin';
            default:
                return 'Account';
        }
    }

    /**
     * Switch to a different account
     */
    public function switchAccount(Request $request)
    {
        $request->validate([
            'account_type' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        $accounts = $this->getCurrentAuthenticatedAccounts();
        $targetAccount = collect($accounts)->firstWhere('type', $request->account_type);

        if (!$targetAccount) {
            return back()->withErrors(['account' => 'Account not found or session expired.']);
        }

        // Switch authentication context based on account type
        switch ($request->account_type) {
            case 'ms365':
            case 'user':
                $user = User::find($request->user_id);
                if ($user) {
                    auth()->login($user);
                    return redirect()->route('user.dashboard');
                }
                break;
            
            case 'superadmin':
            case 'department-admin':
            case 'office-admin':
                $admin = Admin::find($request->user_id);
                if ($admin) {
                    auth('admin')->login($admin);
                    return redirect()->route($request->account_type . '.dashboard');
                }
                break;
        }

        return back()->withErrors(['account' => 'Unable to switch to the selected account.']);
    }

    /**
     * Remove an account from the session
     */
    public function removeAccount(Request $request)
    {
        $request->validate([
            'account_type' => 'required|string',
        ]);

        $accounts = $this->getCurrentAuthenticatedAccounts();
        $accounts = array_filter($accounts, function($account) use ($request) {
            return $account['type'] !== $request->account_type;
        });

        session(['authenticated_accounts' => array_values($accounts)]);

        // If removing current account, logout
        if (auth()->check()) {
            $currentType = $this->getCurrentAccountType();
            if ($currentType === $request->account_type) {
                auth()->logout();
                
                // If there are other accounts, switch to the first one
                if (!empty($accounts)) {
                    $firstAccount = reset($accounts);
                    return $this->switchAccount(new Request([
                        'account_type' => $firstAccount['type'],
                        'user_id' => $firstAccount['user_id']
                    ]));
                }
            }
        }

        return back()->with('success', 'Account removed successfully.');
    }

    /**
     * Get current account type
     */
    private function getCurrentAccountType()
    {
        if (auth('admin')->check()) {
            $admin = auth('admin')->user();
            return $admin->role === 'superadmin' ? 'superadmin' : 
                   ($admin->role === 'department_admin' ? 'department-admin' : 'office-admin');
        } elseif (auth()->check()) {
            $user = auth()->user();
            return $user->ms365_account ? 'ms365' : 'user';
        }
        return null;
    }

    /**
     * Get all locked accounts with their lockout information
     */
    private function getLockedAccounts()
    {
        $lockedAccounts = [];
        $sessionData = session()->all();
        
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'lockout_time_') === 0) {
                try {
                    // Ensure we have a valid Carbon instance
                    $lockoutTime = is_string($value) ? \Carbon\Carbon::parse($value) : $value;
                    
                    // Skip if not a valid Carbon instance
                    if (!$lockoutTime instanceof \Carbon\Carbon) {
                        continue;
                    }
                    
                    if (now()->lessThan($lockoutTime)) {
                        // Find corresponding attempts key
                        $attemptsKey = str_replace('lockout_time_', 'login_attempts_', $key);
                        $attempts = session($attemptsKey, 0);
                        
                        $lockedAccounts[] = [
                            'key' => $key,
                            'lockout_time' => $lockoutTime,
                            'attempts' => $attempts,
                            'remaining_minutes' => now()->diffInMinutes($lockoutTime, false)
                        ];
                    }
                } catch (\Exception $e) {
                    // Skip invalid lockout entries
                    continue;
                }
            }
        }
        
        return $lockedAccounts;
    }

    /**
     * Check if specific account identifier is locked
     */
    public function isAccountLocked($accountIdentifier)
    {
        $key = 'lockout_time_' . md5($accountIdentifier);
        $lockoutTime = session($key);
        
        if (!$lockoutTime) {
            return false;
        }
        
        try {
            // Ensure we have a valid Carbon instance
            $lockoutTime = is_string($lockoutTime) ? \Carbon\Carbon::parse($lockoutTime) : $lockoutTime;
            
            // Skip if not a valid Carbon instance
            if (!$lockoutTime instanceof \Carbon\Carbon) {
                return false;
            }
            
            if (now()->greaterThan($lockoutTime)) {
                // Lockout expired, clear it
                $attemptsKey = 'login_attempts_' . md5($accountIdentifier);
                session()->forget([$key, $attemptsKey]);
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            // If there's an error parsing the date, assume not locked
            return false;
        }
    }

    /**
     * Clear all login attempts and lockouts (for debugging/admin purposes)
     */
    public function clearAllLoginAttempts(Request $request)
    {
        $sessionData = session()->all();
        $keysToForget = [];
        
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
                $keysToForget[] = $key;
            }
        }
        
        if (!empty($keysToForget)) {
            session()->forget($keysToForget);
        }
        
        return back()->with('success', 'All login attempts and lockouts have been cleared.');
    }

    /**
     * Debug lockout status (for troubleshooting)
     */
    public function debugLockoutStatus(Request $request)
    {
        $sessionData = session()->all();
        $lockoutData = [];
        
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
                $lockoutData[$key] = $value;
            }
        }
        
        $currentAccountIdentifier = $this->getAccountIdentifier($request);
        $isLockedOut = $this->isLockedOut($request);
        $timeRemaining = $this->getLockoutTimeRemaining($request);
        $attemptsRemaining = $this->getRemainingAttempts($request);
        
        return response()->json([
            'current_account_identifier' => $currentAccountIdentifier,
            'is_locked_out' => $isLockedOut,
            'lockout_time_remaining_minutes' => $timeRemaining,
            'login_attempts_remaining' => $attemptsRemaining,
            'session_lockout_data' => $lockoutData,
            'lockout_key' => $this->getLockoutKey($request),
            'attempts_key' => $this->getLoginAttemptsKey($request),
            'current_time' => now()->toDateTimeString()
        ]);
    }

    /**
     * Clear current account lockout (called by frontend after countdown expires)
     */
    public function clearCurrentLockout(Request $request)
    {
        $this->clearLoginAttempts($request);
        
        \Log::info('Lockout cleared by frontend countdown', [
            'account_identifier' => $this->getAccountIdentifier($request),
            'ip' => $request->ip()
        ]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Clear login attempts for a specific account type and username
     */
    public function clearSpecificLoginAttempts(Request $request)
    {
        $request->validate([
            'login_type' => 'required|in:user,ms365,superadmin,department-admin,office-admin',
            'username' => 'nullable|string',
            'email' => 'nullable|string',
        ]);

        // Create a mock request to get the account identifier
        $mockRequest = new Request();
        $mockRequest->merge([
            'login_type' => $request->login_type,
            'username' => $request->username,
            'ms365_account' => $request->email,
            'gmail_account' => $request->email,
        ]);

        $identifier = $this->getAccountIdentifier($mockRequest);
        $attemptsKey = 'login_attempts_' . md5($identifier);
        $lockoutKey = 'lockout_time_' . md5($identifier);
        
        session()->forget([$attemptsKey, $lockoutKey]);
        
        return back()->with('success', "Login attempts cleared for {$request->login_type} account: " . ($request->username ?: $request->email));
    }

    /**
     * Validate secure input - fallback method if SecurityService not available
     */
    private function validateSecureInput(Request $request)
    {
        if ($this->securityService) {
            return $this->securityService->validateSecureInput($request);
        }
        
        // Fallback validation
        $allInput = $request->all();
        foreach ($allInput as $key => $value) {
            if (is_string($value) && $this->containsDangerousPatterns($value)) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], []),
                    [$key => ['Invalid characters detected in input.']]
                );
            }
        }
    }

    /**
     * Get secure validation rules - fallback method if SecurityService not available
     */
    private function getSecureValidationRules()
    {
        if ($this->securityService) {
            return $this->securityService->getSecureValidationRules();
        }
        
        // Fallback rules
        return [
            'ms365_account' => [
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in email address.');
                    }
                },
            ],
            'gmail_account' => [
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in email address.');
                    }
                },
            ],
            'username' => [
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9._-]+$/',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in username.');
                    }
                },
            ],
            'password' => [
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in password.');
                    }
                },
            ],
        ];
    }

    /**
     * Get secure validation messages - fallback method if SecurityService not available
     */
    private function getSecureValidationMessages()
    {
        if ($this->securityService) {
            return $this->securityService->getSecureValidationMessages();
        }
        
        // Fallback messages
        return [
            'ms365_account.email' => 'Please enter a valid MS365 email address.',
            'ms365_account.regex' => 'MS365 email format is invalid.',
            'gmail_account.email' => 'Please enter a valid Gmail address.',
            'gmail_account.regex' => 'Gmail format is invalid.',
            'username.regex' => 'Username can only contain letters, numbers, dots, underscores, and hyphens.',
            'password.required' => 'Password is required.',
        ];
    }

    /**
     * Check if input contains dangerous patterns - fallback method if SecurityService not available
     */
    private function containsDangerousPatterns($input)
    {
        if ($this->securityService) {
            return $this->securityService->containsDangerousPatterns($input);
        }
        
        // Fallback dangerous patterns check
        $dangerousPatterns = [
            // SQL Injection patterns
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b)/i',
            '/(\bOR\s+1\s*=\s*1\b|\bAND\s+1\s*=\s*1\b)/i',
            '/(\'\s*OR\s*\'\s*=\s*\'|\"\s*OR\s*\"\s*=\s*\")/i',
            
            // XSS patterns
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            
            // Command injection patterns
            '/(\bsystem\b|\bexec\b|\bshell_exec\b|\bpassthru\b)/i',
            '/(\|\s*\w+|\&\&\s*\w+|\;\s*\w+)/i',
            
            // PHP code injection patterns
            '/(\beval\b|\binclude\b|\brequire\b|\bfile_get_contents\b)/i',
            '/<\?php/i',
            
            // Path traversal patterns
            '/(\.\.\/)|(\.\.\\\\)/i',
            
            // Control characters and null bytes
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/i',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }

}