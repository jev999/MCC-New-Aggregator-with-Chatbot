<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class OfficeAdminController extends Controller
{
    /**
     * Display a listing of office admins
     */
    public function index()
    {
        $officeAdmins = Admin::officeAdmins()->latest()->get();
        return view('superadmin.office-admins.index', compact('officeAdmins'));
    }

    /**
     * Show the form for creating a new office admin
     */
    public function create()
    {
        return view('superadmin.office-admins.create');
    }

    /**
     * Send registration email to office admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'email',
                'unique:admins,username',
                'max:255'
            ],
            'office' => 'required|in:NSTP,SSC,GUIDANCE,REGISTRAR,CLINIC',
        ], [
            'username.unique' => 'This MS365 account has already been used for another admin. Each MS365 account can only be used once.',
        ]);

        // Check if office admin already exists for this office
        $existingOfficeAdmin = Admin::where('role', 'office_admin')
                                   ->where('office', $request->office)
                                   ->first();

        if ($existingOfficeAdmin) {
            return back()->withErrors(['office' => 'An office admin already exists for ' . $request->office . ' office.'])
                        ->withInput();
        }

        $email = $request->username;
        $office = $request->office;

        // Generate secure registration token with additional security layers
        $timestamp = now()->timestamp;
        $secureToken = hash('sha256', $email . $office . $timestamp . config('app.key') . request()->ip());
        
        // Store token in cache with expiration for additional validation
        \Cache::put('office_admin_registration_' . $secureToken, [
            'email' => $email,
            'office' => $office,
            'ip' => request()->ip(),
            'timestamp' => $timestamp,
            'used' => false
        ], now()->addMinutes(30));

        // Generate secure registration URL with enhanced parameters
        $registrationUrl = URL::temporarySignedRoute(
            'office-admin.register.form',
            now()->addMinutes(30),
            [
                'email' => $email,
                'office' => $office,
                'token' => $secureToken,
                'timestamp' => $timestamp
            ]
        );

        try {
            Mail::send('emails.office-admin-registration', [
                'email' => $email,
                'office' => $office,
                'registrationUrl' => $registrationUrl
            ], function ($message) use ($email, $office) {
                $message->to($email)
                       ->subject('Office Admin Registration - MCC News Aggregator (' . $office . ')');
            });

            return redirect()->route('superadmin.office-admins.index')
                            ->with('success', 'Registration email sent to ' . $email . ' for ' . $office . ' office!');
        } catch (\Exception $e) {
            \Log::error('Office admin registration email failed: ' . $e->getMessage());
            return back()->withErrors('Failed to send registration email. Please try again.')
                        ->withInput();
        }
    }

    /**
     * Display the specified office admin
     */
    public function show(Admin $officeAdmin)
    {
        return view('superadmin.office-admins.show', compact('officeAdmin'));
    }

    /**
     * Show the form for editing the specified office admin
     */
    public function edit(Admin $officeAdmin)
    {
        return view('superadmin.office-admins.edit', compact('officeAdmin'));
    }

    /**
     * Update the specified office admin
     */
    public function update(Request $request, Admin $officeAdmin)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:admins,username,' . $officeAdmin->id,
            'password' => 'nullable|string|min:6',
            'password_confirmation' => 'nullable|string|same:password',
            'office' => 'required|in:NSTP,SSC,GUIDANCE,REGISTRAR,CLINIC',
        ]);

        // Check if office admin already exists for this office (excluding current admin)
        $existingOfficeAdmin = Admin::where('role', 'office_admin')
                                   ->where('office', $request->office)
                                   ->where('id', '!=', $officeAdmin->id)
                                   ->first();

        if ($existingOfficeAdmin) {
            return back()->withErrors(['office' => 'An office admin already exists for ' . $request->office . ' office.'])
                        ->withInput();
        }

        $updateData = [
            'username' => $request->username,
            'office' => $request->office,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $officeAdmin->update($updateData);

        return redirect()->route('superadmin.office-admins.index')
                        ->with('success', 'Office admin updated successfully!');
    }

    /**
     * Remove the specified office admin
     */
    public function destroy(Admin $officeAdmin)
    {
        $officeAdmin->delete();

        return redirect()->route('superadmin.office-admins.index')
                        ->with('success', 'Office admin deleted successfully!');
    }

    /**
     * Show the office admin registration form (from email link)
     */
    public function showOfficeAdminRegistrationForm(Request $request)
    {
        $email = $request->email;
        $office = $request->office;
        $token = $request->token;
        $timestamp = $request->timestamp;

        // Enhanced security validation
        if (!$token || !$timestamp) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'Invalid registration link. Missing security parameters.']);
        }

        // Validate token from cache
        $cachedData = \Cache::get('office_admin_registration_' . $token);
        if (!$cachedData) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link has expired or is invalid. Please request a new registration link.']);
        }

        // Verify token hasn't been used (safe array access)
        if (isset($cachedData['used']) && $cachedData['used']) {
            \Log::warning('Office admin registration form viewed with already used token', [
                'email' => $email,
                'office' => $office,
                'ip' => request()->ip(),
                'token' => $token,
                'cached_data' => $cachedData
            ]);

            // If the office admin account already exists, guide them to login instead of showing a hard error
            $existingAdmin = Admin::where('username', $email)->first();
            if ($existingAdmin) {
                return redirect()->route('login')
                               ->with('success', 'Your office admin account has already been created. You can now login with your email and password.');
            }

            return redirect()->route('login')
                           ->withErrors(['error' => 'This registration link has already been used. Please request a new registration link.']);
        }

        // Verify email and office match
        if ($cachedData['email'] !== $email || $cachedData['office'] !== $office) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link parameters do not match. Security violation detected.']);
        }

        // Verify timestamp (additional protection against replay attacks)
        if ($cachedData['timestamp'] != $timestamp) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link timestamp mismatch. Security violation detected.']);
        }

        // Check if admin already exists
        $existingAdmin = Admin::where('username', $email)->first();
        if ($existingAdmin) {
            return redirect()->route('login')
                           ->with('error', 'This admin account already exists. Please login instead.');
        }

        // Mark token as viewed (but not used yet) and log access
        \Log::info('Office admin registration form accessed successfully', [
            'email' => $email,
            'office' => $office,
            'ip' => request()->ip(),
            'token' => $token,
            'cached_data' => $cachedData
        ]);
        \Cache::put('office_admin_registration_' . $token, array_merge($cachedData, ['viewed' => true]), now()->addMinutes(30));

        return view('auth.office-admin-register', compact('email', 'office', 'token', 'timestamp'));
    }

    /**
     * Complete the office admin registration
     */
    public function completeOfficeAdminRegistration(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'office' => 'required|in:NSTP,SSC,GUIDANCE,REGISTRAR,CLINIC',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:128',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,128}$/'
            ],
            'password_confirmation' => 'required|string|same:password',
            'secure_token' => 'required|string',
            'timestamp' => 'required|integer',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password must not exceed 128 characters.',
            'secure_token.required' => 'Security token is required.',
            'timestamp.required' => 'Security timestamp is required.',
        ]);

        // Enhanced security token validation
        $secureToken = $request->secure_token;
        $timestamp = $request->timestamp;

        // Validate token from cache
        $cachedData = \Cache::get('office_admin_registration_' . $secureToken);
        if (!$cachedData) {
            \Log::warning('Office admin registration attempted with invalid/expired token', [
                'email' => $request->email,
                'ip' => request()->ip(),
                'token' => $secureToken
            ]);
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration session has expired or is invalid. Please request a new registration link.']);
        }

        // Verify token hasn't been used (safe array access)
        if (isset($cachedData['used']) && $cachedData['used']) {
            \Log::warning('Office admin registration attempted with already used token', [
                'email' => $request->email,
                'ip' => request()->ip(),
                'token' => $secureToken,
                'cached_data' => $cachedData
            ]);
            return redirect()->route('login')
                           ->withErrors(['error' => 'This registration link has already been used. Please request a new registration link.']);
        }

        // Verify all parameters match cached data
        if ($cachedData['email'] !== $request->email || 
            $cachedData['office'] !== $request->office || 
            $cachedData['timestamp'] != $timestamp) {
            \Log::warning('Office admin registration attempted with mismatched parameters', [
                'email' => $request->email,
                'office' => $request->office,
                'ip' => request()->ip(),
                'cached_email' => $cachedData['email'],
                'cached_office' => $cachedData['office']
            ]);
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration parameters do not match. Security violation detected.']);
        }

        // Additional timestamp validation (prevent old tokens)
        if (now()->timestamp - $timestamp > 1800) { // 30 minutes
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link has expired. Please request a new registration link.']);
        }

        // Double-check if admin already exists (handle encrypted username field)
        $existingAdmin = Admin::all()->first(function($admin) use ($request) {
            return $admin->username === $request->email;
        });
        if ($existingAdmin) {
            return redirect()->route('login')
                           ->with('error', 'This MS365 account has already been used for another admin. Each MS365 account can only be used once.');
        }

        // Check if office admin already exists for this office
        $existingOfficeAdmin = Admin::where('role', 'office_admin')
                                   ->where('office', $request->office)
                                   ->first();

        if ($existingOfficeAdmin) {
            return back()->withErrors(['office' => 'An office admin already exists for ' . $request->office . ' office.']);
        }

        try {
            // Mark token as used before creating account
            \Cache::put('office_admin_registration_' . $secureToken, array_merge($cachedData, ['used' => true]), now()->addHours(24));

            // Create the admin account using email as username
            $admin = Admin::create([
                'username' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'office_admin',
                'office' => $request->office,
            ]);

            // Log successful registration
            \Log::info('Office admin registration completed successfully', [
                'admin_id' => $admin->id,
                'username' => $admin->username,
                'office' => $admin->office,
                'ip' => request()->ip(),
                'timestamp' => now()
            ]);

            // Clear the token from cache after successful registration
            \Cache::forget('office_admin_registration_' . $secureToken);

            return redirect()->route('login')
                            ->with('success', 'Your office admin account has been created successfully! You can now login with your email and password.');
        } catch (\Exception $e) {
            \Log::error('Office admin registration failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'office' => $request->office,
                'ip' => request()->ip()
            ]);
            return back()->withErrors(['error' => 'Registration failed. Please try again or contact the administrator.'])
                        ->withInput();
        }
    }
}
