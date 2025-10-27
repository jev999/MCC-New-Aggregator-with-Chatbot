<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DepartmentAdminAuthController extends Controller
{
    /**
     * Show the department admin login form
     */
    public function showLoginForm()
    {
        return view('department-admin.auth.login');
    }

    /**
     * Handle department admin login
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

        // Manual authentication to handle encrypted username field
        $admin = Admin::all()->first(function($admin) use ($request) {
            return $admin->username === $request->ms365_account;
        });

        if ($admin && Hash::check($request->password, $admin->password)) {
            // Check if the user is specifically a department admin
            if (!$admin->isDepartmentAdmin()) {
                // Provide specific error messages based on admin type
                if ($admin->isSuperAdmin()) {
                    return back()->withErrors(['ms365_account' => 'Super admins should use the super admin login.']);
                } else {
                    return back()->withErrors(['ms365_account' => 'You do not have department admin privileges.']);
                }
            }

            // Manual login with admin guard
            Auth::guard('admin')->login($admin);

            // Successful department admin login
            $request->session()->regenerate();

            // Log admin access
            AdminAccessLog::create([
                'admin_id' => $admin->id,
                'role' => $admin->role,
                'status' => 'success',
                'ip_address' => $request->ip(),
                'time_in' => Carbon::now(),
            ]);

            return redirect()->route('department-admin.dashboard')
                           ->with('login_success', true);
        }

        // Authentication failed
        return back()->withErrors(['ms365_account' => 'The provided credentials do not match our records.'])
                    ->withInput($request->only('ms365_account'));
    }

    /**
     * Handle department admin logout
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
            'ms365_account.email' => 'Please enter a valid MS365 email address.',
            'ms365_account.regex' => 'MS365 email format is invalid.',
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
