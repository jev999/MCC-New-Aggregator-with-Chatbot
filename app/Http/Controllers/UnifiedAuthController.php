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
// reCAPTCHA rule removed
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
use App\Models\PasswordReset;
use App\Services\GeolocationService;
use App\Services\MicrosoftGraphService;
use Carbon\Carbon;
class UnifiedAuthController extends Controller
{
    protected $securityService;
    protected $geolocationService;
    protected $graphService;
    
    public function __construct()
    {
        // Initialize security service if available, otherwise use fallback methods
        if (class_exists('\App\Services\SecurityService')) {
            $this->securityService = app('\App\Services\SecurityService');
        }
        $this->geolocationService = new GeolocationService();
        if (class_exists('App\\Services\\MicrosoftGraphService')) {
            $this->graphService = new MicrosoftGraphService();
        }
    }

    /**
     * Get geolocation data for logging
     * Returns IP-based location with clear labeling that GPS will update later
     */
    protected function getGeolocationData($ip)
    {
        $location = $this->geolocationService->getLocationFromIp($ip);
        
        // Add clear note that this is IP-based; when GPS is available it can refine further
        if ($location && isset($location['location_details'])) {
            if (strpos($location['location_details'], '[IP-Based') === false) {
                $location['location_details'] .= ' [IP-Based]';
            }
        }
        
        return $location;
    }

    /**
     * Resolve the best client IP and its geolocation data in one step.
     *
     * @return array{0:string,1:array|null}
     */
    protected function resolveIpAndLocation(Request $request): array
    {
        $clientIp = $this->resolveClientIp($request);
        $geoData = $this->getGeolocationData($clientIp);

        return [$clientIp, $geoData];
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
        // Verify reCAPTCHA v3 token
        $recaptchaToken = $request->input('recaptcha_token');
        
        if (!$recaptchaToken) {
            \Log::warning('reCAPTCHA token missing', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return back()->withErrors([
                'recaptcha' => 'Security verification failed. Please try again.'
            ])->withInput($request->except('password'));
        }
        
        // Verify token with Google reCAPTCHA API
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $recaptchaToken,
                'remoteip' => $request->ip()
            ]);
            
            $recaptchaResult = $response->json();
            
            \Log::info('reCAPTCHA verification result', [
                'success' => $recaptchaResult['success'] ?? false,
                'score' => $recaptchaResult['score'] ?? 0,
                'action' => $recaptchaResult['action'] ?? 'unknown',
                'hostname' => $recaptchaResult['hostname'] ?? 'unknown',
                'ip' => $request->ip()
            ]);
            
            // Check if verification was successful
            if (!isset($recaptchaResult['success']) || !$recaptchaResult['success']) {
                \Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $recaptchaResult['error-codes'] ?? [],
                    'ip' => $request->ip()
                ]);
                return back()->withErrors([
                    'recaptcha' => 'Security verification failed. Please refresh the page and try again.'
                ])->withInput($request->except('password'));
            }
            
            // Verify the action matches what we expect
            if (isset($recaptchaResult['action']) && $recaptchaResult['action'] !== 'login') {
                \Log::warning('reCAPTCHA action mismatch', [
                    'expected' => 'login',
                    'received' => $recaptchaResult['action'],
                    'ip' => $request->ip()
                ]);
                return back()->withErrors([
                    'recaptcha' => 'Security verification failed. Invalid action.'
                ])->withInput($request->except('password'));
            }
            
            // Check the score against threshold
            $score = $recaptchaResult['score'] ?? 0;
            $threshold = config('services.recaptcha.threshold', 0.5);
            
            if ($score < $threshold) {
                \Log::warning('reCAPTCHA score below threshold', [
                    'score' => $score,
                    'threshold' => $threshold,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                // For low scores, you can:
                // 1. Block the login attempt
                // 2. Require additional verification (like email OTP)
                // 3. Add to a review queue
                
                // Option 1: Block with informative message
                return back()->withErrors([
                    'recaptcha' => 'We detected unusual activity. Please try again later or contact support if this continues.'
                ])->withInput($request->except('password'));
            }
            
        } catch (\Exception $e) {
            \Log::error('reCAPTCHA verification exception', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
            
            // In production, you might want to allow login if reCAPTCHA service is down
            // For security, we'll block it here
            return back()->withErrors([
                'recaptcha' => 'Security verification service is temporarily unavailable. Please try again later.'
            ])->withInput($request->except('password'));
        }
        
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
            $validationRules['ms365_account'] = array_merge(['required'], $secureRules['ms365_account']);
            // Require admin consent checkbox
            $validationRules['location_permission'] = 'accepted';
        } elseif (in_array($loginType, ['ms365', 'department-admin', 'office-admin'])) {
            $validationRules['ms365_account'] = array_merge(['required'], $secureRules['ms365_account']);
            // Require admin consent checkbox for department and office admins
            if (in_array($loginType, ['department-admin', 'office-admin'])) {
                $validationRules['location_permission'] = 'accepted';
            }
        } elseif ($loginType === 'user') {
            $validationRules['gmail_account'] = array_merge(['required'], $secureRules['gmail_account']);
        }
        
        // Validate the request
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
                    
                    // Check if account is locked
                    if ($user->isLocked()) {
                        $lockTimeRemaining = $user->getLockTimeRemaining();
                        \Log::warning('MS365 login attempt on locked account', [
                            'ms365_account' => $credentials['ms365_account'],
                            'user_id' => $user->id,
                            'lock_time_remaining' => $lockTimeRemaining
                        ]);
                        
                        $result = back()->withErrors(['ms365_account' => "Account is locked. Please try again in {$lockTimeRemaining} minutes."])
                                    ->withInput($request->only('ms365_account', 'login_type'));
                        $loginSuccessful = false;
                    }
                    // Check if password has expired
                    elseif ($user->isPasswordExpired()) {
                        \Log::warning('MS365 login attempt with expired password', [
                            'ms365_account' => $credentials['ms365_account'],
                            'user_id' => $user->id,
                            'password_expires_at' => $user->password_expires_at
                        ]);
                        
                        $result = back()->withErrors(['ms365_account' => 'Your password has expired. Please reset your password.'])
                                    ->withInput($request->only('ms365_account', 'login_type'));
                        $loginSuccessful = false;
                    }
                    // Verify password
                    elseif (Hash::check($credentials['password'], $user->password)) {
                        // Check if password must be changed
                        if ($user->mustChangePassword()) {
                            \Log::info('MS365 login successful but password must be changed', [
                                'user_id' => $user->id,
                                'ms365_account' => $user->ms365_account
                            ]);
                            
                            // Log in user but redirect to password change
                            auth()->login($user, $request->filled('remember'));
                            $request->session()->regenerate();
                            $result = redirect()->route('password.change')->with('warning', 'You must change your password before continuing.');
                            $loginSuccessful = true;
                        } else {
                            // Step 1: Generate OTP and send to user's MS365 account
                            $otpSent = $this->sendOTP($request, 'ms365', $user, $user->ms365_account);
                            
                            if ($otpSent) {
                                \Log::info('MS365 OTP sent', ['user_id' => $user->id, 'email' => $user->ms365_account]);
                                // Redirect to unified login with OTP modal flag
                                $result = redirect()->route('login', ['type' => 'ms365'])
                                    ->with('show_otp_modal', true)
                                    ->with('otp_login_type', 'ms365')
                                    ->with('status', 'We sent a 6-digit OTP to your MS365 email. Please enter it to continue.');
                                $loginSuccessful = true; // mark as successful step to avoid attempts increment
                            } else {
                                // If email could not be sent, treat as error
                                $result = back()->withErrors(['ms365_account' => 'Unable to send OTP email. Please try again later.'])
                                               ->withInput($request->only('ms365_account', 'login_type'));
                                $loginSuccessful = false;
                            }
                        }
                    } else {
                        // Password verification failed - increment failed attempts
                        $user->incrementFailedLoginAttempts();
                        \Log::warning('MS365 password verification failed', [
                            'ms365_account' => $credentials['ms365_account'],
                            'user_id' => $user->id,
                            'failed_attempts' => $user->failed_login_attempts
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
                    
                    // Check if account is locked
                    if ($user->isLocked()) {
                        $lockTimeRemaining = $user->getLockTimeRemaining();
                        \Log::warning('User login attempt on locked account', [
                            'gmail_account' => $credentials['gmail_account'],
                            'user_id' => $user->id,
                            'lock_time_remaining' => $lockTimeRemaining
                        ]);
                        
                        $result = back()->withErrors(['gmail_account' => "Account is locked. Please try again in {$lockTimeRemaining} minutes."])
                                    ->withInput($request->only('gmail_account', 'login_type'));
                        $loginSuccessful = false;
                    }
                    // Check if password has expired
                    elseif ($user->isPasswordExpired()) {
                        \Log::warning('User login attempt with expired password', [
                            'gmail_account' => $credentials['gmail_account'],
                            'user_id' => $user->id,
                            'password_expires_at' => $user->password_expires_at
                        ]);
                        
                        $result = back()->withErrors(['gmail_account' => 'Your password has expired. Please reset your password.'])
                                    ->withInput($request->only('gmail_account', 'login_type'));
                        $loginSuccessful = false;
                    }
                    // Verify password
                    elseif (Hash::check($credentials['password'], $user->password)) {
                        // Check if password must be changed
                        if ($user->mustChangePassword()) {
                            \Log::info('User login successful but password must be changed', [
                                'user_id' => $user->id,
                                'gmail_account' => $user->gmail_account
                            ]);
                            
                            // Log in user but redirect to password change
                            auth()->login($user, $request->filled('remember'));
                            $request->session()->regenerate();
                            $result = redirect()->route('password.change')->with('warning', 'You must change your password before continuing.');
                            $loginSuccessful = true;
                        } else {
                            // Step 1: Generate OTP and send to user's Gmail account
                            $otpSent = $this->sendOTP($request, 'user', $user, $user->gmail_account);
                            
                            if ($otpSent) {
                                \Log::info('User OTP sent', ['user_id' => $user->id, 'email' => $user->gmail_account]);
                                // Redirect to unified login with OTP modal flag
                                $result = redirect()->route('login', ['type' => 'user'])
                                    ->with('show_otp_modal', true)
                                    ->with('otp_login_type', 'user')
                                    ->with('status', 'We sent a 6-digit OTP to your Gmail account. Please enter it to continue.');
                                $loginSuccessful = true; // mark as successful step to avoid attempts increment
                            } else {
                                // If email could not be sent, treat as error
                                $result = back()->withErrors(['gmail_account' => 'Unable to send OTP email. Please try again later.'])
                                               ->withInput($request->only('gmail_account', 'login_type'));
                                $loginSuccessful = false;
                            }
                            
                            \Log::info('User login initiated with OTP', [
                                'user_id' => $user->id,
                                'gmail_account' => $user->gmail_account
                            ]);
                        }
                    } else {
                        // Password verification failed - increment failed attempts
                        $user->incrementFailedLoginAttempts();
                        \Log::warning('User password verification failed', [
                            'gmail_account' => $credentials['gmail_account'],
                            'user_id' => $user->id,
                            'failed_attempts' => $user->failed_login_attempts
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
                // Handle superadmin authentication with MS365 account lookup (same as other admins)
                $credentials = $request->only('ms365_account', 'password');
                
                // Enhanced debug logging for superadmin authentication
                \Log::info('Superadmin authentication attempt - ENHANCED DEBUG', [
                    'ms365_account' => $credentials['ms365_account'] ?? null,
                    'ms365_account_length' => strlen($credentials['ms365_account'] ?? ''),
                    'password_provided' => !empty($credentials['password']),
                    'password_length' => strlen($credentials['password'] ?? ''),
                    'ip' => $request->ip()
                ]);
                
                // Log all admins in database for debugging
                $allAdmins = Admin::all();
                \Log::info('All admins in database for superadmin auth', [
                    'total_admins' => $allAdmins->count(),
                    'admins' => $allAdmins->map(function($admin) {
                        return [
                            'id' => $admin->id,
                            'username' => $admin->username,
                            'username_length' => strlen($admin->username ?? ''),
                            'role' => $admin->role,
                            'is_superadmin' => $admin->isSuperAdmin(),
                            'password_hash_exists' => !empty($admin->password),
                            'password_hash_length' => strlen($admin->password ?? '')
                        ];
                    })->toArray()
                ]);
                
                // Find admin by MS365 account (stored as username for admins)
                $inputEmail = trim($credentials['ms365_account'] ?? '');
                $inputLower = strtolower($inputEmail);
                
                \Log::info('Searching for superadmin', [
                    'input_original' => $inputEmail,
                    'input_lowercase' => $inputLower,
                    'search_method' => 'case_insensitive_comparison'
                ]);
                
                $admin = Admin::all()->first(function ($admin) use ($inputLower) {
                    $storedUsername = trim($admin->username ?? '');
                    $storedLower = strtolower($storedUsername);
                    $matches = $storedLower === $inputLower;
                    
                    \Log::info('Superadmin comparison check', [
                        'admin_id' => $admin->id,
                        'stored_username' => $storedUsername,
                        'stored_lowercase' => $storedLower,
                        'input_lowercase' => $inputLower,
                        'matches' => $matches ? 'YES' : 'NO'
                    ]);
                    
                    return $matches;
                });
                
                // Fallback: match by local-part (before @) to tolerate domain differences (e.g., aliases)
                if (!$admin && !empty($inputLower) && str_contains($inputLower, '@')) {
                    $inputLocal = substr($inputLower, 0, strpos($inputLower, '@'));
                    \Log::info('Superadmin fallback local-part match initiated', [
                        'input_local' => $inputLocal
                    ]);
                    $admin = Admin::all()->first(function ($admin) use ($inputLocal) {
                        $stored = strtolower(trim($admin->username ?? ''));
                        $storedLocal = str_contains($stored, '@') ? substr($stored, 0, strpos($stored, '@')) : $stored;
                        $match = ($storedLocal === $inputLocal) && $admin->isSuperAdmin();
                        \Log::info('Superadmin local-part comparison', [
                            'admin_id' => $admin->id,
                            'stored_username' => $admin->username,
                            'stored_local' => $storedLocal,
                            'input_local' => $inputLocal,
                            'is_superadmin' => $admin->isSuperAdmin(),
                            'matches' => $match ? 'YES' : 'NO'
                        ]);
                        return $match;
                    });
                }
                
                if ($admin) {
                    // Test password verification
                    $passwordCheck = Hash::check($credentials['password'], $admin->password);
                    
                    \Log::info('Admin found for authentication - ENHANCED DEBUG', [
                        'admin_id' => $admin->id,
                        'username' => $admin->username,
                        'username_raw' => $admin->getRawOriginal('username') ?? 'N/A',
                        'role' => $admin->role,
                        'is_superadmin' => $admin->isSuperAdmin(),
                        'password_hash_exists' => !empty($admin->password),
                        'password_hash_length' => strlen($admin->password ?? ''),
                        'password_hash_preview' => substr($admin->password ?? '', 0, 20) . '...',
                        'input_password_length' => strlen($credentials['password'] ?? ''),
                        'password_check_result' => $passwordCheck,
                        'password_check_method' => 'Hash::check()'
                    ]);
                    
                    // Verify password and role
                    if ($passwordCheck) {
                        // Check if the user is specifically a super admin
                        if (!$admin->isSuperAdmin()) {
                            \Log::warning('Non-superadmin tried to login as superadmin', [
                                'admin_id' => $admin->id,
                                'actual_role' => $admin->role
                            ]);
                            
                            // Provide specific error messages based on admin type
                            if ($admin->isDepartmentAdmin()) {
                                $result = back()->withErrors(['ms365_account' => 'Department admins should use the department admin login.'])
                                            ->withInput($request->only('ms365_account', 'login_type'));
                            } elseif ($admin->isOfficeAdmin()) {
                                $result = back()->withErrors(['ms365_account' => 'Office admins should use the office admin login.'])
                                            ->withInput($request->only('ms365_account', 'login_type'));
                            } else {
                                $result = back()->withErrors(['ms365_account' => 'You do not have super admin privileges.'])
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
                            // Step 1: Generate OTP and send to superadmin's MS365 account, do NOT log them in yet
                            $otpCode = (string) random_int(100000, 999999);
                            $otpPayload = [
                                'user_id' => $admin->id, // Changed from admin_id to user_id for consistency
                                'admin_id' => $admin->id, // Keep for backward compatibility
                                'email' => $admin->username,
                                'code_hash' => \Hash::make($otpCode),
                                'expires_at' => now()->addMinutes(10)->toIso8601String(),
                                'attempts' => 0,
                                'max_attempts' => 5,
                                'location_permission' => $request->has('location_permission') ? (bool)$request->input('location_permission') : false,
                            ];

                            // Store OTP data in session under a dedicated key
                            $request->session()->put('superadmin_otp', $otpPayload);

                            // Try sending via Microsoft Graph first, then fallback to Laravel Mail
                            $sent = false;
                            $subject = 'Your Super Admin OTP Code';
                            $htmlBody = view('emails.superadmin-otp', [
                                'code' => $otpCode,
                                'expiresInMinutes' => 10,
                            ])->render();

                            try {
                                if ($this->graphService) {
                                    $sent = (bool) $this->graphService->sendEmail($admin->username, $subject, $htmlBody, true);
                                }
                            } catch (\Exception $e) {
                                \Log::error('Graph email send failed for superadmin OTP', ['error' => $e->getMessage()]);
                            }

                            if (!$sent) {
                                try {
                                    \Mail::send('emails.superadmin-otp', [
                                        'code' => $otpCode,
                                        'expiresInMinutes' => 10,
                                    ], function ($message) use ($admin, $subject) {
                                        $message->to($admin->username)->subject($subject);
                                    });
                                    $sent = true;
                                } catch (\Exception $e) {
                                    \Log::error('Fallback mail send failed for superadmin OTP', ['error' => $e->getMessage()]);
                                }
                            }

                            if ($sent) {
                                \Log::info('Superadmin OTP sent', ['admin_id' => $admin->id, 'email' => $admin->username]);
                                // Redirect to unified login with OTP modal flag
                                $result = redirect()->route('login', ['type' => 'superadmin'])
                                    ->with('show_superadmin_otp', true)
                                    ->with('status', 'We sent a 6-digit OTP to your MS365 email. Please enter it to continue.');
                                $loginSuccessful = true; // mark as successful step to avoid attempts increment
                            } else {
                                // If email could not be sent, treat as error and do not proceed
                                $result = back()->withErrors(['ms365_account' => 'Unable to send OTP email. Please try again later.'])
                                               ->withInput($request->only('ms365_account', 'login_type'));
                                $loginSuccessful = false;
                            }
                        }
                    } else {
                        // Password verification failed
                        \Log::warning('Superadmin password verification failed', [
                            'ms365_account' => $credentials['ms365_account'] ?? null,
                            'admin_id' => $admin->id
                        ]);
                        
                        // Log failed login attempt with geolocation
                        [$clientIp, $geoData] = $this->resolveIpAndLocation($request);
                        AdminAccessLog::create([
                            'admin_id' => null,
                            'role' => 'superadmin',
                            'status' => 'failed',
                            'username_attempted' => $credentials['ms365_account'] ?? null,
                            'ip_address' => $clientIp,
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
                    // Admin not found - Enhanced debugging
                    \Log::warning('Superadmin not found - ENHANCED DEBUG', [
                        'ms365_account_attempted' => $credentials['ms365_account'] ?? null,
                        'input_email_trimmed' => $inputEmail,
                        'input_email_lowercase' => $inputLower,
                        'total_admins_checked' => $allAdmins->count(),
                        'all_usernames_in_db' => $allAdmins->pluck('username')->toArray(),
                        'all_roles_in_db' => $allAdmins->pluck('role')->toArray(),
                        'superadmin_usernames' => $allAdmins->filter(function($a) { return $a->isSuperAdmin(); })->pluck('username')->toArray()
                    ]);
                    
                    // Log failed login attempt with geolocation
                    [$clientIp, $geoData] = $this->resolveIpAndLocation($request);
                    AdminAccessLog::create([
                        'admin_id' => null,
                        'role' => 'superadmin',
                        'status' => 'failed',
                        'username_attempted' => $credentials['ms365_account'] ?? null,
                        'ip_address' => $clientIp,
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
                    $input = trim(strtolower($credentials['ms365_account'] ?? ''));
                    $stored = trim(strtolower($admin->username ?? ''));
                    return $stored === $input;
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
                            // Step 1: Generate OTP and send to department admin's MS365 account
                            $otpSent = $this->sendOTP($request, 'department-admin', $admin, $admin->username);
                            
                            if ($otpSent) {
                                \Log::info('Department admin OTP sent', ['admin_id' => $admin->id, 'email' => $admin->username]);
                                // Redirect to unified login with OTP modal flag
                                $result = redirect()->route('login', ['type' => 'department-admin'])
                                    ->with('show_otp_modal', true)
                                    ->with('otp_login_type', 'department-admin')
                                    ->with('status', 'We sent a 6-digit OTP to your MS365 email. Please enter it to continue.');
                                $loginSuccessful = true; // mark as successful step to avoid attempts increment
                            } else {
                                // If email could not be sent, treat as error
                                $result = back()->withErrors(['ms365_account' => 'Unable to send OTP email. Please try again later.'])
                                               ->withInput($request->only('ms365_account', 'login_type'));
                                $loginSuccessful = false;
                            }
                        }
                    } else {
                        // Password verification failed
                        \Log::warning('Department admin password verification failed', [
                            'ms365_account' => $credentials['ms365_account'],
                            'admin_id' => $admin->id
                        ]);
                        
                        // Log failed login attempt with geolocation
                        [$clientIp, $geoData] = $this->resolveIpAndLocation($request);
                        AdminAccessLog::create([
                            'admin_id' => null,
                            'role' => 'department_admin',
                            'status' => 'failed',
                            'username_attempted' => $credentials['ms365_account'],
                            'ip_address' => $clientIp,
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
                    [$clientIp, $geoData] = $this->resolveIpAndLocation($request);
                    AdminAccessLog::create([
                        'admin_id' => null,
                        'role' => 'department_admin',
                        'status' => 'failed',
                        'username_attempted' => $credentials['ms365_account'],
                        'ip_address' => $clientIp,
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
                            // Step 1: Generate OTP and send to office admin's MS365 account
                            $otpSent = $this->sendOTP($request, 'office-admin', $admin, $admin->username);
                            
                            if ($otpSent) {
                                \Log::info('Office admin OTP sent', ['admin_id' => $admin->id, 'email' => $admin->username]);
                                // Redirect to unified login with OTP modal flag
                                $result = redirect()->route('login', ['type' => 'office-admin'])
                                    ->with('show_otp_modal', true)
                                    ->with('otp_login_type', 'office-admin')
                                    ->with('status', 'We sent a 6-digit OTP to your MS365 email. Please enter it to continue.');
                                $loginSuccessful = true; // mark as successful step to avoid attempts increment
                            } else {
                                // If email could not be sent, treat as error
                                $result = back()->withErrors(['ms365_account' => 'Unable to send OTP email. Please try again later.'])
                                               ->withInput($request->only('ms365_account', 'login_type'));
                                $loginSuccessful = false;
                            }
                        }
                    } else {
                        // Password verification failed
                        \Log::warning('Office admin password verification failed', [
                            'ms365_account' => $credentials['ms365_account'],
                            'admin_id' => $admin->id
                        ]);
                        
                        // Log failed login attempt with geolocation
                        [$clientIp, $geoData] = $this->resolveIpAndLocation($request);
                        AdminAccessLog::create([
                            'admin_id' => null,
                            'role' => 'office_admin',
                            'status' => 'failed',
                            'username_attempted' => $credentials['ms365_account'],
                            'ip_address' => $clientIp,
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
                    [$clientIp, $geoData] = $this->resolveIpAndLocation($request);
                    AdminAccessLog::create([
                        'admin_id' => null,
                        'role' => 'office_admin',
                        'status' => 'failed',
                        'username_attempted' => $credentials['ms365_account'] ?? 'NULL',
                        'ip_address' => $clientIp,
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
                'min:12',
                'confirmed',
                new StrongPassword(),
            ]),
            'terms_and_privacy' => 'required|accepted',
        ], array_merge($secureMessages, [
            'first_name.regex' => 'First name should only contain letters, spaces, and apostrophes',
            'middle_name.regex' => 'Middle name should only contain letters, spaces, and apostrophes',
            'surname.regex' => 'Surname should only contain letters, spaces, and apostrophes',
            'department.required_if' => 'Department is required for your selected role',
            'year_level.required_if' => 'Year level is required for students',
            'terms_and_privacy.required' => 'You must accept the Terms and Conditions and Privacy Policy to register',
            'terms_and_privacy.accepted' => 'You must accept the Terms and Conditions and Privacy Policy',
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
                'password_changed_at' => now(),
                'password_expires_at' => now()->addDays(90), // 90 days expiration
                'password_history' => [],
                'failed_login_attempts' => 0,
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
        
        // Get session ID before logout for logging
        $sessionId = $request->session()->getId();
        
        // Log the logout event for security monitoring
        \Log::info('User logout initiated', [
            'admin_user_id' => $adminUser ? $adminUser->id : null,
            'web_user_id' => $webUser ? $webUser->id : null,
            'admin_username' => $adminUser ? $adminUser->username : null,
            'web_user_email' => $webUser ? ($webUser->ms365_account ?? $webUser->gmail_account) : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $sessionId,
            'timestamp' => now()->toISOString()
        ]);
        
        try {
            // Logout from appropriate guard
            if ($adminUser) {
                Auth::guard('admin')->logout();
            }
            if ($webUser) {
                Auth::guard('web')->logout();
            }
            
            // Clear all security-related session data
            $securityKeys = [
                'authenticated_accounts',
                'security.ip_address',
                'security.user_agent',
                'security.fingerprint',
                'security.session_start_time',
                'security.last_activity',
                'security.request_count',
                'security.timeout_warning',
                'security.time_remaining'
            ];
            
            foreach ($securityKeys as $key) {
                $request->session()->forget($key);
            }
            
            // Clear login attempt data
            $sessionData = $request->session()->all();
            foreach ($sessionData as $key => $value) {
                if (strpos($key, 'login_attempts_') === 0 || 
                    strpos($key, 'lockout_time_') === 0 ||
                    strpos($key, 'security.') === 0) {
                    $request->session()->forget($key);
                }
            }
            
            // Invalidate the session completely
            $request->session()->invalidate();
            
            // Regenerate CSRF token
            $request->session()->regenerateToken();
            
            // Clear all session data
            $request->session()->flush();
            
            // Force garbage collection of old sessions
            $request->session()->migrate(true);
            
            // Clear remember me cookies if they exist
            $cookies = [];
            if ($request->hasCookie(Auth::getRecallerName())) {
                $cookies[] = \Cookie::forget(Auth::getRecallerName());
            }
            
            // Log successful logout
            \Log::info('User logout completed successfully', [
                'admin_user_id' => $adminUser ? $adminUser->id : null,
                'web_user_id' => $webUser ? $webUser->id : null,
                'session_id' => $sessionId,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Prepare response with security headers
            $response = redirect()->route('login')
                ->with('success', 'You have been logged out successfully.');
            
            // Add security headers to prevent caching
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('Clear-Site-Data', '"cache", "cookies", "storage"');
            
            // Clear remember me cookies
            foreach ($cookies as $cookie) {
                $response->withCookie($cookie);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            // Log logout error
            \Log::error('User logout failed', [
                'admin_user_id' => $adminUser ? $adminUser->id : null,
                'web_user_id' => $webUser ? $webUser->id : null,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Force logout anyway for security
            if ($adminUser) {
                Auth::guard('admin')->logout();
            }
            if ($webUser) {
                Auth::guard('web')->logout();
            }
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flush();
            
            return redirect()->route('login')
                ->with('error', 'Logout encountered an error, but you have been logged out for security.');
        }
    }

    /**
     * Show Superadmin OTP verification form
     */
    public function showSuperadminOtpForm(Request $request)
    {
        $otpSession = $request->session()->get('superadmin_otp');
        if (!$otpSession || empty($otpSession['admin_id'])) {
            return redirect()->route('login')->withErrors(['ms365_account' => 'Session expired. Please login again as Super Admin.']);
        }
        // Redirect to unified login with the OTP modal shown
        return redirect()->route('login', ['type' => 'superadmin'])
            ->with('show_superadmin_otp', true);
    }

    /**
     * Verify Superadmin OTP and complete login
     */
    public function verifySuperadminOtp(Request $request)
    {
        // Basic validation: 6 digits numeric
        $validator = \Validator::make($request->all(), [
            'otp' => ['required', 'digits:6']
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('show_superadmin_otp', true);
        }

        $otpSession = $request->session()->get('superadmin_otp');
        if (!$otpSession || empty($otpSession['admin_id'])) {
            return redirect()->route('login')->withErrors(['ms365_account' => 'Session expired. Please login again as Super Admin.']);
        }

        // Check expiry
        if (now()->greaterThan(\Carbon\Carbon::parse($otpSession['expires_at']))) {
            $request->session()->forget('superadmin_otp');
            return redirect()->route('login')->withErrors(['ms365_account' => 'OTP expired. Please login again to receive a new code.']);
        }

        // Check attempts
        if (($otpSession['attempts'] ?? 0) >= ($otpSession['max_attempts'] ?? 5)) {
            $request->session()->forget('superadmin_otp');
            return redirect()->route('login')->withErrors(['ms365_account' => 'Too many invalid OTP attempts. Please login again.']);
        }

        // Verify the code
        $isValid = \Hash::check($request->input('otp'), $otpSession['code_hash']);
        if (!$isValid) {
            // Increment attempts
            $otpSession['attempts'] = ($otpSession['attempts'] ?? 0) + 1;
            $request->session()->put('superadmin_otp', $otpSession);
            return back()->withErrors(['otp' => 'Invalid code. Please try again.'])->with('show_superadmin_otp', true);
        }

        // Valid OTP; complete admin login
        $admin = \App\Models\Admin::find($otpSession['admin_id']);
        if (!$admin || !$admin->isSuperAdmin()) {
            $request->session()->forget('superadmin_otp');
            return redirect()->route('login')->withErrors(['ms365_account' => 'Account not found or no longer authorized.']);
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        // Store a session snapshot to allow read-only access even if the admin row is later deleted
        $request->session()->put('admin_session_snapshot', [
            'id' => $admin->id,
            'username' => $admin->username,
            'role' => $admin->role,
            'logged_in_at' => now()->toDateTimeString(),
        ]);

        // Store location permission preference
        $locationPermission = $otpSession['location_permission'] ?? false;
        $request->session()->put('admin_location_permission', $locationPermission);

        // Clear OTP session
        $request->session()->forget('superadmin_otp');

        // Log admin access with geolocation
        [$clientIp, $geoData] = $this->resolveIpAndLocation($request);
        AdminAccessLog::startSession([
            'admin_id' => $admin->id,
            'role' => $admin->role,
            'status' => 'success',
            'ip_address' => $clientIp,
            'latitude' => $geoData['latitude'] ?? null,
            'longitude' => $geoData['longitude'] ?? null,
            'location_details' => $geoData['location_details'] ?? null,
            'time_in' => \Carbon\Carbon::now(),
        ]);

        return redirect()->route('superadmin.dashboard')->with('login_success', true);
    }

    /**
     * Generic method to send OTP for any login type
     */
    protected function sendOTP($request, $loginType, $user, $email)
    {
        // Generate 6-digit OTP
        $otpCode = (string) random_int(100000, 999999);
        $otpPayload = [
            'user_id' => $user->id,
            'login_type' => $loginType,
            'email' => $email,
            'code_hash' => \Hash::make($otpCode),
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
            'attempts' => 0,
            'max_attempts' => 5,
            'location_permission' => $request->has('location_permission') ? (bool)$request->input('location_permission') : false,
        ];

        // Store OTP data in session with login_type specific key
        $sessionKey = $loginType . '_otp';
        $request->session()->put($sessionKey, $otpPayload);

        // Determine subject and login type display name
        $loginTypeDisplayMap = [
            'ms365' => 'Student/Faculty',
            'user' => 'Student/Faculty',
            'department-admin' => 'Department Admin',
            'office-admin' => 'Office Admin',
            'superadmin' => 'Super Admin'
        ];
        
        $loginTypeDisplay = $loginTypeDisplayMap[$loginType] ?? 'User';
        $subject = 'Your ' . $loginTypeDisplay . ' OTP Code';

        // Prepare email body using generic template
        $htmlBody = view('emails.login-otp', [
            'code' => $otpCode,
            'expiresInMinutes' => 10,
            'loginType' => $loginType,
            'loginTypeDisplay' => $loginTypeDisplay,
        ])->render();

        // Try sending via Microsoft Graph first, then fallback to Laravel Mail
        $sent = false;
        try {
            if ($this->graphService && str_contains($email, '.edu.ph')) {
                $sent = (bool) $this->graphService->sendEmail($email, $subject, $htmlBody, true);
            }
        } catch (\Exception $e) {
            \Log::error('Graph email send failed for ' . $loginType . ' OTP', ['error' => $e->getMessage()]);
        }

        if (!$sent) {
            try {
                \Mail::send('emails.login-otp', [
                    'code' => $otpCode,
                    'expiresInMinutes' => 10,
                    'loginType' => $loginType,
                    'loginTypeDisplay' => $loginTypeDisplay,
                ], function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
                $sent = true;
            } catch (\Exception $e) {
                \Log::error('Fallback mail send failed for ' . $loginType . ' OTP', ['error' => $e->getMessage()]);
            }
        }

        if ($sent) {
            \Log::info($loginType . ' OTP sent', ['user_id' => $user->id, 'email' => $email]);
        }

        return $sent;
    }

    /**
     * Resend OTP code for any login type
     */
    public function resendOTP(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'login_type' => ['required', 'in:user,ms365,department-admin,office-admin,superadmin']
        ]);
        
        if ($validator->fails()) {
            return back()
                ->withErrors(['error' => 'Invalid request.'])
                ->with('show_otp_modal', true)
                ->with('otp_login_type', $request->login_type);
        }

        $loginType = $request->login_type;
        $sessionKey = $loginType . '_otp';
        $otpSession = $request->session()->get($sessionKey);
        
        // Check for both user_id and admin_id (superadmin uses admin_id)
        $userId = $otpSession['user_id'] ?? $otpSession['admin_id'] ?? null;
        
        if (!$otpSession || empty($userId)) {
            return redirect()->route('login', ['type' => $loginType])
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }

        // Get user/admin based on login type
        if (in_array($loginType, ['department-admin', 'office-admin', 'superadmin'])) {
            $user = Admin::find($userId);
            if (!$user) {
                return redirect()->route('login', ['type' => $loginType])
                    ->withErrors(['email' => 'User not found. Please login again.']);
            }
            $email = $user->username; // Admin email is stored in username field
        } else {
            $user = User::find($userId);
            if (!$user) {
                return redirect()->route('login', ['type' => $loginType])
                    ->withErrors(['email' => 'User not found. Please login again.']);
            }
            $email = $loginType === 'ms365' ? $user->ms365_account : $user->gmail_account;
        }

        // Send new OTP
        $otpSent = $this->sendOTP($request, $loginType, $user, $email);

        if ($otpSent) {
            \Log::info('OTP resent for ' . $loginType, ['user_id' => $user->id, 'email' => $email]);
            
            // Determine login type display name
            $loginTypeDisplayMap = [
                'ms365' => 'Student/Faculty',
                'user' => 'Student/Faculty',
                'department-admin' => 'Department Admin',
                'office-admin' => 'Office Admin',
                'superadmin' => 'Super Admin'
            ];
            $loginTypeDisplay = $loginTypeDisplayMap[$loginType] ?? 'User';
            
            return back()
                ->with('show_otp_modal', true)
                ->with('otp_login_type', $loginType)
                ->with('status', 'A new 6-digit OTP has been sent to your email (Outlook app). Please check your inbox.');
        } else {
            return back()
                ->withErrors(['error' => 'Unable to send OTP email. Please try again later.'])
                ->with('show_otp_modal', true)
                ->with('otp_login_type', $loginType);
        }
    }

    /**
     * Generic method to verify OTP for any login type
     */
    public function verifyOTP(Request $request)
    {
        // Basic validation: 6 digits numeric
        $validator = \Validator::make($request->all(), [
            'otp' => ['required', 'digits:6'],
            'login_type' => ['required', 'in:user,ms365,department-admin,office-admin,superadmin']
        ]);
        
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('show_otp_modal', true)
                ->with('otp_login_type', $request->login_type);
        }

        $loginType = $request->login_type;
        $sessionKey = $loginType . '_otp';
        $otpSession = $request->session()->get($sessionKey);
        
        // Check for both user_id and admin_id (superadmin uses admin_id)
        $userId = $otpSession['user_id'] ?? $otpSession['admin_id'] ?? null;
        
        if (!$otpSession || empty($userId)) {
            return redirect()->route('login', ['type' => $loginType])
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }

        // Check expiry
        if (now()->greaterThan(\Carbon\Carbon::parse($otpSession['expires_at']))) {
            $request->session()->forget($sessionKey);
            return redirect()->route('login', ['type' => $loginType])
                ->withErrors(['email' => 'OTP expired. Please login again to receive a new code.']);
        }

        // Check attempts
        if (($otpSession['attempts'] ?? 0) >= ($otpSession['max_attempts'] ?? 5)) {
            $request->session()->forget($sessionKey);
            return redirect()->route('login', ['type' => $loginType])
                ->withErrors(['email' => 'Too many invalid OTP attempts. Please login again.']);
        }

        // Verify the code
        $isValid = \Hash::check($request->input('otp'), $otpSession['code_hash']);
        if (!$isValid) {
            // Increment attempts
            $otpSession['attempts'] = ($otpSession['attempts'] ?? 0) + 1;
            $request->session()->put($sessionKey, $otpSession);
            return back()
                ->withErrors(['otp' => 'Invalid code. Please try again.'])
                ->with('show_otp_modal', true)
                ->with('otp_login_type', $loginType);
        }

        // Valid OTP - proceed with login based on type
        if (in_array($loginType, ['ms365', 'user'])) {
            // Regular user login
            $user = User::find($userId);
            if (!$user) {
                $request->session()->forget($sessionKey);
                return redirect()->route('login', ['type' => $loginType])
                    ->withErrors(['email' => 'Account not found.']);
            }

            auth()->login($user, true); // Remember me enabled
            $request->session()->regenerate();
            $user->resetFailedLoginAttempts();
            
            // Clear OTP session
            $request->session()->forget($sessionKey);

            \Log::info($loginType . ' login successful with OTP', ['user_id' => $user->id]);
            return redirect()->route('user.dashboard')->with('login_success', true);
            
        } else {
            // Admin login (department-admin, office-admin, superadmin)
            $admin = Admin::find($userId);
            if (!$admin) {
                $request->session()->forget($sessionKey);
                return redirect()->route('login', ['type' => $loginType])
                    ->withErrors(['email' => 'Account not found.']);
            }

            // Role verification
            $roleValid = false;
            $dashboardRoute = '';
            
            if ($loginType === 'department-admin' && $admin->isDepartmentAdmin()) {
                $roleValid = true;
                $dashboardRoute = 'department-admin.dashboard';
            } elseif ($loginType === 'office-admin' && $admin->isOfficeAdmin()) {
                $roleValid = true;
                $dashboardRoute = 'office-admin.dashboard';
            } elseif ($loginType === 'superadmin' && $admin->isSuperAdmin()) {
                $roleValid = true;
                $dashboardRoute = 'superadmin.dashboard';
            }

            if (!$roleValid) {
                $request->session()->forget($sessionKey);
                return redirect()->route('login', ['type' => $loginType])
                    ->withErrors(['email' => 'Account role mismatch.']);
            }

            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();

            // Store admin session snapshot
            $request->session()->put('admin_session_snapshot', [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role,
                'logged_in_at' => now()->toDateTimeString(),
            ]);

            // Store location permission preference
            $locationPermission = $otpSession['location_permission'] ?? false;
            $request->session()->put('admin_location_permission', $locationPermission);

            // Clear OTP session
            $request->session()->forget($sessionKey);

            // Log admin access with geolocation
            [$clientIp, $geoData] = $this->resolveIpAndLocation($request);
            AdminAccessLog::startSession([
                'admin_id' => $admin->id,
                'role' => $admin->role,
                'status' => 'success',
                'ip_address' => $clientIp,
                'latitude' => $geoData['latitude'] ?? null,
                'longitude' => $geoData['longitude'] ?? null,
                'location_details' => $geoData['location_details'] ?? null,
                'time_in' => Carbon::now(),
            ]);

            \Log::info($loginType . ' login successful with OTP', ['admin_id' => $admin->id]);
            return redirect()->route($dashboardRoute)->with('login_success', true);
        }
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
        PasswordReset::updateOrCreateToken($request->ms365_account, $token);

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
        $resetRecord = PasswordReset::where('email', $email)->first();

        if (!$resetRecord || !PasswordReset::verifyToken($email, $token)) {
            return redirect()->route('password.request')
                           ->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        // Check if token is expired (60 minutes)
        if ($resetRecord->isExpired(60)) {
            PasswordReset::deleteToken($email);
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
                'min:12',
                'confirmed',
                new StrongPassword(),
            ]),
        ], $secureMessages);

        // Verify token
        $resetRecord = PasswordReset::where('email', $request->email)->first();

        if (!$resetRecord || !PasswordReset::verifyToken($request->email, $request->token)) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        // Check if token is expired
        if ($resetRecord->isExpired(60)) {
            PasswordReset::deleteToken($request->email);
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

        // Update password with security checks
        $user->updatePassword($request->password);

        // Delete the reset token
        PasswordReset::deleteToken($request->email);

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
                return $loginType . '_' . ($request->ms365_account ?? 'unknown');
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
            'location_permission.accepted' => 'You must allow location tracking to continue with admin login.',
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
