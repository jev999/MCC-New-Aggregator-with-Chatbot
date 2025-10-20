<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Traits\SecurityValidationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class SuperAdminAuthController extends Controller
{
    use SecurityValidationTrait;
    
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
}
