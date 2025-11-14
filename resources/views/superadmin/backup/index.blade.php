<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Database Backup - Super Admin - MCC Portal</title>
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
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
        }

        .header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 1.8rem;
        }
        
        .refresh-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3);
        }
        
        .refresh-btn i {
            font-size: 0.9rem;
        }
        
        .new-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-size: 0.7rem;
            font-weight: bold;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-left: 0.5rem;
            vertical-align: middle;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                box-shadow: 0 0 0 5px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }
        
        .new-backup-row {
            animation: fadeIn 1s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }


        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #10b981;
            color: white;
        }

        .alert-error {
            background: #ef4444;
            color: white;
        }

        .backup-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .info-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .info-card h2 {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem;
            border-radius: 10px;
        }

        .info-item strong {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .create-backup-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .create-backup-btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .create-backup-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .backups-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .backups-table thead {
            background: #f1f5f9;
        }

        .backups-table th,
        .backups-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .backups-table th {
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .backups-table tr:hover {
            background: #f8fafc;
        }

        .action-btn {
            padding: 0.4rem 0.7rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-right: 0.3rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        .download-btn {
            background: #3b82f6;
            color: white;
        }

        .download-btn:hover {
            background: #2563eb;
        }

        .delete-btn {
            background: #ef4444;
            color: white;
        }

        .delete-btn:hover {
            background: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading.active {
            display: flex;
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <div class="dashboard">
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

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-database"></i> Database Backup Management</h1>
                <button onclick="window.location.reload()" class="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
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

            <div class="info-card">
                <h2><i class="fas fa-info-circle"></i> Automated Backup System</h2>
                <p>Backups are automatically created every 5 hours via scheduled tasks. You can also manually create a backup anytime below.</p>
                
                <div class="info-grid">
                    <div class="info-item">
                        <strong><i class="fas fa-server"></i> Database</strong>
                        {{ $dbStats['database_name'] }}
                    </div>
                    <div class="info-item">
                        <strong><i class="fas fa-clock"></i> Schedule</strong>
                        {{ $dbStats['backup_schedule'] }}
                    </div>
                    <div class="info-item">
                        <strong><i class="fas fa-hdd"></i> Storage Location</strong>
                        {{ $dbStats['backup_disk'] }} disk
                    </div>
                    <div class="info-item">
                        <strong><i class="fas fa-archive"></i> Total Backups</strong>
                        {{ count($backups) }} file(s)
                    </div>
                </div>
            </div>

            <div class="backup-container">
                <button class="create-backup-btn" id="createBackupBtn" onclick="createBackup()">
                    <i class="fas fa-plus-circle"></i> Create Manual Backup
                </button>

                <h2 style="margin-bottom: 1.5rem; color: #333;">
                    <i class="fas fa-history"></i> Backup History
                </h2>

                @if(count($backups) > 0)
                    <table class="backups-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-file-archive"></i> Filename</th>
                                <th><i class="fas fa-weight"></i> Size</th>
                                <th><i class="fas fa-calendar"></i> Created</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                                <tr>
                                    <td><strong>{{ $backup['filename'] }}</strong></td>
                                    <td>{{ $backup['size'] }}</td>
                                    <td>{{ $backup['created_at_human'] }}</td>
                                    <td>
                                        <a href="{{ route('superadmin.backup.download.direct', $backup['filename']) }}" 
                                           class="action-btn download-btn">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <a href="{{ url('superadmin/backup/download-direct', $backup['filename']) }}" 
                                           class="action-btn download-btn" style="background-color: #2563eb;">
                                            <i class="fas fa-cloud-download-alt"></i> Direct
                                        </a>
                                        <a href="{{ route('direct.download', $backup['filename']) }}" 
                                           class="action-btn download-btn" style="background-color: #059669;">
                                            <i class="fas fa-file-download"></i> Binary
                                        </a>
                                        <a href="{{ url('direct-download.php') }}?file={{ $backup['filename'] }}" 
                                           class="action-btn download-btn" style="background-color: #9333ea;">
                                            <i class="fas fa-bolt"></i> Raw
                                        </a>
                                        <button onclick="deleteBackup('{{ $backup['filename'] }}')" 
                                                class="action-btn delete-btn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No backups found</h3>
                        <p>Create your first backup using the button above.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Check for backup files in root directory when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Only run if no backups are shown
            if (document.querySelector('.empty-state')) {
                checkForBackupsInRoot();
            }
        });
        
        // Function to check for backup files in root directory
        function checkForBackupsInRoot() {
            fetch('{{ route('superadmin.backup.check') }}', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.found && data.count > 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Backup Files Found!',
                        html: `
                            <div style="text-align: left; padding: 10px;">
                                <p>Found ${data.count} backup file(s) that are not being displayed.</p>
                                <p>Would you like to fix this issue?</p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Fix Now',
                        cancelButtonText: 'Later',
                        confirmButtonColor: '#3b82f6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ url('/fix-backup-display.php') }}';
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error checking for backup files:', error);
            });
        }
        
        function createBackup() {
            const btn = document.getElementById('createBackupBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
            document.getElementById('loading').classList.add('active');

            // Set a timeout to prevent UI from being stuck if the server doesn't respond
            const timeoutId = setTimeout(() => {
                document.getElementById('loading').classList.remove('active');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus-circle"></i> Create Manual Backup';
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Backup Taking Too Long',
                    html: `
                        <div style="text-align: left;">
                            <p>The backup process is taking longer than expected. This could be because:</p>
                            <ul style="text-align: left; padding-left: 20px;">
                                <li>Your database is very large</li>
                                <li>The server is experiencing high load</li>
                                <li>The connection timed out</li>
                            </ul>
                            <p>The backup may still be processing in the background. You can:</p>
                            <ol style="text-align: left; padding-left: 20px;">
                                <li>Wait a few minutes and refresh the page to check if a new backup appears</li>
                                <li>Try again with the button below</li>
                            </ol>
                        </div>
                    `,
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#3b82f6',
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    width: 600
                }).then((result) => {
                    if (result.isConfirmed) {
                        createBackup();
                    }
                });
            }, 60000); // 60 second timeout

            fetch('{{ route('superadmin.backup.create') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                clearTimeout(timeoutId); // Clear the timeout since we got a response
                
                if (!response.ok) {
                    if (response.status === 419) {
                        // CSRF token mismatch
                        throw new Error('Your session has expired. Please refresh the page and try again.');
                    } else {
                        return response.json().then(data => {
                            throw new Error(data.message || `Server error (${response.status})`);
                        }).catch(e => {
                            if (e instanceof SyntaxError) {
                                throw new Error(`HTTP error ${response.status}: The server response was not valid JSON`);
                            }
                            throw e;
                        });
                    }
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('loading').classList.remove('active');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus-circle"></i> Create Manual Backup';

                if (data.success) {
                    // Add the new backup to the table immediately
                    const backupTable = document.querySelector('.backups-table');
                    const emptyState = document.querySelector('.empty-state');
                    
                    // If there's an empty state message, remove it and create a table
                    if (emptyState) {
                        emptyState.remove();
                        
                        // Create a new table
                        const tableHTML = `
                            <table class="backups-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file-archive"></i> Filename</th>
                                        <th><i class="fas fa-weight"></i> Size</th>
                                        <th><i class="fas fa-calendar"></i> Created</th>
                                        <th><i class="fas fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="new-backup-row" style="background-color: rgba(16, 185, 129, 0.1);">
                                        <td><strong>${data.filename}</strong> <span class="new-badge">NEW</span></td>
                                        <td>${data.size}</td>
                                        <td>Just now</td>
                                        <td>
                                            <a href="{{ url('superadmin/backup/download-direct') }}/${data.filename}" 
                                               class="action-btn download-btn">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                            <a href="/superadmin/backup/download-direct/${data.filename}" 
                                               class="action-btn download-btn" style="background-color: #2563eb;">
                                                <i class="fas fa-cloud-download-alt"></i> Direct
                                            </a>
                                            <a href="/direct-download/${data.filename}" 
                                               class="action-btn download-btn" style="background-color: #059669;">
                                                <i class="fas fa-file-download"></i> Binary
                                            </a>
                                            <a href="/direct-download.php?file=${data.filename}" 
                                               class="action-btn download-btn" style="background-color: #9333ea;">
                                                <i class="fas fa-bolt"></i> Raw
                                            </a>
                                            <button onclick="deleteBackup('${data.filename}')" 
                                                    class="action-btn delete-btn">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        `;
                        
                        // Insert the table into the backup container
                        document.querySelector('.backup-container').insertAdjacentHTML('beforeend', tableHTML);
                        
                    } else if (backupTable) {
                        // Add a new row to the existing table
                        const newRow = document.createElement('tr');
                        newRow.className = 'new-backup-row';
                        newRow.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                        
                        newRow.innerHTML = `
                            <td><strong>${data.filename}</strong> <span class="new-badge">NEW</span></td>
                            <td>${data.size}</td>
                            <td>Just now</td>
                            <td>
                                <a href="{{ url('superadmin/backup/download-direct') }}/${data.filename}" 
                                   class="action-btn download-btn">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <a href="/superadmin/backup/download-direct/${data.filename}" 
                                   class="action-btn download-btn" style="background-color: #2563eb;">
                                    <i class="fas fa-cloud-download-alt"></i> Direct
                                </a>
                                <a href="/direct-download/${data.filename}" 
                                   class="action-btn download-btn" style="background-color: #059669;">
                                    <i class="fas fa-file-download"></i> Binary
                                </a>
                                <a href="/direct-download.php?file=${data.filename}" 
                                   class="action-btn download-btn" style="background-color: #9333ea;">
                                    <i class="fas fa-bolt"></i> Raw
                                </a>
                                <button onclick="deleteBackup('${data.filename}')" 
                                        class="action-btn delete-btn">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        `;
                        
                        // Insert at the top of the table
                        const tbody = backupTable.querySelector('tbody');
                        if (tbody.firstChild) {
                            tbody.insertBefore(newRow, tbody.firstChild);
                        } else {
                            tbody.appendChild(newRow);
                        }
                    }
                    
                    // Update the total backups count
                    const totalBackupsElements = document.querySelectorAll('.info-item');
                    totalBackupsElements.forEach(element => {
                        if (element.textContent.includes('Total Backups')) {
                            const countText = element.textContent;
                            const match = countText.match(/(\d+)\s+file/);
                            if (match) {
                                const currentCount = parseInt(match[1]);
                                element.textContent = element.textContent.replace(
                                    `${currentCount} file`, 
                                    `${currentCount + 1} file`
                                );
                            }
                        }
                    });
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Backup Created Successfully!',
                        html: `
                            <div style="text-align: left; padding: 10px;">
                                <p><strong>✓</strong> ${data.message}</p>
                                ${data.method ? `<p><small>Method: ${data.method.toUpperCase()}</small></p>` : ''}
                                ${data.filename ? `<p><small>File: ${data.filename}</small></p>` : ''}
                                ${data.size ? `<p><small>Size: ${data.size}</small></p>` : ''}
                                <p><strong>✓</strong> Backup is now visible in the list below!</p>
                            </div>
                        `,
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Backup Failed',
                        html: `
                            <div style="text-align: left;">
                                <p>${data.message || 'An unknown error occurred'}</p>
                                <details style="margin-top: 10px; font-size: 12px;">
                                    <summary style="cursor: pointer;">Technical Details</summary>
                                    <pre style="text-align: left; padding: 10px; background: #f5f5f5; border-radius: 5px; margin-top: 5px;">${JSON.stringify(data, null, 2)}</pre>
                                </details>
                            </div>
                        `,
                        confirmButtonColor: '#ef4444',
                        width: 600
                    });
                }
            })
            .catch(error => {
                clearTimeout(timeoutId); // Clear the timeout since we got a response
                document.getElementById('loading').classList.remove('active');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus-circle"></i> Create Manual Backup';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error Creating Backup',
                    html: `
                        <div style="text-align: left;">
                            <p>Failed to create backup. Please check:</p>
                            <ul style="text-align: left; padding-left: 20px;">
                                <li>Database connection is working</li>
                                <li>Storage directory permissions</li>
                                <li>Server has enough disk space</li>
                                <li>Your session is still valid (try refreshing the page)</li>
                            </ul>
                            <details style="margin-top: 10px; font-size: 12px;">
                                <summary style="cursor: pointer;">Error Details</summary>
                                <pre style="text-align: left; padding: 10px; background: #f5f5f5; border-radius: 5px; margin-top: 5px;">${error.message}</pre>
                            </details>
                        </div>
                    `,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Try Again',
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    width: 600
                }).then((result) => {
                    if (result.isConfirmed) {
                        createBackup();
                    }
                });
                console.error('Backup Error:', error);
            });
        }

        function deleteBackup(filename) {
            Swal.fire({
                title: 'Delete Backup?',
                text: `Are you sure you want to delete ${filename}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('loading').classList.add('active');

                    fetch(`{{ url('superadmin/backup/delete') }}/${filename}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('loading').classList.remove('active');

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                confirmButtonColor: '#10b981'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete Failed',
                                text: data.message,
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    })
                    .catch(error => {
                        document.getElementById('loading').classList.remove('active');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete backup. Please try again.',
                            confirmButtonColor: '#ef4444'
                        });
                        console.error('Error:', error);
                    });
                }
            });
        }

    </script>
</body>
</html>
