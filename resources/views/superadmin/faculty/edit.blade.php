@extends('layouts.app')

@section('title', 'Edit Faculty - Super Admin')

@section('content')
<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-crown"></i> Super Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="{{ route('superadmin.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="{{ route('superadmin.admins.index') }}">
                <i class="fas fa-users-cog"></i> Admin Management
            </a></li>
                         <li><a href="{{ route('superadmin.office-admins.index') }}">
                    <i class="fas fa-briefcase"></i> Officer Management
                </a></li>
            <li><a href="{{ route('superadmin.announcements.index') }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('superadmin.events.index') }}">
                <i class="fas fa-calendar-alt"></i> Events
            </a></li>
            <li><a href="{{ route('superadmin.news.index') }}">
                <i class="fas fa-newspaper"></i> News
            </a></li>
            <li><a href="{{ route('superadmin.faculty.index') }}" class="active">
                <i class="fas fa-chalkboard-teacher"></i> Faculty
            </a></li>
            <li><a href="{{ route('superadmin.students.index') }}">
                <i class="fas fa-user-graduate"></i> Students
            </a></li>
            <li>
                
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="enhanced-header faculty-edit-header">
            <div class="header-content">
                <div class="header-text">
                    <div class="header-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h1>Edit Faculty Member</h1>
                        <p>Update faculty information and details</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="{{ route('superadmin.faculty.index') }}" class="btn-modern btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Faculty</span>
                    </a>
                </div>
            </div>
        </div>
        
        @if($errors->any())
            <div class="alert-modern alert-danger">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    <h4>Please fix the following errors:</h4>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        
        <!-- Faculty Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    {{ strtoupper(substr($faculty->first_name, 0, 1) . substr($faculty->surname, 0, 1)) }}
                </div>
                <div class="profile-info">
                    <h2>{{ $faculty->first_name }} {{ $faculty->surname }}</h2>
                    <p>Faculty ID: #{{ str_pad($faculty->id, 4, '0', STR_PAD_LEFT) }}</p>
                    <div class="profile-meta">
                        <span class="meta-item">
                            <i class="fas fa-calendar"></i>
                            Joined {{ $faculty->created_at->format('M d, Y') }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-clock"></i>
                            {{ $faculty->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Form -->
        <div class="form-container">
            <div class="form-header">
                <h3><i class="fas fa-edit"></i> Faculty Information</h3>
                <p>Update the faculty member's personal and contact information</p>
            </div>
            
            <form method="POST" action="{{ route('superadmin.faculty.update', $faculty->id) }}" class="enhanced-form">
                @csrf
                @method('PUT')
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name" class="form-label">
                            <i class="fas fa-user"></i>
                            First Name
                        </label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="{{ old('first_name', $faculty->first_name) }}" 
                               class="form-input @error('first_name') error @enderror"
                               placeholder="Enter first name"
                               required>
                        @error('first_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="middle_name" class="form-label">
                            <i class="fas fa-user"></i>
                            Middle Name
                        </label>
                        <input type="text" 
                               id="middle_name" 
                               name="middle_name" 
                               value="{{ old('middle_name', $faculty->middle_name) }}" 
                               class="form-input @error('middle_name') error @enderror"
                               placeholder="Enter middle name (optional)">
                        @error('middle_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="surname" class="form-label">
                            <i class="fas fa-user"></i>
                            Surname
                        </label>
                        <input type="text" 
                               id="surname" 
                               name="surname" 
                               value="{{ old('surname', $faculty->surname) }}" 
                               class="form-input @error('surname') error @enderror"
                               placeholder="Enter surname"
                               required>
                        @error('surname')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="ms365_account" class="form-label">
                            <i class="fas fa-envelope"></i>
                            Microsoft 365 Account
                        </label>
                        <input type="email" 
                               id="ms365_account" 
                               name="ms365_account" 
                               value="{{ old('ms365_account', $faculty->ms365_account) }}" 
                               class="form-input @error('ms365_account') error @enderror"
                               placeholder="Enter Microsoft 365 email address"
                               required>
                        @error('ms365_account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('superadmin.faculty.index') }}" class="btn-modern btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn-modern btn-primary">
                        <i class="fas fa-save"></i>
                        Update Faculty
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}

// Form validation
document.querySelector('.enhanced-form').addEventListener('submit', function(e) {
    const firstName = document.getElementById('first_name').value.trim();
    const surname = document.getElementById('surname').value.trim();
    const email = document.getElementById('ms365_account').value.trim();
    
    if (!firstName || !surname || !email) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return false;
    }
});

// Auto-focus first input
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('first_name').focus();
});
</script>

<style>
/* Enhanced Header */
.enhanced-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.faculty-edit-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-text {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: white;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    backdrop-filter: blur(10px);
}

.header-text h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
}

.header-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 1rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Modern Buttons */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.btn-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-modern:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
}

.btn-secondary {
    background: linear-gradient(135deg, #a8edea, #fed6e3);
    color: #333;
    box-shadow: 0 4px 15px rgba(168, 237, 234, 0.4);
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(168, 237, 234, 0.6);
}

/* Profile Card */
.profile-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.profile-header {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.profile-info h2 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-size: 1.75rem;
    font-weight: 700;
}

.profile-info p {
    margin: 0 0 1rem 0;
    color: #64748b;
    font-size: 1rem;
}

.profile-meta {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.875rem;
}

.meta-item i {
    color: #9ca3af;
}

/* Form Container */
.form-container {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.form-header h3 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-header p {
    margin: 0;
    color: #64748b;
    font-size: 0.875rem;
}

.enhanced-form {
    padding: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    color: #374151;
    font-weight: 600;
    font-size: 0.875rem;
}

.form-label i {
    color: #9ca3af;
}

.form-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    background: #f9fafb;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.error {
    border-color: #ef4444;
    background: #fef2f2;
}

.error-message {
    display: block;
    margin-top: 0.5rem;
    color: #ef4444;
    font-size: 0.75rem;
    font-weight: 500;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* Alert Modern */
.alert-modern {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.alert-danger {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border: 1px solid #f87171;
}

.alert-icon {
    font-size: 1.25rem;
    color: #dc2626;
    margin-top: 0.125rem;
}

.alert-content h4 {
    margin: 0 0 0.5rem 0;
    color: #991b1b;
    font-weight: 600;
}

.alert-content ul {
    color: #b91c1c;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .mobile-menu-btn {
        display: block !important;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .enhanced-header {
        padding: 1.5rem;
    }

    .header-text h1 {
        font-size: 1.5rem;
    }

    .profile-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .profile-meta {
        justify-content: center;
    }

    .enhanced-form {
        padding: 1.5rem;
    }

    .form-actions {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .enhanced-header {
        padding: 1rem;
    }

    .header-text {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .header-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }

    .profile-avatar {
        width: 60px;
        height: 60px;
        font-size: 1.25rem;
    }

    .enhanced-form {
        padding: 1rem;
    }
}
</style>
@endsection
