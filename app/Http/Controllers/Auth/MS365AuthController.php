<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MicrosoftGraphService;
use App\Traits\SecurityValidationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class MS365AuthController extends Controller
{
    use SecurityValidationTrait;
    public function showSignupForm()
    {
        return view('auth.ms365-signup');
    }

    public function sendRegistrationLink(Request $request)
    {
        $request->validate([
            'ms365_account' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@.*\.edu\.ph$/',
                'unique:users,ms365_account'
            ]
        ], [
            'ms365_account.regex' => 'Please enter a valid .edu.ph email address',
            'ms365_account.unique' => 'This email address is already registered.',
        ]);

        $email = $request->ms365_account;

        // Validate MS365 account using Microsoft Graph API
        $graphService = new MicrosoftGraphService();
        $validationResult = $graphService->validateUser($email);

        if (!$validationResult['exists']) {
            $errorMessage = $validationResult['error'] ?? 'Invalid Microsoft 365 account. Please ensure your account exists and is active.';
            return back()->withErrors(['ms365_account' => $errorMessage])->withInput();
        }

        $registrationUrl = URL::temporarySignedRoute(
            'ms365.register.form',
            now()->addMinutes(30),
            ['email' => $email]
        );

        try {
            // Try to send via Microsoft Graph API first, fallback to Laravel Mail
            $emailSent = $graphService->sendEmail(
                $email,
                'Complete Your Registration - MCC News Aggregator',
                view('emails.ms365-registration', [
                    'email' => $email,
                    'registrationUrl' => $registrationUrl
                ])->render()
            );

            if (!$emailSent) {
                // Fallback to Laravel Mail
                Mail::send('emails.ms365-registration', [
                    'email' => $email,
                    'registrationUrl' => $registrationUrl
                ], function ($message) use ($email) {
                    $message->to($email)
                            ->subject('Complete Your Registration - MCC News Aggregator');
                });
            }

            return back()->with('success', 'Registration link sent to your Microsoft 365 email! Please check your inbox and complete registration within 30 minutes.');
        } catch (\Exception $e) {
            \Log::error('MS365 registration email failed: ' . $e->getMessage());
            return back()->withErrors('Failed to send registration email. Please try again.');
        }
    }

    public function showRegistrationForm(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'This link has expired or is invalid.');
        }

        $email = $request->email;

        if (User::where('ms365_account', $email)->exists()) {
            return redirect()->route('login')->withErrors('This email address is already registered.');
        }

        return view('auth.ms365-register', [
            'email' => $email
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@.*\.edu\.ph$/',
                'unique:users,ms365_account'
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
            'department' => 'required_if:role,student,faculty|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management',
            'year_level' => 'required_if:role,student|in:1st Year,2nd Year,3rd Year,4th Year',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.regex' => 'Please enter a valid .edu.ph email address',
            'email.unique' => 'This email address is already registered.',
            'first_name.regex' => 'First name should only contain letters, spaces, and apostrophes',
            'middle_name.regex' => 'Middle name should only contain letters, spaces, and apostrophes',
            'surname.regex' => 'Surname should only contain letters, spaces, and apostrophes',
            'role.required' => 'Please select your role',
            'role.in' => 'Please select a valid role',
            'department.required_if' => 'Department is required for your role',
            'department.in' => 'Please select a valid department',
            'year_level.required_if' => 'Year level is required for students',
            'year_level.in' => 'Please select a valid year level',
            'password.min' => 'Password must be at least 8 characters long',
        ]);

        if ($validator->fails()) {
            \Log::info('MS365 registration validation failed', [
                'email' => $request->email,
                'errors' => $validator->errors()->toArray()
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            $userData = [
                'ms365_account' => $request->email,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'surname' => $request->surname,
                'role' => $request->role,
                'department' => $request->department,
                'year_level' => $request->role === 'student' ? $request->year_level : null,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ];

            $user = User::create($userData);

            auth()->login($user);

            return redirect()->route('user.dashboard')->with('status', 'Registration successful! Welcome to MCC News Aggregator.');
        } catch (\Exception $e) {
            \Log::error('MS365 registration failed: ' . $e->getMessage());
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'ms365_account_unique')) {
                return back()->withErrors('This email address is already registered. Please use a different email address or try logging in.')->withInput();
            }
            return back()->withErrors('Registration failed. Please try again.')->withInput();
        }
    }

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

        // Since ms365_account is encrypted in the database, we need to find the user manually
        // and then verify the password, rather than using auth()->attempt()
        $email = $request->ms365_account;
        $password = $request->password;

        // Find user by checking decrypted ms365_account field
        $user = User::all()->first(function ($user) use ($email) {
            return $user->ms365_account === $email;
        });

        if ($user && Hash::check($password, $user->password)) {
            // Login the user
            auth()->login($user, $request->filled('remember'));
            $request->session()->regenerate();
            
            return redirect()->intended(route('user.dashboard'));
        }

        return back()->withErrors([
            'ms365_account' => 'The provided credentials do not match our records.',
        ])->onlyInput('ms365_account');
    }
}