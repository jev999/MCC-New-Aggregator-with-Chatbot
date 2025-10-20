<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    <meta http-equiv="Permissions-Policy" content="geolocation=(), microphone=(), camera=()">
    <title>Forgot Password - MCC News Aggregator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #111827;
            --primary-light: #1f2937;
            --secondary: #2563eb;
            --secondary-light: #3b82f6;
            --accent: #8b5cf6;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: url("{{ asset('images/mccfront.jpg') }}") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--gray-800);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            text-size-adjust: 100%;
            touch-action: manipulation;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
        }

        .auth-container {
            background: white;
            width: 100%;
            max-width: 480px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition);
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 0 1.5rem;
            background: white;
            position: relative;
            cursor: pointer;
            transition: var(--transition);
        }

        .logo {
            height: 100px;
            width: auto;
            max-width: 110px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            position: relative;
            z-index: 2;
            margin-top: 10px;
            transition: var(--transition);
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }

        .logo-container:hover .logo {
            transform: scale(1.1);
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
        }

        .logo-container:active .logo {
            transform: scale(1.05);
        }

        /* Disable hover effects on touch devices */
        @media (hover: none) and (pointer: coarse) {
            .logo-container:hover .logo {
                transform: none;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            }
            
            .logo-container:active .logo {
                transform: scale(1.05);
                filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
            }
        }

        .auth-header {
            background: white;
            color: var(--secondary);
            padding: 0 2rem 2rem;
            text-align: center;
            position: relative;
        }

        .auth-header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
            color: var(--secondary);
        }

        .auth-header p {
            font-size: 1rem;
            color: var(--gray-600);
            font-weight: 400;
            margin: 0;
        }

        .auth-content {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }

        .form-group label i {
            margin-right: 0.75rem;
            color: var(--gray-600);
            width: 16px;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: var(--transition);
            background-color: white;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            -webkit-tap-highlight-color: transparent;
            min-height: 44px;
            touch-action: manipulation;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 44px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
        }

        .btn:active {
            transform: translateY(1px) scale(0.98);
        }

        /* Disable hover effects on touch devices */
        @media (hover: none) and (pointer: coarse) {
            .btn:hover {
                background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
                box-shadow: none;
            }
            
            .btn:active {
                transform: translateY(1px) scale(0.96);
                background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            }
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
            box-shadow: var(--shadow-sm);
        }

        .auth-links {
            margin-top: 1.5rem;
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .auth-links a {
            color: var(--secondary);
            text-decoration: none;
            font-size: 0.875rem;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .auth-links a:hover {
            color: var(--secondary-light);
        }

        .auth-links a i {
            margin-right: 0.5rem;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.05);
            color: var(--danger);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            font-size: 0.875rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
        }

        .error-message::before {
            content: '\f06a';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 0.5rem;
        }

        .success-message {
            background: rgba(16, 185, 129, 0.05);
            color: var(--success);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            font-size: 0.875rem;
            border: 1px solid rgba(16, 185, 129, 0.2);
            display: flex;
            align-items: center;
        }

        .success-message::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 0.5rem;
        }

        .form-group .error {
            border-color: var(--danger);
        }

        .form-group .error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }

        .signup-notice {
            background: rgba(245, 158, 11, 0.05);
            color: var(--warning);
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-top: 1rem;
            font-size: 0.875rem;
            border: 1px solid rgba(245, 158, 11, 0.2);
            text-align: center;
        }

        .signup-notice a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-notice a:hover {
            text-decoration: underline;
        }

        /* Animation for form fields */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Enhanced Responsive Design */
        
        /* Ultra small devices (phones, 320px to 359px) */
        @media (max-width: 359px) {
            body {
                padding: 8px;
            }
            
            .auth-container {
                max-width: 100%;
                border-radius: var(--radius-sm);
            }
            
            .logo-container {
                padding: 1.5rem 0 1rem;
            }
            
            .logo {
                height: 70px;
                max-width: 80px;
            }
            
            .auth-header {
                padding: 0 1rem 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.75rem;
            }
            
            .auth-header p {
                font-size: 0.875rem;
            }
            
            .auth-content {
                padding: 1rem;
            }
            
            .form-control {
                padding: 0.75rem;
                font-size: 0.875rem;
                min-height: 44px;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.875rem;
                min-height: 44px;
            }
            
            .signup-notice {
                padding: 0.875rem;
                font-size: 0.75rem;
            }
        }
        
        /* Extra small devices (phones, 360px to 480px) */
        @media (min-width: 360px) and (max-width: 480px) {
            body {
                padding: 10px;
                min-height: 100vh;
            }
            
            .auth-container {
                max-width: 100%;
                border-radius: var(--radius-sm);
            }
            
            .logo-container {
                padding: 1.75rem 0 1.25rem;
            }
            
            .logo {
                height: 80px;
                max-width: 90px;
            }
            
            .auth-header {
                padding: 0 1.25rem 1.75rem;
            }
            
            .auth-header h1 {
                font-size: 1.875rem;
            }
            
            .auth-header p {
                font-size: 0.9rem;
            }
            
            .auth-content {
                padding: 1.25rem;
            }
            
            .form-control {
                padding: 0.8rem;
                font-size: 0.9rem;
                min-height: 44px;
            }
            
            .btn {
                padding: 0.9rem;
                font-size: 0.9rem;
                min-height: 44px;
            }
            
            .signup-notice {
                padding: 1rem;
                font-size: 0.8rem;
            }
        }
        
        /* Small devices (phones, 481px to 576px) */
        @media (min-width: 481px) and (max-width: 576px) {
            body {
                padding: 15px;
            }
            
            .auth-container {
                max-width: 100%;
            }
            
            .logo-container {
                padding: 2rem 0 1.5rem;
            }
            
            .logo {
                height: 90px;
                max-width: 100px;
            }
            
            .auth-header {
                padding: 0 1.5rem 2rem;
            }
            
            .auth-header h1 {
                font-size: 2rem;
            }
            
            .auth-content {
                padding: 1.5rem;
            }
            
            .form-control {
                min-height: 44px;
            }
            
            .btn {
                min-height: 44px;
            }
        }
        
        /* Medium devices (tablets, 577px to 768px) */
        @media (min-width: 577px) and (max-width: 768px) {
            .auth-container {
                max-width: 90%;
            }
            
            .logo {
                height: 95px;
                max-width: 105px;
            }
        }
        
        /* Large devices (desktops, 769px and up) */
        @media (min-width: 769px) {
            .auth-container {
                max-width: 480px;
            }
            
            .logo {
                height: 100px;
                max-width: 110px;
            }
        }
        
        /* Landscape orientation adjustments for mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            body {
                padding: 10px 20px;
            }
            
            .logo-container {
                padding: 1rem 0 0.75rem;
            }
            
            .logo {
                height: 60px;
                max-width: 70px;
            }
            
            .auth-header {
                padding: 0 1.5rem 1rem;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
            }
            
            .auth-content {
                padding: 1rem;
            }
            
            .signup-notice {
                padding: 0.75rem;
                margin-top: 0.75rem;
            }
        }
        
        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .form-control,
            .btn {
                -webkit-font-smoothing: antialiased;
            }
        }
        
        /* Dark mode support (if user prefers dark mode) */
        @media (prefers-color-scheme: dark) {
            .auth-container {
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.15);
            }
        }
        
        /* Reduced motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Enhanced focus and accessibility for mobile */
        @media (max-width: 768px) {
            .form-control:focus,
            .btn:focus {
                outline: 2px solid var(--secondary);
                outline-offset: 2px;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            }
            
            /* Improve touch targets */
            .form-control,
            .btn {
                min-height: 44px;
                touch-action: manipulation;
            }
            
            /* Better text rendering on mobile */
            body {
                -webkit-text-size-adjust: 100%;
                text-rendering: optimizeLegibility;
            }
        }
        
        /* Additional mobile optimizations */
        @media (max-width: 576px) {
            /* Improve text selection and input behavior */
            input, select, textarea {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                border-radius: var(--radius-sm);
            }
            
            /* Prevent zoom on input focus */
            input[type="email"]:focus {
                font-size: 16px;
            }
            
            /* Better button spacing */
            .btn {
                margin-top: 0.5rem;
            }
            
            /* Responsive auth links */
            .auth-links a {
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
                touch-action: manipulation;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Logo at the top center -->
        <div class="logo-container">
            <img src="{{ asset('images/mcclogo.png') }}" alt="MCC Logo" class="logo">
        </div>
        
        <div class="auth-header">
            <h1><i class="fas fa-key"></i> Forgot Password</h1>
            <p>Enter your MS365 email to reset your password</p>
        </div>

        <div class="auth-content">
            @if(session('status'))
                <div class="success-message">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="error-message">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label for="ms365_account">
                        <i class="fab fa-microsoft"></i>
                        MS365 Email Account
                    </label>
                    <input type="email"
                           id="ms365_account"
                           name="ms365_account"
                           class="form-control @error('ms365_account') error @enderror"
                           value="{{ old('ms365_account') }}"
                           placeholder="example@mcc-nac.edu.ph"
                           pattern="[a-zA-Z0-9._%+-]+@.*\.edu\.ph"
                           title="Please enter a valid .edu.ph email address"
                           required
                           autofocus>
                    @error('ms365_account')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i>
                    Send Password Reset Link
                </button>
            </form>

            @if(session('show_signup'))
                <div class="signup-notice">
                    <i class="fas fa-info-circle"></i>
                    Don't have an account? 
                    <a href="{{ route('ms365.signup') }}">Sign up here</a>
                </div>
            @endif

            <div class="auth-links">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>