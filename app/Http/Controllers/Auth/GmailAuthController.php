<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;

class GmailAuthController extends Controller
{
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
     * Show the signup form
     */
    public function showSignupForm()
    {
        return view('auth.gmail-signup');
    }

    /**
     * Send registration link to Gmail
     */
    public function sendRegistrationLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gmail_account' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
                'unique:users,gmail_account'
            ],
        ], [
            'gmail_account.regex' => 'Please enter a valid Gmail address (@gmail.com)',
            'gmail_account.unique' => 'This Gmail address is already registered.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->gmail_account;

        // Generate signed URL that expires in 30 minutes
        $registrationUrl = URL::temporarySignedRoute(
            'gmail.register.form',
            now()->addMinutes(30),
            ['email' => $email]
        );

        try {
            // Send registration email
            Mail::send('emails.gmail-registration', [
                'email' => $email,
                'registrationUrl' => $registrationUrl
            ], function ($message) use ($email) {
                $message->to($email)
                       ->subject('Complete Your Registration - MCC News Aggregator');
            });

            return back()->with('success', 'Registration link sent to your Gmail! Please check your inbox and complete registration within 30 minutes.');

        } catch (\Exception $e) {
            \Log::error('Gmail registration email failed: ' . $e->getMessage());
            return back()->withErrors('Failed to send registration email. Please try again.');
        }
    }

    /**
     * Show registration form from email link
     */
    public function showRegistrationForm(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('login')->withErrors('Invalid or expired registration link.');
        }

        $email = $request->email;

        // Check if user already exists
        if (User::where('gmail_account', $email)->exists()) {
            return redirect()->route('login')->withErrors('This Gmail address is already registered.');
        }

        return view('auth.gmail-register', [
            'email' => $email
        ]);
    }

    /**
     * Complete registration
     */
    public function completeRegistration(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('login')->withErrors('Invalid or expired registration link.');
        }

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
                'unique:users,gmail_account'
            ],
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Z\'\s]+$/',
            'middle_name' => 'nullable|string|max:255|regex:/^[a-zA-Z\'\s]+$/',
            'surname' => 'required|string|max:255|regex:/^[a-zA-Z\'\s]+$/',
            'role' => 'required|in:student,faculty',
            'department' => 'required_if:role,student,faculty|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management',
            'year_level' => 'required_if:role,student|in:1st Year,2nd Year,3rd Year,4th Year',
            'password' => 'required|confirmed|min:8',
        ], [
            'email.regex' => 'Please enter a valid Gmail address (@gmail.com)',
            'email.unique' => 'This Gmail address is already registered.',
            'first_name.regex' => 'First name should only contain letters, spaces, and apostrophes',
            'middle_name.regex' => 'Middle name should only contain letters, spaces, and apostrophes',
            'surname.regex' => 'Surname should only contain letters, spaces, and apostrophes',
            'department.required_if' => 'Department is required for students and faculty',
            'year_level.required_if' => 'Year level is required for students',
        ]);

        if ($validator->fails()) {
            \Log::info('Gmail registration validation failed', [
                'email' => $request->email,
                'errors' => $validator->errors()->toArray()
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Create user with all fields
            $userData = [
                'gmail_account' => $request->email,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'surname' => $request->surname,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ];

            // Add department for both students and faculty
            if (in_array($request->role, ['student', 'faculty'])) {
                $userData['department'] = $request->department;
            }

            // Add year level only for students
            if ($request->role === 'student') {
                $userData['year_level'] = $request->year_level;
            }

            // Generate full name from individual name parts
            $fullName = $request->first_name;
            if ($request->middle_name) {
                $fullName .= ' ' . $request->middle_name;
            }
            $fullName .= ' ' . $request->surname;
            $userData['full_name'] = $fullName;

            $user = User::create($userData);

            return redirect()->route('login')->with('success', 'Registration completed successfully! You can now login with your Gmail account.');

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during registration: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_data' => $userData ?? null,
                'error_code' => $e->getCode()
            ]);

            // Check for duplicate entry error
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'gmail_account_unique')) {
                return back()->withErrors('This Gmail address is already registered. Please use a different email address or try logging in.')->withInput();
            }

            return back()->withErrors('Registration failed due to a database error. Please try again.')->withInput();
        } catch (\Exception $e) {
            \Log::error('User registration failed: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_data' => $userData ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Registration failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gmail_account' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'
            ],
            'password' => 'required',
            'g-recaptcha-response' => 'required',
        ], [
            'gmail_account.regex' => 'Please enter a valid Gmail address (@gmail.com)',
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate reCAPTCHA
        if (!$this->validateRecaptcha($request)) {
            return back()->withErrors(['captcha' => 'reCAPTCHA validation failed. Please try again.'])->withInput();
        }

        // Since gmail_account is encrypted in the database, we need to find the user manually
        // and then verify the password, rather than using Auth::attempt()
        $email = $request->gmail_account;
        $password = $request->password;

        // Find user by checking decrypted gmail_account field
        $user = User::all()->first(function ($user) use ($email) {
            return $user->gmail_account === $email;
        });

        if ($user && Hash::check($password, $user->password)) {
            // Login the user
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            
            return redirect()->intended(route('user.dashboard'));
        }

        return back()->withErrors([
            'gmail_account' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        // Log the logout event for security monitoring
        \Log::info('Gmail Auth logout initiated', [
            'user_id' => $user ? $user->id : null,
            'gmail_account' => $user ? $user->gmail_account : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $sessionId,
            'timestamp' => now()->toISOString()
        ]);

        try {
            // Clear all security-related session data
            $securityKeys = [
                'security.ip_address',
                'security.user_agent',
                'security.fingerprint',
                'security.session_start_time',
                'security.last_activity',
                'security.request_count',
                'security.timeout_warning',
                'security.time_remaining'
            ];
            
            foreach ($securityKeys as $key) {
                $request->session()->forget($key);
            }

            // Logout the user
            Auth::logout();

            // Invalidate the session completely
            $request->session()->invalidate();
            
            // Regenerate CSRF token
            $request->session()->regenerateToken();
            
            // Clear all session data
            $request->session()->flush();
            
            // Force garbage collection of old sessions
            $request->session()->migrate(true);

            // Clear remember me cookies if they exist
            $cookies = [];
            if ($request->hasCookie(Auth::getRecallerName())) {
                $cookies[] = \Cookie::forget(Auth::getRecallerName());
            }

            // Log successful logout
            \Log::info('Gmail Auth logout completed successfully', [
                'user_id' => $user ? $user->id : null,
                'session_id' => $sessionId,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            // Prepare response with security headers
            $response = redirect()->route('login')
                ->with('success', 'You have been logged out successfully.');
            
            // Add security headers to prevent caching
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('Clear-Site-Data', '"cache", "cookies", "storage"');
            
            // Clear remember me cookies
            foreach ($cookies as $cookie) {
                $response->withCookie($cookie);
            }

            return $response;

        } catch (\Exception $e) {
            // Log logout error
            \Log::error('Gmail Auth logout failed', [
                'user_id' => $user ? $user->id : null,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            // Force logout anyway for security
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flush();

            return redirect()->route('login')
                ->with('error', 'Logout encountered an error, but you have been logged out for security.');
        }
    }
}
