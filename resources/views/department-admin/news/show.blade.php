@extends('layouts.app')

@section('title', 'View News Article - Department Admin')

@section('content')
<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-building"></i> Department Admin</h3>
            <div class="dept-info">{{ auth('admin')->user()->department }} Department</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="{{ route('department-admin.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="{{ route('department-admin.announcements.index') }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('department-admin.events.index') }}">
                <i class="fas fa-calendar-alt"></i> Events
            </a></li>
            <li><a href="{{ route('department-admin.news.index') }}" class="active">
                <i class="fas fa-newspaper"></i> News
            </a></li>
            <li>
               
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><i class="fas fa-eye"></i> View News Article</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">News article details and information</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('department-admin.news.index') }}" class="btn-action-view">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Content Card -->
        <div class="content-card">
            <!-- News Meta -->
            <div class="news-meta">
                <div class="meta-item">
                    <i class="fas fa-heading"></i>
                    <div>
                        <div class="meta-label">Title</div>
                        <div class="meta-value">{{ $news->title }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <div>
                        <div class="meta-label">Created by</div>
                        <div class="meta-value">{{ $news->admin->username ?? 'Unknown' }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-building"></i>
                    <div>
                        <div class="meta-label">Department</div>
                        <div class="meta-value">{{ $news->admin->department ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <div>
                        <div class="meta-label">Created</div>
                        <div class="meta-value">{{ $news->created_at->format('F d, Y g:i A') }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-edit"></i>
                    <div>
                        <div class="meta-label">Last Updated</div>
                        <div class="meta-value">{{ $news->updated_at->format('F d, Y g:i A') }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <div class="meta-label">Status</div>
                        <div class="meta-value">
                            <span class="status-badge {{ $news->is_published ? 'published' : 'draft' }}">
                                <i class="fas fa-{{ $news->is_published ? 'check-circle' : 'clock' }}"></i>
                                {{ $news->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-hashtag"></i>
                    <div>
                        <div class="meta-label">ID</div>
                        <div class="meta-value">#{{ $news->id }}</div>
                    </div>
                </div>
            </div>

            <!-- Media Section -->
            @php
                // Debug: Check what fields exist
                $imagePaths = $news->image_paths;
                $videoPaths = $news->video_paths;
                $singleImage = $news->image_path;
                $singleVideo = $news->video_path;

                // Handle JSON decoding for multiple media
                if (is_string($imagePaths)) {
                    $decoded = json_decode($imagePaths, true);
                    if (is_string($decoded)) {
                        $imagePaths = json_decode($decoded, true);
                    } else {
                        $imagePaths = $decoded;
                    }
                }

                if (is_string($videoPaths)) {
                    $decoded = json_decode($videoPaths, true);
                    if (is_string($decoded)) {
                        $videoPaths = json_decode($decoded, true);
                    } else {
                        $videoPaths = $decoded;
                    }
                }

                $hasMedia = (!empty($imagePaths) && is_array($imagePaths)) ||
                          (!empty($videoPaths) && is_array($videoPaths)) ||
                          $singleImage ||
                          $singleVideo;
            @endphp

            @if($hasMedia)
                <div class="media-section">
                    <h3><i class="fas fa-paperclip"></i> Media Attachments</h3>

                    <div class="media-grid">
                        <!-- Multiple Images -->
                        @if(!empty($imagePaths) && is_array($imagePaths))
                            @foreach($imagePaths as $image)
                                <div class="media-item">
                                    <img src="{{ storage_asset($image) }}" alt="News Image" onclick="openImageModal(this.src)">
                                </div>
                            @endforeach
                        @endif

                        <!-- Single Image (backward compatibility) -->
                        @if($singleImage && (empty($imagePaths) || !is_array($imagePaths)))
                            <div class="media-item">
                                <img src="{{ storage_asset($singleImage) }}" alt="News Image" onclick="openImageModal(this.src)">
                            </div>
                        @endif

                        <!-- Multiple Videos -->
                        @if(!empty($videoPaths) && is_array($videoPaths))
                            @foreach($videoPaths as $video)
                                <div class="media-item">
                                    <video controls>
                                        <source src="{{ storage_asset($video) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            @endforeach
                        @endif

                        <!-- Single Video (backward compatibility) -->
                        @if($singleVideo && (empty($videoPaths) || !is_array($videoPaths)))
                            <div class="media-item">
                                <video controls>
                                    <source src="{{ storage_asset($singleVideo) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- News Content -->
            <div class="news-content">
                {!! nl2br(e($news->content)) !!}
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #10b981;
        --secondary-color: #1f2937;
        --accent-color: #3b82f6;
        --success-color: #059669;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --text-primary: #111827;
        --text-secondary: #6b7280;
        --bg-primary: #ffffff;
        --bg-secondary: #f9fafb;
        --border-color: #e5e7eb;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: white;
        min-height: 100vh;
        margin: 0;
        color: var(--text-primary);
    }

    .dashboard {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar Styling - Enhanced to match events page */
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
        text-align: center !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
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
        text-align: center !important;
        width: 100%;
        display: block;
    }

    .sidebar-menu {
        list-style: none;
        padding: 1.5rem 0;
        margin: 0;
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
        margin-left: 320px;
        padding: 2rem;
        background: white;
        min-height: 100vh;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: var(--radius-md);
        cursor: pointer;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
    }

    .mobile-menu-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
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

    .mobile-menu-btn {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-md);
    }

    .mobile-menu-btn:hover {
        background: #4b5563;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        .mobile-menu-btn {
            display: block !important;
        }

        .header {
            padding: 1.5rem;
        }

        .header h1 {
            font-size: 1.5rem;
        }

        .news-meta {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }
    }

    .header {
        background: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        border: 1px solid var(--border-color);
    }

    .header h1 {
        color: var(--text-primary);
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .content-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }

    .news-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        border-left: 4px solid var(--primary-color);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .meta-item i {
        color: var(--primary-color);
        width: 1.25rem;
        font-size: 1.1rem;
    }

    .meta-label {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .meta-value {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .status-badge.published {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.draft {
        background: #fef3c7;
        color: #92400e;
    }

    .news-content {
        line-height: 1.8;
        color: var(--text-primary);
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: var(--radius-md);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        background: linear-gradient(135deg, var(--primary-color), var(--success-color));
        color: white;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6b7280, #4b5563);
    }

    .btn-info {
        background: linear-gradient(135deg, var(--accent-color), #2563eb);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #dc2626);
    }

    /* Enhanced Action Buttons */
    .btn-action-view,
    .btn-action-edit,
    .btn-action-delete {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        min-width: 70px;
        justify-content: center;
    }

    /* View Button (Blue) */
    .btn-action-view {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }

    .btn-action-view:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-action-view i {
        font-size: 0.875rem;
        transition: transform 0.3s ease;
    }

    .btn-action-view:hover i {
        transform: scale(1.1);
    }

    /* Edit Button (Orange) */
    .btn-action-edit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
    }

    .btn-action-edit:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
        color: white;
    }

    .btn-action-edit i {
        font-size: 0.875rem;
        transition: transform 0.3s ease;
    }

    .btn-action-edit:hover i {
        transform: scale(1.1) rotate(5deg);
    }

    /* Delete Button (Red) */
    .btn-action-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
    }

    .btn-action-delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        color: white;
    }

    .btn-action-delete i {
        font-size: 0.875rem;
        transition: transform 0.3s ease;
    }

    .btn-action-delete:hover i {
        transform: scale(1.1) rotate(-5deg);
    }

    /* Shine Effect for Action Buttons */
    .btn-action-view::before,
    .btn-action-edit::before,
    .btn-action-delete::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-action-view:hover::before,
    .btn-action-edit:hover::before,
    .btn-action-delete:hover::before {
        left: 100%;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .media-section {
        margin: 2rem 0;
        padding: 1.5rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
    }

    .media-section h3 {
        margin: 0 0 1rem 0;
        color: var(--text-primary);
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .media-item {
        text-align: center;
    }

    .media-item img,
    .media-item video {
        max-width: 100%;
        max-height: 300px;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
    }
</style>

<script>
    // Mobile menu toggle function
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
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

    // Handle logout
    function handleLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will be logged out of your account.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Logging out...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                document.getElementById('logout-form').submit();
            }
        });
    }

    // Enhanced confirmation dialogs
    function confirmDelete(newsTitle) {
        event.preventDefault();

        Swal.fire({
            title: 'Delete News Article?',
            html: `Are you sure you want to delete "<strong>${newsTitle}</strong>"?<br><br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the news article',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                event.target.submit();
            }
        });

        return false;
    }

    function confirmPublish(newsTitle) {
        event.preventDefault();

        Swal.fire({
            title: 'Publish News Article?',
            html: `Are you sure you want to publish "<strong>${newsTitle}</strong>"?<br><br>This will make it visible to all users.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Publish',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Publishing...',
                    text: 'Please wait while we publish the news article',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                event.target.submit();
            }
        });

        return false;
    }

    function confirmUnpublish(newsTitle) {
        event.preventDefault();

        Swal.fire({
            title: 'Unpublish News Article?',
            html: `Are you sure you want to unpublish "<strong>${newsTitle}</strong>"?<br><br>This will hide it from all users.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Unpublish',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Unpublishing...',
                    text: 'Please wait while we unpublish the news article',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                event.target.submit();
            }
        });

        return false;
    }

    // Image modal functions
    function openImageModal(src) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').style.display = 'block';
    }

    function closeImageModal() {
        document.getElementById('imageModal').style.display = 'none';
    }

    // Mobile sidebar toggle
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
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
</script>

<!-- Image Modal -->
<div id="imageModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); cursor: pointer;" onclick="closeImageModal()">
    <img id="modalImage" style="margin: auto; display: block; max-width: 90%; max-height: 90%; margin-top: 5%;">
    <span style="position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer;" onclick="closeImageModal()">&times;</span>
</div>
