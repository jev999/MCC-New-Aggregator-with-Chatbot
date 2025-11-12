<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Office Admin - Super Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
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

        .logout-btn {
            background: none;
            border: none;
            color: #cbd5e1;
            font-weight: 500;
            width: 100%;
            text-align: left;
            padding: 0.875rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .logout-btn i {
            width: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .logout-btn:hover i {
            transform: scale(1.1);
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            width: calc(100% - 280px);
        }

        .header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header h1 i {
            color: #667eea;
            font-size: 2.2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .office-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .office-card {
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .office-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }

        .office-card.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .office-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f9fafb;
        }

        .office-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .office-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            text-align: center;
        }

        .office-nstp .office-icon { color: #10b981; }
        .office-ssc .office-icon { color: #3b82f6; }
        .office-guidance .office-icon { color: #8b5cf6; }
        .office-registrar .office-icon { color: #f59e0b; }
        .office-clinic .office-icon { color: #ef4444; }

        .office-info {
            text-align: center;
        }

        .office-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .office-desc {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
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

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .office-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }

        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
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
                <li><a href="{{ route('superadmin.office-admins.index') }}" class="active">
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
                <li><a href="{{ route('superadmin.faculty.index') }}">
                    <i class="fas fa-chalkboard-teacher"></i> Faculty
                </a></li>
                <li><a href="{{ route('superadmin.students.index') }}">
                    <i class="fas fa-user-graduate"></i> Students
                </a></li>
                <li><a href="{{ route('superadmin.admin-access') }}">
                    <i class="fas fa-clipboard-list"></i> Admin Access Logs
                </a></li>
                <li><a href="{{ route('superadmin.backup') }}">
                    <i class="fas fa-database"></i> Database Backup
                </a></li>
                <li>
                   
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-briefcase"></i> Create Office Admin</h1>
                <a href="{{ route('superadmin.office-admins.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            @if ($errors->has('office'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>{{ $errors->first('office') }}</strong>
                    </div>
                </div>
            @endif

            <div class="form-container">
                <form action="{{ route('superadmin.office-admins.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-envelope"></i> MS365 Account
                        </label>
                        <div style="position: relative;">
                            <input type="email"
                                   id="username"
                                   name="username"
                                   class="form-control @error('username') error @enderror"
                                   value="{{ old('username') }}"
                                   placeholder="Enter MS365 email address (e.g., user@domain.com)"
                                   style="padding-left: 3rem;"
                                   required>
                            <i class="fas fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6b7280;"></i>
                        </div>
                        @error('username')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-briefcase"></i> Select Office
                        </label>
                        <p style="color: #6b7280; margin-bottom: 1rem;">Choose the office this administrator will manage:</p>

                        @php
                            $existingOffices = \App\Models\Admin::where('role', 'office_admin')->pluck('office')->toArray();
                        @endphp

                        <div class="office-grid">
                            <div class="office-card office-nstp {{ in_array('NSTP', $existingOffices) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="office" value="NSTP" {{ old('office') === 'NSTP' ? 'checked' : '' }}
                                           {{ in_array('NSTP', $existingOffices) ? 'disabled' : '' }} required>
                                    <i class="fas fa-flag office-icon"></i>
                                    <div class="office-info">
                                        <div class="office-name">NSTP Office</div>
                                        <div class="office-desc">National Service Training Program</div>
                                        @if(in_array('NSTP', $existingOffices))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            <div class="office-card office-ssc {{ in_array('SSC', $existingOffices) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="office" value="SSC" {{ old('office') === 'SSC' ? 'checked' : '' }}
                                           {{ in_array('SSC', $existingOffices) ? 'disabled' : '' }} required>
                                    <i class="fas fa-users office-icon"></i>
                                    <div class="office-info">
                                        <div class="office-name">SSC Office</div>
                                        <div class="office-desc">Student Supreme Council</div>
                                        @if(in_array('SSC', $existingOffices))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            <div class="office-card office-guidance {{ in_array('GUIDANCE', $existingOffices) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="office" value="GUIDANCE" {{ old('office') === 'GUIDANCE' ? 'checked' : '' }}
                                           {{ in_array('GUIDANCE', $existingOffices) ? 'disabled' : '' }} required>
                                    <i class="fas fa-heart office-icon"></i>
                                    <div class="office-info">
                                        <div class="office-name">Guidance Office</div>
                                        <div class="office-desc">Student Counseling & Support</div>
                                        @if(in_array('GUIDANCE', $existingOffices))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            <div class="office-card office-registrar {{ in_array('REGISTRAR', $existingOffices) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="office" value="REGISTRAR" {{ old('office') === 'REGISTRAR' ? 'checked' : '' }}
                                           {{ in_array('REGISTRAR', $existingOffices) ? 'disabled' : '' }} required>
                                    <i class="fas fa-file-alt office-icon"></i>
                                    <div class="office-info">
                                        <div class="office-name">Registrar Office</div>
                                        <div class="office-desc">Academic Records & Enrollment</div>
                                        @if(in_array('REGISTRAR', $existingOffices))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            <div class="office-card office-clinic {{ in_array('CLINIC', $existingOffices) ? 'disabled' : '' }}">
                                <label>
                                    <input type="radio" name="office" value="CLINIC" {{ old('office') === 'CLINIC' ? 'checked' : '' }}
                                           {{ in_array('CLINIC', $existingOffices) ? 'disabled' : '' }} required>
                                    <i class="fas fa-stethoscope office-icon"></i>
                                    <div class="office-info">
                                        <div class="office-name">Clinic Office</div>
                                        <div class="office-desc">Health Services & Medical Care</div>
                                        @if(in_array('CLINIC', $existingOffices))
                                            <div style="color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Already has an admin
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('superadmin.office-admins.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Office Admin
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

        // Handle office card selection
        document.querySelectorAll('.office-card input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.office-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // Add selected class to the parent card of the checked radio
                if (this.checked) {
                    this.closest('.office-card').classList.add('selected');
                }
            });
        });

        // Set initial selection if there's an old value
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('.office-card input[type="radio"]:checked');
            if (checkedRadio) {
                checkedRadio.closest('.office-card').classList.add('selected');
            }
        });

    </script>
</body>
</html>
