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

            return redirect()->route('office-admin.dashboard')
                           ->with('login_success', true);
        }

        // Authentication failed
        return back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                    ->withInput($request->only('ms365_account'));
    }

    /**
     * Handle office admin logout
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

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                        ->with('success', 'You have been logged out successfully.');
    }
}
