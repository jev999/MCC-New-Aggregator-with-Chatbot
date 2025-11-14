<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MCC News Aggregator')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/mcc_logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/mcc_logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/mcc_logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style @nonce>
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

        .office-info {
            color: white;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            font-weight: 400;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0.5rem 0;
        }

        .sidebar-menu li {
            margin: 0.25rem 0;
        }

        .sidebar-menu a, .sidebar-menu button {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .sidebar-menu a:hover, .sidebar-menu button:hover,
        .sidebar-menu a.active {
            background: #333333;
            color: white;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
            max-width: calc(100vw - 280px);
            overflow-x: hidden;
            box-sizing: border-box;
        }

        .header {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
            max-width: 100%;
            overflow: hidden;
            box-sizing: border-box;
        }

        .dashboard-card {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
        }

        .dashboard-card h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dashboard-card .count {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .analytics-section {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .analytics-section h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            max-width: 100%;
            overflow: hidden;
            box-sizing: border-box;
        }

        .chart-card {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        .chart-card h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

            .header {
                padding: 1.5rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
                gap: 1rem;
                max-width: 100%;
            }

            .dashboard-card {
                padding: 1.5rem;
            }

            .charts-container {
                grid-template-columns: 1fr;
                gap: 1rem;
                max-width: 100%;
            }
        }

        @media (max-width: 640px) {
            .main-content {
                padding: 0.5rem;
            }

            .header {
                padding: 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .dashboard-card {
                padding: 1rem;
            }

            .analytics-section {
                padding: 1rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    @yield('styles')
    @stack('styles')
</head>
<body>
    @yield('content')

    <script @nonce>
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
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
