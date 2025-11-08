
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - MCC Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .sidebar-menu a:hover i,
        .sidebar-menu a.active i {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }

        .sidebar-menu a i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
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

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        /* Enhanced button transitions matching admin dashboard */
        .btn {
            transition: all 0.3s ease;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #495057, #6c757d);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        /* Enhanced animations matching admin dashboard */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Apply animations to elements */
        .stat-card {
            animation: slideInUp 0.6s ease-out;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        .stat-card:nth-child(6) { animation-delay: 0.6s; }
        .stat-card:nth-child(7) { animation-delay: 0.7s; }

        .quick-action-card {
            animation: slideInUp 0.6s ease-out;
        }

        .quick-action-card:nth-child(1) { animation-delay: 0.8s; }
        .quick-action-card:nth-child(2) { animation-delay: 0.9s; }
        .quick-action-card:nth-child(3) { animation-delay: 1.0s; }
        .quick-action-card:nth-child(4) { animation-delay: 1.1s; }
        .quick-action-card:nth-child(5) { animation-delay: 1.2s; }

        .sidebar {
            animation: slideInLeft 0.5s ease-out;
        }

        .main-content {
            animation: fadeIn 0.8s ease-out;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
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
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(31, 38, 135, 0.5);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .stat-card .change {
            font-size: 0.85rem;
            color: #10b981;
            font-weight: 600;
        }

        .chart-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(156, 163, 175, 0.1);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            animation: chartSlideIn 0.8s ease-out;
            margin-bottom: 2rem;
            padding: 1.5rem;
        }

        @keyframes chartSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px 16px 0 0;
        }

        .chart-wrapper {
            position: relative;
            height: 320px;
            width: 100%;
        }

        /* Enhanced chart title */
        .chart-container h2 {
            color: #333;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chart-container h2 i {
            color: #667eea;
            font-size: 1.6rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            text-align: center;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(31, 38, 135, 0.5);
            color: #333;
        }

        .quick-action-card .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #667eea;
        }

        .quick-action-card h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .quick-action-card p {
            font-size: 0.9rem;
            color: #666;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
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
                <li><a href="{{ route('superadmin.dashboard') }}" class="active">
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
                <li><a href="{{ route('superadmin.backup') }}">
                    <i class="fas fa-database"></i> Database Backup
                </a></li>
                @endif
                <li>

                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-crown"></i> Super Admin Dashboard</h1>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    @auth('admin')
                        <span style="color: #666;">Welcome, {{ auth('admin')->user()->username }}</span>
                        <button onclick="handleLogout()" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    @else
                        <script>
                            window.location.href = "{{ route('login', ['type' => 'superadmin']) }}";
                        </script>
                    @endauth
                </div>
            </div>

            @if(session('success'))
                <div style="background: #10b981; color: white; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background: #ef4444; color: white; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-users-cog"></i> Total Admins</h3>
                    <div class="number">{{ $counts['total_admins'] }}</div>
                    <div class="change">{{ $counts['super_admins'] }} Super, {{ $counts['department_admins'] }} Dept</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
                    <div class="number">{{ $counts['announcements'] }}</div>
                    <div class="change">Published this month</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-calendar-alt"></i> Events</h3>
                    <div class="number">{{ $counts['events'] }}</div>
                    <div class="change">Active content</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-newspaper"></i> News</h3>
                    <div class="number">{{ $counts['news'] }}</div>
                    <div class="change">Published articles</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-chalkboard-teacher"></i> Faculty</h3>
                    <div class="number">{{ $counts['faculty'] }}</div>
                    <div class="change">Registered faculty</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-user-graduate"></i> Students</h3>
                    <div class="number">{{ $counts['students'] }}</div>
                    <div class="change"> Registered Students</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-layer-group"></i> Total Contents</h3>
                    <div class="number">{{ $systemStats['total_content'] }}</div>
                    <div class="change">{{ $systemStats['content_this_month'] }} this month</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="chart-container">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="{{ route('superadmin.admins.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h4>Add Admin</h4>
                        <p>Create new admin account</p>
                    </a>
                    <a href="{{ route('superadmin.department-admins.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h4>Department Admin</h4>
                        <p>Create department admin</p>
                    </a>
                    <a href="{{ route('superadmin.announcements.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h4>New Announcement</h4>
                        <p>Create campus announcement</p>
                    </a>
                    <a href="{{ route('superadmin.events.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h4>Schedule Event</h4>
                        <p>Add new campus event</p>
                    </a>
                    <a href="{{ route('superadmin.news.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h4>Publish News</h4>
                        <p>Share latest news</p>
                    </a>
                </div>
            </div>

            <!-- Charts -->
            <div class="chart-container">
                <h2><i class="fas fa-chart-line"></i> System Activity (Last 7 Days)</h2>
                <div class="chart-wrapper">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced Activity Chart with Professional Styling - Same as Office Admin
        const ctx = document.getElementById('activityChart').getContext('2d');

        // Create gradient backgrounds using superadmin theme colors
        const announcementsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        announcementsGradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        announcementsGradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

        const eventsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        eventsGradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
        eventsGradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');

        const newsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        newsGradient.addColorStop(0, 'rgba(245, 158, 11, 0.3)');
        newsGradient.addColorStop(1, 'rgba(245, 158, 11, 0.05)');

        const studentsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        studentsGradient.addColorStop(0, 'rgba(239, 68, 68, 0.3)');
        studentsGradient.addColorStop(1, 'rgba(239, 68, 68, 0.05)');

        const facultyGradient = ctx.createLinearGradient(0, 0, 0, 400);
        facultyGradient.addColorStop(0, 'rgba(139, 92, 246, 0.3)');
        facultyGradient.addColorStop(1, 'rgba(139, 92, 246, 0.05)');

        const activityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: '📢 Announcements',
                        data: @json($chartData['announcements']),
                        borderColor: '#3b82f6',
                        backgroundColor: announcementsGradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '📅 Events',
                        data: @json($chartData['events']),
                        borderColor: '#10b981',
                        backgroundColor: eventsGradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '📰 News',
                        data: @json($chartData['news']),
                        borderColor: '#f59e0b',
                        backgroundColor: newsGradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '🎓 Students',
                        data: @json($chartData['students']),
                        borderColor: '#ef4444',
                        backgroundColor: studentsGradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '👨‍🏫 Faculty',
                        data: @json($chartData['faculty']),
                        borderColor: '#8b5cf6',
                        backgroundColor: facultyGradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#8b5cf6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                },
                hover: {
                    animationDuration: 1500
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '600',
                                family: 'Segoe UI'
                            },
                            color: '#6b7280',
                            generateLabels: function(chart) {
                                const datasets = chart.data.datasets;
                                return datasets.map((dataset, i) => ({
                                    text: dataset.label,
                                    fillStyle: dataset.borderColor,
                                    strokeStyle: dataset.borderColor,
                                    lineWidth: 3,
                                    pointStyle: 'circle',
                                    index: i
                                }));
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#f9fafb',
                        bodyColor: '#f3f4f6',
                        borderColor: 'rgba(107, 114, 128, 0.3)',
                        borderWidth: 1,
                        cornerRadius: 12,
                        displayColors: true,
                        usePointStyle: true,
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            title: function(tooltipItems) {
                                return tooltipItems[0].label.replace(/[📢📅📰🎓👨‍🏫]/g, '').trim();
                            },
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y} items`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(156, 163, 175, 0.1)',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 11,
                                weight: '500'
                            },
                            padding: 10
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(156, 163, 175, 0.1)',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 11,
                                weight: '500'
                            },
                            padding: 10,
                            callback: function(value) {
                                return value + ' items';
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    point: {
                        hoverBorderWidth: 4,
                        hoverShadowOffsetX: 0,
                        hoverShadowOffsetY: 4,
                        hoverShadowBlur: 10,
                        hoverShadowColor: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            activityChart.resize();
        });

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
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

        // SweetAlert logout functionality
        async function handleLogout() {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'You will be logged out of your account.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });
            
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Logging out...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Create and submit logout form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('superadmin.logout') }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // =================================================================
        // GPS LOCATION CAPTURE - Get exact location from device
        // =================================================================
        function captureGPSLocation() {
            // Check if geolocation is supported
            if (!navigator.geolocation) {
                console.log('Geolocation is not supported by this browser.');
                return;
            }

            // Request GPS coordinates
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    console.log('GPS Location captured:', {
                        latitude: latitude,
                        longitude: longitude,
                        accuracy: accuracy + ' meters'
                    });

                    // Send coordinates to server
                    fetch('{{ route('admin.update-gps-location') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: latitude,
                            longitude: longitude
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('GPS location updated successfully:', data.location);
                            
                            // Show success notification (optional, subtle)
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Exact location captured',
                                text: 'Your GPS location has been recorded',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            console.error('Failed to update GPS location:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error sending GPS coordinates:', error);
                    });
                },
                function(error) {
                    // Handle errors
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            console.log('User denied GPS location permission.');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            console.log('GPS location information is unavailable.');
                            break;
                        case error.TIMEOUT:
                            console.log('GPS location request timed out.');
                            break;
                        default:
                            console.log('An unknown error occurred getting GPS location.');
                    }
                },
                {
                    enableHighAccuracy: true,  // Request high accuracy GPS
                    timeout: 10000,            // 10 second timeout
                    maximumAge: 0              // Don't use cached position
                }
            );
        }

        // Capture GPS location on page load
        window.addEventListener('load', function() {
            // Wait 2 seconds before requesting GPS to avoid overwhelming the user
            setTimeout(function() {
                captureGPSLocation();
            }, 2000);
        });

        // =================================================================
        // PREVENT BACK BUTTON ACCESS AFTER LOGOUT
        // =================================================================
        
        // Check authentication status on page load
        window.addEventListener('load', function() {
            @guest('admin')
                // If user is not authenticated, redirect immediately
                window.location.href = "{{ route('login', ['type' => 'superadmin']) }}";
            @endguest
        });

        // Prevent caching and back button access
        window.addEventListener('pageshow', function(event) {
            // Check if page was loaded from cache (back button)
            if (event.persisted || window.performance && window.performance.navigation.type === 2) {
                // Verify authentication status
                fetch('{{ route('superadmin.dashboard') }}', {
                    method: 'HEAD',
                    credentials: 'same-origin'
                }).then(response => {
                    // If unauthorized (401, 403, or redirected to login), redirect
                    if (!response.ok || response.redirected) {
                        window.location.href = "{{ route('login', ['type' => 'superadmin']) }}";
                    }
                }).catch(() => {
                    // On error, redirect to login
                    window.location.href = "{{ route('login', ['type' => 'superadmin']) }}";
                });
            }
        });

        // Clear browser history on logout
        if (window.history && window.history.pushState) {
            window.history.pushState(null, null, window.location.href);
            window.addEventListener('popstate', function() {
                window.history.pushState(null, null, window.location.href);
            });
        }

        // Disable browser back button after logout
        (function() {
            if (window.history && window.history.pushState) {
                window.addEventListener('load', function() {
                    window.history.pushState({noBack: true}, '');
                });
                
                window.addEventListener('popstate', function(event) {
                    if (event.state && event.state.noBack) {
                        window.history.pushState({noBack: true}, '');
                        // Verify if still authenticated
                        @guest('admin')
                            window.location.href = "{{ route('login', ['type' => 'superadmin']) }}";
                        @endguest
                    }
                });
            }
        })();
    </script>
</body>
</html>



