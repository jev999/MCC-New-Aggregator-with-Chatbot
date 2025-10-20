<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Traits\SecurityValidationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DepartmentAdminAuthController extends Controller
{
    use SecurityValidationTrait;
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
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                        ->with('success', 'You have been logged out successfully.');
    }
}
