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
    <title>Office Admin Registration - MCC News Aggregator</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            max-width: 500px;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .auth-header h1 i {
            color: var(--secondary);
            font-size: 2.2rem;
        }

        .auth-header p {
            font-size: 1rem;
            color: var(--gray-600);
            font-weight: 400;
            margin: 0 0 1rem 0;
        }

        .office-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--secondary), var(--secondary-light));
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .security-notice {
            margin-top: 15px;
            padding: 10px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            color: var(--success);
        }

        .auth-content {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
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

        .form-control.error {
            border-color: var(--danger);
            background-color: #fef2f2;
        }

        .form-control[readonly] {
            background-color: var(--gray-50);
            cursor: not-allowed;
        }

        .password-field {
            position: relative;
        }

        .password-field .form-control {
            padding-right: 55px;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: var(--transition);
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            touch-action: manipulation;
            font-size: 1.1rem;
        }

        .password-toggle:hover {
            color: var(--secondary);
            background-color: var(--gray-100);
        }
        
        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }

        /* Disable hover effects on touch devices */
        @media (hover: none) and (pointer: coarse) {
            .password-toggle:hover {
                color: var(--gray-600);
                background-color: transparent;
            }
            
            .password-toggle:active {
                color: var(--secondary);
                background-color: var(--gray-100);
                transform: translateY(-50%) scale(0.95);
            }
        }

        .password-strength {
            margin-top: 10px;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            display: none;
        }

        .password-strength.weak {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            display: block;
        }

        .password-strength.medium {
            background-color: #fef3c7;
            color: #d97706;
            border: 1px solid #fed7aa;
            display: block;
        }

        .password-strength.strong {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            display: block;
        }

        .password-requirements {
            margin-top: 10px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .password-requirements h4 {
            color: #374151;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .requirement i {
            width: 16px;
            text-align: center;
        }

        .requirement.met {
            color: #16a34a;
        }

        .requirement.met i {
            color: #16a34a;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
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
            gap: 10px;
            min-height: 44px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .btn:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
        }

        .btn:active:not(:disabled) {
            transform: translateY(1px) scale(0.98);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Disable hover effects on touch devices */
        @media (hover: none) and (pointer: coarse) {
            .btn:hover:not(:disabled) {
                background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
                box-shadow: none;
            }
            
            .btn:active:not(:disabled) {
                transform: translateY(1px) scale(0.96);
                background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            }
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
            
            .auth-header h1 i {
                font-size: 1.9rem;
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
            
            .office-badge {
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            
            .security-notice {
                font-size: 0.75rem;
                padding: 8px;
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
            
            .auth-header h1 i {
                font-size: 2rem;
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
            
            .office-badge {
                font-size: 0.85rem;
            }
            
            .security-notice {
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
            
            .auth-header h1 i {
                font-size: 2.1rem;
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
                max-width: 500px;
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
            
            .auth-header h1 i {
                font-size: 1.7rem;
            }
            
            .auth-content {
                padding: 1rem;
            }
            
            .security-notice {
                padding: 8px;
                margin-top: 10px;
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
            .btn,
            .password-toggle {
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
            input[type="password"]:focus,
            input[type="email"]:focus {
                font-size: 16px;
            }
            
            /* Better button spacing */
            .btn {
                margin-top: 0.5rem;
            }
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            <h1><i class="fas fa-briefcase"></i> Office Admin Registration</h1>
            <p>Complete your office admin account setup</p>
            <div class="office-badge">{{ $office }} Office</div>
            <div class="security-notice">
                <i class="fas fa-shield-alt"></i> <strong>Secure Registration</strong> - This link is protected with advanced security tokens and expires in 30 minutes.
            </div>
        </div>
        
        <div class="auth-content">

        <form id="registrationForm" action="{{ route('office-admin.register.complete') }}" method="POST">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="office" value="{{ $office }}">
            <input type="hidden" name="secure_token" value="{{ $token }}">
            <input type="hidden" name="timestamp" value="{{ $timestamp }}">

            <div class="form-group">
                <label for="email_display">Email Address</label>
                <input type="email" 
                       id="email_display" 
                       class="form-control" 
                       value="{{ $email }}" 
                       readonly
                       style="background-color: #f8fafc; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color: red;">*</span></label>
                <div class="password-field">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control @error('password') error @enderror" 
                           placeholder="Create a strong password"
                           required>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
                </div>
                <div id="passwordStrength" class="password-strength"></div>
                <div class="password-requirements">
                    <h4><i class="fas fa-shield-alt"></i> Password Requirements</h4>
                    <div class="requirement" id="req-length">
                        <i class="fas fa-times"></i>
                        At least 8 characters long
                    </div>
                    <div class="requirement" id="req-uppercase">
                        <i class="fas fa-times"></i>
                        One uppercase letter (A-Z)
                    </div>
                    <div class="requirement" id="req-lowercase">
                        <i class="fas fa-times"></i>
                        One lowercase letter (a-z)
                    </div>
                    <div class="requirement" id="req-number">
                        <i class="fas fa-times"></i>
                        One number (0-9)
                    </div>
                    <div class="requirement" id="req-special">
                        <i class="fas fa-times"></i>
                        One special character (@$!%*?&)
                    </div>
                </div>
                @error('password')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span style="color: red;">*</span></label>
                <div class="password-field">
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="form-control @error('password_confirmation') error @enderror" 
                           placeholder="Confirm your password"
                           required>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password_confirmation', this)"></i>
                </div>
                <div id="passwordMatch" class="password-strength"></div>
                @error('password_confirmation')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-user-plus"></i>
                <span id="submitText">Create Office Admin Account</span>
                <div class="loading-spinner" id="loadingSpinner"></div>
            </button>
        </form>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[@$!%*?&]/.test(password)
            };

            // Update requirement indicators
            updateRequirement('req-length', requirements.length);
            updateRequirement('req-uppercase', requirements.uppercase);
            updateRequirement('req-lowercase', requirements.lowercase);
            updateRequirement('req-number', requirements.number);
            updateRequirement('req-special', requirements.special);

            const metCount = Object.values(requirements).filter(Boolean).length;
            const strengthDiv = document.getElementById('passwordStrength');

            if (password.length === 0) {
                strengthDiv.style.display = 'none';
                return false;
            }

            if (metCount < 3) {
                strengthDiv.className = 'password-strength weak';
                strengthDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Weak password - Please meet more requirements';
                return false;
            } else if (metCount < 5) {
                strengthDiv.className = 'password-strength medium';
                strengthDiv.innerHTML = '<i class="fas fa-shield-alt"></i> Medium strength - Consider meeting all requirements';
                return false;
            } else {
                strengthDiv.className = 'password-strength strong';
                strengthDiv.innerHTML = '<i class="fas fa-check-circle"></i> Strong password!';
                return true;
            }
        }

        function updateRequirement(id, met) {
            const element = document.getElementById(id);
            const icon = element.querySelector('i');
            
            if (met) {
                element.classList.add('met');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-check');
            } else {
                element.classList.remove('met');
                icon.classList.remove('fa-check');
                icon.classList.add('fa-times');
            }
        }

        // Password confirmation checker
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const matchDiv = document.getElementById('passwordMatch');

            if (confirmation.length === 0) {
                matchDiv.style.display = 'none';
                return false;
            }

            if (password === confirmation) {
                matchDiv.className = 'password-strength strong';
                matchDiv.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match!';
                return true;
            } else {
                matchDiv.className = 'password-strength weak';
                matchDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Passwords do not match';
                return false;
            }
        }

        // Form validation
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const submitBtn = document.getElementById('submitBtn');

            const isPasswordStrong = checkPasswordStrength(password);
            const isPasswordMatch = checkPasswordMatch();
            const isValid = isPasswordStrong && isPasswordMatch && password.length > 0 && confirmation.length > 0;

            submitBtn.disabled = !isValid;
            return isValid;
        }

        // Event listeners
        document.getElementById('password').addEventListener('input', function() {
            validateForm();
        });

        document.getElementById('password_confirmation').addEventListener('input', function() {
            validateForm();
        });

        // Security check to prevent form tampering
        function validateSecurityTokens() {
            const secureToken = document.querySelector('input[name="secure_token"]').value;
            const timestamp = document.querySelector('input[name="timestamp"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const office = document.querySelector('input[name="office"]').value;

            if (!secureToken || !timestamp || !email || !office) {
                Swal.fire({
                    icon: 'error',
                    title: 'Security Error',
                    text: 'Security tokens are missing or invalid. Please request a new registration link.',
                    confirmButtonColor: '#667eea'
                }).then(() => {
                    window.location.href = '{{ route("login") }}';
                });
                return false;
            }

            // Check if timestamp is too old (30 minutes)
            const currentTime = Math.floor(Date.now() / 1000);
            const tokenTime = parseInt(timestamp);
            if (currentTime - tokenTime > 1800) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Registration Expired',
                    text: 'This registration link has expired. Please request a new registration link.',
                    confirmButtonColor: '#667eea'
                }).then(() => {
                    window.location.href = '{{ route("login") }}';
                });
                return false;
            }

            return true;
        }

        // Form submission with loading state and security validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingSpinner = document.getElementById('loadingSpinner');

            // Security validation first
            if (!validateSecurityTokens()) {
                e.preventDefault();
                return;
            }

            if (!validateForm()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Form',
                    text: 'Please ensure your password meets all requirements and passwords match.',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitText.textContent = 'Creating Account...';
            loadingSpinner.style.display = 'block';
        });

        // Show success message if there are no errors
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#667eea'
            });
        @endif

        // Show error message if there are errors
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                html: '@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach',
                confirmButtonColor: '#667eea'
            });
        @endif

        // Initial validation and security check
        document.addEventListener('DOMContentLoaded', function() {
            // Validate security tokens on page load
            if (!validateSecurityTokens()) {
                return;
            }
            
            // Initial form validation
            validateForm();

            // Add periodic security check (every 5 minutes)
            setInterval(function() {
                const timestamp = document.querySelector('input[name="timestamp"]').value;
                const currentTime = Math.floor(Date.now() / 1000);
                const tokenTime = parseInt(timestamp);
                
                if (currentTime - tokenTime > 1800) { // 30 minutes
                    Swal.fire({
                        icon: 'warning',
                        title: 'Session Expired',
                        text: 'Your registration session has expired for security reasons. Please request a new registration link.',
                        confirmButtonColor: '#667eea',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        window.location.href = '{{ route("login") }}';
                    });
                }
            }, 300000); // Check every 5 minutes
        });
    </script>
</body>
</html>
