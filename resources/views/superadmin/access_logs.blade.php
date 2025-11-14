<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Access Logs - Super Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
            box-shadow: 8px 0 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-header h3 {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .sidebar-header h3 i {
            color: #ffd700;
            font-size: 1.5rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
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
            position: relative;
        }

        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #2563eb;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-menu a.active::before {
            transform: scaleY(1);
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2.5rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 2rem 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.5);
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header h1 {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header h1 i {
            color: #2563eb;
            font-size: 2.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.75rem;
            margin-bottom: 2.5rem;
            animation: fadeIn 0.6s ease 0.1s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #2563eb;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .stat-card:hover::before {
            top: -20%;
            right: -20%;
        }

        .stat-card.success {
            border-left-color: #16a34a;
        }

        .stat-card.danger {
            border-left-color: #ef4444;
        }

        .stat-card.warning {
            border-left-color: #f59e0b;
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
        }

        .card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
            animation: fadeIn 0.6s ease 0.2s both;
        }

        .card-header {
            padding: 2rem 2.5rem;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(30, 64, 175, 0.05) 100%);
        }

        .card-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            transform: translateY(-2px);
        }

        .table-wrapper {
            overflow-x: auto;
            padding: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-bottom: 2px solid #cbd5e1;
        }

        th {
            padding: 1.25rem;
            text-align: left;
            font-weight: 700;
            color: #334155;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            color: #333;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(30, 64, 175, 0.05) 100%);
            box-shadow: inset 0 0 10px rgba(37, 99, 235, 0.1);
        }

        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
        }

        .badge-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .badge-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .badge-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #2563eb;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.75rem;
            padding: 2.5rem 1.5rem;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 0.6rem 0.9rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .pagination a:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .pagination span[style*="background: linear-gradient"] {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border-color: #2563eb;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #333;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                transform: translateX(0);
            }

            .main-content {
                padding: 1rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.5rem;
            }

            .btn-group {
                width: 100%;
            }

            .btn {
                flex: 1;
                justify-content: center;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-card .number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
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
                <li><a href="{{ route('superadmin.admin-access') }}" class="active">
                    <i class="fas fa-clipboard-list"></i> Admin Access Logs
                </a></li>
                <li><a href="{{ route('superadmin.backup') }}">
                    <i class="fas fa-database"></i> Database Backup
                </a></li>
                @endif
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
                    <h3>Total Attempts</h3>
                    <div class="number">{{ $stats['total_attempts'] }}</div>
                </div>
                <div class="stat-card success">
                    <h3>Successful Logins</h3>
                    <div class="number">{{ $stats['successful_logins'] }}</div>
                </div>
                <div class="stat-card danger">
                    <h3>Failed Logins</h3>
                    <div class="number">{{ $stats['failed_logins'] }}</div>
                </div>
                <div class="stat-card warning">
                    <h3>Active Sessions</h3>
                    <div class="number">{{ $stats['active_sessions'] }}</div>
                </div>
            </div>

            <!-- Role Statistics -->
            <div class="stats-grid">
                <div class="stat-card info" style="border-left-color: #8b5cf6;">
                    <h3>Superadmin Logins</h3>
                    <div class="number">{{ $stats['superadmin_logins'] }}</div>
                </div>
                <div class="stat-card info" style="border-left-color: #06b6d4;">
                    <h3>Department Admin Logins</h3>
                    <div class="number">{{ $stats['department_admin_logins'] }}</div>
                </div>
                <div class="stat-card info" style="border-left-color: #ec4899;">
                    <h3>Office Admin Logins</h3>
                    <div class="number">{{ $stats['office_admin_logins'] }}</div>
                </div>
                <div class="stat-card success">
                    <h3>Completed Sessions</h3>
                    <div class="number">{{ $stats['completed_sessions'] }}</div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h2>Access Log Records</h2>
                    <div class="btn-group">
                        <button class="btn btn-danger" onclick="bulkDeleteLogs()">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>

                <div class="table-wrapper">
                    @if($logs->count() > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" class="checkbox" onchange="toggleSelectAll()"></th>
                                    <th>Admin</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Login Time</th>
                                    <th>Logout Time</th>
                                    <th>Duration</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td style="text-align: center;"><input type="checkbox" class="log-checkbox" value="{{ $log->id }}" onchange="updateSelectAll()"></td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">
                                                    {{ strtoupper(substr($log->admin->username ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong style="color: #333;">{{ $log->admin->username ?? 'Unknown' }}</strong><br>
                                                    <small style="color: #999;">{{ $log->admin->email ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <i class="fas fa-user-tie" style="margin-right: 0.4rem;"></i>
                                                {{ ucfirst(str_replace('_', ' ', $log->role)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->status === 'success')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle" style="margin-right: 0.4rem;"></i>
                                                    Success
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times-circle" style="margin-right: 0.4rem;"></i>
                                                    Failed
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="fas fa-clock" style="color: #2563eb; margin-right: 0.5rem;"></i>
                                            {{ $log->time_in ? $log->time_in->format('M d, Y H:i:s') : '-' }}
                                        </td>
                                        <td>
                                            <i class="fas fa-sign-out-alt" style="color: #ef4444; margin-right: 0.5rem;"></i>
                                            {{ $log->time_out ? $log->time_out->format('M d, Y H:i:s') : '<span style="color: #16a34a; font-weight: 700;">Active</span>' }}
                                        </td>
                                        <td>
                                            <span style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); padding: 0.5rem 0.75rem; border-radius: 6px; font-weight: 600; color: #334155;">
                                                @if($log->time_in && $log->time_out)
                                                    {{ $log->time_in->diff($log->time_out)->format('%h:%i:%s') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <small style="color: #666;">
                                                <i class="fas fa-map-marker-alt" style="color: #ef4444; margin-right: 0.4rem;"></i>
                                                {{ $log->location_details ?? 'N/A' }}
                                            </small>
                                        </td>
                                        <td style="text-align: center;">
                                            <button class="btn btn-danger" onclick="deleteLog({{ $log->id }})" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="pagination">
                            @if ($logs->onFirstPage())
                                <span style="padding: 0.6rem 0.9rem; border: 2px solid #e2e8f0; border-radius: 8px; color: #ccc; cursor: not-allowed;">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </span>
                            @else
                                <a href="{{ $logs->previousPageUrl() }}" style="padding: 0.6rem 0.9rem; border: 2px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #333; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            @endif

                            @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                                @if ($page == $logs->currentPage())
                                    <span style="padding: 0.6rem 0.9rem; border: 2px solid #2563eb; border-radius: 8px; background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; font-weight: 600;">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" style="padding: 0.6rem 0.9rem; border: 2px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #333; transition: all 0.3s ease; font-weight: 600; display: inline-block;">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            @if ($logs->hasMorePages())
                                <a href="{{ $logs->nextPageUrl() }}" style="padding: 0.6rem 0.9rem; border: 2px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #333; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <span style="padding: 0.6rem 0.9rem; border: 2px solid #e2e8f0; border-radius: 8px; color: #ccc; cursor: not-allowed;">
                                    Next <i class="fas fa-chevron-right"></i>
                                </span>
                            @endif

                            <span style="margin-left: 1rem; color: #666; font-weight: 600;">
                                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
                            </span>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Access Logs Found</h3>
                            <p>There are no admin access logs to display.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.log-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        function updateSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.log-checkbox');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            selectAll.checked = allChecked;
        }

        function deleteLog(logId) {
            Swal.fire({
                title: 'Delete Access Log?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/superadmin/admin-access/${logId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Failed to delete log', 'error');
                    });
                }
            });
        }

        function bulkDeleteLogs() {
            const selectedIds = Array.from(document.querySelectorAll('.log-checkbox:checked')).map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                Swal.fire('No Selection', 'Please select at least one log to delete.', 'info');
                return;
            }

            Swal.fire({
                title: 'Delete Selected Logs?',
                text: `You are about to delete ${selectedIds.length} log(s). This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/superadmin/admin-access/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ log_ids: selectedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Failed to delete logs', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>
