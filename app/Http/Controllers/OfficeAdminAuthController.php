<?php

namespace App\Http\Controllers;

use App\Models\AdminAccessLog;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OfficeAdminAuthController extends Controller
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
     * Show the office admin login form
     */
    public function showLoginForm()
    {
        return view('office-admin.auth.login');
    }

    /**
     * Handle office admin login
     */
    public function login(Request $request)
    {
        // Enhanced security validation
        $this->validateSecureInput($request);

        $secureRules = $this->getSecureValidationRules();
        $secureMessages = $this->getSecureValidationMessages();

        $request->validate([
            'ms365_account' => array_merge($secureRules['ms365_account'], ['required']),
            'password' => array_merge($secureRules['password'], ['required']),
        ], $secureMessages);

        // Find admin by decrypted username since username field is encrypted
        $username = $request->ms365_account;
        $admin = \App\Models\Admin::all()->first(function ($admin) use ($username) {
            return $admin->username === $username;
        });

        // Check if admin exists and password is correct
        if ($admin && \Hash::check($request->password, $admin->password)) {
            // Check if the user is specifically an office admin
            if (!$admin->isOfficeAdmin()) {
                // Provide specific error messages based on admin type
                if ($admin->isSuperAdmin()) {
                    return back()->withErrors(['ms365_account' => 'Super admins should use the super admin login.']);
                } elseif ($admin->isDepartmentAdmin()) {
                    return back()->withErrors(['ms365_account' => 'Department admins should use the department admin login.']);
                } else {
                    return back()->withErrors(['ms365_account' => 'You do not have office admin privileges.']);
                }
            }

            // Manually log in the admin
            Auth::guard('admin')->login($admin);

            // Successful office admin login
            $request->session()->regenerate();

            // Log admin access with geolocation
            $clientIp = $this->resolveClientIp($request);
            $geoData = $this->getGeolocationData($clientIp);
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

            return redirect()->route('office-admin.dashboard')
                           ->with('login_success', true);
        }

        // Authentication failed - Log the failed attempt
        $clientIp = $this->resolveClientIp($request);
        $geoData = $this->getGeolocationData($clientIp);
        AdminAccessLog::create([
            'admin_id' => null, // No admin_id for failed attempts
            'role' => 'office_admin', // Role they were trying to access
            'status' => 'failed',
            'username_attempted' => $request->ms365_account,
            'ip_address' => $clientIp,
            'latitude' => $geoData['latitude'] ?? null,
            'longitude' => $geoData['longitude'] ?? null,
            'location_details' => $geoData['location_details'] ?? null,
            'time_in' => Carbon::now(),
        ]);

        return back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                    ->withInput($request->only('ms365_account'));
    }

    /**
     * Handle office admin logout
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $sessionId = $request->session()->getId();

        // Log the logout event for security monitoring
        \Log::info('Office Admin logout initiated', [
            'user_id' => $user ? $user->id : null,
            'username' => $user ? $user->username : null,
            'role' => $user ? $user->role : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $sessionId,
            'timestamp' => now()->toISOString()
        ]);

        try {
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

            // Clear all security-related session data
            $securityKeys = [
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

            // Logout the user
            Auth::guard('admin')->logout();

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
            if ($request->hasCookie(Auth::guard('admin')->getRecallerName())) {
                $cookies[] = \Cookie::forget(Auth::guard('admin')->getRecallerName());
            }

            // Log successful logout
            \Log::info('Office Admin logout completed successfully', [
                'user_id' => $user ? $user->id : null,
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
            \Log::error('Office Admin logout failed', [
                'user_id' => $user ? $user->id : null,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            // Force logout anyway for security
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flush();

            return redirect()->route('login')
                ->with('error', 'Logout encountered an error, but you have been logged out for security.');
        }
    }
}
