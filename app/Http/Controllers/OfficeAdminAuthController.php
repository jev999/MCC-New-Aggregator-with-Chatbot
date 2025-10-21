<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeAdminAuthController extends Controller
{
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
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                        ->with('success', 'You have been logged out successfully.');
    }
}
