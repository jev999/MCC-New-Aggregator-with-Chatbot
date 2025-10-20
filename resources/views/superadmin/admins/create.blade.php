<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Department Admin - Super Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
            background: #f8fafc;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h3 {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .sidebar-header h3 i {
            color: #ffd700;
            font-size: 1.5rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li {
            margin: 0.5rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            gap: 0.75rem;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover i,
        .sidebar-menu a.active i {
            transform: scale(1.1);
        }

        /* Mobile responsiveness */
        @media (max-width: 1024px) {
            .mobile-menu-btn {
                display: block !important;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: #f8fafc;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header h1 i {
            color: #ff6b6b;
            font-size: 2.2rem;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .form-control.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-secondary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .role-card {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .role-card:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .role-card input[type="radio"] {
            margin-right: 0.5rem;
        }

        .role-card label {
            display: flex;
            align-items: center;
            cursor: pointer;
            margin: 0;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 500;
        }

        .role-card .role-icon {
            margin-right: 1rem;
            font-size: 1.5rem;
            color: #667eea;
        }

        .role-card .role-info {
            flex: 1;
        }

        .role-card .role-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.2rem;
        }

        .role-card .role-desc {
            font-size: 0.85rem;
            color: #666;
        }

        .department-section {
            display: none;
            margin-top: 1rem;
        }

        .department-section.show {
            display: block;
        }

        .department-card {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .department-card:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .department-card input[type="radio"] {
            margin-right: 0.5rem;
        }

        .department-card label {
            display: flex;
            align-items: center;
            cursor: pointer;
            margin: 0;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 500;
        }

        .department-card .dept-icon {
            margin-right: 1rem;
            font-size: 1.5rem;
            color: #667eea;
        }

        .department-card .dept-info {
            flex: 1;
        }

        .department-card .dept-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.2rem;
        }

        .department-card .dept-desc {
            font-size: 0.85rem;
            color: #666;
        }

        .department-card.disabled {
            opacity: 0.5;
            background: #f5f5f5;
            border-color: #ddd;
            cursor: not-allowed;
        }

        .department-card.disabled:hover {
            border-color: #ddd;
            background: #f5f5f5;
        }

        .department-card.disabled input[type="radio"] {
            cursor: not-allowed;
        }

        .department-card.disabled label {
            cursor: not-allowed;
        }

        /* Password field with toggle icon */
        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .password-field .form-control {
            padding-right: 45px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
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
                <li><a href="{{ route('superadmin.admins.index') }}" class="active">
                    <i class="fas fa-users-cog"></i> Department Admin Management
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
                <li><a href="{{ route('superadmin.faculty.index') }}">
                    <i class="fas fa-chalkboard-teacher"></i> Faculty
                </a></li>
                <li><a href="{{ route('superadmin.students.index') }}">
                    <i class="fas fa-user-graduate"></i> Students
                </a></li>
                <li>
                    <form method="POST" action="{{ route('superadmin.logout') }}" style="display: inline; width: 100%;">
                        @csrf
                       
                    </form>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-building"></i> Create Department Admin</h1>
                <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            <div class="form-container">
                <form action="{{ route('superadmin.admins.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="username">MS365 Account</label>
                        <input type="email" 
                               id="username" 
                               name="username" 
                               class="form-control @error('username') error @enderror" 
                               value="{{ old('username') }}" 
                               placeholder="Enter MS365 email address (e.g., user@domain.com)"
                               required>
                        @error('username')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Hidden role field set to department_admin -->
                    <input type="hidden" name="role" value="department_admin">

                    <div class="form-group">
                        
                        <div class="role-card">
                            <div style="display: flex; align-items: center; padding: 0.5rem;">
                                <i class="fas fa-building role-icon"></i>
                                <div class="role-info">
                                    <div class="role-name">Department Admin</div>
                                    <div class="role-desc">Manages content for assigned department only</div>
                                </div>
                            </div>
                        </div>
                        <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 1rem; margin-top: 1rem; font-size: 0.9rem; color: #1976d2;">
                            <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                            <strong>Note:</strong> Only one department admin is allowed per department. If a department already has an admin, you cannot create another one for the same department.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="department">Select Department <span style="color: red;">*</span></label>
                        <div class="department-cards">
                            @php
                                $existingAdmins = \App\Models\Admin::where('role', 'department_admin')->pluck('department')->toArray();
                            @endphp

                            <div class="department-card {{ in_array('BSIT', $existingAdmins) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="department" value="BSIT" {{ old('department') === 'BSIT' ? 'checked' : '' }}
                                           {{ in_array('BSIT', $existingAdmins) ? 'disabled' : '' }} required>
                                    <i class="fas fa-laptop-code dept-icon"></i>
                                    <div class="dept-info">
                                        <div class="dept-name">BSIT</div>
                                        <div class="dept-desc">Bachelor of Science in Information Technology</div>
                                        @if(in_array('BSIT', $existingAdmins))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            <div class="department-card {{ in_array('BSBA', $existingAdmins) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="department" value="BSBA" {{ old('department') === 'BSBA' ? 'checked' : '' }}
                                           {{ in_array('BSBA', $existingAdmins) ? 'disabled' : '' }} required>
                                    <i class="fas fa-chart-line dept-icon"></i>
                                    <div class="dept-info">
                                        <div class="dept-name">BSBA</div>
                                        <div class="dept-desc">Bachelor of Science in Business Administration</div>
                                        @if(in_array('BSBA', $existingAdmins))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            <div class="department-card {{ in_array('EDUC', $existingAdmins) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="department" value="EDUC" {{ old('department') === 'EDUC' ? 'checked' : '' }}
                                           {{ in_array('EDUC', $existingAdmins) ? 'disabled' : '' }} required>
                                    <i class="fas fa-chalkboard-teacher dept-icon"></i>
                                    <div class="dept-info">
                                        <div class="dept-name">EDUC</div>
                                        <div class="dept-desc">College of Education</div>
                                        @if(in_array('EDUC', $existingAdmins))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            <div class="department-card {{ in_array('BSHM', $existingAdmins) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="department" value="BSHM" {{ old('department') === 'BSHM' ? 'checked' : '' }}
                                           {{ in_array('BSHM', $existingAdmins) ? 'disabled' : '' }} required>
                                    <i class="fas fa-concierge-bell dept-icon"></i>
                                    <div class="dept-info">
                                        <div class="dept-name">BSHM</div>
                                        <div class="dept-desc">Bachelor of Science in Hospitality Management</div>
                                        @if(in_array('BSHM', $existingAdmins))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        </div>
                        @error('department')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="form-actions">
                        <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Admin Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>

        // Mobile menu toggle function
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const mobileBtn = document.querySelector('.mobile-menu-btn');

            if (window.innerWidth <= 1024 &&
                !sidebar.contains(event.target) &&
                !mobileBtn.contains(event.target) &&
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('open');
            }
        });

        // Form validation to ensure at least one available department
        document.addEventListener('DOMContentLoaded', function() {
            const availableDepartments = document.querySelectorAll('input[name="department"]:not([disabled])');
            const form = document.querySelector('form');

            if (availableDepartments.length === 0) {
                // Disable form submission if no departments are available
                const submitBtn = document.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> No Available Departments';
                submitBtn.style.background = '#6c757d';

                // Show message
                const formContainer = document.querySelector('.form-container');
                const noDeptsMessage = document.createElement('div');
                noDeptsMessage.style.cssText = 'background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; color: #856404;';
                noDeptsMessage.innerHTML = '<i class="fas fa-info-circle"></i> <strong>All departments already have admins assigned.</strong> You cannot create more department admins until existing ones are removed.';
                formContainer.insertBefore(noDeptsMessage, form);
            }
        });
    </script>
</body>
</html>
