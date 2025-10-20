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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--gray-800);
            background: linear-gradient(rgba(17, 24, 39, 0.7), rgba(17, 24, 39, 0.7)), 
                        url('https://images.unsplash.com/photo-1497215728101-856f4ea42174?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80') no-repeat center center;
            background-size: cover;
            position: relative;
        }

        /* Alternative background pattern if image doesn't load */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            z-index: -1;
        }

        .auth-container {
            background: white;
            width: 100%;
            max-width: 520px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition);
        }

        .auth-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 2rem 2rem;
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
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .auth-header h2 {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .auth-header p {
            font-size: 0.9rem;
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

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-container {
                max-width: 100%;
            }
            
            .auth-header {
                padding: 1.5rem;
            }
            
            .auth-content {
                padding: 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.75rem;
            }
            
            .auth-header h2 {
                font-size: 1.1rem;
            }
            
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>MCC-NAC</h1>
            <h2>Complete Your Registration</h2>
            <p>Madridejos Community College - News Aggregator</p>
        </div>

        <div class="auth-content">
            <div class="greeting">
                <i class="fas fa-user-circle"></i> Hello,
            </div>

            <div class="message">
                <i class="fas fa-info-circle"></i> Thank you for registering with MCC News Aggregator. To complete your registration, please click the button below:
            </div>

            <div class="reset-button">
                <a href="{{ $registrationUrl }}" class="btn" target="_blank">
                    <i class="fas fa-user-check"></i> Complete Registration
                </a>
            </div>

            <div class="expiry-notice">
                <i class="fas fa-clock"></i> This link will expire on {{ $expiresAt }} for security reasons.
            </div>

            <div class="alternative-link">
                <p><i class="fas fa-link"></i> If the button above doesn't work, copy and paste this URL into your browser:</p>
                <a href="{{ $registrationUrl }}" target="_blank">{{ $registrationUrl }}</a>
            </div>

            <div class="security-notice">
                <h3><i class="fas fa-shield-alt"></i> Security Notice</h3>
                <p>If you didn't request this registration, please ignore this email.</p>
            </div>

            <div class="message">
                <i class="fas fa-handshake"></i> Best regards,<br>The MCC News Aggregator Team
            </div>
        </div>

        <div class="footer">
            <p><strong>MCC News Aggregator</strong></p>
            <p>Madridejos Community College</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>