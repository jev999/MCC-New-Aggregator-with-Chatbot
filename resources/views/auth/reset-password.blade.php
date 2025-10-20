<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MCC News Aggregator</title>
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
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
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

        .auth-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 2.5rem 2rem;
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

        .auth-header p {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 300;
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
        }

        .password-toggle:hover {
            color: var(--secondary);
            background-color: var(--gray-100);
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
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn i {
            margin-right: 0.5rem;
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

        .email-display {
            background: var(--gray-50);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: var(--gray-700);
            border: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
        }

        .email-display i {
            margin-right: 0.5rem;
            color: var(--secondary);
        }

        .password-requirements {
            font-size: 0.75rem;
            color: var(--gray-600);
            margin-top: 0.75rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
        }

        .requirements-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: var(--gray-700);
            font-size: 0.8125rem;
        }

        .requirements-header i {
            margin-right: 0.5rem;
            color: var(--secondary);
        }

        .password-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .password-requirements li {
            margin: 0.375rem 0;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .password-requirements li::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--gray-400);
            margin-right: 0.5rem;
            font-size: 0.625rem;
            transition: var(--transition);
        }

        .password-requirements li.met {
            color: var(--success);
        }

        .password-requirements li.met::before {
            color: var(--success);
            content: '\f058';
        }

        .password-tips {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .tips-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
            font-size: 0.8125rem;
        }

        .tips-header i {
            margin-right: 0.5rem;
            color: var(--warning);
        }

        .tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tips-list li {
            margin: 0.25rem 0;
            padding-left: 1rem;
            position: relative;
            font-size: 0.75rem;
            color: var(--gray-600);
        }

        .tips-list li::before {
            content: '\f0eb';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--warning);
            position: absolute;
            left: 0;
            font-size: 0.625rem;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 6px;
            border-radius: 3px;
            background-color: var(--gray-200);
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: var(--transition);
        }

        .strength-weak {
            background-color: var(--danger);
        }

        .strength-medium {
            background-color: var(--warning);
        }

        .strength-strong {
            background-color: var(--success);
        }

        .strength-text {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            text-align: right;
        }

        /* Animation for form fields */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-container {
                max-width: 100%;
            }
            
            .auth-header {
                padding: 2rem 1.5rem;
            }
            
            .auth-content {
                padding: 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1><i class="fas fa-lock"></i> Reset Password</h1>
            <p>Enter your new password below</p>
        </div>

        <div class="auth-content">
            @if($errors->any())
                <div class="error-message">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="email-display">
                <i class="fab fa-microsoft"></i>
                Resetting password for: <strong>{{ $email }}</strong>
            </div>

            <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        New Password
                    </label>
                    <div class="password-input-container">
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control @error('password') error @enderror"
                               placeholder="Enter your new password"
                               required
                               autofocus>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="password-strength-bar"></div>
                    </div>
                    <div class="strength-text" id="strength-text"></div>
                    <div class="password-requirements">
                        <div class="requirements-header">
                            <i class="fas fa-shield-alt"></i>
                            <strong>Strong Password Requirements:</strong>
                        </div>
                        <ul>
                            <li id="length-req">At least 8 characters long</li>
                            <li id="letter-req">Contains at least one letter (a-z, A-Z)</li>
                            <li id="number-req">Contains at least one number (0-9)</li>
                            <li id="symbol-req">Contains at least one symbol (!@#$%^&*)</li>
                            <li id="case-req">Mix of uppercase and lowercase letters</li>
                            <li id="common-req">Avoid common passwords (password123, etc.)</li>
                        </ul>
                        <div class="password-tips">
                            <div class="tips-header">
                                <i class="fas fa-lightbulb"></i>
                                <strong>Password Tips:</strong>
                            </div>
                            <ul class="tips-list">
                                <li>Use a passphrase: "Coffee@Morning2024!"</li>
                                <li>Combine words with numbers and symbols</li>
                                <li>Avoid personal information (name, birthday)</li>
                                <li>Don't reuse passwords from other accounts</li>
                            </ul>
                        </div>
                    </div>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        <i class="fas fa-lock"></i>
                        Confirm New Password
                    </label>
                    <div class="password-input-container">
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Confirm your new password"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                            <i class="fas fa-eye" id="password_confirmation-eye"></i>
                        </button>
                    </div>
                    <div id="password-match-message" class="strength-text"></div>
                </div>

                <button type="submit" class="btn" id="submit-btn" disabled>
                    <i class="fas fa-save"></i>
                    Reset Password
                </button>
            </form>

            <div class="auth-links">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
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

        // Password strength validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const submitBtn = document.getElementById('submit-btn');
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('strength-text');
        const passwordMatchMessage = document.getElementById('password-match-message');
        
        // Requirement elements
        const lengthReq = document.getElementById('length-req');
        const letterReq = document.getElementById('letter-req');
        const numberReq = document.getElementById('number-req');
        const symbolReq = document.getElementById('symbol-req');
        const caseReq = document.getElementById('case-req');
        const commonReq = document.getElementById('common-req');

        // Common weak passwords to avoid
        const commonPasswords = [
            'password', 'password123', '123456', '123456789', 'qwerty', 'abc123',
            'password1', 'admin', 'letmein', 'welcome', 'monkey', '1234567890',
            'dragon', 'master', 'hello', 'login', 'pass', 'admin123', 'root',
            'user', 'test', 'guest', 'info', 'administrator', 'passw0rd'
        ];

        function checkPasswordStrength(password) {
            let strength = 0;
            let requirements = {
                length: false,
                letter: false,
                number: false,
                symbol: false,
                case: false,
                common: false
            };

            // Check length
            if (password.length >= 8) {
                strength += 15;
                requirements.length = true;
                lengthReq.classList.add('met');
            } else {
                lengthReq.classList.remove('met');
            }

            // Check for letters
            if (/[a-zA-Z]/.test(password)) {
                strength += 15;
                requirements.letter = true;
                letterReq.classList.add('met');
            } else {
                letterReq.classList.remove('met');
            }

            // Check for numbers
            if (/[0-9]/.test(password)) {
                strength += 15;
                requirements.number = true;
                numberReq.classList.add('met');
            } else {
                numberReq.classList.remove('met');
            }

            // Check for symbols
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password)) {
                strength += 15;
                requirements.symbol = true;
                symbolReq.classList.add('met');
            } else {
                symbolReq.classList.remove('met');
            }

            // Check for mixed case
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
                strength += 20;
                requirements.case = true;
                caseReq.classList.add('met');
            } else {
                caseReq.classList.remove('met');
            }

            // Check against common passwords
            const isCommon = commonPasswords.some(common => 
                password.toLowerCase().includes(common.toLowerCase()) ||
                common.toLowerCase().includes(password.toLowerCase())
            );
            
            if (!isCommon && password.length > 0) {
                strength += 20;
                requirements.common = true;
                commonReq.classList.add('met');
            } else {
                commonReq.classList.remove('met');
            }

            // Update strength bar
            strengthBar.style.width = Math.min(strength, 100) + '%';
            
            // Update strength text and color
            if (strength < 40) {
                strengthBar.className = 'password-strength-bar strength-weak';
                strengthText.textContent = 'Weak Password';
                strengthText.style.color = 'var(--danger)';
            } else if (strength < 80) {
                strengthBar.className = 'password-strength-bar strength-medium';
                strengthText.textContent = 'Medium Password';
                strengthText.style.color = 'var(--warning)';
            } else {
                strengthBar.className = 'password-strength-bar strength-strong';
                strengthText.textContent = 'Strong Password';
                strengthText.style.color = 'var(--success)';
            }

            return requirements;
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length === 0) {
                passwordMatchMessage.textContent = '';
                return false;
            }
            
            if (password === confirmPassword) {
                passwordMatchMessage.textContent = 'Passwords match';
                passwordMatchMessage.style.color = 'var(--success)';
                return true;
            } else {
                passwordMatchMessage.textContent = 'Passwords do not match';
                passwordMatchMessage.style.color = 'var(--danger)';
                return false;
            }
        }

        function validateForm() {
            const password = passwordInput.value;
            const requirements = checkPasswordStrength(password);
            const passwordsMatch = checkPasswordMatch();
            
            // Enable submit button only if all requirements are met and passwords match
            const allRequirementsMet = requirements.length && requirements.letter && 
                                      requirements.number && requirements.symbol &&
                                      requirements.case && requirements.common;
            
            submitBtn.disabled = !(allRequirementsMet && passwordsMatch && password.length > 0);
            
            // Update button text based on requirements
            if (allRequirementsMet && passwordsMatch && password.length > 0) {
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Reset Password';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Complete Requirements';
            }
        }

        // Event listeners
        passwordInput.addEventListener('input', validateForm);
        confirmPasswordInput.addEventListener('input', validateForm);

        // Form submission handler
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const requirements = checkPasswordStrength(password);
            const allRequirementsMet = requirements.length && requirements.letter && 
                                      requirements.number && requirements.symbol &&
                                      requirements.case && requirements.common;
            
            if (!allRequirementsMet) {
                e.preventDefault();
                let missingRequirements = [];
                if (!requirements.length) missingRequirements.push('at least 8 characters');
                if (!requirements.letter) missingRequirements.push('letters');
                if (!requirements.number) missingRequirements.push('numbers');
                if (!requirements.symbol) missingRequirements.push('symbols');
                if (!requirements.case) missingRequirements.push('mixed case letters');
                if (!requirements.common) missingRequirements.push('avoid common passwords');
                
                alert('Please ensure your password meets all requirements:\n• ' + missingRequirements.join('\n• '));
            }
        });
    </script>
</body>
</html>