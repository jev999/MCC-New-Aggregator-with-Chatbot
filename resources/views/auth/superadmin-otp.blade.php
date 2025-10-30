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
    <title>Verify OTP - Super Admin</title>
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

        .container {
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

        /* Logo Container Styles */
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
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            -webkit-user-drag: none;
            user-select: none;
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

        .header {
            background: white;
            color: var(--secondary);
            padding: 0 2rem 2rem;
            text-align: center;
            position: relative;
        }

        .header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 0.95rem;
            color: var(--gray-600);
        }

        .content {
            padding: 0 2rem 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            font-size: 1.25rem;
            letter-spacing: 6px;
            text-align: center;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            touch-action: manipulation;
            -webkit-appearance: none;
            appearance: none;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn {
            width: 100%;
            padding: 0.875rem 1rem;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            min-height: 44px;
            touch-action: manipulation;
            -webkit-appearance: none;
            appearance: none;
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            box-shadow: var(--shadow);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0) scale(0.98);
        }

        .message {
            padding: 0.875rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status {
            background: #ecfeff;
            color: #0e7490;
            border: 1px solid #a5f3fc;
        }

        .note {
            font-size: 0.8125rem;
            color: var(--gray-600);
            text-align: center;
            margin-top: 1rem;
            line-height: 1.5;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .container {
                max-width: 100%;
            }

            .logo {
                height: 85px;
                max-width: 95px;
            }

            .header h2 {
                font-size: 1.5rem;
            }

            .header p {
                font-size: 0.875rem;
            }

            .content {
                padding: 0 1.5rem 1.5rem;
            }

            input[type="text"] {
                font-size: 1.125rem;
                padding: 0.75rem 0.875rem;
            }
        }

        @media (max-width: 480px) {
            .logo {
                height: 75px;
                max-width: 85px;
            }

            .header {
                padding: 0 1.5rem 1.5rem;
            }

            .header h2 {
                font-size: 1.375rem;
            }

            .content {
                padding: 0 1.25rem 1.25rem;
            }

            input[type="text"] {
                font-size: 1rem;
                padding: 0.75rem;
                letter-spacing: 4px;
            }

            .btn {
                padding: 0.75rem;
                font-size: 0.9375rem;
            }
        }

        @media (max-width: 360px) {
            .logo {
                height: 70px;
                max-width: 80px;
            }

            .logo-container {
                padding: 1.5rem 0 1rem;
            }

            .header h2 {
                font-size: 1.25rem;
            }

            .header p {
                font-size: 0.8125rem;
            }

            input[type="text"] {
                letter-spacing: 3px;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('otp');
            if (input) input.focus();
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });
        });
    </script>
    </head>
<body>
    <div class="container">
        <!-- Logo at the top center -->
        <div class="logo-container">
            <img src="{{ asset('images/mcclogo.png') }}" alt="MCC Logo" class="logo">
        </div>
        
        <div class="header">
            <h2>Super Admin OTP Verification</h2>
            <p>Enter the 6-digit code sent to your MS365 email</p>
        </div>
        <div class="content">
            @if(session('status'))
                <div class="message status">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="message error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('superadmin.otp.verify') }}">
                @csrf
                <div class="form-group">
                    <label for="otp">One-Time Password</label>
                    <input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="••••••" required>
                </div>
                <button type="submit" class="btn">Verify and Continue</button>
            </form>
            <p class="note">Code expires in 10 minutes. Maximum 5 attempts.</p>
        </div>
    </div>
</body>
</html>


