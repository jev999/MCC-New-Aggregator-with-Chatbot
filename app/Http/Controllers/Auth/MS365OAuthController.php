<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Ms365Account;
use App\Models\User;
use App\Models\RegistrationToken;
use App\Services\MicrosoftGraphService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class MS365OAuthController extends Controller
{
    protected $graphService;

    public function __construct()
    {
        $this->graphService = new MicrosoftGraphService();
    }

    /**
     * Show the MS365 signup form
     */
    public function showSignupForm()
    {
        return view('auth.ms365-signup');
    }

    /**
     * Send registration link via Microsoft Graph API
     */
    public function sendSignupLink(Request $request)
    {
        $request->validate([
            'ms365_account' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@.*\.edu\.ph$/'
            ]
        ], [
            'ms365_account.regex' => 'Please enter a valid .edu.ph email address',
        ]);

        $email = $request->ms365_account;

        // Check if email exists in ms365_accounts table
        $ms365Account = Ms365Account::where('user_principal_name', $email)->first();
        
        if (!$ms365Account) {
            return back()->withErrors(['ms365_account' => 'Email not found in MS365 records. Please contact your administrator.'])->withInput();
        }

        if (!$ms365Account->isValidForRegistration()) {
            return back()->withErrors(['ms365_account' => 'This MS365 account is not active for registration.'])->withInput();
        }

        // Check if user already exists
        if (User::where('ms365_account', $email)->exists()) {
            return back()->withErrors(['ms365_account' => 'This email address is already registered. Please log in instead.'])->withInput();
        }

        // Generate registration token
        $token = Str::random(64);
        $expiresAt = now()->addMinutes(30);

        // Store registration token
        RegistrationToken::createToken($email, $token, $expiresAt);

        // Prepare registration link
        $registrationUrl = URL::to("/register/{$token}");

        // Send email via Microsoft Graph API
        try {
            $emailSent = $this->graphService->sendEmail(
                $email,
                'Complete Your Registration - MCC News Aggregator',
                view('emails.ms365-registration', [
                    'email' => $email,
                    'registrationUrl' => $registrationUrl,
                    'expiresAt' => $expiresAt->format('M d, Y \a\t g:i A')
                ])->render()
            );

            if ($emailSent === true) {
                return back()->with('success', 'Registration link sent to your Outlook email! Please check your inbox and complete registration within 30 minutes.');
            }
        } catch (\Exception $e) {
            Log::error('Microsoft Graph API error: ' . $e->getMessage());
            // Continue to fallback
        }

        // Fallback to Laravel Mail if Graph API fails
        try {
            \Mail::send('emails.gmail-registration', [
                'email' => $email,
                'registrationUrl' => $registrationUrl,
                'expiresAt' => $expiresAt->format('M d, Y \a\t g:i A')
            ], function ($message) use ($email) {
                $message->to($email)
                        ->subject('Complete Your Registration - MCC News Aggregator');
            });
            
            return back()->with('success', 'Registration link sent to your email! Please check your inbox and complete registration within 30 minutes.');
        } catch (\Exception $e) {
            Log::error('MS365 registration email failed: ' . $e->getMessage());
            
            // Final fallback: Store email details for manual sending
            try {
                // Store the registration request for manual processing
                DB::table('pending_registrations')->insert([
                    'email' => $email,
                    'token' => $token,
                    'registration_url' => $registrationUrl,
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'status' => 'pending'
                ]);
                
                return back()->with('success', 'Registration request received! Due to email delivery issues, an administrator will manually send your registration link. Please check back later or contact support.');
                
            } catch (\Exception $dbError) {
                Log::error('Failed to store pending registration: ' . $dbError->getMessage());
                return back()->withErrors(['ms365_account' => 'Registration system temporarily unavailable. Please try again later or contact support.'])->withInput();
            }
        }
    }

    /**
     * Show registration form with token validation
     */
    public function showRegisterForm($token)
    {
        $tokenRecord = RegistrationToken::findValidToken($token);

        if (!$tokenRecord) {
            return redirect()->route('login')->withErrors('This registration link has expired or is invalid.');
        }

        $ms365Account = Ms365Account::where('user_principal_name', $tokenRecord->email)->first();
        
        if (!$ms365Account || !$ms365Account->isValidForRegistration()) {
            return redirect()->route('login')->withErrors('This MS365 account is not valid for registration.');
        }

        return view('auth.ms365-register', [
            'email' => $tokenRecord->email,
            'token' => $token,
            'ms365Account' => $ms365Account
        ]);
    }

    /**
     * Handle user registration
     */
    public function handleRegister(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'ms365_account' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@.*\.edu\.ph$/'
            ],
            'first_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z\' ]+$/'
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[A-Za-z\' ]+$/'
            ],
            'surname' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z\' ]+$/'
            ],
            'role' => 'required|in:student,faculty',
            'department' => 'required|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management',
            'year_level' => 'required_if:role,student|in:1st Year,2nd Year,3rd Year,4th Year',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'ms365_account.regex' => 'Please enter a valid .edu.ph email address',
            'first_name.regex' => 'First name should only contain letters, spaces, and apostrophes',
            'middle_name.regex' => 'Middle name should only contain letters, spaces, and apostrophes',
            'surname.regex' => 'Surname should only contain letters, spaces, and apostrophes',
            'role.required' => 'Please select a role',
            'role.in' => 'Please select a valid role',
            'department.required' => 'Department is required',
            'department.in' => 'Please select a valid department',
            'year_level.required_if' => 'Year level is required for students',
            'year_level.in' => 'Please select a valid year level',
            'password.min' => 'Password must be at least 8 characters long',
        ]);

        // Validate token
        $tokenRecord = RegistrationToken::findValidToken($request->token);

        if (!$tokenRecord || $tokenRecord->email !== $request->ms365_account) {
            return back()->withErrors(['ms365_account' => 'Invalid or expired registration token.'])->withInput();
        }

        // Validate MS365 account
        $ms365Account = Ms365Account::where('user_principal_name', $request->ms365_account)->first();
        
        if (!$ms365Account || !$ms365Account->isValidForRegistration()) {
            return back()->withErrors(['ms365_account' => 'This MS365 account is not valid for registration.'])->withInput();
        }

        // Check if user already exists
        if (User::where('ms365_account', $request->ms365_account)->exists()) {
            return back()->withErrors(['ms365_account' => 'This email address is already registered.'])->withInput();
        }

        try {
            // Create user
            $userData = [
                'ms365_account' => $request->ms365_account,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'surname' => $request->surname,
                'role' => $request->role,
                'department' => $request->department,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ];

            // Only set year_level for students
            if ($request->role === 'student') {
                $userData['year_level'] = $request->year_level;
            }

            $user = User::create($userData);

            // Delete used token
            RegistrationToken::deleteToken($request->token);

            // Redirect back to unified login with success message
            return redirect()->route('login')
                ->with('success', 'Registration successful! You can now log in with your MS365 account.')
                ->with('login_type', 'ms365');
        } catch (\Exception $e) {
            Log::error('MS365 registration failed: ' . $e->getMessage());
            return back()->withErrors(['ms365_account' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    /**
     * Redirect to Microsoft OAuth2
     */
    public function redirectToProvider()
    {
        return Socialite::driver('microsoft')
            ->scopes(['openid', 'profile', 'email', 'offline_access'])
            ->redirect();
    }

    /**
     * Handle Microsoft OAuth2 callback
     */
    public function handleProviderCallback()
    {
        try {
            $msUser = Socialite::driver('microsoft')->user();
            
            // Check if email exists in ms365_accounts table
            $ms365Account = Ms365Account::where('user_principal_name', $msUser->email)->first();
            
            if (!$ms365Account) {
                return redirect()->route('ms365.signup')->withErrors(['email' => 'Account not authorized. Please sign up first.']);
            }

            if (!$ms365Account->isValidForRegistration()) {
                return redirect()->route('ms365.signup')->withErrors(['email' => 'This MS365 account is not active for login.']);
            }

            // Check if user exists, if not create one
            $user = User::where('ms365_account', $msUser->email)->first();
            
            if (!$user) {
                // Create user from MS365 account data
                $user = User::create([
                    'ms365_account' => $msUser->email,
                    'first_name' => $ms365Account->first_name ?: 'User',
                    'surname' => $ms365Account->last_name ?: 'User',
                    'role' => 'student', // Default role since it's not in ms365_accounts
                    'department' => 'BSIT', // Default department since it's not in ms365_accounts
                    'password' => Hash::make(Str::random(12)), // Random password for OAuth users
                    'email_verified_at' => now(),
                ]);
            }

            // Log in the user
            Auth::login($user);

            return redirect()->intended(route('user.dashboard'));
            
        } catch (\Exception $e) {
            Log::error('MS365 OAuth callback error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Authentication failed. Please try again.']);
        }
    }

    /**
     * Show login form (for manual login with MS365 credentials)
     */
    public function showLoginForm()
    {
        return view('auth.ms365-login');
    }

    /**
     * Handle manual MS365 login
     */
    public function login(Request $request)
    {
        $request->validate([
            'ms365_account' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@.*\.edu\.ph$/'
            ],
            'password' => 'required|string',
        ], [
            'ms365_account.regex' => 'Please enter a valid .edu.ph email address',
        ]);

        // Check if email exists in ms365_accounts table
        $ms365Account = Ms365Account::where('user_principal_name', $request->ms365_account)->first();
        
        if (!$ms365Account) {
            return back()->withErrors(['ms365_account' => 'Account not authorized. Please sign up first.'])->withInput();
        }

        if (!$ms365Account->isValidForRegistration()) {
            return back()->withErrors(['ms365_account' => 'This MS365 account is not active for login.'])->withInput();
        }

        // Attempt authentication
        if (Auth::attempt(['ms365_account' => $request->ms365_account, 'password' => $request->password], $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('user.dashboard'));
        }

        return back()->withErrors([
            'ms365_account' => 'The provided credentials do not match our records.',
        ])->onlyInput('ms365_account');
    }
}
