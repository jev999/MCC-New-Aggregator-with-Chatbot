<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Registration - MCC News Aggregator</title>
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
            padding: 1.5rem 0 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }

        .logo {
            height: 90px;
            width: auto;
            border-radius: 50%;
            background: white;
            padding: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .auth-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 1rem 2rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: rotate(30deg);
        }

        .auth-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .auth-header h2 {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .auth-header p {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .auth-content {
            padding: 2rem;
        }

        .greeting {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            color: var(--gray-800);
            font-weight: 500;
        }

        .message {
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            color: var(--gray-700);
        }

        .email-highlight {
            background: rgba(37, 99, 235, 0.1);
            color: var(--secondary);
            padding: 1.25rem;
            border-radius: var(--radius-sm);
            margin: 1.5rem 0;
            text-align: center;
            border: 1px solid rgba(37, 99, 235, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        .email-highlight i {
            margin-right: 0.75rem;
            color: var(--secondary);
            font-size: 1.2rem;
        }

        .email-highlight strong {
            color: var(--gray-900);
            margin-left: 0.5rem;
        }

        .reset-button {
            text-align: center;
            margin: 2rem 0;
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
            text-decoration: none;
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .expiry-notice {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            padding: 0.875rem 1rem;
            border-radius: var(--radius-sm);
            margin: 1.5rem 0;
            font-size: 0.875rem;
            text-align: center;
            border: 1px solid rgba(245, 158, 11, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .expiry-notice i {
            margin-right: 0.5rem;
            color: var(--warning);
        }

        .alternative-link {
            background: var(--gray-50);
            padding: 1.25rem;
            border-radius: var(--radius-sm);
            margin: 1.5rem 0;
            border-left: 4px solid var(--gray-300);
        }

        .alternative-link p {
            margin: 0 0 0.75rem 0;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .alternative-link a {
            word-break: break-all;
            color: var(--secondary);
            text-decoration: none;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .alternative-link a:hover {
            color: var(--secondary-light);
            text-decoration: underline;
        }

        .features {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: var(--radius-sm);
            margin: 1.5rem 0;
            border-left: 4px solid var(--success);
        }

        .features h3 {
            display: flex;
            align-items: center;
            color: var(--gray-800);
            margin: 0 0 1rem 0;
            font-size: 1rem;
        }

        .features h3 i {
            margin-right: 0.5rem;
            color: var(--success);
        }

        .features ul {
            margin: 0.75rem 0;
            padding-left: 1.5rem;
            color: var(--gray-700);
        }

        .features li {
            margin: 0.5rem 0;
            font-size: 0.85rem;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
        }

        .features li i {
            margin-right: 0.75rem;
            color: var(--success);
            min-width: 16px;
        }

        .security-notice {
            background: rgba(239, 68, 68, 0.05);
            padding: 1.25rem;
            border-radius: var(--radius-sm);
            margin: 1.5rem 0;
            border: 1px solid rgba(239, 68, 68, 0.1);
        }

        .security-notice h3 {
            display: flex;
            align-items: center;
            color: var(--danger);
            margin: 0 0 0.75rem 0;
            font-size: 0.95rem;
        }

        .security-notice h3 i {
            margin-right: 0.5rem;
            color: var(--danger);
        }

        .security-notice p {
            margin: 0.5rem 0;
            font-size: 0.85rem;
            color: var(--gray-700);
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-size: 0.8rem;
        }

        .footer p {
            margin: 0.4rem 0;
        }

        /* Animation for content */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-content > div {
            animation: fadeIn 0.4s ease forwards;
        }

        .auth-content > div:nth-child(1) { animation-delay: 0.1s; }
        .auth-content > div:nth-child(2) { animation-delay: 0.2s; }
        .auth-content > div:nth-child(3) { animation-delay: 0.3s; }
        .auth-content > div:nth-child(4) { animation-delay: 0.4s; }
        .auth-content > div:nth-child(5) { animation-delay: 0.5s; }
        .auth-content > div:nth-child(6) { animation-delay: 0.6s; }
        .auth-content > div:nth-child(7) { animation-delay: 0.7s; }
        .auth-content > div:nth-child(8) { animation-delay: 0.8s; }
        .auth-content > div:nth-child(9) { animation-delay: 0.9s; }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-container {
                max-width: 100%;
            }

            .auth-header {
                padding: 1rem 1.5rem 2rem;
            }

            .auth-content {
                padding: 1.5rem;
            }

            .auth-header h1 {
                font-size: 2rem;
            }

            .auth-header h2 {
                font-size: 1.25rem;
            }

            .logo {
                height: 80px;
            }

            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Logo at the top center -->
        <div class="logo-container">
            <img src="{{ asset('images/mcc-logo.png') }}" alt="MCC Logo" class="logo" onerror="this.style.display='none'">
        </div>

        <div class="auth-header">
            <h1>MCC-NAC</h1>
            <h2>Complete Your Registration</h2>
            <p>Madridejos Community College - News Aggregator with Chatbot</p>
        </div>

        <div class="auth-content">
            <div class="greeting">
                <i class="fas fa-user-circle"></i> Hello!
            </div>

            <div class="message">
                <i class="fas fa-info-circle"></i> Thank you for signing up for the MCC News Aggregator with Chatbot! We're excited to have you join our community.
            </div>

            <div class="email-highlight">
                <i class="fas fa-envelope"></i> Registration requested for: <strong>{{ $email }}</strong>
            </div>

            <div class="message">
                <i class="fas fa-check-circle"></i> To complete your registration and create your account, please click the button below:
            </div>

            <div class="reset-button">
                <a href="{{ $registrationUrl }}" class="btn" target="_blank">
                    <i class="fas fa-user-plus"></i> Complete Your Registration
                </a>
            </div>

            <div class="expiry-notice">
                <i class="fas fa-clock"></i> This registration link will expire in 30 minutes for security reasons.
            </div>

            <div class="alternative-link">
                <p><i class="fas fa-link"></i> If the button above doesn't work, copy and paste this link into your browser:</p>
                <a href="{{ $registrationUrl }}" target="_blank">{{ $registrationUrl }}</a>
            </div>

            <div class="features">
                <h3><i class="fas fa-star"></i> What you'll get access to:</h3>
                <ul>
                    <li><i class="fas fa-bullhorn"></i> Latest announcements and news from MCC</li>
                    <li><i class="fas fa-calendar-alt"></i> Upcoming events and important dates</li>
                    <li><i class="fas fa-robot"></i> AI-powered chatbot for academic assistance</li>
                    <li><i class="fas fa-comments"></i> Interactive discussions and comments</li>
                    <li><i class="fas fa-bell"></i> Personalized notifications</li>
                    <li><i class="fas fa-mobile-alt"></i> Mobile-friendly interface</li>
                </ul>
            </div>

            <div class="security-notice">
                <h3><i class="fas fa-shield-alt"></i> Security Notice</h3>
                <p>If you didn't request this registration, please ignore this email. The link will expire automatically and no account will be created.</p>
            </div>
        </div>

        <div class="footer">
            <p><strong>MCC News Aggregator with Chatbot</strong></p>
            <p>Madridejos Community College</p>
            <p>Bunakan, Madridejos, Cebu, Philippines</p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>© {{ date('Y') }} Madridejos Community College. All rights reserved.</p>
        </div>
    </div>
</body>
</html>