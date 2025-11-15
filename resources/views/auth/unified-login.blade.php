<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - MCC News Aggregator</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/mcc_logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/mcc_logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/mcc_logo.png') }}">
    
    <!-- reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
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
            position: relative;
            z-index: 10;
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
            
            .btn:hover {
                background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
                box-shadow: none;
            }
            
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

        .auth-header h2 {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
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
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease, visibility 0.3s ease;
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

        .login-type-select {
            position: relative;
        }

        .login-type-select select {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            background-color: var(--gray-50);
            appearance: none;
            transition: var(--transition);
            color: var(--gray-800);
            font-weight: 500;
        }

        .login-type-select::before {
            content: '\f0d7';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-600);
            pointer-events: none;
        }

        .login-type-select::after {
            content: '\f0c0';
            font-family: 'Font Awesome 6 Free', 'FontAwesome', sans-serif;
            font-weight: 900;
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
            z-index: 1;
            font-size: 1rem;
            display: inline-block;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
        }

        .login-type-select select:focus {
            outline: none;
            border-color: var(--secondary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
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
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
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
        }

        .password-toggle:hover {
            color: var(--secondary);
            background-color: var(--gray-100);
        }
        
        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .checkbox-label input {
            margin-right: 0.5rem;
            width: 16px;
            height: 16px;
            accent-color: var(--secondary);
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
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            min-height: 44px;
            touch-action: manipulation;
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
        }

        .btn:active {
            transform: translateY(1px) scale(0.98);
        }
        .btn i {
            margin-right: 0.5rem;
        }

        .btn:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn:disabled:hover {
            transform: none;
        }

        .btn.locked {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            cursor: not-allowed;
        }

        .btn.locked:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: none;
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

        .lockout-message {
            background: rgba(220, 38, 38, 0.1);
            border: 2px solid rgba(220, 38, 38, 0.3);
            color: #dc2626;
            animation: lockoutPulse 2s ease-in-out infinite;
            font-weight: 600;
        }

        .lockout-message::before {
            content: none;
        }

        .lockout-message i {
            margin-right: 0.5rem;
            color: #dc2626;
            animation: lockIconShake 0.5s ease-in-out;
        }

        @keyframes lockoutPulse {
            0%, 100% { border-color: rgba(220, 38, 38, 0.3); }
            50% { border-color: rgba(220, 38, 38, 0.6); }
        }

        @keyframes lockIconShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-2px); }
            75% { transform: translateX(2px); }
        }

        .form-disabled {
            opacity: 0.6;
            pointer-events: none;
            filter: grayscale(50%);
            transition: all 0.3s ease;
            position: relative;
        }

        .form-disabled::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.3);
            z-index: 10;
            cursor: not-allowed;
        }

        .form-disabled input,
        .form-disabled select,
        .form-disabled button,
        .form-disabled textarea {
            pointer-events: none !important;
            cursor: not-allowed !important;
            background-color: #f5f5f5 !important;
            color: #999 !important;
        }

        .form-disabled button {
            background: #ccc !important;
            border-color: #ccc !important;
        }

        .lockout-countdown {
            font-weight: 700;
            color: #dc2626;
            font-size: 1.1em;
            text-align: center;
            margin-top: 8px;
            padding: 8px;
            background: rgba(220, 38, 38, 0.05);
            border-radius: 6px;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }
        
        #countdown-timer {
            font-family: 'Courier New', monospace;
            font-size: 1.2em;
            color: #b91c1c;
            font-weight: 800;
        }

        .attempts-warning {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.3);
            animation: pulse 2s infinite;
        }

        .attempts-warning::before {
            content: none;
        }

        .attempts-warning i {
            margin-right: 0.5rem;
            color: var(--warning);
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Authenticated Accounts Section */
        .authenticated-accounts-section {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .accounts-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .accounts-title i {
            margin-right: 0.5rem;
            color: var(--secondary);
        }

        .accounts-list {
            space-y: 0.75rem;
        }

        .account-item {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            transition: var(--transition);
        }

        .account-item:hover {
            border-color: var(--secondary-light);
            box-shadow: var(--shadow-sm);
        }

        .account-info {
            flex: 1;
        }

        .account-type {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
        }

        .account-type i {
            margin-right: 0.5rem;
            width: 16px;
        }

        .account-details {
            display: flex;
            flex-direction: column;
        }

        .account-name {
            font-weight: 500;
            color: var(--gray-800);
            font-size: 0.9375rem;
        }

        .account-email {
            font-size: 0.8125rem;
            color: var(--gray-600);
        }

        .account-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-switch, .btn-remove {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 0.875rem;
        }

        .btn-switch {
            background: var(--secondary);
            color: white;
        }

        .btn-switch:hover {
            background: var(--secondary-light);
            transform: translateY(-1px);
        }

        .btn-remove {
            background: var(--danger);
            color: white;
        }

        .btn-remove:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .add-account-note {
            margin-top: 1rem;
            padding: 0.75rem;
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            color: var(--secondary);
            display: flex;
            align-items: center;
        }

        .add-account-note i {
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

        .warning-message {
            background: rgba(245, 158, 11, 0.05);
            color: var(--warning);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            font-size: 0.875rem;
            border: 1px solid rgba(245, 158, 11, 0.2);
            display: flex;
            align-items: center;
        }

        .warning-message::before {
            content: '\f071';
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

        .forgot-password {
            text-align: center;
            margin: 1rem 0;
        }

        .forgot-password a {
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .forgot-password a:hover {
            color: var(--secondary);
        }

            /* reCAPTCHA Styling removed */

        .text-muted {
            color: var(--gray-600);
            font-size: 0.75rem;
            margin-top: 0.5rem;
            text-align: center;
            line-height: 1.4;
        }

        .text-muted a {
            color: var(--secondary);
            text-decoration: none;
        }

        .text-muted a:hover {
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
                margin: 0;
                border-radius: 6px;
            }
            
            .logo {
                height: 70px;
                max-width: 80px;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
            }
            
            .auth-header h2 {
                font-size: 1rem;
            }
            
            .auth-content {
                padding: 0.75rem;
            }
            
            .form-control,
            .login-type-select select,
            .btn {
                min-height: 48px;
                font-size: 0.875rem;
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
                margin: 0;
                border-radius: var(--radius-sm);
                box-shadow: var(--shadow);
            }
            
            .logo-container {
                padding: 1.5rem 0 1rem;
            }
            
            .logo {
                height: 80px;
                max-width: 90px;
                margin-top: 8px;
            }
            
            
            .auth-header {
                padding: 0 1rem 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.75rem;
                margin-bottom: 0.25rem;
            }
            
            .auth-header h2 {
                font-size: 1.125rem;
                margin-bottom: 0.5rem;
            }
            
            .auth-header p {
                font-size: 0.875rem;
            }
            
            .auth-content {
                padding: 1rem;
            }
            
            .form-group {
                margin-bottom: 1.25rem;
            }
            
            .form-group label {
                font-size: 0.8125rem;
                margin-bottom: 0.375rem;
            }
            
            .form-control,
            .login-type-select select {
                padding: 0.75rem 0.875rem;
                font-size: 0.9375rem;
                min-height: 46px; /* Enhanced touch-friendly minimum */
                touch-action: manipulation;
            }
            
            .login-type-select select {
                padding-left: 2.75rem;
                padding-right: 2.75rem;
            }
            
            .btn {
                padding: 0.875rem 1rem;
                font-size: 0.9375rem;
                min-height: 46px; /* Enhanced touch-friendly minimum */
                touch-action: manipulation;
            }
            
            .btn:active {
                transform: translateY(1px) scale(0.96);
            }
            
            .password-toggle {
                right: 0.625rem;
                padding: 0.375rem;
                min-width: 46px;
                min-height: 46px;
            }
            
            .checkbox-label {
                font-size: 0.8125rem;
            }
            
            .checkbox-label input {
                width: 18px;
                height: 18px;
            }
            
            .auth-links a {
                font-size: 0.8125rem;
                padding: 0.5rem;
            }
            
            .forgot-password a {
                font-size: 0.8125rem;
            }
            
            .error-message,
            .success-message,
            .warning-message {
                padding: 0.625rem 0.875rem;
                font-size: 0.8125rem;
            }
            
            /* Mobile responsive for authenticated accounts */
            .authenticated-accounts-section {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .accounts-title {
                font-size: 0.9375rem;
                margin-bottom: 0.75rem;
            }
            
            .account-item {
                padding: 0.75rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .account-info {
                width: 100%;
            }
            
            .account-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .btn-switch, .btn-remove {
                width: 44px;
                height: 44px;
                min-height: 46px;
                touch-action: manipulation;
            }
            
            .btn-switch:active, .btn-remove:active {
                transform: translateY(-1px) scale(0.95);
            }
            
            .add-account-note {
                padding: 0.625rem;
                font-size: 0.8125rem;
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
            
            .logo {
                height: 90px;
                max-width: 100px;
                margin-top: 9px;
            }
            
            
            .auth-header {
                padding: 0 1.25rem 1.75rem;
            }
            
            .auth-header h1 {
                font-size: 2rem;
            }
            
            .auth-header h2 {
                font-size: 1.25rem;
            }
            
            .auth-content {
                padding: 1.25rem;
            }
            
            .form-control,
            .login-type-select select {
                min-height: 48px;
                touch-action: manipulation;
            }
            
            .btn {
                min-height: 48px;
                touch-action: manipulation;
            }
            
            .btn:active {
                transform: translateY(1px) scale(0.97);
            }
            
            .password-toggle {
                min-width: 48px;
                min-height: 48px;
            }
        }
        
        /* Medium devices (tablets, 577px to 768px) */
        @media (min-width: 577px) and (max-width: 768px) {
            .auth-container {
                max-width: 90%;
            }
            
            .auth-header {
                padding: 0 1.5rem 2rem;
            }
            
            .auth-content {
                padding: 1.75rem;
            }
        }
        
        /* Large devices (desktops, 769px and up) */
        @media (min-width: 769px) {
            .auth-container {
                max-width: 480px;
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
                height: 45px;
                max-width: 55px;
                margin-top: 5px;
            }
            
            
            .auth-header {
                padding: 0 1.5rem 1rem;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
            }
            
            .auth-header h2 {
                font-size: 1.125rem;
                margin-bottom: 0.25rem;
            }
            
            .auth-header p {
                font-size: 0.875rem;
            }
            
            .auth-content {
                padding: 1rem 1.5rem;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
        }
        
        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .form-control,
            .login-type-select select,
            .btn {
                border-width: 0.5px;
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
            .login-type-select select:focus,
            .btn:focus {
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
                outline: 2px solid transparent;
            }
            
            .password-toggle:focus {
                outline: 2px solid var(--secondary);
                outline-offset: 2px;
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            }
            
            /* Enhanced touch feedback for all interactive elements */
            .form-control:active,
            .login-type-select select:active {
                transform: scale(0.99);
            }
            
            .checkbox-label:active {
                transform: scale(0.98);
            }
            
            .auth-links a:active {
                transform: scale(0.97);
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
            
            /* Better spacing for small screens */
            .form-group {
                margin-bottom: 1.25rem;
            }
            
            /* Enhanced error message styling for mobile */
            .error-message,
            .success-message,
            .warning-message {
                border-radius: var(--radius-sm);
                line-height: 1.4;
            }
            
            /* Improved checkbox styling for touch */
            .checkbox-label input {
                min-width: 20px;
                min-height: 20px;
                margin-right: 0.75rem;
            }
        }
        
        /* OTP Resend Button Styling */
        #otp-resend-btn {
            transition: all 0.3s ease;
        }
        
        #otp-resend-btn:hover:not(:disabled) {
            background: #2563eb !important;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
        }
        
        #otp-resend-btn:active:not(:disabled) {
            transform: translateY(0) scale(0.98);
        }
        
        #otp-resend-btn:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
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
            <h1>MCC-NAC</h1>
          
            <p>Select your login type and enter your credentials</p>
        </div>

        <div class="auth-content">
            @if(session('success'))
                <div class="success-message" id="swal-success" data-message="{{ session('success') }}" style="display:none"></div>
            @endif

            @if(session('warning'))
                <div class="warning-message">
                    {{ session('warning') }}
                </div>
            @endif


            @if($errors->has('account_lockout'))
                <div class="error-message lockout-message" id="lockout-message">
                    <i class="fas fa-lock"></i>
                    <span id="lockout-text">{{ $errors->first('account_lockout') }}</span>
                    <div class="lockout-countdown" id="lockout-countdown" style="margin-top: 8px; display: block;">
                        <strong>Time remaining: <span id="countdown-timer">Loading...</span></strong>
                    </div>
                </div>
            @elseif($errors->any() && !$errors->has('ms365_account') && !$errors->has('gmail_account') && !$errors->has('username') && !$errors->has('password') && !$errors->has('account_lockout'))
                <div class="error-message">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if((session('attempts_left') && session('attempts_left') > 0 && session('attempts_left') < 3) || (isset($attemptsLeft) && $attemptsLeft > 0 && $attemptsLeft < 3))
                <div class="warning-message attempts-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Warning: {{ session('attempts_left') ?? $attemptsLeft }} login attempt(s) remaining before temporary lockout.
                </div>
            @endif

            

            <!-- Unified Login Form -->
            <div class="unified-login-form" style="opacity: 1; visibility: visible;">
                <form method="POST" action="{{ url('/login') }}" id="unified-form" @if($errors->has('account_lockout')) class="form-disabled" @endif>
                    @csrf

                    <!-- Login Type Selector -->
                    <div class="form-group login-type-selector">
                        <label for="login_type">
                            <i class="fas fa-users-cog"></i>
                            Login Type
                        </label>
                        <div class="login-type-select">
                            <select name="login_type" id="login_type" class="form-control" required>
                                <option value="ms365" {{ old('login_type', 'ms365') == 'ms365' ? 'selected' : '' }}>
                                    Student/Faculty (MS365)
                                </option>
                                <option value="superadmin" {{ old('login_type') == 'superadmin' ? 'selected' : '' }}>
                                    Super Admin
                                </option>
                                <option value="department-admin" {{ old('login_type') == 'department-admin' ? 'selected' : '' }}>
                                    Department Admin
                                </option>
                                <option value="office-admin" {{ old('login_type') == 'office-admin' ? 'selected' : '' }}>
                                    Office Admin
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Gmail Account Field (for students/faculty) -->
                    <div class="form-group" id="gmail-field" style="display: none;">
                        <label for="gmail_account">
                            <i class="fab fa-google"></i>
                            Gmail Account
                        </label>
                        <input type="email"
                               id="gmail_account"
                               name="gmail_account"
                               class="form-control @error('gmail_account') error @enderror"
                               value="{{ old('gmail_account') }}"
                               placeholder="example@gmail.com"
                               pattern="[a-zA-Z0-9._%+-]+@gmail\.com"
                               title="Please enter a valid Gmail address">
                        @error('gmail_account')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- MS365 Account Field -->
                    <div class="form-group" id="ms365-field" style="display: block;">
                        <label for="ms365_account">
                            <i class="fab fa-microsoft"></i>
                            MS365 Account
                        </label>
                        <input type="email"
                               id="ms365_account"
                               name="ms365_account"
                               class="form-control @error('ms365_account') error @enderror"
                               value="{{ old('ms365_account') ? e(old('ms365_account')) : '' }}"
                               placeholder="example@mcc-nac.edu.ph"
                               pattern="[a-zA-Z0-9._%+-]+@.*\.edu\.ph"
                               title="Please enter a valid .edu.ph email address"
                               maxlength="100"
                               minlength="10"
                               data-security-check="true"
                               autocomplete="email"
                               spellcheck="false">
                        @error('ms365_account')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Username Field (for admins) -->
                    <div class="form-group" id="username-field" style="display: none;">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            Username
                        </label>
                        <input type="text"
                               id="username"
                               name="username"
                               class="form-control @error('username') error @enderror"
                               value="{{ old('username') ? e(old('username')) : '' }}"
                               placeholder="Enter your username"
                               maxlength="50"
                               minlength="3"
                               pattern="[a-zA-Z0-9_-]+"
                               title="Username can only contain letters, numbers, underscores, and hyphens"
                               data-security-check="true"
                               autocomplete="username"
                               spellcheck="false">
                        @error('username')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-group" id="password-field" style="display: block;">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-input-container">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control @error('password') error @enderror"
                                   placeholder="Enter your password"
                                   maxlength="255"
                                   minlength="8"
                                   data-security-check="true"
                                   autocomplete="current-password"
                                   spellcheck="false">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Forgot Password (MS365) -->
                    <div class="forgot-password" id="forgot-password" style="display:block;">
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    </div>

                    <!-- Location Permission Checkbox (Only for Admin Types) -->
                    <div class="form-group" id="location-permission-field" style="display: none;">
                        <div class="checkbox-container" style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 1rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: var(--radius-sm); margin-top: 0.5rem;">
                            <input type="checkbox" 
                                   id="location_permission" 
                                   name="location_permission" 
                                   value="1" {{ old('location_permission') ? 'checked' : '' }}
                                   style="margin-top: 0.25rem; width: 18px; height: 18px; cursor: pointer; accent-color: var(--secondary); flex-shrink: 0;">
                            <label for="location_permission" style="margin: 0; font-size: 0.875rem; color: var(--gray-700); cursor: pointer; line-height: 1.5; flex: 1;">
                                <i class="fas fa-map-marker-alt" style="color: var(--secondary); margin-right: 0.5rem;"></i>
                                <strong>Allow location tracking</strong> for security monitoring. This is required for admin login. Your IP and approximate location may be recorded for security and auditing purposes.
                            </label>
                        </div>
                        @error('location_permission')
                            <div class="error-message" style="margin-top: 8px;">{{ $message }}</div>
                        @enderror
                    </div>

            <!-- reCAPTCHA removed -->

                    

                    <!-- Hidden reCAPTCHA token field -->
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn" id="submit-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Login with MS365
                    </button>
                </form>

                <!-- Auth Links -->
                <div class="auth-links" id="auth-links" style="display: block;">
                    <a href="{{ route('ms365.signup') }}">
                        <i class="fas fa-user-plus"></i>
                        Don't have an MS365 account? Sign up
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Dynamic OTP Modal for All Login Types -->
    <div id="otp-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #fff; width: 100%; max-width: 420px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); overflow: hidden; position: relative;">
            <!-- Close button - Top Right -->
            <button id="otp-close" type="button" aria-label="Close" style="position: absolute; top: 16px; right: 16px; background: rgba(0, 0, 0, 0.05); border: 0; width: 32px; height: 32px; border-radius: 50%; font-size: 20px; font-weight: 600; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; color: #6b7280; transition: all 0.2s ease; line-height: 1;" onmouseover="this.style.background='rgba(0,0,0,0.1)'; this.style.color='#111827';" onmouseout="this.style.background='rgba(0,0,0,0.05)'; this.style.color='#6b7280';">×</button>
            
            <!-- Logo at the top center -->
            <div style="display: flex; justify-content: center; align-items: center; padding: 2rem 0 1.5rem; background: white; position: relative;">
                <img src="{{ asset('images/mcclogo.png') }}" alt="MCC Logo" style="height: 80px; width: auto; max-width: 90px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1)); position: relative; z-index: 2; margin-top: 10px; transition: all 0.3s ease; image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; user-select: none;">
            </div>
            <div style="padding: 0 20px 16px; border-bottom: 1px solid #e5e7eb; text-align: center;">
                <h3 id="otp-modal-title" style="margin: 0; color: #2563eb;">OTP Verification</h3>
            </div>
            <div style="padding: 20px;">
                @if(session('status'))
                    <div style="background:#ecfeff;border:1px solid #a5f3fc;color:#0e7490;padding:10px 12px;border-radius:8px;margin-bottom:12px;">{{ session('status') }}</div>
                @endif
                <form method="POST" action="{{ route('otp.verify') }}" id="otp-form">
                    @csrf
                    <input type="hidden" id="otp-login-type" name="login_type" value="">
                    <div class="form-group">
                        <label for="otp" style="display:block;font-size:14px;color:#374151;margin-bottom:6px;">Enter 6-digit code</label>
                        <input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="••••••" required class="form-control" style="text-align:center;letter-spacing:6px;font-size:20px;">
                    </div>
                    @error('otp')
                        <div class="error-message" style="margin-top:8px;">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn" style="margin-top:8px;">
                        <i class="fas fa-shield-alt"></i>
                        Verify and Continue
                    </button>
                </form>
                
                <!-- Resend OTP Form -->
                <form method="POST" action="{{ route('otp.resend') }}" id="otp-resend-form" style="margin-top: 12px;">
                    @csrf
                    <input type="hidden" id="otp-resend-login-type" name="login_type" value="">
                    <button type="submit" id="otp-resend-btn" class="btn" style="width: 100%; background: white; color: #2563eb; border: 2px solid #2563eb;">
                        <i class="fas fa-paper-plane"></i> Resend Code
                    </button>
                </form>
                
                <div class="text-muted" style="margin-top:8px; text-align: center; font-size: 13px;">Code expires in 10 minutes. Max 5 attempts. Check your Outlook app inbox.</div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('swal-success');
            if (el) {
                var msg = el.getAttribute('data-message') || 'Success';
                
                // Determine appropriate title based on message content
                var title = 'Success';
                if (msg.includes('Registration completed') || msg.includes('registered')) {
                    title = 'Registration Complete';
                } else if (msg.includes('logged out')) {
                    title = 'Logged Out';
                } else if (msg.includes('login') || msg.includes('Login')) {
                    title = 'Login Success';
                }
                
                Swal.fire({
                    icon: 'success',
                    title: title,
                    text: msg,
                    confirmButtonColor: '#111827'
                });
            }
        });

        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId + '-eye');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
    <script>
        // Security validation patterns
        const DANGEROUS_PATTERNS = [
            // TypeScript/JavaScript patterns
            /\bfunction\s*\(/i,
            /\bvar\s+/i,
            /\blet\s+/i,
            /\bconst\s+/i,
            /\bclass\s+/i,
            /\binterface\s+/i,
            /\btype\s+/i,
            /\bnamespace\s+/i,
            /\bimport\s+/i,
            /\bexport\s+/i,
            /\brequire\s*\(/i,
            /\bconsole\./i,
            /\balert\s*\(/i,
            /\beval\s*\(/i,
            /\bsetTimeout\s*\(/i,
            /\bsetInterval\s*\(/i,
            // SQL injection patterns
            /\bunion\s+select/i,
            /\bselect\s+.*\bfrom\s+/i,
            /\binsert\s+into/i,
            /\bupdate\s+.*\bset\s+/i,
            /\bdelete\s+from/i,
            /\bdrop\s+table/i,
            /\balter\s+table/i,
            /\bcreate\s+table/i,
            /\btruncate\s+table/i,
            /\bexec\s*\(/i,
            /\bexecute\s*\(/i,
            // Script tags and HTML
            /<script[^>]*>/i,
            /<\/script>/i,
            /<iframe[^>]*>/i,
            /<object[^>]*>/i,
            /<embed[^>]*>/i,
            /<link[^>]*>/i,
            /<meta[^>]*>/i,
            // PHP patterns
            /<\?php/i,
            /<\?=/i,
            /\bphp:/i,
            // Command injection
            /\bsystem\s*\(/i,
            /\bexec\s*\(/i,
            /\bshell_exec\s*\(/i,
            /\bpassthru\s*\(/i,
            // Other dangerous patterns
            /javascript:/i,
            /vbscript:/i,
            /data:text\/html/i,
            /\bon\w+\s*=/i, // event handlers like onclick=
            /\\\x[0-9a-f]{2}/i, // hex encoding
            /\\\u[0-9a-f]{4}/i, // unicode encoding
        ];

        function validateInput(value, fieldName) {
            if (!value) return { valid: true };

            // Check for dangerous patterns
            for (let pattern of DANGEROUS_PATTERNS) {
                if (pattern.test(value)) {
                    return {
                        valid: false,
                        message: `Invalid characters detected in ${fieldName}. Please use only standard alphanumeric characters.`
                    };
                }
            }

            // Additional length checks
            if (value.length > 255) {
                return {
                    valid: false,
                    message: `${fieldName} is too long. Maximum 255 characters allowed.`
                };
            }

            // Check for excessive special characters (potential obfuscation)
            const specialCharCount = (value.match(/[^a-zA-Z0-9@._-\s]/g) || []).length;
            if (specialCharCount > value.length * 0.3) {
                return {
                    valid: false,
                    message: `${fieldName} contains too many special characters.`
                };
            }

            return { valid: true };
        }

        function showSecurityError(message) {
            // Remove existing security error
            const existingError = document.querySelector('.security-error-message');
            if (existingError) {
                existingError.remove();
            }

            // Create new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message security-error-message';
            errorDiv.textContent = message;
            
            // Insert at the top of the form
            const form = document.getElementById('unified-form');
            form.insertBefore(errorDiv, form.firstChild);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const loginTypeSelect = document.getElementById('login_type');
            const gmailField = document.getElementById('gmail-field');
            const ms365Field = document.getElementById('ms365-field');
            const usernameField = document.getElementById('username-field');
            const passwordField = document.getElementById('password-field');
            const submitBtn = document.getElementById('submit-btn');
            const authLinks = document.getElementById('auth-links');
            const forgotPassword = document.getElementById('forgot-password');
            
            // Check if there's an account lockout message
            const hasAccountLockout = document.querySelector('.lockout-message') !== null;
            
            // Initialize lockout countdown if there's an account lockout
            if (hasAccountLockout) {
                initializeLockoutCountdown();
            }

            // Add real-time validation to security-checked inputs
            document.querySelectorAll('[data-security-check="true"]').forEach(input => {
                input.addEventListener('input', function() {
                    const validation = validateInput(this.value, this.name);
                    if (!validation.valid) {
                        this.classList.add('error');
                        showSecurityError(validation.message);
                        this.value = ''; // Clear the dangerous input
                    } else {
                        this.classList.remove('error');
                    }
                });

                // Prevent paste of dangerous content
                input.addEventListener('paste', function(e) {
                    setTimeout(() => {
                        const validation = validateInput(this.value, this.name);
                        if (!validation.valid) {
                            this.classList.add('error');
                            showSecurityError(validation.message);
                            this.value = ''; // Clear the dangerous input
                            e.preventDefault();
                        }
                    }, 10);
                });
            });

            // Enhanced form submission validation + reCAPTCHA v3 execution
            document.getElementById('unified-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                let hasErrors = false;

                // Validate all form inputs
                for (let [key, value] of formData.entries()) {
                    if (key !== '_token' && value) {
                        const validation = validateInput(value, key);
                        if (!validation.valid) {
                            showSecurityError(validation.message);
                            hasErrors = true;
                            break;
                        }
                    }
                }

                if (hasErrors) {
                    return false;
                }

                // Enforce location permission for admin login types before proceeding
                const selectedLoginType = document.getElementById('login_type').value;
                if (["superadmin", "department-admin", "office-admin"].includes(selectedLoginType)) {
                    const locCheckbox = document.getElementById('location_permission');
                    if (!locCheckbox || !locCheckbox.checked) {
                        showSecurityError('You must allow location tracking to continue with admin login.');
                        if (locCheckbox) {
                            locCheckbox.classList.add('error');
                            try { locCheckbox.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (err) {}
                            try { locCheckbox.focus(); } catch (err) {}
                        }
                        return false;
                    }
                }

                // If browser supports constraint validation API, ensure form is valid
                if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                    return false;
                }

                // Execute reCAPTCHA v3 before submitting
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
                        // Add the token to the hidden input field
                        document.getElementById('recaptcha_token').value = token;
                        // Submit the form
                        form.submit();
                    }).catch(function(error) {
                        console.error('reCAPTCHA error:', error);
                        showSecurityError('Security verification failed. Please try again.');
                    });
                });
            });

            function toggleFields() {
                const selectedType = loginTypeSelect.value;
                console.log('Selected login type:', selectedType); // Debug log

                // Hide all fields initially
                gmailField.style.display = 'none';
                ms365Field.style.display = 'none';
                usernameField.style.display = 'none';
                passwordField.style.display = 'none';
                authLinks.style.display = 'none';
                forgotPassword.style.display = 'none';

                // Clear fields and error classes when switching types
                function clearGroup(groupIds) {
                    groupIds.forEach(function (id) {
                        var el = document.getElementById(id);
                        if (!el) return;
                        var inputs = el.querySelectorAll('input');
                        inputs.forEach(function (i) {
                            if (i.type === 'checkbox') { 
                                i.checked = false; 
                            } else { 
                                i.value = ''; 
                            }
                            i.classList.remove('error');
                        });
                    });
                }

                // Set required attribute for fields
                function setRequired(elId, required) {
                    var el = document.getElementById(elId);
                    if (el) { 
                        el.required = !!required; 
                    }
                }

                // Remove all required flags initially
                setRequired('gmail_account', false);
                setRequired('ms365_account', false);
                setRequired('username', false);
                setRequired('password', false);

                // Show fields based on selection
                // Get location permission field
                const locationPermissionField = document.getElementById('location-permission-field');
                
                if (selectedType === 'ms365') {
                    // Clear admin fields
                    clearGroup(['username-field']);
                    
                    // Hide location permission checkbox for students/faculty
                    if (locationPermissionField) locationPermissionField.style.display = 'none';
                    // Ensure not required for non-admin
                    setRequired('location_permission', false);
                    // Uncheck if previously checked
                    const locCb1 = document.getElementById('location_permission');
                    if (locCb1) { locCb1.checked = false; }
                    
                    // Show student/faculty fields
                    ms365Field.style.display = 'block';
                    passwordField.style.display = 'block';
                    authLinks.style.display = 'block';
                    forgotPassword.style.display = 'block';
                    
                    console.log('Forgot password should now be visible'); // Debug log
                    
                    // Set required fields
                    setRequired('ms365_account', true);
                    setRequired('password', true);
                    
                    // Update button text
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login with MS365';
                    submitBtn.disabled = false;
                    
                } else if (selectedType === 'superadmin') {
                    // Clear student/gmail fields
                    clearGroup(['gmail-field', 'username-field']);
                    
                    // Show location permission checkbox for superadmin
                    if (locationPermissionField) locationPermissionField.style.display = 'block';
                    setRequired('location_permission', true);
                    
                    // Show MS365 fields for superadmin
                    ms365Field.style.display = 'block';
                    passwordField.style.display = 'block';
                    
                    // Set required fields
                    setRequired('ms365_account', true);
                    setRequired('password', true);
                    
                    // Update button text
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login as Super Admin';
                    submitBtn.disabled = false;
                    
                } else if (selectedType === 'department-admin' || selectedType === 'office-admin') {
                    // Clear other fields
                    clearGroup(['username-field', 'gmail-field']);
                    
                    // Show location permission checkbox for department and office admins
                    if (locationPermissionField) locationPermissionField.style.display = 'block';
                    setRequired('location_permission', true);
                    
                    // Show MS365 fields for department and office admins
                    ms365Field.style.display = 'block';
                    passwordField.style.display = 'block';
                    
                    // Set required fields
                    setRequired('ms365_account', true);
                    setRequired('password', true);
                    
                    // Update button text
                    const adminType = selectedType === 'department-admin' ? 'Department Admin' : 'Office Admin';
                    submitBtn.innerHTML = `<i class="fas fa-sign-in-alt"></i> Login as ${adminType}`;
                    submitBtn.disabled = false;
                    
                } else {
                    // Hide location permission checkbox for other types
                    if (locationPermissionField) locationPermissionField.style.display = 'none';
                    setRequired('location_permission', false);
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Select Login Type';
                    submitBtn.disabled = true;
                }
            }

            // Initial call to set the correct state
            console.log('Initializing form fields...'); // Debug log
            // Respect current selected value (from old input or server), do not override
            toggleFields();

            // OTP modal behavior
            const otpModal = document.getElementById('otp-modal');
            const otpClose = document.getElementById('otp-close');
            const otpModalTitle = document.getElementById('otp-modal-title');
            const otpLoginTypeInput = document.getElementById('otp-login-type');
            
            if (otpClose) {
                otpClose.addEventListener('click', function() {
                    if (otpModal) otpModal.style.display = 'none';
                });
            }

            // Server flag to show OTP modal for any login type
            const shouldShowOtp = {{ (session('show_otp_modal') || session('show_superadmin_otp') || ($errors && $errors->has('otp'))) ? 'true' : 'false' }};
            const otpLoginType = '{{ session('otp_login_type') ?? 'superadmin' }}';
            
            if (shouldShowOtp && otpModal) {
                // Map login types to display names
                const loginTypeDisplayMap = {
                    'ms365': 'Student/Faculty',
                    'user': 'Student/Faculty',
                    'department-admin': 'Department Admin',
                    'office-admin': 'Office Admin',
                    'superadmin': 'Super Admin'
                };
                
                // Update modal title based on login type
                if (otpModalTitle) {
                    otpModalTitle.textContent = (loginTypeDisplayMap[otpLoginType] || 'User') + ' OTP Verification';
                }
                
                // Set hidden login_type field
                if (otpLoginTypeInput) {
                    otpLoginTypeInput.value = otpLoginType;
                }
                
                // Preselect appropriate fields based on login type
                if (loginTypeSelect) {
                    loginTypeSelect.value = otpLoginType;
                    toggleFields();
                }
                
                otpModal.style.display = 'flex';
                
                // Focus OTP input
                const otpInput = document.getElementById('otp');
                if (otpInput) {
                    otpInput.focus();
                    otpInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                    });
                }
                
                // Setup resend button functionality
                const resendForm = document.getElementById('otp-resend-form');
                const resendBtn = document.getElementById('otp-resend-btn');
                const resendLoginTypeInput = document.getElementById('otp-resend-login-type');
                
                if (resendLoginTypeInput) {
                    resendLoginTypeInput.value = otpLoginType;
                }
                
                if (resendForm && resendBtn) {
                    let cooldownTime = 0;
                    let cooldownInterval = null;
                    
                    // Check if there's a cooldown in sessionStorage
                    const storedCooldown = sessionStorage.getItem('otp_resend_cooldown_' + otpLoginType);
                    if (storedCooldown) {
                        const remainingTime = parseInt(storedCooldown) - Date.now();
                        if (remainingTime > 0) {
                            startCooldown(Math.ceil(remainingTime / 1000));
                        }
                    }
                    
                    resendForm.addEventListener('submit', function(e) {
                        if (resendBtn.disabled) {
                            e.preventDefault();
                            return;
                        }
                        
                        // Start 60-second cooldown after submitting
                        setTimeout(function() {
                            startCooldown(60);
                        }, 100);
                    });
                    
                    function startCooldown(seconds) {
                        cooldownTime = seconds;
                        resendBtn.disabled = true;
                        resendBtn.style.opacity = '0.5';
                        resendBtn.style.cursor = 'not-allowed';
                        
                        // Store cooldown end time
                        sessionStorage.setItem('otp_resend_cooldown_' + otpLoginType, Date.now() + (seconds * 1000));
                        
                        updateResendButton();
                        
                        cooldownInterval = setInterval(function() {
                            cooldownTime--;
                            if (cooldownTime <= 0) {
                                clearInterval(cooldownInterval);
                                resendBtn.disabled = false;
                                resendBtn.style.opacity = '1';
                                resendBtn.style.cursor = 'pointer';
                                resendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Resend Code';
                                sessionStorage.removeItem('otp_resend_cooldown_' + otpLoginType);
                            } else {
                                updateResendButton();
                            }
                        }, 1000);
                    }
                    
                    function updateResendButton() {
                        resendBtn.innerHTML = '<i class="fas fa-clock"></i> Resend in ' + cooldownTime + 's';
                    }
                }
            }

            // Add event listener for changes
            loginTypeSelect.addEventListener('change', toggleFields);
            
            // Add real-time account lockout checking
            function checkAccountLockout() {
                const loginType = loginTypeSelect.value;
                let accountValue = '';
                
                switch(loginType) {
                    case 'ms365':
                        accountValue = document.getElementById('ms365_account')?.value || '';
                        break;
                    case 'user':
                        accountValue = document.getElementById('gmail_account')?.value || '';
                        break;
                    case 'superadmin':
                        accountValue = document.getElementById('ms365_account')?.value || '';
                        break;
                    case 'department-admin':
                    case 'office-admin':
                        accountValue = document.getElementById('ms365_account')?.value || '';
                        break;
                }
                
                if (accountValue) {
                    // You can add AJAX call here to check if this specific account is locked
                    // For now, we'll rely on server-side validation
                }
            }
            
            // Add event listeners to account fields
            document.addEventListener('input', function(e) {
                if (['ms365_account', 'gmail_account', 'username'].includes(e.target.name)) {
                    checkAccountLockout();
                }
            });
            
            // Force show forgot password link for MS365 (fallback)
            setTimeout(function() {
                if (loginTypeSelect.value === 'ms365') {
                    const forgotPasswordElement = document.getElementById('forgot-password');
                    if (forgotPasswordElement) {
                        forgotPasswordElement.style.display = 'block';
                        console.log('Forced forgot password to show'); // Debug log
                    }
                }
            }, 100);
        });

        // Enhanced lockout countdown functionality
        function initializeLockoutCountdown() {
            const lockoutMessage = document.querySelector('.lockout-message');
            const lockoutCountdown = document.getElementById('lockout-countdown');
            const countdownTimer = document.getElementById('countdown-timer');
            const lockoutText = document.getElementById('lockout-text');
            const loginForm = document.getElementById('unified-form');
            
            if (!lockoutMessage || !lockoutCountdown || !countdownTimer) {
                console.error('Required lockout elements not found');
                return;
            }
            
            // Disable the form during lockout
            if (loginForm && !loginForm.classList.contains('form-disabled')) {
                loginForm.classList.add('form-disabled');
            }
            
            // Disable all form elements
            const formElements = loginForm.querySelectorAll('input, select, button, textarea');
            formElements.forEach(element => {
                element.disabled = true;
                element.style.cursor = 'not-allowed';
            });
            
            // Get remaining seconds from backend
            let remainingSeconds = {{ session('lockout_seconds', 180) }};
            
            // Fallback if no backend seconds available
            if (!remainingSeconds || remainingSeconds <= 0) {
                remainingSeconds = 180; // Default 3 minutes
            }
            
            // Ensure reasonable bounds (max 5 minutes for safety)
            if (remainingSeconds > 300 || remainingSeconds < 0) {
                console.warn('Invalid seconds value detected:', remainingSeconds, 'defaulting to 180 seconds');
                remainingSeconds = 180;
            }
            
            // Show countdown element
            lockoutCountdown.style.display = 'block';
            
            // Add debug logging
            console.log('Lockout countdown initialized:', {
                remainingSeconds: remainingSeconds,
                backendSeconds: {{ session('lockout_seconds', 0) }},
                currentTime: new Date().toISOString()
            });
            
            // Update countdown immediately
            updateCountdownDisplay(remainingSeconds, countdownTimer, lockoutText);
            
            const countdown = setInterval(() => {
                remainingSeconds--;
                
                // Safety check to prevent infinite countdown
                if (remainingSeconds < -10) {
                    console.warn('Countdown went negative, clearing interval');
                    clearInterval(countdown);
                    return;
                }
                
                if (remainingSeconds <= 0) {
                    clearInterval(countdown);
                    
                    // Re-enable the form
                    if (loginForm) {
                        loginForm.classList.remove('form-disabled');
                        
                        // Re-enable all form elements
                        const formElements = loginForm.querySelectorAll('input, select, button, textarea');
                        formElements.forEach(element => {
                            element.disabled = false;
                            element.style.cursor = '';
                        });
                    }
                    
                    // Hide the lockout message
                    lockoutMessage.style.display = 'none';
                    
                    // Show success message that account is unlocked
                    const successDiv = document.createElement('div');
                    successDiv.className = 'success-message';
                    successDiv.innerHTML = '<i class="fas fa-unlock"></i> Account unlocked! You can now try logging in again.';
                    lockoutMessage.parentNode.insertBefore(successDiv, lockoutMessage);
                    
                    // Remove success message after 5 seconds
                    setTimeout(() => {
                        if (successDiv.parentNode) {
                            successDiv.remove();
                        }
                    }, 5000);
                    
                    // Clear the lockout from session (optional - backend handles expiration)
                    fetch('/clear-current-lockout', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    }).catch(error => {
                        console.log('Lockout clear request failed (this is normal):', error);
                    });
                    
                    return;
                }
                
                // Update countdown display
                updateCountdownDisplay(remainingSeconds, countdownTimer, lockoutText);
                
            }, 1000); // Update every second
        }
        
        // Helper function to update countdown display
        function updateCountdownDisplay(remainingSeconds, countdownTimer, lockoutText) {
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;

            // Format time as MM:SS
            const timeText = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            // Update countdown timer
            if (countdownTimer) {
                countdownTimer.textContent = timeText;
            }

            // Update main lockout text with current minutes
            if (lockoutText) {
                const originalText = lockoutText.textContent;
                const updatedText = originalText.replace(/(\d+(?:\.\d+)?)\s+minutes?/, `${Math.ceil(remainingSeconds / 60)} minute${Math.ceil(remainingSeconds / 60) !== 1 ? 's' : ''}`);
                lockoutText.textContent = updatedText;
            }
        }
    </script>

</body>
</html>
