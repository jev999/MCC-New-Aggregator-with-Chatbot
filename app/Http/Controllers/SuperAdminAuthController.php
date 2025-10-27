<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminAccessLog;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class SuperAdminAuthController extends Controller
{
    protected $geolocationService;
    
    public function __construct()
    {
        $this->geolocationService = new GeolocationService();
    }

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
     * Show the super admin login form
     */
    public function showLoginForm()
    {
        return view('superadmin.auth.login');
    }

    /**
     * Handle super admin login
     */
    public function login(Request $request)
    {
        // Enhanced security validation
        $this->validateSecureInput($request);

        $secureRules = $this->getSecureValidationRules();
        $secureMessages = $this->getSecureValidationMessages();

        $request->validate([
            'username' => array_merge($secureRules['username'], ['required']),
            'password' => array_merge($secureRules['password'], ['required']),
        ], $secureMessages);

        // Attempt to authenticate with admin guard
        if (Auth::guard('admin')->attempt($request->only('username', 'password'))) {
            $admin = Auth::guard('admin')->user();

            // Check if the user is specifically a super admin
            if (!$admin->isSuperAdmin()) {
                Auth::guard('admin')->logout();
                
                // Provide specific error messages based on admin type
                if ($admin->isDepartmentAdmin()) {
                    return back()->withErrors(['username' => 'Department admins should use the department admin login.']);
                } else {
                    return back()->withErrors(['username' => 'You do not have super admin privileges.']);
                }
            }

            // Successful super admin login
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

            return redirect()->route('superadmin.dashboard')
                           ->with('login_success', true);
        }

        // Authentication failed
        return back()->withErrors(['username' => 'Invalid credentials. Please check your username and password.'])
                    ->withInput($request->only('username'));
    }

    /**
     * Handle super admin logout
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('admin')->user();

        // Log time out and duration for admin access log
        if ($user && in_array($user->role, ['superadmin', 'department_admin', 'office_admin'])) {
            $log = AdminAccessLog::where('admin_id', $user->id)
                ->whereNull('time_out')
                ->latest()
                ->first();

            if ($log) {
                $timeOut = Carbon::now();
                $duration = $timeOut->diffForHumans(Carbon::parse($log->time_in), true);
                $log->update([
                    'time_out' => $timeOut,
                    'duration' => $duration,
                ]);
            }
        }

        // Log the logout event
        \Log::info('Superadmin logout', [
            'admin_id' => Auth::guard('admin')->id(),
            'username' => Auth::guard('admin')->user() ? Auth::guard('admin')->user()->username : 'unknown',
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);
        
        Auth::guard('admin')->logout();

        // Clear authenticated accounts session data
        $request->session()->forget('authenticated_accounts');
        
        // Clear login attempt data but preserve other session data
        $sessionData = $request->session()->all();
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'login_attempts_') === 0 || strpos($key, 'lockout_time_') === 0) {
                $request->session()->forget($key);
            }
        }
        
        // Regenerate session ID for security but don't invalidate everything
        $request->session()->regenerate(true);

        return redirect()->route('login')
                        ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Validate secure input - fallback method if SecurityService not available
     */
    private function validateSecureInput(Request $request)
    {
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
        // Fallback rules
        return [
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
        // Fallback messages
        return [
            'username.regex' => 'Username can only contain letters, numbers, dots, underscores, and hyphens.',
            'password.required' => 'Password is required.',
        ];
    }

    /**
     * Check if input contains dangerous patterns - fallback method if SecurityService not available
     */
    private function containsDangerousPatterns($input)
    {
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
