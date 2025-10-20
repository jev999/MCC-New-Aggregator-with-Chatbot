@extends('layouts.app')

@section('title', 'View Announcement - Office Admin')

@push('styles')
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

    .sidebar-header .office-info {
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

    .main-content {
        margin-left: 280px;
        padding: 2rem;
        background: white;
        min-height: 100vh;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
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

        .announcement-meta {
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

    .announcement-meta {
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

    .announcement-content {
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
@endpush

@section('content')
@php
    // Make sure $office is always defined
    $office = $office ?? optional($announcement->admin)->office ?? optional(auth('admin')->user())->office ?? 'Office';
    $officeCode = strtoupper($office); // normalized for icon comparisons
@endphp

<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Updated Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>
                @php
                    $office = $office ?? optional($announcement->admin)->office ?? optional(auth('admin')->user())->office ?? 'Office';
                    $officeCode = strtoupper($office);
                @endphp
                @if($officeCode === 'NSTP')
                    <i class="fas fa-flag"></i>
                @elseif($officeCode === 'SSC')
                    <i class="fas fa-users"></i>
                @elseif($officeCode === 'GUIDANCE')
                    <i class="fas fa-heart"></i>
                @elseif($officeCode === 'REGISTRAR')
                    <i class="fas fa-file-alt"></i>
                @elseif($officeCode === 'CLINIC')
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
        <!-- Header -->
        <div class="header">
            <div>
                <h1><i class="fas fa-eye"></i> View Announcement</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Announcement details and information</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('office-admin.announcements.index') }}" class="btn-action-view">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; border: 1px solid #bbf7d0;">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Content Card -->
        <div class="content-card">
            <!-- Announcement Meta -->
            <div class="announcement-meta">
                <div class="meta-item">
                    <i class="fas fa-heading"></i>
                    <div>
                        <div class="meta-label">Title</div>
                        <div class="meta-value">{{ $announcement->title }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <div>
                        <div class="meta-label">Created by</div>
                        <div class="meta-value">{{ optional($announcement->admin)->username ?? 'Unknown' }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-building"></i>
                    <div>
                        <div class="meta-label">Office</div>
                        <div class="meta-value">{{ optional($announcement->admin)->office ?? $office }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <div>
                        <div class="meta-label">Created</div>
                        <div class="meta-value">{{ $announcement->created_at->format('F d, Y g:i A') }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <div class="meta-label">Status</div>
                        <div class="meta-value">
                            <span class="status-badge {{ $announcement->is_published ? 'published' : 'draft' }}">
                                <i class="fas fa-{{ $announcement->is_published ? 'check-circle' : 'clock' }}"></i>
                                {{ $announcement->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                    </div>
                </div>
                @if($announcement->expires_at)
                <div class="meta-item">
                    <i class="fas fa-calendar-times"></i>
                    <div>
                        <div class="meta-label">Expires</div>
                        <div class="meta-value">{{ $announcement->expires_at->format('F d, Y g:i A') }}</div>
                    </div>
                </div>
                @endif
                <div class="meta-item">
                    <i class="fas fa-hashtag"></i>
                    <div>
                        <div class="meta-label">ID</div>
                        <div class="meta-value">#{{ $announcement->id }}</div>
                    </div>
                </div>
            </div>

            <!-- Media Section -->
            @php
                // Debug: Check what fields exist
                $imagePaths = $announcement->image_paths;
                $videoPaths = $announcement->video_paths;
                $singleImage = $announcement->image_path;
                $singleVideo = $announcement->video_path;

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
                                    <img src="{{ asset('storage/' . $image) }}" alt="Announcement Image" onclick="openImageModal(this.src)">
                                </div>
                            @endforeach
                        @endif

                        <!-- Single Image (backward compatibility) -->
                        @if($singleImage && (empty($imagePaths) || !is_array($imagePaths)))
                            <div class="media-item">
                                <img src="{{ asset('storage/' . $singleImage) }}" alt="Announcement Image" onclick="openImageModal(this.src)">
                            </div>
                        @endif

                        <!-- Multiple Videos -->
                        @if(!empty($videoPaths) && is_array($videoPaths))
                            @foreach($videoPaths as $video)
                                <div class="media-item">
                                    <video controls>
                                        <source src="{{ asset('storage/' . $video) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            @endforeach
                        @endif

                        <!-- Single Video (backward compatibility) -->
                        @if($singleVideo && (empty($videoPaths) || !is_array($videoPaths)))
                            <div class="media-item">
                                <video controls>
                                    <source src="{{ asset('storage/' . $singleVideo) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Announcement Content -->
            <div class="announcement-content">
                {!! nl2br(e($announcement->content)) !!}
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); cursor: pointer;" onclick="closeImageModal()">
    <img id="modalImage" style="margin: auto; display: block; max-width: 90%; max-height: 90%; margin-top: 5%;">
    <span style="position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer;" onclick="closeImageModal()">&times;</span>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    window.toggleSidebar = function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
    };

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
    window.handleLogout = function() {
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
    };

    // Enhanced confirmation dialogs
    window.confirmDelete = function(announcementTitle) {
        event.preventDefault();

        Swal.fire({
            title: 'Delete Announcement?',
            html: `Are you sure you want to delete "<strong>${announcementTitle}</strong>"?<br><br>This action cannot be undone.`,
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
                    text: 'Please wait while we delete the announcement',
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
    };

    window.confirmPublish = function(announcementTitle) {
        event.preventDefault();

        Swal.fire({
            title: 'Publish Announcement?',
            html: `Are you sure you want to publish "<strong>${announcementTitle}</strong>"?<br><br>This will make it visible to all users.`,
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
                    text: 'Please wait while we publish the announcement',
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
    };

    window.confirmUnpublish = function(announcementTitle) {
        event.preventDefault();

        Swal.fire({
            title: 'Unpublish Announcement?',
            html: `Are you sure you want to unpublish "<strong>${announcementTitle}</strong>"?<br><br>This will hide it from all users.`,
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
                    text: 'Please wait while we unpublish the announcement',
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
    };

    // Image modal functions
    window.openImageModal = function(src) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').style.display = 'block';
    };

    window.closeImageModal = function() {
        document.getElementById('imageModal').style.display = 'none';
    };
});
</script>
@endpush
@endsection