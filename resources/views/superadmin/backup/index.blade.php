<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Database Backup - MCC Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: #f8fafc;
            min-height: 100vh;
        }

        .header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.875rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header h1 i {
            color: #667eea;
        }

        .logout-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-info h3 {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .action-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .action-section h2 {
            color: #1e293b;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .action-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .backup-list {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .backup-list h2 {
            color: #1e293b;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .backup-table {
            width: 100%;
            border-collapse: collapse;
        }

        .backup-table th {
            background: #f1f5f9;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .backup-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #64748b;
        }

        .backup-table tr:hover {
            background: #f8fafc;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn-download,
        .btn-delete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-download {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .loading {
            display: none;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .loading.active {
            display: flex;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .table-stats {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .table-stats h3 {
            color: #1e293b;
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }

        .table-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .table-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }

        .table-item .table-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .table-item .table-count {
            color: #64748b;
            font-size: 0.875rem;
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
                <li><a href="{{ route('superadmin.admin-access') }}">
                    <i class="fas fa-clipboard-list"></i> Admin Access Logs
                </a></li>
                <li><a href="{{ route('superadmin.backup') }}" class="active">
                    <i class="fas fa-database"></i> Database Backup
                </a></li>
                @endif
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-database"></i> Database Backup</h1>
                <button onclick="handleLogout()" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>

            <!-- Database Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-table"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Tables</h3>
                        <p>{{ $dbStats['total_tables'] }}</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Records</h3>
                        <p>{{ number_format($dbStats['total_records']) }}</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-save"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Saved Backups</h3>
                        <p>{{ $backups->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Create Backup Section -->
            <div class="action-section">
                <h2><i class="fas fa-plus-circle"></i> Create New Backup</h2>
                <button onclick="createBackup()" class="action-btn" id="backupBtn">
                    <i class="fas fa-download"></i> Create Backup Now
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <span>Creating backup...</span>
                    </div>
                </button>
                <p style="margin-top: 1rem; color: #64748b; font-size: 0.875rem;">
                    <i class="fas fa-info-circle"></i> This will create a complete backup of database: <strong>{{ $dbStats['database_name'] }}</strong>
                </p>
            </div>

            <!-- Backup List -->
            <div class="backup-list">
                <h2><i class="fas fa-history"></i> Backup History</h2>
                
                @if($backups->count() > 0)
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="backupTableBody">
                            @foreach($backups as $backup)
                            <tr data-filename="{{ $backup['filename'] }}">
                                <td>
                                    <i class="fas fa-file-archive"></i> {{ $backup['filename'] }}
                                </td>
                                <td>{{ $backup['size'] }}</td>
                                <td>{{ $backup['created_at_human'] }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('superadmin.backup.download', $backup['filename']) }}" 
                                           class="btn-download">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <button onclick="deleteBackup('{{ $backup['filename'] }}')" class="btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No backups found. Create your first backup above.</p>
                    </div>
                @endif
            </div>

            <!-- Table Statistics -->
            @if($dbStats['tables']->count() > 0)
            <div class="table-stats">
                <h3><i class="fas fa-chart-bar"></i> Top Tables by Records</h3>
                <div class="table-list">
                    @foreach($dbStats['tables'] as $table)
                    <div class="table-item">
                        <div class="table-name">{{ $table['name'] }}</div>
                        <div class="table-count">{{ number_format($table['records']) }} records</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        // Create backup
        async function createBackup() {
            const btn = document.getElementById('backupBtn');
            const loading = document.getElementById('loading');
            
            btn.disabled = true;
            loading.classList.add('active');

            try {
                const response = await fetch('{{ route('superadmin.backup.create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Check if response is ok (status 200-299)
                if (!response.ok) {
                    const contentType = response.headers.get('content-type');
                    
                    // If HTML is returned instead of JSON, it's likely an error page
                    if (contentType && contentType.includes('text/html')) {
                        throw new Error(`Server returned an error page (Status: ${response.status}). Please check if you're logged in and try again.`);
                    }
                    
                    throw new Error(`Server error: ${response.status} ${response.statusText}`);
                }

                // Try to parse JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned unexpected response format. Expected JSON but got ' + contentType);
                }

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    });
                    
                    // Reload page to show new backup
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Backup creation failed');
                }
            } catch (error) {
                console.error('Backup creation error:', error);
                
                let errorMessage = error.message || 'Failed to create backup';
                
                // Provide helpful error messages
                if (errorMessage.includes('NetworkError') || errorMessage.includes('Failed to fetch')) {
                    errorMessage = 'Network error. Please check your internet connection and try again.';
                } else if (errorMessage.includes('401')) {
                    errorMessage = 'Authentication required. Please login again.';
                    setTimeout(() => window.location.href = '{{ route('login') }}', 2000);
                } else if (errorMessage.includes('403')) {
                    errorMessage = 'Access denied. You do not have permission to create backups.';
                } else if (errorMessage.includes('419')) {
                    errorMessage = 'Your session has expired. Please refresh the page and try again.';
                    setTimeout(() => window.location.reload(), 2000);
                } else if (errorMessage.includes('500')) {
                    errorMessage = 'Server error. Please check the Laravel logs or contact the administrator.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Backup Creation Failed',
                    text: errorMessage,
                    confirmButtonColor: '#ef4444',
                    footer: 'If the problem persists, please check server logs or contact support.'
                });
            } finally {
                btn.disabled = false;
                loading.classList.remove('active');
            }
        }

        // Delete backup
        async function deleteBackup(filename) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to recover this backup!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/superadmin/backup/delete/${filename}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message,
                            confirmButtonColor: '#10b981'
                        });
                        
                        // Remove row from table
                        document.querySelector(`tr[data-filename="${filename}"]`).remove();
                        
                        // Reload if no backups left
                        if (document.querySelectorAll('#backupTableBody tr').length === 0) {
                            window.location.reload();
                        }
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'Failed to delete backup',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        }

        // Logout
        async function handleLogout() {
            const result = await Swal.fire({
                title: 'Logout',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b'
            });

            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('superadmin.logout') }}';
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
