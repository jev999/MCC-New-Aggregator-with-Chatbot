<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>{{ $office }} Office Dashboard - MCC News Aggregator</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 80px;
            --transition-speed: 0.3s;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --card-padding: 1.5rem;
            
            /* Color variables based on office */
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
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
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
            -webkit-overflow-scrolling: touch;
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
            min-height: 44px;
            touch-action: manipulation;
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

   /* Enhanced logout button styling */
.header .logout-btn {
    color: var(--text-muted);
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    min-height: 44px;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.header .logout-btn:hover {
    color: var(--primary-color);
    background: var(--primary-light);
}

.header .logout-btn:active {
    transform: scale(0.98);
}

.header .logout-btn i {
    font-size: 1rem;
}

/* Touch device logout button */
@media (hover: none) and (pointer: coarse) {
    .header .logout-btn:hover {
        color: var(--text-muted);
        background: transparent;
    }
    
    .header .logout-btn:active {
        color: var(--primary-color);
        background: var(--primary-light);
    }
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
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header h1 i {
            color: var(--primary-color);
            font-size: 1.8rem;
        }

        .header p {
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 400;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--bg-primary);
            padding: var(--card-padding);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s ease;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-icon.announcements {
            background: var(--primary-gradient);
        }

        .stat-icon.events {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
        }

        .stat-icon.news {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .stat-icon.total {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-icon.students {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-note {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
            font-weight: 500;
        }

        /* NSTP Notice Styling */
        .nstp-notice {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 1px solid #10b981;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
        }

        .notice-icon {
            background: #10b981;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.25rem;
        }

        .notice-content h4 {
            color: #065f46;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .notice-content p {
            color: #047857;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0;
        }

        .stat-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }

        .stat-content p {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .chart-container {
            background: var(--bg-primary);
            padding: var(--card-padding);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            position: relative;
            height: 380px;
        }

        .chart-container h3 {
            color: var(--text-primary);
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-container h3 i {
            color: var(--primary-color);
        }

        .chart-wrapper {
            position: relative;
            height: 280px;
            width: 100%;
        }

        /* Quick Actions */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .chart-wrapper {
            height: 300px;
            position: relative;
            margin-top: 1rem;
        }

        .quick-action-card {
            background: var(--bg-primary);
            padding: 1.25rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
            text-align: center;
            display: block;
            border: 1px solid var(--border-light);
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            min-height: 44px;
        }

        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-light);
            text-decoration: none;
            color: var(--text-primary);
        }

        .quick-action-card:active {
            transform: scale(0.98);
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .stat-card:hover {
                transform: none;
            }
            
            .quick-action-card:hover {
                transform: none;
            }
            
            .stat-card:active {
                transform: scale(0.98);
            }
        }

        .quick-action-card .action-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            color: var(--primary-color);
            transition: transform 0.3s ease;
        }

        .quick-action-card:hover .action-icon {
            transform: scale(1.1);
        }

        .quick-action-card h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .quick-action-card p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Enhanced Mobile Responsive Design */
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        /* Tablet and Large Mobile (768px and below) */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
                padding-top: 4rem;
            }

            .header {
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .header h1 {
                font-size: 1.4rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .header h1 i {
                font-size: 1.5rem;
            }

            .header > div {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .logout-btn {
                align-self: flex-end;
                min-height: 44px;
                padding: 0.75rem 1rem !important;
                touch-action: manipulation;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 1.2rem;
            }

            .stat-content h3 {
                font-size: 1.3rem;
            }

            .nstp-notice {
                flex-direction: column;
                text-align: center;
                padding: 1.25rem;
            }

            .notice-icon {
                align-self: center;
            }

            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .quick-action-card {
                padding: 1rem;
                min-height: 120px;
            }

            .quick-action-card .action-icon {
                font-size: 1.75rem;
            }

            .chart-container {
                height: 320px;
                padding: 1rem;
            }

            .chart-wrapper {
                height: 220px;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        /* Medium Mobile (576px and below) */
        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
                padding-top: 4rem;
            }

            .header {
                padding: 0.875rem;
                border-radius: 8px;
            }

            .header h1 {
                font-size: 1.25rem;
            }

            .header p {
                font-size: 0.9rem;
            }

            .stats-grid {
                gap: 0.875rem;
            }

            .stat-card {
                padding: 1rem;
                gap: 0.75rem;
            }

            .stat-icon {
                width: 44px;
                height: 44px;
                font-size: 1.1rem;
            }

            .stat-content h3 {
                font-size: 1.2rem;
            }

            .stat-content p {
                font-size: 0.85rem;
            }

            .chart-container {
                height: 300px;
                padding: 0.875rem;
            }

            .chart-container h3 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .chart-wrapper {
                height: 200px;
            }

            .quick-actions-grid {
                gap: 0.875rem;
            }

            .quick-action-card {
                padding: 0.875rem;
                min-height: 110px;
            }

            .quick-action-card .action-icon {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }

            .quick-action-card h4 {
                font-size: 0.9rem;
            }

            .quick-action-card p {
                font-size: 0.8rem;
            }
        }

        /* Small Mobile (480px and below) */
        @media (max-width: 480px) {
            .main-content {
                padding: 0.5rem;
                padding-top: 3.5rem;
            }

            .mobile-menu-btn {
                top: 0.75rem;
                left: 0.75rem;
                padding: 0.625rem;
            }

            .header {
                padding: 0.75rem;
                margin-bottom: 0.875rem;
            }

            .header h1 {
                font-size: 1.1rem;
            }

            .header h1 i {
                font-size: 1.3rem;
            }

            .header p {
                font-size: 0.85rem;
            }

            .logout-btn {
                font-size: 0.85rem !important;
                padding: 0.625rem 0.875rem !important;
            }

            .stats-grid {
                gap: 0.75rem;
            }

            .stat-card {
                padding: 0.875rem;
                gap: 0.625rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .stat-content h3 {
                font-size: 1.1rem;
            }

            .stat-content p {
                font-size: 0.8rem;
            }

            .stat-note {
                font-size: 0.7rem;
            }

            .nstp-notice {
                padding: 1rem;
            }

            .notice-icon {
                width: 36px;
                height: 36px;
                font-size: 1.1rem;
            }

            .notice-content h4 {
                font-size: 1rem;
            }

            .notice-content p {
                font-size: 0.85rem;
            }

            .quick-actions-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .quick-action-card {
                padding: 0.75rem;
                min-height: 100px;
            }

            .quick-action-card .action-icon {
                font-size: 1.4rem;
                margin-bottom: 0.5rem;
            }

            .quick-action-card h4 {
                font-size: 0.85rem;
                margin-bottom: 0.25rem;
            }

            .quick-action-card p {
                font-size: 0.75rem;
            }

            .chart-container {
                height: 280px;
                padding: 0.75rem;
            }

            .chart-container h3 {
                font-size: 1rem;
                margin-bottom: 0.875rem;
            }

            .chart-wrapper {
                height: 180px;
            }
        }

        /* Extra Small Mobile (360px and below) */
        @media (max-width: 360px) {
            .main-content {
                padding: 0.375rem;
                padding-top: 3.25rem;
            }

            .mobile-menu-btn {
                top: 0.5rem;
                left: 0.5rem;
                padding: 0.5rem;
                min-height: 40px;
                min-width: 40px;
            }

            .header {
                padding: 0.625rem;
                margin-bottom: 0.75rem;
            }

            .header h1 {
                font-size: 1rem;
            }

            .header h1 i {
                font-size: 1.2rem;
            }

            .header p {
                font-size: 0.8rem;
            }

            .logout-btn {
                font-size: 0.8rem !important;
                padding: 0.5rem 0.75rem !important;
            }

            .stats-grid {
                gap: 0.625rem;
            }

            .stat-card {
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }

            .stat-content h3 {
                font-size: 1rem;
            }

            .stat-content p {
                font-size: 0.75rem;
            }

            .stat-note {
                font-size: 0.65rem;
            }

            .nstp-notice {
                padding: 0.875rem;
            }

            .notice-icon {
                width: 32px;
                height: 32px;
                font-size: 1rem;
            }

            .notice-content h4 {
                font-size: 0.9rem;
            }

            .notice-content p {
                font-size: 0.8rem;
            }

            .quick-actions-grid {
                gap: 0.625rem;
            }

            .quick-action-card {
                padding: 0.625rem;
                min-height: 90px;
            }

            .quick-action-card .action-icon {
                font-size: 1.25rem;
                margin-bottom: 0.375rem;
            }

            .quick-action-card h4 {
                font-size: 0.8rem;
                margin-bottom: 0.2rem;
            }

            .quick-action-card p {
                font-size: 0.7rem;
            }

            .chart-container {
                height: 260px;
                padding: 0.625rem;
            }

            .chart-container h3 {
                font-size: 0.9rem;
                margin-bottom: 0.75rem;
            }

            .chart-wrapper {
                height: 160px;
            }

            .sidebar {
                width: 260px;
            }

            .sidebar-header {
                padding: 1.5rem 1.25rem;
            }

            .sidebar-header h3 {
                font-size: 1.1rem;
            }

            .sidebar-menu a {
                padding: 0.75rem 1.25rem;
                font-size: 0.9rem;
            }
        }

        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: var(--bg-sidebar);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 44px;
            min-width: 44px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-menu-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
        }

        /* Active state for mobile menu button */
        .mobile-menu-btn:active {
            transform: scale(0.95);
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            opacity: 0;
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Enhanced Typography */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            line-height: 1.25;
        }

        /* Smooth transitions */
        a, button, .stat-card, .quick-action-card {
            transition: all 0.2s ease-out;
        }

        /* Enhanced accessibility and touch support */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* High DPI display optimizations */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .stat-icon, .notice-icon, .action-icon {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }

        /* Landscape mobile optimizations */
        @media (max-width: 768px) and (orientation: landscape) {
            .main-content {
                padding-top: 3rem;
            }
            
            .mobile-menu-btn {
                top: 0.5rem;
            }
            
            .header {
                padding: 0.75rem;
            }
            
            .chart-container {
                height: 250px;
            }
            
            .chart-wrapper {
                height: 180px;
            }
        }

        .chart-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            animation: chartSlideIn 0.8s ease-out;
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
            background: var(--primary-gradient);
            border-radius: 16px 16px 0 0;
        }

        .chart-wrapper {
            position: relative;
            height: 320px;
            width: 100%;
            padding: 1rem;
        }

        /* Enhanced chart title */
        .chart-container h3 {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1.5rem;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chart-container h3 i {
            color: var(--primary-color);
            font-size: 1.4rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
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
                        <i class="fas fa-flag"></i> NSTP Office
                    @elseif($office === 'SSC')
                        <i class="fas fa-users"></i> Student Council
                    @elseif($office === 'GUIDANCE')
                        <i class="fas fa-heart"></i> Guidance Office
                    @elseif($office === 'REGISTRAR')
                        <i class="fas fa-file-alt"></i> Registrar Office
                    @elseif($office === 'CLINIC')
                        <i class="fas fa-stethoscope"></i> Health Clinic
                    @else
                        <i class="fas fa-briefcase"></i> {{ $office }} Office
                    @endif
                </h3>
                <div class="office-info">{{ $admin->username }}</div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('office-admin.dashboard') }}" class="active">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a></li>
                <li><a href="{{ route('office-admin.announcements.index') }}">
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
            <!-- In the header div (replace the current header) -->
<div class="header">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1>
                @if($office === 'NSTP')
                    <i class="fas fa-flag"></i> NSTP Office Dashboard
                @elseif($office === 'SSC')
                    <i class="fas fa-users"></i> Student Council Dashboard
                @elseif($office === 'GUIDANCE')
                    <i class="fas fa-heart"></i> Guidance Office Dashboard
                @elseif($office === 'REGISTRAR')
                    <i class="fas fa-file-alt"></i> Registrar Dashboard
                @elseif($office === 'CLINIC')
                    <i class="fas fa-stethoscope"></i> Clinic Dashboard
                @else
                    <i class="fas fa-briefcase"></i> Office Dashboard
                @endif
            </h1>
            <p>Welcome back, {{ $admin->username }}! Here's what's happening today.</p>
        </div>
        <button onclick="handleLogout()" class="logout-btn" style="background: none; border: none; cursor: pointer;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</div>

            @if($office === 'NSTP')
                <!-- NSTP Special Notice -->
                <div class="nstp-notice">
                    <div class="notice-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="notice-content">
                        <h4>NSTP Content Visibility</h4>
                        <p>All your announcements, events, and news are automatically visible to <strong>1st year students across all departments</strong> (BSIT, BSBA, BEED, BSHM, BSED). This ensures NSTP program information reaches all eligible students.</p>
                    </div>
                </div>
            @endif

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon announcements">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $counts['announcements'] }}</h3>
                        <p>Published Announcements</p>
                        @if($office === 'NSTP')
                            <small class="stat-note">For 1st Year Students Only</small>
                        @endif
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon events">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $counts['events'] }}</h3>
                        <p>Upcoming Events</p>
                        @if($office === 'NSTP')
                            <small class="stat-note">For 1st Year Students Only</small>
                        @endif
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon news">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $counts['news'] }}</h3>
                        <p>News Articles</p>
                        @if($office === 'NSTP')
                            <small class="stat-note">For 1st Year Students Only</small>
                        @endif
                    </div>
                </div>

                @if($office === 'NSTP')
                    <div class="stat-card">
                        <div class="stat-icon students">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $counts['first_year_students'] ?? 0 }}</h3>
                            <p>1st Year Students</p>
                            <small class="stat-note">Your Target Audience</small>
                        </div>
                    </div>
                @else
                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $officeStats['total_content'] }}</h3>
                            <p>Total Content</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Charts Section -->
            <div class="content-grid">
                <!-- Pie Chart -->
                <div class="chart-container">
                    <h2><i class="fas fa-chart-pie"></i> Content Distribution</h2>
                    <div class="chart-wrapper">
                        <canvas id="contentDistributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="chart-container">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="quick-actions-grid">
                    <a href="{{ route('office-admin.announcements.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h4>New Announcement</h4>
                        <p>Create office announcement</p>
                    </a>
                    <a href="{{ route('office-admin.events.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h4>Schedule Event</h4>
                        <p>Add office event</p>
                    </a>
                    <a href="{{ route('office-admin.news.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h4>Publish News</h4>
                        <p>Share office news</p>
                    </a>
                    <a href="#" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h4>Settings</h4>
                        <p>Manage preferences</p>
                    </a>
                </div>
            </div>

            <div class="content-grid">
                <div class="chart-container">
                    <h3><i class="fas fa-chart-line"></i> Content Activity (Last 7 Days)</h3>
                    <div class="chart-wrapper">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced mobile sidebar toggle with touch support
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            
            sidebar.classList.toggle('active');
            
            // Add overlay when sidebar is active
            if (sidebar.classList.contains('active')) {
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.onclick = toggleSidebar;
                
                // Add touch event for better mobile support
                overlay.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    toggleSidebar();
                }, { passive: false });
                
                body.appendChild(overlay);
                body.style.overflow = 'hidden'; // Prevent background scrolling
            } else {
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) {
                    overlay.remove();
                }
                body.style.overflow = ''; // Restore scrolling
            }
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !menuBtn.contains(e.target)) {
                toggleSidebar();
            }
        });
        
        // Handle window resize for sidebar
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                document.querySelector('.sidebar-overlay')?.remove();
                document.body.style.overflow = '';
            }
        });
        
        // Prevent sidebar from closing when clicking inside it
        document.querySelector('.sidebar').addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Initialize Content Distribution Pie Chart
        document.addEventListener('DOMContentLoaded', function() {
            // Content Distribution Pie Chart
            const ctxPie = document.getElementById('contentDistributionChart').getContext('2d');
            
            // Add default values of 1 to ensure the chart always displays something
            const announcementCount = {{ $counts['announcements'] ?? 0 }} || 1;
            const eventCount = {{ $counts['events'] ?? 0 }} || 1;
            const newsCount = {{ $counts['news'] ?? 0 }} || 1;
            
            window.contentDistributionChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['Announcements', 'Events', 'News'],
                    datasets: [{
                        data: [
                            announcementCount, 
                            eventCount, 
                            newsCount
                        ],
                        backgroundColor: [
                            '#3b82f6',  // Blue for announcements
                            '#10b981',  // Green for events
                            '#f59e0b',  // Orange for news
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                boxWidth: 12,
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });

        // Enhanced Activity Chart with Modern Styling
        const ctx = document.getElementById('activityChart').getContext('2d');

        // Create gradient backgrounds
        const announcementsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        announcementsGradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        announcementsGradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

        const eventsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        eventsGradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
        eventsGradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');

        const newsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        newsGradient.addColorStop(0, 'rgba(245, 158, 11, 0.3)');
        newsGradient.addColorStop(1, 'rgba(245, 158, 11, 0.05)');

        window.activityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: '📢 Announcements',
                        data: {!! json_encode($chartData['announcements']) !!},
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
                        data: {!! json_encode($chartData['events']) !!},
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
                        data: {!! json_encode($chartData['news']) !!},
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
                                family: 'Inter'
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
                        borderColor: 'rgba(75, 85, 99, 0.3)',
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
                                return tooltipItems[0].label.replace(/[📢📅📰]/g, '').trim();
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

        // Enhanced window resize handler
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (typeof activityChart !== 'undefined') {
                    activityChart.resize();
                }
                if (typeof contentDistributionChart !== 'undefined') {
                    contentDistributionChart.resize();
                }
            }, 250);
        });
        
        // Touch event optimizations for mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Add touch feedback for interactive elements
            const interactiveElements = document.querySelectorAll('.stat-card, .quick-action-card, .mobile-menu-btn, .logout-btn');
            
            interactiveElements.forEach(function(element) {
                element.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                }, { passive: true });
                
                element.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }, { passive: true });
            });
            
            // Optimize chart rendering for mobile
            if (window.innerWidth <= 768) {
                Chart.defaults.font.size = 10;
                Chart.defaults.elements.point.radius = 4;
                Chart.defaults.elements.point.hoverRadius = 6;
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
                form.action = '{{ route('office-admin.logout') }}';
                
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
        function syncLoginLogLocation(latitude, longitude, accuracy) {
            fetch('{{ route('admin-login-location.precise') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    latitude: latitude,
                    longitude: longitude,
                    accuracy: accuracy
                })
            }).catch(error => {
                console.warn('Admin login precise location sync failed:', error);
            });
        }

        function captureGPSLocation() {
            if (!navigator.geolocation) {
                console.log('Geolocation is not supported by this browser.');
                return;
            }

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
                            syncLoginLogLocation(latitude, longitude, accuracy);
                        }
                    })
                    .catch(error => {
                        console.error('Error sending GPS coordinates:', error);
                    });
                },
                function(error) {
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
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // Capture GPS location on page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                captureGPSLocation();
            }, 2000);
        });
    </script>
</body>
</html>