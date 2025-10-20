<style>
    :root {
        --primary-color: #000000;
        --secondary-color: #ffffff;
        --accent-color: #3b82f6;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --text-muted: #9ca3af;
        --border-color: #e5e7eb;
        --background-light: #f8fafc;
        --background-dark: #1f2937;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        color: var(--text-primary);
        line-height: 1.6;
        min-height: 100vh;
    }

    .dashboard-container {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 280px;
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
        font-size: 1.4rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #e5e7eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
    }

    .sidebar-header h3 i {
        font-size: 1.6rem;
        color: #ffffff;
        background: linear-gradient(135deg, #ffffff 0%, #d1d5db 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }

    .sidebar-header .dept-info {
        font-size: 0.9rem;
        margin-top: 0.5rem;
        opacity: 0.8;
        color: #d1d5db;
        font-weight: 500;
        letter-spacing: 0.5px;
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
        padding: 1rem 1.5rem;
        color: #d1d5db;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        gap: 0.75rem;
        position: relative;
        border-radius: 0 25px 25px 0;
        margin: 0.25rem 0;
        overflow: hidden;
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
        width: 20px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }

    .sidebar-menu a:hover i,
    .sidebar-menu a.active i {
        transform: scale(1.2) rotate(5deg);
        color: #ffffff;
    }

    .sidebar-menu a span {
        transition: all 0.3s ease;
    }

    .sidebar-menu a:hover span,
    .sidebar-menu a.active span {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 2rem;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #e2e8f0 100%);
        min-height: 100vh;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
    }

    /* Mobile Toggle Button */
    .mobile-toggle {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: linear-gradient(135deg, #000000, #4a4a4a);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    .mobile-toggle:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    /* Sidebar Overlay for Mobile */
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

    @media (max-width: 768px) {
        .mobile-toggle {
            display: block;
        }

        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar-overlay.active {
            display: block;
        }

        .main-content {
            margin-left: 0;
            padding: 1rem;
            padding-top: 4rem;
        }

        .dashboard-container {
            margin-left: 0;
            padding: 1rem;
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

    /* Enhanced Page Header */
    .page-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
    }

    .header-title h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-title h1 i {
        background: linear-gradient(135deg, #000000, #4a4a4a);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .header-subtitle {
        color: #6b7280;
        font-size: 1.1rem;
        margin: 0;
        font-weight: 500;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    /* Filter Bar */
    .filter-bar {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 2rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-section {
        flex: 1;
        min-width: 300px;
    }

    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-input-group i {
        position: absolute;
        left: 1rem;
        color: #9ca3af;
        z-index: 1;
    }

    .search-input-group input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .search-input-group input:focus {
        outline: none;
        border-color: #000000;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
    }

    .filter-section {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-section select {
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
        min-width: 140px;
        transition: all 0.3s ease;
    }

    .filter-section select:focus {
        outline: none;
        border-color: #000000;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }

    .stat-card:hover::before {
        transform: translateX(100%);
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
        position: relative;
        z-index: 1;
    }

    .stat-icon.published {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .stat-icon.draft {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .stat-icon.media {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .stat-content h3 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        color: #1a1a1a;
    }

    .stat-content p {
        margin: 0;
        color: #6b7280;
        font-weight: 500;
        font-size: 0.9rem;
    }

    /* Announcements Grid */
    .announcements-container {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .announcements-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    .announcement-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
    }

    .announcement-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.published {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
        border: 1px solid #22c55e;
    }

    .status-badge.draft {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        border: 1px solid #f59e0b;
    }

    .card-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .card-content {
        margin-bottom: 1.5rem;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 0.75rem 0;
        line-height: 1.4;
    }

    .card-description {
        color: #6b7280;
        line-height: 1.6;
        margin: 0 0 1rem 0;
    }

    .media-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .media-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .media-badge.image {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
    }

    .media-badge.video {
        background: linear-gradient(135deg, #fecaca, #fca5a5);
        color: #991b1b;
    }

    .media-badge.csv {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }

    .card-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        font-size: 0.9rem;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn-primary {
        background: linear-gradient(135deg, #000000, #4a4a4a);
        color: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        transform: translateY(-2px);
    }

    .btn-outline {
        background: transparent;
        color: #374151;
        border: 2px solid #d1d5db;
    }

    .btn-outline:hover {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-color: #9ca3af;
        transform: translateY(-2px);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        grid-column: 1 / -1;
    }

    .empty-icon {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1.5rem;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #374151;
        margin: 0 0 0.5rem 0;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 1.1rem;
        margin: 0 0 2rem 0;
    }

    /* Animations */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .header-title h1 {
            font-size: 2rem;
        }

        .header-actions {
            justify-content: stretch;
        }

        .filter-bar {
            flex-direction: column;
            gap: 1rem;
        }

        .search-section {
            min-width: auto;
        }

        .filter-section {
            justify-content: stretch;
        }

        .filter-section select {
            flex: 1;
            min-width: auto;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .announcements-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .card-actions {
            justify-content: stretch;
        }

        .card-actions .btn {
            flex: 1;
            justify-content: center;
        }
    }

    .main-content {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Professional Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 3px solid rgba(255, 255, 255, 0.1);
        border-top: 3px solid #ffffff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Glass Morphism Effects */
    .glass-card {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: var(--radius-xl);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    }

    /* Shimmer Animation */
    @keyframes shimmer {
        0% {
            background-position: -200px 0;
        }
        100% {
            background-position: calc(200px + 100%) 0;
        }
    }

    .shimmer {
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        background-size: 200px 100%;
        animation: shimmer 2s infinite;
    }

    /* Scroll Animations */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .fade-in-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Enhanced Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #64748b, #475569);
        border-radius: 4px;
        transition: background 0.3s ease;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #475569, #334155);
    }

    /* Mobile Responsive Sidebar Toggle */
    .mobile-menu-btn {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1000;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        padding: 0.75rem;
        font-size: 1.25rem;
        cursor: pointer;
        box-shadow: var(--shadow-lg);
        transition: all 0.3s ease;
    }

    .mobile-menu-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    @media (max-width: 1024px) {
        .mobile-menu-btn {
            display: block;
        }
    }

    /* Gradient Text Effects */
    .gradient-text {
        background: linear-gradient(135deg, var(--primary-color), #374151);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
    }

    /* Enhanced Button Hover Effects */
    .btn-hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-hover-lift:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }

    /* Professional Card Animations */
    .card-animate {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-animate:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    /* Status Indicator Animations */
    .status-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    /* Enhanced Form Focus States */
    .form-focus-glow:focus {
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 0 20px rgba(59, 130, 246, 0.2);
        border-color: var(--accent-color);
    }

    /* Professional Notification Styles */
    .notification-slide {
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .notification-slide.show {
        transform: translateX(0);
    }

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        :root {
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --border-color: #374151;
            --background-light: #1f2937;
        }
    }

    /* Print Styles */
    @media print {
        .sidebar,
        .mobile-menu-btn,
        .btn,
        .form-actions {
            display: none !important;
        }
        
        .dashboard-container {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        
        body {
            background: white !important;
            color: black !important;
        }
    }

    /* Accessibility Improvements */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Focus Visible for Better Accessibility */
    .focus-visible:focus-visible {
        outline: 2px solid var(--accent-color);
        outline-offset: 2px;
    }

    /* High Contrast Mode Support */
    @media (prefers-contrast: high) {
        :root {
            --border-color: #000000;
            --text-secondary: #000000;
        }
        
        .btn {
            border: 2px solid currentColor;
        }
    }

    /* Reduced Motion Support */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
