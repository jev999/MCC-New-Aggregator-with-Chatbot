@extends('layouts.auth')

@section('title', 'Complete MS365 Registration - MCC News Aggregator')

@section('content')
<div class="auth-header">
    <h1>Complete MS365 Registration</h1>
    <p>MCC News Aggregator with Chatbot</p>
    <p class="subtitle">Complete your institutional account setup</p>
</div>

<div class="auth-content">

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(isset($email))
        <div class="email-verified">
            <i class="fas fa-check-circle"></i>
            <span>MS365 verified: <strong>{{ $email }}</strong></span>
        </div>
    @endif

    <form method="POST" action="{{ route('ms365.register.complete') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="ms365_account" value="{{ $email ?? '' }}">

        <div class="form-group">
            <label for="ms365_account">
                <i class="fab fa-microsoft"></i>
                MS365 Account
            </label>
            <input type="email"
                   id="ms365_account"
                   name="ms365_account"
                   class="form-control"
                   value="{{ $email ?? '' }}"
                   readonly
                   required>
            <small class="form-help">
                <i class="fas fa-shield-alt"></i>
                This MS365 address was verified through your registration link
            </small>
        </div>

        <div class="form-group">
            <label for="first_name">
                <i class="fas fa-user"></i>
                First Name
            </label>
            <input type="text"
                   id="first_name"
                   name="first_name"
                   class="form-control @error('first_name') error @enderror"
                   value="{{ old('first_name') }}"
                   placeholder="Enter first name"
                   pattern="[A-Za-z' ]+"
                   title="Only letters and single quotation marks are allowed"
                   required>
            @error('first_name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="middle_name">
                <i class="fas fa-user"></i>
                Middle Name
            </label>
            <input type="text"
                   id="middle_name"
                   name="middle_name"
                   class="form-control @error('middle_name') error @enderror"
                   value="{{ old('middle_name') }}"
                   placeholder="Enter middle name (optional)"
                   pattern="[A-Za-z' ]+"
                   title="Only letters and single quotation marks are allowed">
            @error('middle_name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="surname">
                <i class="fas fa-user"></i>
                Surname
            </label>
            <input type="text"
                   id="surname"
                   name="surname"
                   class="form-control @error('surname') error @enderror"
                   value="{{ old('surname') }}"
                   placeholder="Enter surname"
                   pattern="[A-Za-z' ]+"
                   title="Only letters and single quotation marks are allowed"
                   required>
            @error('surname')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="role">
                <i class="fas fa-user-tag"></i>
                Role
            </label>
            <select id="role"
                    name="role"
                    class="form-control @error('role') error @enderror"
                    onchange="toggleDepartmentFields()"
                    required>
                <option value="">Select Role</option>
                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                <option value="faculty" {{ old('role') == 'faculty' ? 'selected' : '' }}>Faculty</option>
            </select>
            @error('role')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group department-field" id="department-field">
            <label for="department">
                <i class="fas fa-building"></i>
                Department
            </label>
            <select id="department"
                    name="department"
                    class="form-control @error('department') error @enderror">
                <option value="">Select Department</option>
                <option value="Bachelor of Science in Information Technology" {{ old('department') == 'Bachelor of Science in Information Technology' ? 'selected' : '' }}>Bachelor of Science in Information Technology</option>
                <option value="Bachelor of Science in Business Administration" {{ old('department') == 'Bachelor of Science in Business Administration' ? 'selected' : '' }}>Bachelor of Science in Business Administration</option>
                <option value="Bachelor of Elementary Education" {{ old('department') == 'Bachelor of Elementary Education' ? 'selected' : '' }}>Bachelor of Elementary Education</option>
                <option value="Bachelor of Secondary Education" {{ old('department') == 'Bachelor of Secondary Education' ? 'selected' : '' }}>Bachelor of Secondary Education</option>
                <option value="Bachelor of Science in Hospitality Management" {{ old('department') == 'Bachelor of Science in Hospitality Management' ? 'selected' : '' }}>Bachelor of Science in Hospitality Management</option>
            </select>
            @error('department')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group year-level-field" id="year-level-field" style="display: none;">
            <label for="year_level">
                <i class="fas fa-graduation-cap"></i>
                Year Level
            </label>
            <select id="year_level"
                    name="year_level"
                    class="form-control @error('year_level') error @enderror">
                <option value="">Select Year Level</option>
                <option value="1st Year" {{ old('year_level') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                <option value="2nd Year" {{ old('year_level') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                <option value="3rd Year" {{ old('year_level') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                <option value="4th Year" {{ old('year_level') == '4th Year' ? 'selected' : '' }}>4th Year</option>
            </select>
            @error('year_level')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i>
                Password
                <button type="button" class="generate-password-btn" onclick="generateStrongPassword()">
                    <i class="fas fa-magic"></i> Generate Strong Password
                </button>
            </label>
            <div class="password-input-container">
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control @error('password') error @enderror"
                       placeholder="Create a secure password"
                       minlength="8"
                       oninput="checkPasswordStrength(); checkPasswordMatch()"
                       required>
                <span class="toggle-password" onclick="togglePassword('password')">
                    <i class="fas fa-eye" id="password-eye"></i>
                </span>
            </div>
            
            <!-- Password Strength Indicator -->
            <div class="password-strength-container">
                <div class="password-strength-bar">
                    <div class="password-strength-fill" id="password-strength-fill"></div>
                </div>
                <div class="password-strength-text" id="password-strength-text">Enter a password</div>
            </div>
            
            <!-- Password Requirements -->
            <div class="password-requirements">
                <div class="requirement" id="req-length">
                    <i class="fas fa-times"></i> At least 8 characters
                </div>
                <div class="requirement" id="req-uppercase">
                    <i class="fas fa-times"></i> One uppercase letter
                </div>
                <div class="requirement" id="req-lowercase">
                    <i class="fas fa-times"></i> One lowercase letter
                </div>
                <div class="requirement" id="req-number">
                    <i class="fas fa-times"></i> One number
                </div>
                <div class="requirement" id="req-special">
                    <i class="fas fa-times"></i> One special character (!@#$%^&*)
                </div>
            </div>
            
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
            
            <!-- Password Suggestions -->
            <div class="password-suggestions" id="password-suggestions" style="display: none;">
                <div class="suggestion-header">
                    <i class="fas fa-lightbulb"></i> Password Suggestions:
                </div>
                <div class="suggestion-list" id="suggestion-list"></div>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                <i class="fas fa-lock"></i>
                Confirm Password
            </label>
            <div class="password-input-container">
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="form-control @error('password_confirmation') error @enderror"
                       placeholder="Confirm your password"
                       minlength="8"
                       oninput="checkPasswordMatch()"
                       required>
                <span class="toggle-password" onclick="togglePassword('password_confirmation')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            @error('password_confirmation')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>


        <!-- Terms and Conditions & Privacy Policy -->
        <div class="form-group">
            <div class="checkbox-container">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           id="terms_and_privacy" 
                           name="terms_and_privacy" 
                           required>
                    <span class="checkmark"></span>
                    <span class="checkbox-text">
                        I agree to the <a href="#" onclick="openModal('termsModal'); return false;" class="policy-link">Terms and Conditions</a> and <a href="#" onclick="openModal('privacyModal'); return false;" class="policy-link">Privacy Policy</a>, and consent to the processing of my personal data in accordance with the Data Privacy Act of 2012
                    </span>
                </label>
                @error('terms_and_privacy')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                @error('terms_conditions')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                @error('privacy_policy')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fab fa-microsoft"></i>
            Complete MS365 Registration
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left"></i>
            Already have an account? Login
        </a>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const icon = document.querySelector(`#${fieldId} + .toggle-password i`);

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Toggle department and year level fields based on role
    function toggleDepartmentFields() {
        const roleSelect = document.getElementById('role');
        const departmentField = document.getElementById('department-field');
        const yearLevelField = document.getElementById('year-level-field');
        const departmentSelect = document.getElementById('department');
        const yearLevelSelect = document.getElementById('year_level');

        if (roleSelect.value === 'student') {
            departmentField.style.display = 'block';
            yearLevelField.style.display = 'block';
            departmentSelect.required = true;
            yearLevelSelect.required = true;
        } else if (roleSelect.value === 'faculty') {
            departmentField.style.display = 'block';
            yearLevelField.style.display = 'none';
            departmentSelect.required = true;
            yearLevelSelect.required = false;
            yearLevelSelect.value = '';
        } else {
            departmentField.style.display = 'none';
            yearLevelField.style.display = 'none';
            departmentSelect.required = false;
            yearLevelSelect.required = false;
            departmentSelect.value = '';
            yearLevelSelect.value = '';
        }
    }

    // Validate name inputs (only letters, spaces, and apostrophes)
    function validateNameInput(event) {
        const allowedChars = /[A-Za-z' ]/;
        if (!allowedChars.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') {
            event.preventDefault();
        }
    }

    // Add event listeners
    document.getElementById('first_name').addEventListener('keypress', validateNameInput);
    document.getElementById('middle_name').addEventListener('keypress', validateNameInput);
    document.getElementById('surname').addEventListener('keypress', validateNameInput);

    // Initialize department fields visibility on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleDepartmentFields();
    });
</script>

<script>
    function togglePassword(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const icon = document.querySelector(`#${fieldId} + .toggle-password i`);

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Toggle department and year level fields based on role
    function toggleDepartmentFields() {
        const roleSelect = document.getElementById('role');
        const departmentField = document.getElementById('department-field');
        const yearLevelField = document.getElementById('year-level-field');
        const departmentSelect = document.getElementById('department');
        const yearLevelSelect = document.getElementById('year_level');

        if (roleSelect.value === 'student') {
            departmentField.style.display = 'block';
            yearLevelField.style.display = 'block';
            departmentSelect.required = true;
            yearLevelSelect.required = true;
        } else if (roleSelect.value === 'faculty') {
            departmentField.style.display = 'block';
            yearLevelField.style.display = 'none';
            departmentSelect.required = true;
            yearLevelSelect.required = false;
            yearLevelSelect.value = '';
        } else {
            departmentField.style.display = 'none';
            yearLevelField.style.display = 'none';
            departmentSelect.required = false;
            yearLevelSelect.required = false;
            departmentSelect.value = '';
            yearLevelSelect.value = '';
        }
    }

    // Validate name inputs (only letters, spaces, and apostrophes)
    function validateNameInput(event) {
        const allowedChars = /[A-Za-z' ]/;
        if (!allowedChars.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') {
            event.preventDefault();
        }
    }

    // Add event listeners
    document.getElementById('first_name').addEventListener('keypress', validateNameInput);
    document.getElementById('middle_name').addEventListener('keypress', validateNameInput);
    document.getElementById('surname').addEventListener('keypress', validateNameInput);

    // Initialize department fields visibility on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleDepartmentFields();
    });
</script>

<style>
    .subtitle {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .email-verified {
        background: #d1ecf1;
        color: #0c5460;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        border-left: 4px solid #17a2b8;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .form-help {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        font-size: 12px;
        color: #666;
    }

    .password-input-container {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #666;
        z-index: 10;
    }

    .toggle-password:hover {
        color: #333;
    }

    .form-control {
        padding-right: 35px;
    }

    .form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0078d4, #005a9e);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 500;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #106ebe, #004578);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3);
    }

    .auth-links a {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .auth-links a:hover {
        color: #0078d4;
    }

    .fab.fa-microsoft {
        color: #0078d4;
    }

    .fas.fa-shield-alt {
        color: #28a745;
    }

    .form-control:focus {
        border-color: #0078d4;
        box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.2);
    }

    /* Department and Year Level Fields */
    .department-field, .year-level-field {
        transition: all 0.3s ease;
    }

    .department-field.show, .year-level-field.show {
        display: block !important;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Icon colors */
    .fas.fa-user-tag {
        color: #6f42c1;
    }

    .fas.fa-building {
        color: #fd7e14;
    }

    .fas.fa-graduation-cap {
        color: #20c997;
    }

    /* Form validation styling */
    .form-control.error {
        border-color: #dc3545;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
    }

    .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .error-message::before {
        content: "⚠️";
        font-size: 10px;
    }
</style>






<script>

    function togglePassword(fieldId) {

        const passwordField = document.getElementById(fieldId);

        const icon = document.querySelector(`#${fieldId} + .toggle-password i`);



        if (passwordField.type === 'password') {

            passwordField.type = 'text';

            icon.classList.remove('fa-eye');

            icon.classList.add('fa-eye-slash');

        } else {

            passwordField.type = 'password';

            icon.classList.remove('fa-eye-slash');

            icon.classList.add('fa-eye');

        }

    }



    // Toggle department and year level fields based on role

    function toggleDepartmentFields() {

        const roleSelect = document.getElementById('role');

        const departmentField = document.getElementById('department-field');

        const yearLevelField = document.getElementById('year-level-field');

        const departmentSelect = document.getElementById('department');

        const yearLevelSelect = document.getElementById('year_level');



        if (roleSelect.value === 'student') {

            departmentField.style.display = 'block';

            yearLevelField.style.display = 'block';

            departmentSelect.required = true;

            yearLevelSelect.required = true;

        } else if (roleSelect.value === 'faculty') {

            departmentField.style.display = 'block';

            yearLevelField.style.display = 'none';

            departmentSelect.required = true;

            yearLevelSelect.required = false;

            yearLevelSelect.value = '';

        } else {

            departmentField.style.display = 'none';

            yearLevelField.style.display = 'none';

            departmentSelect.required = false;

            yearLevelSelect.required = false;

            departmentSelect.value = '';

            yearLevelSelect.value = '';

        }

    }



    // Validate name inputs (only letters, spaces, and apostrophes)

    function validateNameInput(event) {

        const allowedChars = /[A-Za-z' ]/;

        if (!allowedChars.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') {

            event.preventDefault();

        }

    }



    // Add event listeners

    document.getElementById('first_name').addEventListener('keypress', validateNameInput);

    document.getElementById('middle_name').addEventListener('keypress', validateNameInput);

    document.getElementById('surname').addEventListener('keypress', validateNameInput);



    // Initialize department fields visibility on page load

    document.addEventListener('DOMContentLoaded', function() {

        toggleDepartmentFields();

    });

</script>

<script>
    // Password Strength Checker
    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        const strengthFill = document.getElementById('password-strength-fill');
        const strengthText = document.getElementById('password-strength-text');
        const suggestions = document.getElementById('password-suggestions');
        
        // Requirements elements
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqLowercase = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');
        
        // Check requirements
        const hasLength = password.length >= 8;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        
        // Update requirement indicators
        updateRequirement(reqLength, hasLength);
        updateRequirement(reqUppercase, hasUppercase);
        updateRequirement(reqLowercase, hasLowercase);
        updateRequirement(reqNumber, hasNumber);
        updateRequirement(reqSpecial, hasSpecial);
        
        // Calculate strength
        let strength = 0;
        if (hasLength) strength++;
        if (hasUppercase) strength++;
        if (hasLowercase) strength++;
        if (hasNumber) strength++;
        if (hasSpecial) strength++;
        
        // Update strength indicator
        strengthFill.className = 'password-strength-fill';
        strengthText.className = 'password-strength-text';
        
        if (password.length === 0) {
            strengthText.textContent = 'Enter a password';
        } else if (strength <= 2) {
            strengthFill.classList.add('weak');
            strengthText.classList.add('weak');
            strengthText.textContent = 'Weak Password';
            showPasswordSuggestions(password);
        } else if (strength === 3) {
            strengthFill.classList.add('fair');
            strengthText.classList.add('fair');
            strengthText.textContent = 'Fair Password';
            showPasswordSuggestions(password);
        } else if (strength === 4) {
            strengthFill.classList.add('good');
            strengthText.classList.add('good');
            strengthText.textContent = 'Good Password';
            suggestions.style.display = 'none';
        } else {
            strengthFill.classList.add('strong');
            strengthText.classList.add('strong');
            strengthText.textContent = 'Strong Password!';
            suggestions.style.display = 'none';
        }
    }
    
    function updateRequirement(element, met) {
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
    
    function showPasswordSuggestions(currentPassword) {
        const suggestions = document.getElementById('password-suggestions');
        const suggestionList = document.getElementById('suggestion-list');
        
        const suggestedPasswords = generatePasswordSuggestions(currentPassword);
        
        suggestionList.innerHTML = '';
        suggestedPasswords.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.innerHTML = `
                <span>${suggestion}</span>
                <span class="suggestion-copy" onclick="copyToPassword('${suggestion}')">Use This</span>
            `;
            suggestionList.appendChild(item);
        });
        
        suggestions.style.display = 'block';
    }
    
    function generatePasswordSuggestions(base) {
        const suggestions = [];
        const words = ['Secure', 'Strong', 'Safe', 'Power', 'Shield'];
        const numbers = ['2024', '123', '456', '789'];
        const symbols = ['!', '@', '#', '$', '%'];
        
        // Generate 3 different suggestions
        for (let i = 0; i < 3; i++) {
            const word = words[Math.floor(Math.random() * words.length)];
            const number = numbers[Math.floor(Math.random() * numbers.length)];
            const symbol = symbols[Math.floor(Math.random() * symbols.length)];
            
            suggestions.push(`${word}${number}${symbol}Pass`);
        }
        
        return suggestions;
    }
    
    function generateStrongPassword() {
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const numbers = '0123456789';
        const symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        let password = '';
        
        // Ensure at least one character from each category
        password += uppercase[Math.floor(Math.random() * uppercase.length)];
        password += lowercase[Math.floor(Math.random() * lowercase.length)];
        password += numbers[Math.floor(Math.random() * numbers.length)];
        password += symbols[Math.floor(Math.random() * symbols.length)];
        
        // Fill the rest randomly
        const allChars = uppercase + lowercase + numbers + symbols;
        for (let i = 4; i < 12; i++) {
            password += allChars[Math.floor(Math.random() * allChars.length)];
        }
        
        // Shuffle the password
        password = password.split('').sort(() => Math.random() - 0.5).join('');
        
        // Set the password and update confirmation
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        
        // Check strength
        checkPasswordStrength();
        
        // Show success message
        alert('Strong password generated! Make sure to save it securely.');
    }
    
    function copyToPassword(password) {
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        checkPasswordStrength();
        
        // Show feedback
        const event = new CustomEvent('passwordCopied');
        document.dispatchEvent(event);
        
        // Temporary feedback
        const suggestionItems = document.querySelectorAll('.suggestion-item');
        suggestionItems.forEach(item => {
            if (item.textContent.includes(password)) {
                const copySpan = item.querySelector('.suggestion-copy');
                const originalText = copySpan.textContent;
                copySpan.textContent = 'Copied!';
                copySpan.style.color = '#28a745';
                setTimeout(() => {
                    copySpan.textContent = originalText;
                    copySpan.style.color = '#2196f3';
                }, 1500);
            }
        });
    }
    
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const confirmationField = document.getElementById('password_confirmation');
        
        if (confirmation.length > 0) {
            if (password === confirmation) {
                confirmationField.style.borderColor = '#28a745';
                confirmationField.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.2)';
            } else {
                confirmationField.style.borderColor = '#dc3545';
                confirmationField.style.boxShadow = '0 0 0 2px rgba(220, 53, 69, 0.2)';
            }
        } else {
            confirmationField.style.borderColor = '';
            confirmationField.style.boxShadow = '';
        }
    }
</script>



<style>

    .subtitle {

        color: #666;

        font-size: 14px;

        margin-top: 5px;

    }



    .alert {

        padding: 15px 20px;

        border-radius: 8px;

        margin-bottom: 20px;

        font-size: 14px;

        display: flex;

        align-items: center;

        gap: 10px;

    }



    .alert-success {

        background: #d4edda;

        color: #155724;

        border: 1px solid #c3e6cb;

    }



    .alert-error {

        background: #f8d7da;

        color: #721c24;

        border: 1px solid #f5c6cb;

    }



    .email-verified {

        background: #d1ecf1;

        color: #0c5460;

        padding: 12px 16px;

        border-radius: 6px;

        margin-bottom: 20px;

        display: flex;

        align-items: center;

        gap: 8px;

        font-size: 14px;

        border-left: 4px solid #17a2b8;

    }



    .form-group label {

        display: flex;

        align-items: center;

        gap: 8px;

        font-weight: 500;

        margin-bottom: 8px;

    }



    .form-help {

        display: flex;

        align-items: center;

        gap: 6px;

        margin-top: 6px;

        font-size: 12px;

        color: #666;

    }



    .password-input-container {

        position: relative;

    }



    .toggle-password {

        position: absolute;

        right: 10px;

        top: 50%;

        transform: translateY(-50%);

        cursor: pointer;

        color: #666;

        z-index: 10;

    }



    .toggle-password:hover {

        color: #333;

    }



    .form-control {

        padding-right: 35px;

    }



    .form-control[readonly] {

        background-color: #f8f9fa;

        border-color: #e9ecef;

        color: #6c757d;

    }



    .btn-primary {

        background: linear-gradient(135deg, #0078d4, #005a9e);

        color: white;

        border: none;

        padding: 12px 20px;

        border-radius: 6px;

        font-size: 16px;

        font-weight: 500;

        width: 100%;

        cursor: pointer;

        transition: all 0.3s ease;

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

    }



    .btn-primary:hover {

        background: linear-gradient(135deg, #106ebe, #004578);

        transform: translateY(-1px);

        box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3);

    }



    .auth-links a {

        display: flex;

        align-items: center;

        gap: 8px;

        color: #666;

        text-decoration: none;

        font-size: 14px;

        transition: color 0.3s ease;

    }



    .auth-links a:hover {

        color: #0078d4;

    }



    .fab.fa-microsoft {

        color: #0078d4;

    }



    .fas.fa-shield-alt {

        color: #28a745;

    }



    .form-control:focus {

        border-color: #0078d4;

        box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.2);

    }



    /* Department and Year Level Fields */

    .department-field, .year-level-field {

        transition: all 0.3s ease;

    }



    .department-field.show, .year-level-field.show {

        display: block !important;

        animation: slideDown 0.3s ease;

    }



    @keyframes slideDown {

        from {

            opacity: 0;

            transform: translateY(-10px);

        }

        to {

            opacity: 1;

            transform: translateY(0);

        }

    }



    /* Icon colors */

    .fas.fa-user-tag {

        color: #6f42c1;

    }



    .fas.fa-building {

        color: #fd7e14;

    }



    .fas.fa-graduation-cap {

        color: #20c997;

    }



    /* Form validation styling */

    .form-control.error {

        border-color: #dc3545;

        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);

    }



    .error-message {

        color: #dc3545;

        font-size: 12px;

        margin-top: 5px;

        display: flex;

        align-items: center;

        gap: 5px;

    }



    .error-message::before {
        content: "⚠️";
        font-size: 10px;
    }

    /* Password Strength Indicator Styles */
    .generate-password-btn {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        cursor: pointer;
        margin-left: 10px;
        transition: all 0.3s ease;
    }

    .generate-password-btn:hover {
        background: linear-gradient(135deg, #20c997, #28a745);
        transform: translateY(-1px);
    }

    .password-strength-container {
        margin-top: 8px;
        margin-bottom: 12px;
    }

    .password-strength-bar {
        width: 100%;
        height: 6px;
        background-color: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .password-strength-fill {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
        border-radius: 3px;
    }

    .password-strength-fill.weak {
        background: linear-gradient(90deg, #dc3545, #fd7e14);
        width: 25%;
    }

    .password-strength-fill.fair {
        background: linear-gradient(90deg, #fd7e14, #ffc107);
        width: 50%;
    }

    .password-strength-fill.good {
        background: linear-gradient(90deg, #ffc107, #28a745);
        width: 75%;
    }

    .password-strength-fill.strong {
        background: linear-gradient(90deg, #28a745, #20c997);
        width: 100%;
    }

    .password-strength-text {
        font-size: 0.75rem;
        font-weight: 500;
        text-align: center;
    }

    .password-strength-text.weak { color: #dc3545; }
    .password-strength-text.fair { color: #fd7e14; }
    .password-strength-text.good { color: #ffc107; }
    .password-strength-text.strong { color: #28a745; }

    .password-requirements {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        border-left: 3px solid #6c757d;
    }

    .requirement {
        font-size: 0.75rem;
        margin: 4px 0;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .requirement.met {
        color: #28a745;
    }

    .requirement.met i {
        color: #28a745;
    }

    .requirement i.fa-times {
        color: #dc3545;
    }

    .requirement i.fa-check {
        color: #28a745;
    }

    .password-suggestions {
        margin-top: 10px;
        padding: 10px;
        background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
        border-radius: 6px;
        border-left: 3px solid #2196f3;
    }

    .suggestion-header {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1976d2;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .suggestion-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .suggestion-item {
        background: white;
        padding: 8px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid #e3f2fd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .suggestion-item:hover {
        background: #f5f5f5;
        border-color: #2196f3;
        transform: translateY(-1px);
    }

    .suggestion-copy {
        color: #2196f3;
        font-size: 0.7rem;
        opacity: 0.7;
    }

    .suggestion-copy:hover {
        opacity: 1;
    }

    /* Checkbox Styles for Terms and Privacy Policy */
    .checkbox-container {
        margin: 10px 0;
    }

    .checkbox-label {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        cursor: pointer;
        font-size: 0.9rem;
        line-height: 1.5;
        user-select: none;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .checkbox-text {
        flex: 1;
        color: #333;
    }

    .policy-link {
        color: #0078d4;
        text-decoration: underline;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .policy-link:hover {
        color: #005a9e;
        text-decoration: none;
    }

    .policy-link:focus {
        outline: 2px solid #0078d4;
        outline-offset: 2px;
        border-radius: 2px;
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
        opacity: 1;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .modal-container {
        background: white;
        border-radius: 12px;
        max-width: 90%;
        max-height: 90vh;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        transform: scale(0.9) translateY(20px);
        transition: transform 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-overlay.active .modal-container {
        transform: scale(1) translateY(0);
    }

    .modal-header {
        background: linear-gradient(135deg, #0078d4, #005a9e);
        color: white;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .modal-header.bg-secondary {
        background: linear-gradient(135deg, #6c757d, #5a6268);
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .modal-close {
        background: transparent;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s ease;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .modal-body {
        padding: 2rem;
        overflow-y: auto;
        flex: 1;
        font-size: 0.95rem;
        line-height: 1.8;
    }

    .modal-body h3 {
        color: #0078d4;
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .modal-body h3:first-of-type {
        margin-top: 0;
    }

    .modal-body ul {
        padding-left: 1.5rem;
        margin: 1rem 0;
    }

    .modal-body ul li {
        margin-bottom: 0.5rem;
    }

    .modal-body ul li strong {
        color: #0078d4;
    }

    .modal-body .alert {
        margin: 1rem 0;
        padding: 1rem 1.25rem;
        border-radius: 6px;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .modal-container {
            max-width: 95%;
            max-height: 95vh;
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
        }

        .modal-header h2 {
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 1.5rem;
            font-size: 0.9rem;
        }

        .modal-overlay.active {
            padding: 1rem;
        }
    }

    @media (max-width: 576px) {
        .modal-container {
            max-width: 100%;
            max-height: 100vh;
            border-radius: 0;
        }

        .modal-header {
            padding: 1rem;
        }

        .modal-body {
            padding: 1rem;
            font-size: 0.85rem;
        }
    }

    /* Smooth scroll for modal content */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #0078d4;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #005a9e;
    }

</style>

<!-- Privacy Policy Modal -->
<div id="privacyModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header bg-primary">
            <h2><i class="fas fa-shield-alt"></i> Privacy Policy</h2>
            <button class="modal-close" onclick="closeModal('privacyModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            @include('policies.privacy-policy-content')
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div id="termsModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header bg-secondary">
            <h2><i class="fas fa-file-contract"></i> Terms and Conditions</h2>
            <button class="modal-close" onclick="closeModal('termsModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            @include('policies.terms-content')
        </div>
    </div>
</div>

<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = ''; // Restore scroll
        }
    }

    // Close modal on overlay click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeModal(e.target.id);
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
</script>

@endsection
