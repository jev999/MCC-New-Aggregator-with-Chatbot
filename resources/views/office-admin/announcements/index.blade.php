<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - {{ auth('admin')->user()->office }} Office</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 80px;
            --transition-speed: 0.3s;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --card-padding: 1.5rem;
            
            /* Color variables based on office */
            @php $office = auth('admin')->user()->office; @endphp
            @if($office === 'NSTP')
                --primary-color: #10b981;
                --primary-light: #d1fae5;
                --primary-dark: #059669;
                --primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            @elseif($office === 'SSC')
                --primary-color: #3b82f6;
                --primary-light: #dbeafe;
                --primary-dark: #2563eb;
                --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            @elseif($office === 'GUIDANCE')
                --primary-color: #8b5cf6;
                --primary-light: #ede9fe;
                --primary-dark: #7c3aed;
                --primary-gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            @elseif($office === 'REGISTRAR')
                --primary-color: #f59e0b;
                --primary-light: #fef3c7;
                --primary-dark: #d97706;
                --primary-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            @elseif($office === 'CLINIC')
                --primary-color: #ef4444;
                --primary-light: #fee2e2;
                --primary-dark: #dc2626;
                --primary-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            @else
                --primary-color: #667eea;
                --primary-light: #e0e7ff;
                --primary-dark: #5b67d7;
                --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            @endif
            
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-muted: #6b7280;
            --text-light: #9ca3af;
            
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --bg-sidebar: #1f2937;
            --bg-sidebar-hover: rgba(255, 255, 255, 0.08);
            
            --border-color: #e5e7eb;
            --border-light: #f3f4f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.5;
            min-height: 100vh;
        }

        .dashboard-container {
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
            color: #10b981;
            font-size: 1.5rem;
        }

        .office-info {
            color: #cbd5e1;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            font-weight: 400;
            opacity: 0.8;
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
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            width: calc(100% - 1rem);
            text-align: left;
            padding: 0.75rem 1.25rem;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            margin: 0.5rem;
            border-radius: 6px;
        }

        .logout-btn:hover {
            background: var(--bg-sidebar-hover);
            color: white;
        }

        .logout-btn i {
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-footer .logout-btn {
            width: 100%;
            text-align: left;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            width: calc(100% - var(--sidebar-width));
            transition: margin-left var(--transition-speed) ease;
        }

        .header {
            background: var(--bg-primary);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-light);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header h1 i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        /* Button Styles */
        .btn {
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .btn-info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .btn-blue {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .btn-orange {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-sm {
            padding: 0.5rem 0.9rem;
            font-size: 0.85rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid transparent;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .alert-error {
            background-color: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .alert i {
            font-size: 1.1rem;
        }

        /* Content Container */
        .content-container {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            border: 1px solid var(--border-light);
        }

        .content-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-header h2 {
            color: var(--text-primary);
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .content-header .count {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .table th,
        .table td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }

        .table th {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .table tbody tr:hover {
            background: var(--bg-secondary);
        }

        /* Status Badge */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-published {
            background: #dcfce7;
            color: #166534;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        /* Actions */
        .actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border-color);
            opacity: 0.7;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: var(--bg-sidebar);
            color: white;
            border: none;
            padding: 0.65rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .mobile-menu-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .header h1 {
                font-size: 1.35rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }

            .mobile-menu-btn {
                display: block;
            }

            .table {
                font-size: 0.85rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 1rem;
            }

            .actions {
                flex-direction: column;
                gap: 0.25rem;
            }
        }

        @media (max-width: 480px) {
            .table td:nth-child(2),
            .table th:nth-child(2) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="sidebar">
            <div class="sidebar-header">
                <h3>
                    @if($office === 'NSTP')
                        <i class="fas fa-flag"></i>
                    @elseif($office === 'SSC')
                        <i class="fas fa-users"></i>
                    @elseif($office === 'GUIDANCE')
                        <i class="fas fa-heart"></i>
                    @elseif($office === 'REGISTRAR')
                        <i class="fas fa-file-alt"></i>
                    @elseif($office === 'CLINIC')
                        <i class="fas fa-stethoscope"></i>
                    @else
                        <i class="fas fa-briefcase"></i>
                    @endif
                    {{ $office }} Office
                </h3>
                <div class="office-info">{{ auth('admin')->user()->username }}</div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('office-admin.dashboard') }}">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a></li>
                <li><a href="{{ route('office-admin.announcements.index') }}" class="active">
                    <i class="fas fa-bullhorn"></i> Announcements
                </a></li>
                <li><a href="{{ route('office-admin.events.index') }}">
                    <i class="fas fa-calendar-alt"></i> Events
                </a></li>
                <li><a href="{{ route('office-admin.news.index') }}">
                    <i class="fas fa-newspaper"></i> News
                </a></li>
            </ul>

            
        </div>

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-bullhorn"></i> Announcements</h1>
                <a href="{{ route('office-admin.announcements.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Announcement
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

            <div class="content-container">
                <div class="content-header">
                    <h2>My Announcements</h2>
                    <span class="count">{{ $announcements->count() }} total</span>
                </div>

                @if($announcements->count() > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $announcement)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">
                                            {{ $announcement->title }}
                                        </div>
                                        <div style="color: var(--text-muted); font-size: 0.85rem;">
                                            {{ Str::limit($announcement->content, 60) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $announcement->is_published ? 'status-published' : 'status-draft' }}">
                                            {{ $announcement->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted);">
                                        {{ $announcement->created_at->format('M d, Y') }}
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="{{ route('office-admin.announcements.show', $announcement) }}" class="btn btn-blue btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('office-admin.announcements.edit', $announcement) }}" class="btn btn-orange btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('office-admin.announcements.destroy', $announcement) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    
                @endif
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
            
            // Add overlay when sidebar is active
            if (sidebar.classList.contains('active')) {
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.background = 'rgba(0,0,0,0.5)';
                overlay.style.zIndex = '999';
                overlay.style.backdropFilter = 'blur(2px)';
                overlay.onclick = toggleSidebar;
                document.body.appendChild(overlay);
            } else {
                document.querySelector('.sidebar-overlay')?.remove();
            }
        }
    </script>
</body>
</html>