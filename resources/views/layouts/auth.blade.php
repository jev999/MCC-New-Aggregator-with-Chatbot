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
    <title>@yield('title', 'MCC News Aggregator')</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .auth-header h1 i {
            font-size: 2rem;
            color: var(--secondary);
        }

        .auth-header p {
            color: var(--gray-600);
            font-size: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 400;
        }

        .auth-header .subtitle {
            color: var(--gray-500);
            font-size: 0.875rem;
            font-weight: 300;
        }

        .auth-content {
            padding: 0 2rem 2rem;
            background: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-control.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .btn {
            width: 100%;
            padding: 0.875rem 1rem;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .auth-links a {
            color: #4f46e5;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0 0.5rem;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .success-message, .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            border: 1px solid #a7f3d0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            border: 1px solid #fecaca;
        }

        .email-verified {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid #a7f3d0;
        }

        .form-help {
            color: #6b7280;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .department-field {
            display: none;
        }

        .department-field.show {
            display: block;
        }

        .year-level-field {
            display: none;
        }

        .year-level-field.show {
            display: block;
        }

        /* Login type selector styles */
        .login-type-selector {
            margin-bottom: 2rem;
        }

        .login-type-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .login-type-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .login-type-select option {
            padding: 0.5rem;
            font-weight: 500;
        }

        .login-form {
            display: none;
        }

        .login-form.active {
            display: block;
        }

        .password-input-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 0.25rem;
        }

        .password-toggle:hover {
            color: #374151;
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 1.5rem;
                margin: 0.5rem;
            }

            .login-type-buttons {
                grid-template-columns: 1fr;
                gap: 0.75rem;
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
        
        @yield('content')
    </div>

    <script>
        // Show/hide department and year level fields based on role selection
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const departmentField = document.getElementById('department-field');
            const yearLevelField = document.getElementById('year-level-field');

            if (roleSelect && departmentField && yearLevelField) {
                roleSelect.addEventListener('change', function() {
                    if (this.value === 'student') {
                        departmentField.classList.add('show');
                        yearLevelField.classList.add('show');
                        document.getElementById('department').required = true;
                        document.getElementById('year_level').required = true;
                    } else {
                        departmentField.classList.remove('show');
                        yearLevelField.classList.remove('show');
                        document.getElementById('department').required = false;
                        document.getElementById('year_level').required = false;
                        document.getElementById('department').value = '';
                        document.getElementById('year_level').value = '';
                    }
                });
            }

            // Unified login form functionality
            const loginTypeSelect = document.getElementById('login_type');
            const gmailField = document.getElementById('gmail-field');
            const usernameField = document.getElementById('username-field');
            const passwordField = document.getElementById('password-field');
            const rememberField = document.getElementById('remember-field');
            const submitBtn = document.getElementById('submit-btn');
            const authLinks = document.getElementById('auth-links');
            const unifiedForm = document.getElementById('unified-form');

            if (loginTypeSelect) {
                loginTypeSelect.addEventListener('change', function() {
                    const loginType = this.value;

                    // Hide all fields initially
                    if (gmailField) gmailField.style.display = 'none';
                    if (usernameField) usernameField.style.display = 'none';
                    if (passwordField) passwordField.style.display = 'none';
                    if (rememberField) rememberField.style.display = 'none';
                    if (authLinks) authLinks.style.display = 'none';

                    // Clear required attributes
                    const gmailInput = document.getElementById('gmail_account');
                    const usernameInput = document.getElementById('username');
                    const passwordInput = document.getElementById('password');

                    if (gmailInput) gmailInput.required = false;
                    if (usernameInput) usernameInput.required = false;
                    if (passwordInput) passwordInput.required = false;

                    if (loginType) {
                        showFieldsForLoginType(loginType);
                        if (submitBtn) submitBtn.disabled = false;
                    } else {
                        if (submitBtn) submitBtn.disabled = true;
                    }
                });
            }

            function showFieldsForLoginType(loginType) {
                const gmailInput = document.getElementById('gmail_account');
                const usernameInput = document.getElementById('username');
                const passwordInput = document.getElementById('password');

                switch(loginType) {
                    case 'user':
                        if (gmailField) gmailField.style.display = 'block';
                        if (passwordField) passwordField.style.display = 'block';
                        if (rememberField) rememberField.style.display = 'block';
                        if (authLinks) authLinks.style.display = 'block';
                        if (gmailInput) gmailInput.required = true;
                        if (passwordInput) passwordInput.required = true;
                        if (submitBtn) submitBtn.innerHTML = '<i class="fab fa-microsoft"></i> Login with MS365';
                        if (unifiedForm) unifiedForm.action = '{{ route("ms365.oauth.redirect") }}';
                        break;

                    case 'superadmin':
                        if (usernameField) usernameField.style.display = 'block';
                        if (passwordField) passwordField.style.display = 'block';
                        if (usernameInput) usernameInput.required = true;
                        if (passwordInput) passwordInput.required = true;
                        if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-crown"></i> Login as Super Admin';
                        if (unifiedForm) unifiedForm.action = '{{ route("superadmin.login") }}';
                        break;

                    case 'department-admin':
                        if (usernameField) usernameField.style.display = 'block';
                        if (passwordField) passwordField.style.display = 'block';
                        if (usernameInput) usernameInput.required = true;
                        if (passwordInput) passwordInput.required = true;
                        if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-building"></i> Login as Department Admin';
                        if (unifiedForm) unifiedForm.action = '{{ url("department-admin/login") }}';
                        break;

                    case 'office-admin':
                        if (usernameField) usernameField.style.display = 'block';
                        if (passwordField) passwordField.style.display = 'block';
                        if (usernameInput) usernameInput.required = true;
                        if (passwordInput) passwordInput.required = true;
                        if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-clipboard-list"></i> Login as Office Admin';
                        if (unifiedForm) unifiedForm.action = '{{ route("office-admin.login") }}';
                        break;
                }
            }
        });

        // Password toggle functionality
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-eye');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
