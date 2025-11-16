<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Department Admin Dashboard - {{ $admin->department }} - MCC Portal</title>
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
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #e2e8f0 100%);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Mobile Sidebar Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
            background: linear-gradient(135deg, #000000, #1a1a1a);
            color: white;
            border: none;
            border-radius: 10px;
            width: 44px;
            height: 44px;
            font-size: 1.25rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:active {
            transform: scale(0.95);
        }

        .sidebar {
            width: 320px;
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #2d2d2d 100%);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.02) 50%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%); opacity: 0; }
            50% { transform: translateX(100%); opacity: 1; }
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            color: white;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #ffffff, #e5e7eb, #ffffff);
            animation: headerShimmer 2s ease-in-out infinite;
        }

        @keyframes headerShimmer {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        .sidebar-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #e5e7eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            margin: 0;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .sidebar-header h3 i {
            font-size: 1.5rem;
            color: #ffffff;
            background: linear-gradient(135deg, #ffffff 0%, #d1d5db 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .sidebar-header .dept-info {
            font-size: 0.85rem;
            margin-top: 0.5rem;
            opacity: 0.85;
            color: #d1d5db;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
            line-height: 1.3;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1.5rem 0;
        }

        .sidebar-menu li {
            margin: 0.25rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            color: #d1d5db;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            gap: 1rem;
            position: relative;
            border-radius: 0 25px 25px 0;
            margin: 0.25rem 0;
            overflow: hidden;
            letter-spacing: 0.3px;
            min-height: 44px;
        }

        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #ffffff, #e5e7eb);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 100%);
            color: white;
            transform: translateX(8px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .sidebar-menu a:hover::before,
        .sidebar-menu a.active::before {
            transform: scaleY(1);
        }

        .sidebar-menu a i {
            width: 18px;
            height: 18px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            flex-shrink: 0;
        }

        .sidebar-menu a:hover i,
        .sidebar-menu a.active i {
            transform: scale(1.2) rotate(5deg);
            color: #ffffff;
        }

        .sidebar-menu a span {
            transition: all 0.3s ease;
            flex: 1;
            text-align: left;
            line-height: 1.4;
        }

        .sidebar-menu a:hover span,
        .sidebar-menu a.active span {
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #ffffff;
        }

        .main-content {
            flex: 1;
            margin-left: 320px;
            padding: 2rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #e2e8f0 100%);
            min-height: 100vh;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1.5rem 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #000000, #4a4a4a, #000000);
        }

        .header h1 {
            color: #1a1a1a;
            font-size: 2.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0;
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #4a4a4a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .header h1 i {
            color: #000000;
            font-size: 2.8rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
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
            min-height: 44px;
            touch-action: manipulation;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            background: linear-gradient(135deg, #000000, #4a4a4a);
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
            background: linear-gradient(135deg, #000000, #4a4a4a);
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
            background: linear-gradient(135deg, #000000, #4a4a4a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chart-container h2 i {
            color: #000000;
            font-size: 1.6rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
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
            min-height: 44px;
            touch-action: manipulation;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(31, 38, 135, 0.5);
            color: #333;
        }

        .quick-action-card .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #10b981;
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

        .department-badge {
            background: linear-gradient(135deg, #000000, #1a1a1a, #4a4a4a);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2), 0 2px 8px rgba(0, 0, 0, 0.1);
            letter-spacing: 0.3px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .department-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            animation: badgeShimmer 3s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes badgeShimmer {
            0%, 100% { transform: translateX(-100%); opacity: 0; }
            50% { transform: translateX(100%); opacity: 1; }
        }

        .department-badge i {
            font-size: 1.3rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        /* Mobile Responsiveness */
        @media (max-width: 1200px) {
            .sidebar {
                width: 280px;
            }
            
            .main-content {
                margin-left: 280px;
            }
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-top: 5rem;
            }
            
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1.25rem 1.5rem;
                margin-top: 1rem;
            }

            .header h1 {
                font-size: 1.75rem;
            }

            .header h1 i {
                font-size: 2rem;
            }

            .logout-btn {
                width: 100%;
                justify-content: center;
                padding: 0.75rem 1.25rem;
                font-size: 0.95rem;
            }

            .logout-btn:active {
                transform: scale(0.98);
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .stat-card .number {
                font-size: 2rem;
            }

            .chart-wrapper {
                height: 280px;
            }

            .quick-actions-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .quick-action-card {
                padding: 1.25rem;
            }

            .quick-action-card:active {
                transform: scale(0.98);
            }

            .department-badge {
                font-size: 1rem;
                padding: 0.625rem 1.25rem;
                width: 100%;
                justify-content: center;
                text-align: center;
            }
            
            .chart-container {
                padding: 1.25rem;
            }
            
            .chart-container h2 {
                font-size: 1.2rem;
                margin-bottom: 1.25rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
                padding-top: 5rem;
            }

            .header {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .header h1 i {
                font-size: 1.75rem;
            }

            .logout-btn {
                padding: 0.625rem 1rem;
                font-size: 0.9rem;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-card .number {
                font-size: 1.75rem;
            }

            .chart-wrapper {
                height: 260px;
            }

            .chart-container {
                padding: 1.25rem;
            }

            .chart-container h2 {
                font-size: 1.2rem;
                margin-bottom: 1.25rem;
            }

            .quick-action-card {
                padding: 1rem;
            }

            .quick-action-card .action-icon {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }

            .quick-action-card h4 {
                font-size: 1rem;
            }

            .quick-action-card p {
                font-size: 0.85rem;
            }

            .department-badge {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }

            .sidebar {
                width: 260px;
            }

            .sidebar-header {
                padding: 1.5rem 1.25rem;
            }

            .sidebar-header h3 {
                font-size: 1.3rem;
            }

            .sidebar-menu a {
                padding: 0.875rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.5rem;
                padding-top: 4.5rem;
            }

            .header {
                padding: 0.875rem;
                margin-bottom: 1.25rem;
            }

            .header h1 {
                font-size: 1.3rem;
            }

            .header h1 i {
                font-size: 1.5rem;
            }

            .logout-btn {
                padding: 0.5rem 0.875rem;
                font-size: 0.85rem;
                border-radius: 8px;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-card h3 {
                font-size: 0.8rem;
            }

            .stat-card .number {
                font-size: 1.5rem;
            }

            .chart-wrapper {
                height: 240px;
            }

            .chart-container {
                padding: 1rem;
            }

            .chart-container h2 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .quick-action-card {
                padding: 0.875rem;
            }

            .quick-action-card .action-icon {
                font-size: 1.75rem;
                margin-bottom: 0.5rem;
            }

            .quick-action-card h4 {
                font-size: 0.95rem;
            }

            .quick-action-card p {
                font-size: 0.8rem;
            }

            .department-badge {
                font-size: 0.85rem;
                padding: 0.4rem 0.875rem;
            }

            .sidebar {
                width: 240px;
            }

            .sidebar-header {
                padding: 1.25rem 1rem;
            }

            .sidebar-header h3 {
                font-size: 1.2rem;
            }

            .sidebar-header .dept-info {
                font-size: 0.8rem;
            }

            .sidebar-menu a {
                padding: 0.75rem 1.25rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 360px) {
            .main-content {
                padding: 0.375rem;
                padding-top: 4rem;
            }

            .header {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }

            .header h1 {
                font-size: 1.1rem;
            }

            .header h1 i {
                font-size: 1.3rem;
            }

            .logout-btn {
                padding: 0.4rem 0.75rem;
                font-size: 0.8rem;
                border-radius: 6px;
            }

            .stat-card {
                padding: 0.875rem;
            }

            .stat-card h3 {
                font-size: 0.75rem;
            }

            .stat-card .number {
                font-size: 1.3rem;
            }

            .chart-wrapper {
                height: 220px;
            }

            .chart-container {
                padding: 0.875rem;
            }

            .chart-container h2 {
                font-size: 1rem;
                margin-bottom: 0.875rem;
            }

            .quick-action-card {
                padding: 0.75rem;
            }

            .quick-action-card .action-icon {
                font-size: 1.5rem;
                margin-bottom: 0.4rem;
            }

            .quick-action-card h4 {
                font-size: 0.9rem;
            }

            .quick-action-card p {
                font-size: 0.75rem;
            }

            .department-badge {
                font-size: 0.8rem;
                padding: 0.35rem 0.75rem;
            }

            .sidebar {
                width: 220px;
            }

            .sidebar-header {
                padding: 1rem 0.875rem;
            }

            .sidebar-header h3 {
                font-size: 1.1rem;
            }

            .sidebar-header .dept-info {
                font-size: 0.75rem;
            }

            .sidebar-menu a {
                padding: 0.625rem 1rem;
                font-size: 0.8rem;
            }
        }

        /* Landscape Mobile Optimization */
        @media (max-width: 768px) and (orientation: landscape) {
            .main-content {
                padding-top: 4rem;
            }

            .header {
                padding: 1rem 1.5rem;
            }

            .chart-wrapper {
                height: 240px;
            }
            
            .sidebar {
                overflow-y: auto;
            }
        }

        /* Touch Device Optimizations */
        @media (hover: none) and (pointer: coarse) {
            .stat-card:hover,
            .quick-action-card:hover {
                transform: none;
            }

            .logout-btn:hover {
                transform: none;
            }

            .sidebar-menu a:hover {
                transform: none;
            }
            
            .stat-card:active,
            .quick-action-card:active {
                transform: scale(0.98);
            }
            
            .logout-btn:active {
                transform: scale(0.98);
            }
            
            .sidebar-menu a:active {
                transform: translateX(4px);
            }
        }

        /* High DPI Display Optimizations */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            body {
                -webkit-font-smoothing: subpixel-antialiased;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Mobile-Responsive SweetAlert Styles */
        .mobile-swal-popup {
            border-radius: 16px !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
        }

        .mobile-swal-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1f2937 !important;
            margin-bottom: 1rem !important;
        }

        .mobile-swal-text {
            font-size: 1rem !important;
            color: #6b7280 !important;
            margin-bottom: 1.5rem !important;
            line-height: 1.5 !important;
        }

        .mobile-swal-actions {
            gap: 0.75rem !important;
            flex-direction: column-reverse !important;
        }

        .mobile-swal-confirm,
        .mobile-swal-cancel {
            min-height: 44px !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            width: 100% !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            touch-action: manipulation !important;
        }

        .mobile-swal-confirm {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            color: white !important;
        }

        .mobile-swal-confirm:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
            transform: translateY(-1px) !important;
        }

        .mobile-swal-confirm:active {
            transform: scale(0.98) !important;
        }

        .mobile-swal-cancel {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb) !important;
            color: #374151 !important;
        }

        .mobile-swal-cancel:hover {
            background: linear-gradient(135deg, #e5e7eb, #d1d5db) !important;
            transform: translateY(-1px) !important;
        }

        .mobile-swal-cancel:active {
            transform: scale(0.98) !important;
        }

        .mobile-swal-loading {
            border-radius: 16px !important;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .mobile-swal-loading-title {
            font-size: 1.1rem !important;
            font-weight: 600 !important;
            color: #1f2937 !important;
            margin-bottom: 1rem !important;
        }

        /* Mobile-specific SweetAlert adjustments */
        @media (max-width: 768px) {
            .mobile-swal-popup {
                margin: 1rem !important;
                max-width: calc(100vw - 2rem) !important;
            }

            .mobile-swal-title {
                font-size: 1.1rem !important;
            }

            .mobile-swal-text {
                font-size: 0.9rem !important;
            }

            .mobile-swal-confirm,
            .mobile-swal-cancel {
                padding: 0.875rem 1.25rem !important;
                font-size: 0.9rem !important;
            }
        }

        @media (max-width: 480px) {
            .mobile-swal-popup {
                margin: 0.75rem !important;
                max-width: calc(100vw - 1.5rem) !important;
            }

            .mobile-swal-title {
                font-size: 1rem !important;
            }

            .mobile-swal-text {
                font-size: 0.85rem !important;
            }

            .mobile-swal-confirm,
            .mobile-swal-cancel {
                padding: 0.75rem 1rem !important;
                font-size: 0.85rem !important;
            }
        }

        @media (max-width: 360px) {
            .mobile-swal-popup {
                margin: 0.5rem !important;
                max-width: calc(100vw - 1rem) !important;
            }

            .mobile-swal-title {
                font-size: 0.95rem !important;
            }

            .mobile-swal-text {
                font-size: 0.8rem !important;
            }

            .mobile-swal-confirm,
            .mobile-swal-cancel {
                padding: 0.625rem 0.875rem !important;
                font-size: 0.8rem !important;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ffffff, #d1d5db);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #f3f4f6, #9ca3af);
        }
        
        /* Mobile overlay for sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(5px);
        }
        
        .sidebar-overlay.mobile-open {
            display: block;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-building"></i> Department Admin</h3>
                <div class="dept-info">{{ $admin->department }} Department</div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('department-admin.dashboard') }}" class="active">
                    <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
                </a></li>
                <li><a href="{{ route('department-admin.announcements.index') }}">
                    <i class="fas fa-bullhorn"></i> <span>Announcements</span>
                </a></li>
                <li><a href="{{ route('department-admin.events.index') }}">
                    <i class="fas fa-calendar-alt"></i> <span>Events</span>
                </a></li>
                <li><a href="{{ route('department-admin.news.index') }}">
                    <i class="fas fa-newspaper"></i> <span>News</span>
                </a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <div>
                    <h1><i class="fas fa-building"></i> {{ $admin->department }} Dashboard</h1>
                    <div class="department-badge">
                        <i class="fas fa-user-shield"></i>
                        Department Administrator: {{ $admin->username }}
                    </div>
                    @php
                        $departmentNames = [
                            'BSIT' => 'Bachelor of Science in Information Technology',
                            'BSBA' => 'Bachelor of Science in Business Administration',
                            'BEED' => 'Bachelor of Elementary Education',
                            'BSHM' => 'Bachelor of Science in Hospitality Management',
                            'BSED' => 'Bachelor of Secondary Education',
                        ];
                        $deptFullName = $departmentNames[$admin->department] ?? null;
                    @endphp
                    @if($deptFullName)
                        <div class="department-badge" style="background: linear-gradient(135deg, #3b82f6, #2563eb); margin-top: 0.5rem;">
                            <i class="fas fa-graduation-cap"></i>
                            Program: {{ $deptFullName }}
                        </div>
                    @endif

                </div>
                <button onclick="handleLogout()" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
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
                    <h3><i class="fas fa-bullhorn"></i> My Announcements</h3>
                    <div class="number">{{ $counts['announcements'] }}</div>
                    <div class="change">Published content</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-calendar-alt"></i> My Events</h3>
                    <div class="number">{{ $counts['events'] }}</div>
                    <div class="change">Scheduled events</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-newspaper"></i> My News</h3>
                    <div class="number">{{ $counts['news'] }}</div>
                    <div class="change">Published articles</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> Department Users</h3>
                    <div class="number">{{ $departmentStats['department_users'] }}</div>
                    <div class="change">{{ $counts['department_students'] }} Students</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="chart-container">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="{{ route('department-admin.announcements.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h4>New Announcement</h4>
                        <p>Create department announcement</p>
                    </a>
                    <a href="{{ route('department-admin.events.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h4>Schedule Event</h4>
                        <p>Add department event</p>
                    </a>
                    <a href="{{ route('department-admin.news.create') }}" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h4>Publish News</h4>
                        <p>Share department news</p>
                    </a>
                </div>
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

            <!-- Activity Chart -->
            <div class="chart-container">
                <h2><i class="fas fa-chart-line"></i> My Content Activity (Last 7 Days)</h2>
                <div class="chart-wrapper">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('mobile-open');
                sidebarOverlay.classList.toggle('mobile-open');
            }
            
            mobileMenuToggle.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);
            
            // Close sidebar when clicking on a menu item on mobile
            if (window.innerWidth <= 768) {
                const menuLinks = document.querySelectorAll('.sidebar-menu a');
                menuLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        sidebar.classList.remove('mobile-open');
                        sidebarOverlay.classList.remove('mobile-open');
                    });
                });
            }
            
            // Close sidebar on window resize if it becomes larger
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('mobile-open');
                }
            });
        });

        // Content Distribution Pie Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctxPie = document.getElementById('contentDistributionChart').getContext('2d');
            
            // Get data from backend
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
                        borderColor: [
                            '#ffffff',
                            '#ffffff',
                            '#ffffff'
                        ],
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: window.innerWidth <= 768 ? 'bottom' : 'right',
                            labels: {
                                padding: 20,
                                boxWidth: 15,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    family: 'Inter',
                                    size: window.innerWidth <= 768 ? 11 : 13,
                                    weight: '500'
                                },
                                color: '#4b5563'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#f9fafb',
                            bodyColor: '#f3f4f6',
                            borderColor: 'rgba(107, 114, 128, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 12,
                            padding: 12,
                            titleFont: {
                                size: window.innerWidth <= 768 ? 12 : 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: window.innerWidth <= 768 ? 11 : 13
                            },
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

        // Enhanced Activity Chart with Professional Styling - Department Admin Theme
        const ctx = document.getElementById('activityChart').getContext('2d');

        // Create gradient backgrounds using department admin theme colors (black/gray)
        const announcementsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        announcementsGradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        announcementsGradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

        const eventsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        eventsGradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
        eventsGradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');

        const newsGradient = ctx.createLinearGradient(0, 0, 0, 400);
        newsGradient.addColorStop(0, 'rgba(245, 158, 11, 0.3)');
        newsGradient.addColorStop(1, 'rgba(245, 158, 11, 0.05)');

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
                        position: window.innerWidth <= 768 ? 'bottom' : 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: window.innerWidth <= 768 ? 10 : 12,
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
                        borderColor: 'rgba(107, 114, 128, 0.3)',
                        borderWidth: 1,
                        cornerRadius: 12,
                        displayColors: true,
                        usePointStyle: true,
                        padding: 12,
                        titleFont: {
                            size: window.innerWidth <= 768 ? 12 : 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: window.innerWidth <= 768 ? 11 : 13
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
                                size: window.innerWidth <= 768 ? 9 : 11,
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
                                size: window.innerWidth <= 768 ? 9 : 11,
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

        // Enhanced window resize handler with debouncing for performance
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                // Resize chart with mobile-specific configurations
                const isMobile = window.innerWidth <= 768;
                
                // Update chart options for mobile
                if (isMobile) {
                    activityChart.options.plugins.legend.position = 'bottom';
                    activityChart.options.plugins.legend.labels.font.size = 10;
                    activityChart.options.scales.x.ticks.font.size = 9;
                    activityChart.options.scales.y.ticks.font.size = 9;
                    activityChart.options.plugins.tooltip.titleFont.size = 12;
                    activityChart.options.plugins.tooltip.bodyFont.size = 11;
                    
                    if (typeof contentDistributionChart !== 'undefined') {
                        contentDistributionChart.options.plugins.legend.position = 'bottom';
                        contentDistributionChart.options.plugins.legend.labels.font.size = 11;
                        contentDistributionChart.options.plugins.tooltip.titleFont.size = 12;
                        contentDistributionChart.options.plugins.tooltip.bodyFont.size = 11;
                    }
                } else {
                    activityChart.options.plugins.legend.position = 'top';
                    activityChart.options.plugins.legend.labels.font.size = 12;
                    activityChart.options.scales.x.ticks.font.size = 11;
                    activityChart.options.scales.y.ticks.font.size = 11;
                    activityChart.options.plugins.tooltip.titleFont.size = 14;
                    activityChart.options.plugins.tooltip.bodyFont.size = 13;
                    
                    if (typeof contentDistributionChart !== 'undefined') {
                        contentDistributionChart.options.plugins.legend.position = 'right';
                        contentDistributionChart.options.plugins.legend.labels.font.size = 13;
                        contentDistributionChart.options.plugins.tooltip.titleFont.size = 14;
                        contentDistributionChart.options.plugins.tooltip.bodyFont.size = 13;
                    }
                }
                
                activityChart.resize();
                activityChart.update('none'); // Update without animation for better performance
                
                // Resize pie chart if it exists
                if (typeof contentDistributionChart !== 'undefined') {
                    contentDistributionChart.resize();
                    contentDistributionChart.update('none');
                }
            }, 250);
        });

        // Active menu item highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const menuLinks = document.querySelectorAll('.sidebar-menu a');
            
            menuLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentPath || 
                    (currentPath.includes(link.getAttribute('href')) && link.getAttribute('href') !== '/')) {
                    link.classList.add('active');
                }
            });
        });

        // SweetAlert logout functionality (aligned with Superadmin dashboard)
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
                // Show loading state
                Swal.fire({
                    title: 'Logging out...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create and submit logout form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('department-admin.logout') }}';

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

        // WiFi-Based Real-Time Location Tracking
        let locationWatchId = null;
        let lastLocationUpdate = null;
        const LOCATION_UPDATE_INTERVAL = 30000; // Update every 30 seconds

        function startWiFiLocationTracking() {
            if (!navigator.geolocation) {
                console.log('Geolocation is not supported by this browser.');
                return;
            }

            // Stop any existing watch
            if (locationWatchId !== null) {
                navigator.geolocation.clearWatch(locationWatchId);
            }

            // Use watchPosition for continuous real-time tracking
            // This uses WiFi access points for better accuracy on desktop/laptop
            locationWatchId = navigator.geolocation.watchPosition(
                function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    const accuracy = position.coords.accuracy;
                    const timestamp = Date.now();

                    // Throttle updates to avoid too many requests
                    if (lastLocationUpdate && (timestamp - lastLocationUpdate) < LOCATION_UPDATE_INTERVAL) {
                        return;
                    }

                    lastLocationUpdate = timestamp;

                    console.log('WiFi-Based Real-Time Location:', {
                        latitude: latitude,
                        longitude: longitude,
                        accuracy: accuracy + ' meters',
                        source: 'WiFi Access Points + Network Triangulation'
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
                            console.log('✓ WiFi-based location updated:', data.location);
                            syncLoginLogLocation(latitude, longitude, accuracy);
                        } else {
                            console.error('Failed to update location:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error sending location coordinates:', error);
                    });
                },
                function(error) {
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            console.warn('Location permission denied. Please enable location access for accurate tracking.');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            console.warn('Location information unavailable. WiFi/GPS may be disabled.');
                            break;
                        case error.TIMEOUT:
                            console.warn('Location request timed out. Retrying...');
                            setTimeout(startWiFiLocationTracking, 5000);
                            break;
                        default:
                            console.warn('An unknown error occurred:', error.message);
                    }
                },
                {
                    enableHighAccuracy: true,      // Prioritize WiFi/cell tower over GPS
                    timeout: 15000,                // 15 second timeout
                    maximumAge: 0                  // Always get fresh position (no cache)
                }
            );

            console.log('✓ WiFi-based real-time location tracking started');
        }

        // Start WiFi location tracking on page load (only if permission was granted)
        window.addEventListener('load', function() {
            // Check if location permission was granted during login
            const locationPermissionGranted = {{ session('admin_location_permission', false) ? 'true' : 'false' }};
            
            if (locationPermissionGranted) {
                setTimeout(function() {
                    startWiFiLocationTracking();
                }, 2000);
            } else {
                console.log('Location permission not granted. Using IP-based tracking via server.');
                // Location will be tracked via IP address on the server side
            }
        });

        // Stop tracking when page is unloaded
        window.addEventListener('beforeunload', function() {
            if (locationWatchId !== null) {
                navigator.geolocation.clearWatch(locationWatchId);
                console.log('Location tracking stopped');
            }
        });
    </script>
</body>
</html>