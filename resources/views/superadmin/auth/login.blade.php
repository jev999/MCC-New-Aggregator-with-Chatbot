
@extends('layouts.auth')

@section('title', 'Super Admin Login - MCC News Aggregator')

@section('content')
<div class="auth-container">
    <div class="auth-header">
        <h1>Super Admin Login</h1>
        <p>News Aggregator with Chatbot</p>
    </div>

    <form method="POST" action="{{ route('superadmin.login') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text"
                   id="username"
                   name="username"
                   class="form-control @error('username') error @enderror"
                   value="{{ old('username') }}"
                   required
                   autofocus>
            @error('username')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-input-container">
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control @error('password') error @enderror"
                       required>
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye" id="password-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn">Login as Super Admin</button>
    </form>

    <div class="auth-links">
        <a href="{{ route('department-admin.login') }}">Department Admin Login</a>
        <a href="{{ route('user.login') }}">User Login</a>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .password-input-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-input-container input {
        padding-right: 45px;
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        cursor: pointer;
        color: #6b7280;
        font-size: 16px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        transition: color 0.2s ease;
    }

    .password-toggle:hover {
        color: #4f46e5;
    }

    .password-toggle:focus {
        outline: none;
        color: #4f46e5;
    }

    /* Super admin specific styling */
    .auth-header h1 {
        color: #ff6b6b;
    }

    .btn {
        background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    }

    .btn:hover {
        background: linear-gradient(135deg, #ee5a24, #ff6b6b);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
    }
</style>

<script>
    // Password toggle functionality
    function togglePassword(inputId) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(inputId + '-eye');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // The controller handles the redirect automatically on successful login
    // No need for JavaScript redirect here

    // Show error message with SweetAlert
    @if($errors->any())
        Swal.fire({
            title: 'Login Failed',
            text: '{{ $errors->first() }}',
            icon: 'error',
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#ef4444',
            background: '#fff',
            color: '#333'
        });
    @endif
</script>
@endsection

