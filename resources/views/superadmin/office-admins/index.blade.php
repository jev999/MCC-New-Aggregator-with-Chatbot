<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Admin Management - Super Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            color: #667eea;
            font-size: 2.2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
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

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem 2rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .table th {
            background: rgba(102, 126, 234, 0.1);
            color: #333;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-office {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .office-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
        }

        .office-nstp { background: linear-gradient(135deg, #10b981, #059669); }
        .office-ssc { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .office-guidance { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .office-registrar { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .office-clinic { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .empty-state p {
            margin-bottom: 2rem;
            color: #6b7280;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            margin-right: 0.75rem;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid rgba(102, 126, 234, 0.2);
            transition: all 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.1);
            border-color: rgba(102, 126, 234, 0.4);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .username {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.2rem;
        }

        .user-id {
            font-size: 0.8rem;
            color: #6b7280;
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

            .table-container {
                overflow-x: auto;
            }

            .table {
                min-width: 600px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; background: #667eea; color: white; border: none; padding: 0.75rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
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
                    <i class="fas fa-users-cog"></i> Departmet Admin Management
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
                @if(auth('admin')->check() && auth('admin')->user()->isSuperAdmin())
                <li><a href="{{ route('superadmin.admin-access') }}">
                    <i class="fas fa-clipboard-list"></i> Admin Access Logs
                </a></li>
                <li><a href="{{ route('superadmin.backup') }}">
                    <i class="fas fa-database"></i> Database Backup
                </a></li>
                @endif
                <li>
                    <form method="POST" action="{{ route('superadmin.logout') }}" style="display: inline; width: 100%;">
                        @csrf

                    </form>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-briefcase"></i> Officer Management</h1>
                <a href="{{ route('superadmin.office-admins.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Office Admin
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            <div class="table-container">
                <div class="table-header">
                    <h2><i class="fas fa-list"></i> Office Administrators</h2>
                    <span style="color: #666;">Total: {{ $officeAdmins->count() }} office admins</span>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Admin Info</th>
                            <th>Role</th>
                            <th>Office</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($officeAdmins as $admin)
                            <tr>
                                <td>
                                    <div class="profile-avatar">
                                        @if($admin->hasProfilePicture)
                                            <img src="{{ $admin->profilePictureUrl }}" alt="{{ $admin->username }}">
                                        @else
                                            <i class="fas fa-user-shield"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-details">
                                            <div class="username">{{ $admin->username }}</div>
                                            <div class="user-id">#{{ $admin->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-office">
                                        <i class="fas fa-briefcase"></i> Office Admin
                                    </span>
                                </td>
                                <td>
                                    <span class="office-badge office-{{ strtolower($admin->office) }}">
                                        {{ $admin->office_display }}
                                    </span>
                                </td>
                                <td>
                                    @if($admin->created_at)
                                        {{ $admin->created_at->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('superadmin.office-admins.show', $admin) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('superadmin.office-admins.edit', $admin) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('superadmin.office-admins.destroy', $admin) }}" method="POST" style="display: inline;" onsubmit="return handleOfficeAdminDelete(event, '{{ $admin->username }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-briefcase"></i>
                                        <h3>No Office Administrators</h3>
                                        <p>No office administrators have been created yet.</p>
                                        <a href="{{ route('superadmin.office-admins.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Create First Office Admin
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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

        // Enhanced SweetAlert delete confirmation for Office Admins
        async function handleOfficeAdminDelete(event, adminUsername) {
            event.preventDefault();
            
            const result = await Swal.fire({
                title: 'Delete Office Admin?',
                text: `Are you sure you want to delete office admin "${adminUsername}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    popup: 'swal2-popup-custom',
                    title: 'swal2-title-custom',
                    content: 'swal2-content-custom',
                    confirmButton: 'swal2-confirm-custom',
                    cancelButton: 'swal2-cancel-custom'
                }
            });
            
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting Office Admin...',
                    text: 'Please wait while we delete the office admin.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal2-popup-custom'
                    },
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                event.target.submit();
            }
            
            return false;
        }
    </script>

<style>
    .text-muted {
        color: #6b7280 !important;
        font-style: italic;
    }

    /* Custom SweetAlert2 Styling */
    .swal2-popup-custom {
        border-radius: 15px !important;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25) !important;
        border: none !important;
    }

    .swal2-title-custom {
        color: #1f2937 !important;
        font-weight: 700 !important;
        font-size: 1.5rem !important;
    }

    .swal2-content-custom {
        color: #6b7280 !important;
        font-size: 1rem !important;
        line-height: 1.6 !important;
    }

    .swal2-confirm-custom {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        border: none !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        padding: 0.75rem 1.5rem !important;
        transition: all 0.3s ease !important;
    }

    .swal2-confirm-custom:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4) !important;
    }

    .swal2-cancel-custom {
        background: #6b7280 !important;
        border: none !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        padding: 0.75rem 1.5rem !important;
        transition: all 0.3s ease !important;
    }

    .swal2-cancel-custom:hover {
        background: #4b5563 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 5px 15px rgba(107, 114, 128, 0.4) !important;
    }

    .swal2-icon.swal2-warning {
        border-color: #f59e0b !important;
        color: #f59e0b !important;
    }

    .swal2-icon.swal2-warning .swal2-icon-content {
        color: #f59e0b !important;
        font-weight: 700 !important;
    }
</style>
</body>
</html>
