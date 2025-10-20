<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
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
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                        ->with('success', 'You have been logged out successfully.');
    }
}
