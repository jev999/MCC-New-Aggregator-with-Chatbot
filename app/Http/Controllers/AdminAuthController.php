<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminAccessLog;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminAuthController extends Controller
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
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'login_type' => 'required|in:admin',
        ]);

        // Attempt to authenticate with admin guard
        if (Auth::guard('admin')->attempt($request->only('username', 'password'))) {
            $admin = Auth::guard('admin')->user();

            // Check if the user is specifically a department admin
            if (!$admin->isDepartmentAdmin()) {
                Auth::guard('admin')->logout();

                // Provide specific error messages based on admin type
                if ($admin->isSuperAdmin()) {
                    return back()->withErrors(['username' => 'Super admins should use the super admin login form.']);
                } else {
                    return back()->withErrors(['username' => 'You do not have department admin privileges.']);
                }
            }

            // Successful department admin login
            $request->session()->regenerate();

            // Log admin access with geolocation
            $clientIp = $this->resolveClientIp($request);
            $geoData = $this->getGeolocationData($clientIp);
            AdminAccessLog::create([
                'admin_id' => $admin->id,
                'role' => $admin->role,
                'status' => 'success',
                'ip_address' => $clientIp,
                'latitude' => $geoData['latitude'] ?? null,
                'longitude' => $geoData['longitude'] ?? null,
                'location_details' => $geoData['location_details'] ?? null,
                'time_in' => Carbon::now(),
            ]);

            return redirect()->route('admin.dashboard')
                           ->with('login_success', true);
        }

        // Authentication failed
        return back()->withErrors(['username' => 'Invalid credentials. Please check your username and password.'])
                    ->withInput($request->only('username'));
    }

    public function showRegisterForm()
    {
        return view('admin.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:admins',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|same:password',
        ]);

        Admin::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.login')->with('success', 'Registration successful! Please login.');
    }

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
