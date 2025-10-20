<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of admins (excluding office admins)
     */
    public function index()
    {
        // Get all admins except office admins (they have their own management section)
        $admins = Admin::whereIn('role', ['superadmin', 'department_admin', 'admin'])
                      ->latest()
                      ->get();
        return view('superadmin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin
     */
    public function create()
    {
        return view('superadmin.admins.create');
    }

    /**
     * Send registration email to department admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'email',
                Rule::unique('admins')->where(function ($query) {
                    return $query->where('role', 'department_admin');
                }),
                'max:255'
            ],
            'role' => 'required|in:department_admin',
            'department' => 'required|in:BSIT,BSBA,EDUC,BSED,BSHM',
        ], [
            'username.unique' => 'The account has already been used',
        ]);

        // Check if department admin already exists for this department
        $existingDeptAdmin = Admin::where('role', 'department_admin')
                                 ->where('department', $request->department)
                                 ->first();

        if ($existingDeptAdmin) {
            return back()->withErrors(['department' => 'A department admin already exists for ' . $request->department . ' department.'])
                        ->withInput();
        }

        $email = $request->username;
        $department = $request->department;

        // Generate secure registration token with additional security layers
        $timestamp = now()->timestamp;
        $secureToken = hash('sha256', $email . $department . $timestamp . config('app.key') . request()->ip());
        
        // Store token in cache with expiration for additional validation
        \Cache::put('admin_registration_' . $secureToken, [
            'email' => $email,
            'department' => $department,
            'ip' => request()->ip(),
            'timestamp' => $timestamp,
            'used' => false
        ], now()->addMinutes(30));

        // Generate secure registration URL with enhanced parameters
        $registrationUrl = URL::temporarySignedRoute(
            'admin.register.form',
            now()->addMinutes(30),
            [
                'email' => $email,
                'department' => $department,
                'token' => $secureToken,
                'timestamp' => $timestamp
            ]
        );

        try {
            Mail::send('emails.admin-registration', [
                'email' => $email,
                'department' => $department,
                'registrationUrl' => $registrationUrl
            ], function ($message) use ($email, $department) {
                $message->to($email)
                       ->subject('Department Admin Registration - MCC News Aggregator (' . $department . ')');
            });

            return redirect()->route('superadmin.admins.index')
                            ->with('success', 'Registration email sent to ' . $email . ' for ' . $department . ' department!');
        } catch (\Exception $e) {
            \Log::error('Admin registration email failed: ' . $e->getMessage());
            return back()->withErrors('Failed to send registration email. Please try again.')
                        ->withInput();
        }
    }

    /**
     * Display the specified admin
     */
    public function show(Admin $admin)
    {
        return view('superadmin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin
     */
    public function edit(Admin $admin)
    {
        return view('superadmin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin
     */
    public function update(Request $request, Admin $admin)
    {
        $rules = [
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'password' => 'nullable|string|min:6',
            'password_confirmation' => 'nullable|string|same:password',
        ];

        // If password is provided, make confirmation required
        if ($request->filled('password')) {
            $rules['password_confirmation'] = 'required|string|same:password';
        }

        $request->validate($rules);

        $updateData = [
            'username' => $request->username,
        ];

        // Only update password if it's provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);

        return redirect()->route('superadmin.admins.index')
                        ->with('success', 'Admin updated successfully!');
    }

    /**
     * Remove the specified admin
     */
    public function destroy(Admin $admin)
    {
        // Prevent deletion of the last super admin
        if ($admin->isSuperAdmin() && Admin::superAdmins()->count() <= 1) {
            return redirect()->route('superadmin.admins.index')
                            ->with('error', 'Cannot delete the last super admin!');
        }

        // Prevent self-deletion
        if ($admin->id === auth('admin')->id()) {
            return redirect()->route('superadmin.admins.index')
                            ->with('error', 'You cannot delete your own account!');
        }

        $admin->delete();

        return redirect()->route('superadmin.admins.index')
                        ->with('success', 'Admin deleted successfully!');
    }

    /**
     * Show the form for creating a department admin
     */
    public function createDepartmentAdmin()
    {
        return view('superadmin.department-admins.create');
    }

    /**
     * Store a newly created department admin
     */
    public function storeDepartmentAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:admins|max:255',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|same:password',
            'department' => 'required|in:BSIT,BSBA,EDUC,BSED,BSHM',
        ]);

        // Check if department admin already exists for this department
        $existingDeptAdmin = Admin::where('role', 'department_admin')
                                 ->where('department', $request->department)
                                 ->first();

        if ($existingDeptAdmin) {
            return back()->withErrors(['department' => 'A department admin already exists for ' . $request->department]);
        }

        Admin::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'department_admin',
            'department' => $request->department,
        ]);

        return redirect()->route('superadmin.admins.index')
                        ->with('success', 'Department admin created successfully!');
    }

    /**
     * Display department admins
     */
    public function departmentAdmins()
    {
        $departmentAdmins = Admin::departmentAdmins()->latest()->get();
        return view('superadmin.department-admins.index', compact('departmentAdmins'));
    }

    /**
     * Show the admin registration form (from email link)
     */
    public function showAdminRegistrationForm(Request $request)
    {
        $email = $request->email;
        $department = $request->department;
        $token = $request->token;
        $timestamp = $request->timestamp;

        // Enhanced security validation
        if (!$token || !$timestamp) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'Invalid registration link. Missing security parameters.']);
        }

        // Validate token from cache
        $cachedData = \Cache::get('admin_registration_' . $token);
        
        // Enhanced logging for debugging
        \Log::info('Admin registration form viewed - Cache validation', [
            'email' => $email,
            'department' => $department,
            'ip' => request()->ip(),
            'token' => $token,
            'cache_exists' => $cachedData !== null,
            'cached_data' => $cachedData,
            'cache_key' => 'admin_registration_' . $token,
            'timestamp' => now()->timestamp
        ]);
        
        if (!$cachedData) {
            \Log::warning('Admin registration form viewed with invalid/expired cache', [
                'email' => $email,
                'department' => $department,
                'ip' => request()->ip(),
                'token' => $token
            ]);
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link has expired or is invalid. Please request a new registration link.']);
        }

        // Enhanced cache structure validation
        if (!is_array($cachedData)) {
            \Log::error('Admin registration cache data is not an array', [
                'email' => $email,
                'token' => $token,
                'cached_data_type' => gettype($cachedData),
                'cached_data' => $cachedData
            ]);
            // Clear corrupted cache and return error
            \Cache::forget('admin_registration_' . $token);
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link data is corrupted. Please request a new registration link.']);
        }

        // Verify token hasn't been used (with enhanced validation)
        $isUsed = isset($cachedData['used']) && $cachedData['used'] === true;
        if ($isUsed) {
            \Log::warning('Admin registration form viewed with already used token', [
                'email' => $email,
                'department' => $department,
                'ip' => request()->ip(),
                'token' => $token,
                'cached_data' => $cachedData,
                'used_value' => $cachedData['used'] ?? 'not_set',
                'used_type' => gettype($cachedData['used'] ?? null)
            ]);
            return redirect()->route('login')
                           ->withErrors(['error' => 'This registration link has already been used. Please request a new registration link.']);
        }

        // Verify email and department match
        if ($cachedData['email'] !== $email || $cachedData['department'] !== $department) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link parameters do not match. Security violation detected.']);
        }

        // Verify timestamp (additional protection against replay attacks)
        if ($cachedData['timestamp'] != $timestamp) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link timestamp mismatch. Security violation detected.']);
        }

        // Optional: Verify IP address (uncomment if you want strict IP validation)
        // if ($cachedData['ip'] !== request()->ip()) {
        //     return redirect()->route('login')
        //                    ->withErrors(['error' => 'Registration link can only be used from the original IP address.']);
        // }

        // Check if admin already exists (handle encrypted username field)
        $existingAdmin = Admin::all()->first(function($admin) use ($email) {
            return $admin->username === $email;
        });
        if ($existingAdmin) {
            return redirect()->route('login')
                           ->with('error', 'This admin account already exists. Please login instead.');
        }

        // Mark token as viewed (but not used yet) - explicitly ensure used is false
        $updatedCacheData = array_merge($cachedData, ['viewed' => true, 'used' => false]);
        \Cache::put('admin_registration_' . $token, $updatedCacheData, now()->addMinutes(30));
        
        // Log form viewing for debugging
        \Log::info('Admin registration form viewed', [
            'email' => $email,
            'department' => $department,
            'token' => $token,
            'ip' => request()->ip(),
            'original_cache' => $cachedData,
            'updated_cache' => $updatedCacheData,
            'timestamp' => now()
        ]);

        return view('auth.admin-register', compact('email', 'department', 'token', 'timestamp'));
    }

    /**
     * Complete the admin registration
     */
    public function completeAdminRegistration(Request $request)
    {
        // Debug logging at the start
        \Log::info('Admin registration completion started', [
            'email' => $request->email,
            'department' => $request->department,
            'ip' => request()->ip(),
            'secure_token' => $request->secure_token
        ]);

        $request->validate([
            'email' => 'required|email',
            'department' => 'required|in:BSIT,BSBA,EDUC,BSED,BSHM',
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
        $cachedData = \Cache::get('admin_registration_' . $secureToken);
        
        // Enhanced debug logging for cache data
        \Log::info('Admin registration completion - Cache validation', [
            'email' => $request->email,
            'department' => $request->department,
            'ip' => request()->ip(),
            'token' => $secureToken,
            'cache_exists' => $cachedData !== null,
            'cached_data' => $cachedData,
            'cache_key' => 'admin_registration_' . $secureToken,
            'timestamp' => now()->timestamp
        ]);
        
        if (!$cachedData) {
            \Log::warning('Admin registration attempted with invalid/expired token', [
                'email' => $request->email,
                'ip' => request()->ip(),
                'token' => $secureToken
            ]);
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration session has expired or is invalid. Please request a new registration link.']);
        }

        // Enhanced cache structure validation
        if (!is_array($cachedData)) {
            \Log::error('Admin registration completion cache data is not an array', [
                'email' => $request->email,
                'token' => $secureToken,
                'cached_data_type' => gettype($cachedData),
                'cached_data' => $cachedData
            ]);
            // Clear corrupted cache and return error
            \Cache::forget('admin_registration_' . $secureToken);
            return redirect()->route('login')
                           ->withErrors(['error' => 'Registration link data is corrupted. Please request a new registration link.']);
        }

        // Verify token hasn't been used (with enhanced validation and safety checks)
        $isUsed = false;
        if (array_key_exists('used', $cachedData)) {
            $isUsed = $cachedData['used'] === true || $cachedData['used'] === 'true' || $cachedData['used'] === 1;
        }
        
        if ($isUsed) {
            \Log::warning('Admin registration attempted with already used token', [
                'email' => $request->email,
                'ip' => request()->ip(),
                'token' => $secureToken,
                'cached_data' => $cachedData,
                'used_value' => $cachedData['used'] ?? 'not_set',
                'used_type' => gettype($cachedData['used'] ?? null),
                'array_key_exists_used' => array_key_exists('used', $cachedData),
                'isset_used' => isset($cachedData['used'])
            ]);
            return redirect()->route('login')
                           ->withErrors(['error' => 'This registration link has already been used. Please request a new registration link.']);
        }

        // Verify all parameters match cached data
        if ($cachedData['email'] !== $request->email || 
            $cachedData['department'] !== $request->department || 
            $cachedData['timestamp'] != $timestamp) {
            \Log::warning('Admin registration attempted with mismatched parameters', [
                'email' => $request->email,
                'department' => $request->department,
                'ip' => request()->ip(),
                'cached_email' => $cachedData['email'],
                'cached_department' => $cachedData['department']
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
                           ->with('error', 'This admin account already exists. Please login instead.');
        }

        // Check if department admin already exists for this department
        $existingDeptAdmin = Admin::where('role', 'department_admin')
                                 ->where('department', $request->department)
                                 ->first();

        if ($existingDeptAdmin) {
            return back()->withErrors(['department' => 'A department admin already exists for ' . $request->department . ' department.']);
        }

        try {
            // Create the admin account using email as username (mark as used AFTER success)
            $admin = Admin::create([
                'username' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'department_admin',
                'department' => $request->department,
            ]);

            // Mark token as used ONLY after successful account creation
            \Cache::put('admin_registration_' . $secureToken, array_merge($cachedData, ['used' => true]), now()->addHours(24));

            // Log successful registration
            \Log::info('Admin registration completed successfully', [
                'admin_id' => $admin->id,
                'username' => $admin->username,
                'department' => $admin->department,
                'ip' => request()->ip(),
                'timestamp' => now()
            ]);

            // Clear the token from cache after successful registration
            \Cache::forget('admin_registration_' . $secureToken);

            return redirect()->route('login')
                            ->with('success', 'Your department admin account has been created successfully! You can now login with your email and password.');
        } catch (\Exception $e) {
            \Log::error('Admin registration failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'department' => $request->department,
                'ip' => request()->ip()
            ]);
            return back()->withErrors(['error' => 'Registration failed. Please try again or contact the administrator.'])
                        ->withInput();
        }
    }
}
