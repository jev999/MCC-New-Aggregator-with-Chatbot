<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('user.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'ms365_account' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt(['ms365_account' => $request->ms365_account, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('user.dashboard'));
        }

        return back()->withErrors(['ms365_account' => 'Invalid credentials'])->withInput($request->except('password'));
    }

    public function showRegisterForm()
    {
        return view('user.auth.register');
    }

    public function register(Request $request)
    {
        $rules = [
            'first_name' => 'required|string|max:255|regex:/^[\pL\' ]+$/u',
            'middle_name' => 'nullable|string|max:255|regex:/^[\pL\' ]+$/u',
            'surname' => 'required|string|max:255|regex:/^[\pL\' ]+$/u',
            'ms365_account' => 'required|email|unique:users',
            'password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword()],
            'password_confirmation' => 'required|string|same:password',
            'role' => 'required|in:student,faculty',
        ];

        if ($request->role === 'student') {
            $rules['department'] = 'required|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management';
            $rules['year_level'] = 'required|in:1st Year,2nd Year,3rd Year,4th Year';
        }

        $request->validate($rules);

        User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'surname' => $request->surname,
            'ms365_account' => $request->ms365_account,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department' => $request->role === 'student' ? $request->department : null,
            'year_level' => $request->role === 'student' ? $request->year_level : null,
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }

    public function logout(Request $request)
    {
        // Get user info before logout for logging
        $userId = Auth::id();
        $userEmail = Auth::user() ? Auth::user()->ms365_account : 'unknown';
        
        // Log the logout event for security monitoring
        \Log::info('User logout initiated', [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'timestamp' => now()->toISOString()
        ]);
        
        try {
            // Store session ID for cleanup verification
            $sessionId = $request->session()->getId();
            
            // Logout the user from authentication guard
            Auth::logout();
            
            // Invalidate the session completely - this removes session from storage
            $request->session()->invalidate();
            
            // Regenerate CSRF token to prevent CSRF attacks
            $request->session()->regenerateToken();
            
            // Clear all session data from memory
            $request->session()->flush();
            
            // Force garbage collection of old sessions
            $request->session()->migrate(true);
            
            // Additional security: Clear any remember me tokens
            if ($request->hasCookie(Auth::getRecallerName())) {
                $cookie = \Cookie::forget(Auth::getRecallerName());
            }
            
            // Log successful logout
            \Log::info('User logout completed successfully', [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Prepare response with security headers
            $response = null;
            
            if ($request->ajax() || $request->wantsJson()) {
                $response = response()->json([
                    'success' => true,
                    'message' => 'You have been logged out successfully.',
                    'redirect' => route('login')
                ]);
            } else {
                $response = redirect()->route('login')
                                ->with('success', 'You have been logged out successfully.');
            }
            
            // Add security headers to prevent caching
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            
            // Clear remember me cookie if it exists
            if (isset($cookie)) {
                $response->withCookie($cookie);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            // Log logout error
            \Log::error('User logout failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Force logout anyway for security
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logout encountered an error, but you have been logged out for security.',
                    'redirect' => route('login')
                ], 500);
            }
            
            return redirect()->route('login')
                            ->with('error', 'Logout encountered an error, but you have been logged out for security.');
        }
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'first_name' => 'required|string|max:255|regex:/^[\pL\' ]+$/u',
            'middle_name' => 'nullable|string|max:255|regex:/^[\pL\' ]+$/u',
            'surname' => 'required|string|max:255|regex:/^[\pL\' ]+$/u',
            'ms365_account' => 'required|email|unique:users,ms365_account,' . $user->id,
        ];

        // Add student-specific validation
        if ($user->role === 'student') {
            $rules['department'] = 'required|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management';
            $rules['year_level'] = 'required|in:1st Year,2nd Year,3rd Year,4th Year';
        }

        // Only validate password if provided
        if ($request->filled('new_password')) {
            $rules['current_password'] = 'required';
            $rules['new_password'] = ['required', 'string', 'min:12', new StrongPassword(), new \App\Rules\PasswordNotRecentlyUsed($user)];
            $rules['new_password_confirmation'] = 'required|string|same:new_password';
        }

        $request->validate($rules);
        
        // Verify current password if changing password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->updatePassword($request->new_password);
        }

        // Update profile information
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->surname = $request->surname;
        $user->ms365_account = $request->ms365_account;

        // Update student-specific fields
        if ($user->role === 'student') {
            $user->department = $request->department;
            $user->year_level = $request->year_level;
        }

        $user->save();
        
        return back()->with('success', 'Settings updated successfully!');
    }
}
