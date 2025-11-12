@extends('layouts.app')

@section('title', 'View News Article - Super Admin')

@section('content')
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
            <li><a href="{{ route('superadmin.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="{{ route('superadmin.admins.index') }}">
                <i class="fas fa-users-cog"></i> Admin Management
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
            <li><a href="{{ route('superadmin.news.index') }}" class="active">
                <i class="fas fa-newspaper"></i> News
            </a></li>
            <li><a href="{{ route('superadmin.faculty.index') }}">
                <i class="fas fa-chalkboard-teacher"></i> Faculty
            </a></li>
            <li><a href="{{ route('superadmin.students.index') }}">
                <i class="fas fa-user-graduate"></i> Students
            </a></li>
            <li><a href="{{ route('superadmin.admin-access') }}">
                <i class="fas fa-clipboard-list"></i> Admin Access Logs
            </a></li>
            <li><a href="{{ route('superadmin.backup') }}">
                <i class="fas fa-database"></i> Database Backup
            </a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="header-text">
                    <h1>{{ $news->title }}</h1>
                    <p>News Article Details</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('superadmin.news.index') }}" class="btn btn-green btn-enhanced">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>Success!</strong>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- News Details -->
        <div class="news-container">
            <!-- Status and Meta Info -->
            <div class="news-meta">
                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-{{ $news->is_published ? 'check' : 'eye-slash' }}"></i> Status
                        </div>
                        <div class="meta-value">
                            <span class="status-badge {{ $news->is_published ? 'published' : 'draft' }}">
                                <i class="fas fa-{{ $news->is_published ? 'check' : 'eye-slash' }}"></i>
                                {{ $news->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-user"></i> Created By
                        </div>
                        <div class="meta-value">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-details">
                                    <span class="user-name">{{ $news->admin->username }}</span>
                                    <span class="user-role">{{ ucfirst(str_replace('_', ' ', $news->admin->role)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-building"></i> Department
                        </div>
                        <div class="meta-value">
                            <span class="department-badge">{{ $news->admin->department ?? 'N/A' }}</span>
                        </div>
                    </div>

                    @if($news->category)
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-tag"></i> Category
                        </div>
                        <div class="meta-value">
                            <span class="category-badge">{{ $news->category }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-calendar-plus"></i> Created
                        </div>
                        <div class="meta-value">
                            <div class="date-info">
                                <span class="date">{{ $news->created_at->format('F d, Y') }}</span>
                                <span class="time">{{ $news->created_at->format('g:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-hashtag"></i> ID
                        </div>
                        <div class="meta-value">
                            <span class="id-badge">#{{ $news->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="news-content">
                <div class="content-header">
                    <h2>{{ $news->title }}</h2>
                </div>
                
                @if($news->image)
                <div class="content-image">
                    <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}" class="news-image">
                </div>
                @endif

                <div class="content-body">
                    <div class="content-text">
                        {!! nl2br(e($news->content)) !!}
                    </div>
                    
                    <!-- Display additional media if available -->
                    @if($news->hasMedia)
                    <div class="content-media">
                        <h3><i class="fas fa-images"></i> Article Media</h3>
                        <div class="media-grid">
                            @if($news->allImageUrls && count($news->allImageUrls) > 0)
                                @foreach($news->allImageUrls as $index => $imageUrl)
                                    <div class="media-item image-item">
                                        <img src="{{ $imageUrl }}" alt="Article Image {{ $index + 1 }}" class="content-media-image">
                                    </div>
                                @endforeach
                            @endif
                            
                            @if($news->allVideoUrls && count($news->allVideoUrls) > 0)
                                @foreach($news->allVideoUrls as $index => $videoUrl)
                                    <div class="media-item video-item">
                                        <video controls class="content-media-video">
                                            <source src="{{ $videoUrl }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <p class="video-label">Video {{ $index + 1 }}</p>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard Layout */
    .dashboard {
        display: flex;
        min-height: 100vh;
        background: #f8fafc;
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
        min-height: 100vh;
        background: #f8fafc;
    }

    .mobile-menu-btn {
        display: none !important;
    }

    @media (max-width: 1024px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        .mobile-menu-btn {
            display: block !important;
        }
    }

    .page-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .header-text h1 {
        font-size: 1.875rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        line-height: 1.2;
    }

    .header-text p {
        margin: 0;
        opacity: 0.9;
        font-size: 1rem;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    /* Enhanced Button Styles - Matching Edit Page */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        text-transform: none;
        letter-spacing: 0.025em;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn:active {
        transform: translateY(1px);
    }

    /* Enhanced Button Classes */
    .btn-enhanced {
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        border: 2px solid transparent;
    }

    .btn-enhanced:hover {
        transform: translateY(-2px);
    }

    .btn-enhanced:active {
        transform: translateY(0);
    }

    /* Green Button (Back to List) */
    .btn-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-green:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4), 0 10px 10px -5px rgba(16, 185, 129, 0.04);
    }

    .btn-green:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
    }

    /* Blue Button (Edit) */
    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
    }

    .btn-primary:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }

    /* Header Action Button Enhancement */
    .header-actions .btn {
        padding: 1rem 2rem;
        font-size: 1rem;
        border-radius: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
    }

    .header-actions .btn:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 35px rgba(245, 158, 11, 0.4);
    }

    /* Ripple Effect */
    .btn-enhanced::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
        z-index: 0;
    }

    .btn-enhanced:active::after {
        width: 300px;
        height: 300px;
    }

    .btn-enhanced > * {
        position: relative;
        z-index: 2;
    }

    .news-container {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .news-meta {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        border: 1px solid var(--border-color);
        height: fit-content;
    }

    .meta-grid {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .meta-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .meta-value {
        font-weight: 500;
        color: var(--text-primary);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-badge.published {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.draft {
        background: #fef3c7;
        color: #92400e;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: #f59e0b;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: var(--text-primary);
    }

    .user-role {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .department-badge {
        background: #e0e7ff;
        color: #3730a3;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .category-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .date-info {
        display: flex;
        flex-direction: column;
    }

    .date-info .date {
        font-weight: 500;
        color: var(--text-primary);
    }

    .date-info .time {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .id-badge {
        background: #f59e0b;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .news-content {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .content-header {
        padding: 2rem 2rem 1rem 2rem;
        border-bottom: 1px solid var(--border-color);
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .content-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.3;
        flex: 1;
    }

    .content-image {
        padding: 0;
    }

    .news-image {
        width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: cover;
    }

    .content-body {
        padding: 2rem;
    }

    .content-text {
        font-size: 1rem;
        line-height: 1.7;
        color: var(--text-primary);
    }

    .alert {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        border: 1px solid;
    }

    .alert-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    .alert-icon {
        font-size: 1.25rem;
        margin-top: 0.125rem;
    }

    .alert-content strong {
        display: block;
        margin-bottom: 0.25rem;
    }

    .alert-content p {
        margin: 0;
        font-size: 0.875rem;
    }

    @media (max-width: 1024px) {
        .news-container {
            grid-template-columns: 1fr;
        }

        .page-header {
            flex-direction: column;
            gap: 1.5rem;
            text-align: center;
        }

        .header-actions {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }

        .header-text h1 {
            font-size: 1.5rem;
        }

        .news-meta,
        .news-content {
            padding: 1.5rem;
        }

        .content-header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .content-body {
            padding: 1.5rem;
        }
    }

    /* Content Media Styles */
    .content-media {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    .content-media h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .content-media h3 i {
        color: #f59e0b;
    }

    .status-display {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .info-grid {
        display: grid;
        gap: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .info-label {
        font-weight: 600;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-label i {
        color: #f59e0b;
        width: 16px;
    }

    .info-value {
        font-weight: 500;
        color: var(--text-primary);
        text-align: right;
    }

    .description-content {
        font-size: 1rem;
        line-height: 1.7;
        color: var(--text-primary);
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .media-item {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: white;
    }

    .content-media-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
    }

    .content-media-video {
        width: 100%;
        height: 150px;
        border-radius: 8px;
    }

    .video-label {
        padding: 0.75rem;
        margin: 0;
        font-weight: 500;
        color: var(--text-primary);
        text-align: center;
        background: #f8fafc;
    }
</style>

<script>
    // Sidebar Functions
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
    }


    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const mobileBtn = document.querySelector('.mobile-menu-btn');
        
        if (window.innerWidth <= 1024 && 
            !sidebar.contains(e.target) && 
            !mobileBtn.contains(e.target) && 
            sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });
</script>
@endsection
