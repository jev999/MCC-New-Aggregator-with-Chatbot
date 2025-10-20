<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Details - Super Admin</title>
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
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border-right: 1px solid rgba(255, 255, 255, 0.18);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .sidebar-header h3 {
            color: #2d3748;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li {
            margin: 0.25rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border-left-color: #667eea;
        }

        .sidebar-menu i {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        .page-header h1 {
            color: #2d3748;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #718096;
            font-size: 1.1rem;
        }

        .details-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        .faculty-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 2rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            padding: 1.5rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .detail-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-value {
            color: #2d3748;
            font-size: 1.1rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        .actions-container {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            background: #48bb78;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
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
            </ul>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-user"></i> Faculty Details</h1>
                <p>View faculty member information</p>
            </div>

            <div class="details-container">
                <div class="faculty-avatar">
                    {{ strtoupper(substr($faculty->first_name, 0, 1) . substr($faculty->surname, 0, 1)) }}
                </div>

                <div class="text-center" style="margin-bottom: 2rem;">
                    <h2 style="color: #2d3748; margin-bottom: 0.5rem;">
                        {{ $faculty->first_name }} {{ $faculty->middle_name }} {{ $faculty->surname }}
                    </h2>
                    <span class="status-badge">
                        <i class="fas fa-check-circle"></i> Active Faculty
                    </span>
                </div>

                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-id-card"></i> Faculty ID
                        </div>
                        <div class="detail-value">#{{ str_pad($faculty->id, 4, '0', STR_PAD_LEFT) }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-envelope"></i> Email Address
                        </div>
                        <div class="detail-value">{{ $faculty->ms365_account }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-user-tag"></i> Role
                        </div>
                        <div class="detail-value">Faculty Member</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-calendar-plus"></i> Joined Date
                        </div>
                        <div class="detail-value">{{ $faculty->created_at->format('F d, Y') }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-clock"></i> Last Updated
                        </div>
                        <div class="detail-value">{{ $faculty->updated_at->format('F d, Y g:i A') }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-history"></i> Member Since
                        </div>
                        <div class="detail-value">{{ $faculty->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                <div class="actions-container">
                    <a href="{{ route('superadmin.faculty.edit', $faculty->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Faculty
                    </a>
                    <a href="{{ route('superadmin.faculty.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Faculty List
                    </a>
                    <button onclick="deleteFaculty({{ $faculty->id }})" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Faculty
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteFaculty(id) {
            if (confirm('Are you sure you want to delete this faculty member? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/superadmin/faculty/${id}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
