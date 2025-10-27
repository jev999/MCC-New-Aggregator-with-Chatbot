<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access Logs - MCC Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #6b7280;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --background: #ffffff;
            --sidebar-bg: #1e293b;
            --sidebar-text: #cbd5e1;
            --sidebar-hover: #334155;
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
            background: #f8fafc;
        }

        .sidebar {
            width: 280px;
            background: #1a1a1a;
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: left;
        }

        .sidebar-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
        }

        .sidebar-header h3 i {
            color: white;
            font-size: 1.3rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0.5rem 0;
        }

        .sidebar-menu li {
            margin: 0.25rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #333333;
            color: white;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: #f8fafc;
            min-height: 100vh;
        }

        .header {
            margin-bottom: 2rem;
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.15);
            color: white;
        }

        .header h1 {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header h1 i {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 1.5rem;
        }

        .content-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .card-header {
            padding: 2rem;
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
        }

        .card-header h2 {
            color: #1e293b;
            font-size: 1.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .card-header h2 i {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 1.25rem;
        }

        .table-container {
            overflow-x: auto;
            background: white;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            font-weight: 700;
            padding: 1.25rem 1rem;
            text-align: left;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table th:first-child {
            border-top-left-radius: 0;
        }

        .table th:last-child {
            border-top-right-radius: 0;
        }

        .table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-weight: 500;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            background: white;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .table tbody tr:nth-child(even):hover {
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .badge-superadmin {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

        .badge-department-admin {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .badge-office-admin {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .badge-active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .badge-active::before {
            content: '●';
            animation: pulse 2s infinite;
        }

        .badge-inactive {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .status-active {
            color: #059669;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .status-active::before {
            content: '●';
            color: #10b981;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .delete-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .delete-btn:active {
            transform: translateY(0);
        }

        .delete-btn i {
            font-size: 1rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            gap: 0.75rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            border-top: 1px solid rgba(59, 130, 246, 0.1);
        }

        .pagination a,
        .pagination span {
            padding: 0.75rem 1rem;
            border: 2px solid transparent;
            color: #475569;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            min-width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pagination a:hover {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .pagination .active {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            border-color: #1e40af;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        /* Hide pagination info text */
        .pagination .hidden,
        .pagination p {
            display: none !important;
        }

        /* Hide Laravel pagination info spans */
        .pagination span:not([class*="page-link"]):not([class*="active"]):not([class*="disabled"]) {
            display: none !important;
        }

        /* Disabled pagination styling */
        .pagination .disabled {
            padding: 0.75rem 1rem;
            border: 2px solid transparent;
            color: #cbd5e1;
            border-radius: 12px;
            background: #f1f5f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            min-width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: not-allowed;
        }

        .no-data {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #cbd5e1;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .no-data h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .no-data p {
            font-size: 1rem;
            color: #64748b;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        .stat-icon.success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-icon.active {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
        }

        .stat-icon.completed {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .stat-icon.total {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-description {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        .role-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .role-stat {
            text-align: center;
        }

        .role-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .role-stat-value.superadmin {
            color: #f59e0b;
        }

        .role-stat-value.department {
            color: #3b82f6;
        }

        .role-stat-value.office {
            color: #10b981;
        }

        .role-stat-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Add some modern touches */
        .admin-name {
            font-weight: 600;
            color: #1e293b;
        }

        .ip-address {
            font-family: 'Courier New', monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
            color: #475569;
        }

        .time-display {
            font-weight: 500;
            color: #475569;
        }

        .duration-display {
            font-weight: 600;
            color: #059669;
            background: rgba(16, 185, 129, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                max-width: 100vw;
                overflow-x: hidden;
            }

            .mobile-menu-btn {
                display: block !important;
            }
        }

        @media (max-width: 768px) {

            .header {
                padding: 1.5rem 1rem;
                text-align: center;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .header h1 i {
                padding: 0.5rem;
                font-size: 1.25rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                margin-bottom: 1.5rem;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-value {
                font-size: 2rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .role-stats {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.75rem;
            }

            .role-stat-value {
                font-size: 1.25rem;
            }

            .card-header {
                padding: 1.5rem 1rem;
            }

            .card-header h2 {
                font-size: 1.25rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }

            .badge {
                padding: 0.375rem 0.75rem;
                font-size: 0.625rem;
            }

            .delete-btn {
                width: 36px;
                height: 36px;
                padding: 0.5rem;
            }

            .pagination {
                padding: 1rem;
                gap: 0.5rem;
            }

            .pagination a,
            .pagination span {
                padding: 0.5rem 0.75rem;
                min-width: 36px;
                font-size: 0.875rem;
            }

            .no-data {
                padding: 2rem 1rem;
            }

            .no-data i {
                font-size: 3rem;
            }

            .no-data h3 {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                gap: 0.75rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.75rem;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 0.875rem;
            }

            .stat-title {
                font-size: 0.75rem;
            }

            .stat-description {
                font-size: 0.75rem;
            }

            .role-stats {
                gap: 0.5rem;
                margin-top: 0.75rem;
                padding-top: 0.75rem;
            }

            .role-stat-value {
                font-size: 1rem;
            }

            .role-stat-label {
                font-size: 0.625rem;
            }

            .table-container {
                font-size: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.25rem;
            }

            .badge {
                padding: 0.25rem 0.5rem;
                font-size: 0.625rem;
            }

            .badge i {
                display: none;
            }

            .delete-btn {
                width: 32px;
                height: 32px;
            }

            .ip-address {
                font-size: 0.75rem;
                padding: 0.125rem 0.25rem;
            }

            .duration-display {
                font-size: 0.75rem;
                padding: 0.125rem 0.25rem;
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

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-crown"></i> Super Admin Panel</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('superadmin.dashboard') }}">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a></li>
                <li><a href="{{ route('superadmin.admins.index') }}">
                    <i class="fas fa-users-cog"></i> Department Admin Management
                </a></li>
                <li><a href="{{ route('superadmin.office-admins.index') }}">
                    <i class="fas fa-user-tie"></i> Office Admin Management
                </a></li>
                <li><a href="{{ route('superadmin.students.index') }}">
                    <i class="fas fa-graduation-cap"></i> Student Management
                </a></li>
                <li><a href="{{ route('superadmin.faculty.index') }}">
                    <i class="fas fa-chalkboard-teacher"></i> Faculty Management
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
                <li><a href="{{ route('superadmin.admin-access') }}" class="active">
                    <i class="fas fa-clipboard-list"></i> Admin Access Logs
                </a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-clipboard-list"></i> Admin Access Logs</h1>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Successful Logins</div>
                        <div class="stat-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ number_format($stats['successful_logins']) }}</div>
                    <div class="stat-description">Total successful admin logins</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Failed Logins</div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ number_format($stats['failed_logins']) }}</div>
                    <div class="stat-description">Total failed login attempts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Active Sessions</div>
                        <div class="stat-icon active">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ number_format($stats['active_sessions']) }}</div>
                    <div class="stat-description">Currently logged in admins</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Completed Sessions</div>
                        <div class="stat-icon completed">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ number_format($stats['completed_sessions']) }}</div>
                    <div class="stat-description">Sessions with logout recorded</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Access Logs</div>
                        <div class="stat-icon total">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ number_format($stats['total_attempts']) }}</div>
                    <div class="stat-description">All admin access attempts</div>
                    
                    <div class="role-stats">
                        <div class="role-stat">
                            <div class="role-stat-value superadmin">{{ $stats['superadmin_logins'] }}</div>
                            <div class="role-stat-label">Super Admin</div>
                        </div>
                        <div class="role-stat">
                            <div class="role-stat-value department">{{ $stats['department_admin_logins'] }}</div>
                            <div class="role-stat-label">Department</div>
                        </div>
                        <div class="role-stat">
                            <div class="role-stat-value office">{{ $stats['office_admin_logins'] }}</div>
                            <div class="role-stat-label">Office</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Admin Login & Logout History</h2>
                </div>
                
                @if($logs->count() > 0)
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Admin Name</th>
                                    <th>Role</th>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                <tr id="log-row-{{ $log->id }}">
                                    <td><strong>{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</strong></td>
                                    <td><span class="admin-name">{{ $log->status === 'failed' ? ($log->username_attempted ?? 'Unknown') : ($log->admin->username ?? 'Unknown') }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ str_replace('_', '-', $log->role) }}">
                                            @if($log->role === 'superadmin')
                                                <i class="fas fa-crown"></i>
                                            @elseif($log->role === 'department_admin')
                                                <i class="fas fa-users-cog"></i>
                                            @elseif($log->role === 'office_admin')
                                                <i class="fas fa-user-tie"></i>
                                            @endif
                                            {{ ucfirst(str_replace('_', ' ', $log->role)) }}
                                        </span>
                                    </td>
                                    <td><span class="ip-address">{{ $log->ip_address ?? 'N/A' }}</span></td>
                                    <td>
                                        @if($log->latitude && $log->longitude)
                                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                <span style="color: #059669; font-size: 0.875rem;">{{ $log->location_details ?? 'N/A' }}</span>
                                                <button onclick="showLocationMap({{ $log->latitude }}, {{ $log->longitude }}, '{{ $log->location_details }}', '{{ $log->status === 'failed' ? ($log->username_attempted ?? 'Unknown') : ($log->admin->username ?? 'Unknown') }}')" 
                                                        style="background: linear-gradient(135deg, #3b82f6, #1e40af); color: white; border: none; padding: 0.375rem 0.75rem; border-radius: 8px; cursor: pointer; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <i class="fas fa-map-marker-alt"></i> View Map
                                                </button>
                                            </div>
                                        @else
                                            <span style="color: #64748b; font-size: 0.875rem;">Location unavailable</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="time-display">
                                            @if($log->status === 'failed')
                                                -
                                            @elseif($log->time_in && is_object($log->time_in) && method_exists($log->time_in, 'format'))
                                                {{ $log->time_in->format('M d, Y H:i:s') }}
                                            @elseif($log->time_in)
                                                {{ $log->time_in }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->status === 'failed')
                                            <span class="time-display">-</span>
                                        @elseif($log->time_out && is_object($log->time_out) && method_exists($log->time_out, 'format'))
                                            <span class="time-display">{{ $log->time_out->format('M d, Y H:i:s') }}</span>
                                        @elseif($log->time_out)
                                            <span class="time-display">{{ $log->time_out }}</span>
                                        @else
                                            <span class="status-active">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status === 'failed')
                                            <span class="duration-display">-</span>
                                        @elseif($log->duration)
                                            <span class="duration-display">{{ $log->duration }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status === 'failed')
                                            <span class="badge badge-inactive"><i class="fas fa-times-circle"></i> Failed</span>
                                        @elseif($log->time_out)
                                            <span class="badge badge-inactive"><i class="fas fa-stop-circle"></i> Inactive</span>
                                        @else
                                            <span class="badge badge-active"><i class="fas fa-play-circle"></i> Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button onclick="deleteAccessLog({{ $log->id }}, '{{ $log->status === 'failed' ? ($log->username_attempted ?? 'Unknown') : ($log->admin->username ?? 'Unknown') }}')" 
                                                class="delete-btn" 
                                                title="Delete Access Log">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        @if ($logs->hasPages())
                            {{-- Previous Page Link --}}
                            @if ($logs->onFirstPage())
                                <span class="disabled"><i class="fas fa-chevron-left"></i></span>
                            @else
                                <a href="{{ $logs->previousPageUrl() }}" rel="prev"><i class="fas fa-chevron-left"></i></a>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                                @if ($page == $logs->currentPage())
                                    <span class="active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}">{{ $page }}</a>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($logs->hasMorePages())
                                <a href="{{ $logs->nextPageUrl() }}" rel="next"><i class="fas fa-chevron-right"></i></a>
                            @else
                                <span class="disabled"><i class="fas fa-chevron-right"></i></span>
                            @endif
                        @endif
                    </div>
                @else
                    <div class="no-data">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No Access Logs Found</h3>
                        <p>No admin access logs have been recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Location Map Modal -->
    <div id="locationMapModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; width: 90%; max-width: 800px; max-height: 90vh; overflow: hidden; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); padding: 1.5rem; color: white; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700;" id="mapModalTitle">
                        <i class="fas fa-map-marker-alt"></i> Admin Location
                    </h3>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; opacity: 0.9;" id="mapModalSubtitle">Location details</p>
                </div>
                <button onclick="closeLocationMap()" style="background: rgba(255, 255, 255, 0.2); border: none; color: white; padding: 0.75rem 1rem; border-radius: 8px; cursor: pointer; font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="padding: 1.5rem;">
                <div id="locationMap" style="width: 100%; height: 400px; border-radius: 12px; overflow: hidden;"></div>
                <div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 8px; color: #475569;">
                    <strong>Coordinates:</strong> <span id="mapCoordinates"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>

        // Auto-refresh every 30 seconds to show active sessions
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 30000);

        function deleteAccessLog(logId, adminName) {
            Swal.fire({
                title: 'Delete Access Log',
                html: `Are you sure you want to delete the access log for <strong>${adminName}</strong>?<br><br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the access log.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Make delete request
                    const deleteUrl = `{{ url('superadmin/admin-access') }}/${logId}`;
                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the row from the table
                            const row = document.getElementById(`log-row-${logId}`);
                            if (row) {
                                row.remove();
                            }
                            
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to delete access log.',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the access log.',
                            icon: 'error'
                        });
                    });
                }
            });
        }

        // Mobile menu functionality
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 1024 && 
                !sidebar.contains(event.target) && 
                !mobileMenuBtn.contains(event.target) &&
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });

        // Location Map Functions
        let map = null;

        function showLocationMap(lat, lng, locationDetails, adminName) {
            // Update modal content
            document.getElementById('mapModalTitle').innerHTML = `<i class="fas fa-map-marker-alt"></i> ${adminName}`;
            document.getElementById('mapModalSubtitle').textContent = locationDetails;
            document.getElementById('mapCoordinates').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

            // Show modal
            const modal = document.getElementById('locationMapModal');
            modal.style.display = 'flex';

            // Initialize map after a short delay to ensure DOM is ready
            setTimeout(() => {
                if (map) {
                    map.remove();
                }

                map = L.map('locationMap').setView([lat, lng], 13);

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);

                // Add marker
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`
                    <div style="text-align: center;">
                        <strong>${adminName}</strong><br>
                        <small>${locationDetails}</small>
                    </div>
                `).openPopup();
            }, 100);
        }

        function closeLocationMap() {
            document.getElementById('locationMapModal').style.display = 'none';
            if (map) {
                map.remove();
                map = null;
            }
        }

        // Close modal when clicking outside
        document.getElementById('locationMapModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeLocationMap();
            }
        });
    </script>
</body>
</html>
