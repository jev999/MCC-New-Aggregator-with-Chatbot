@extends('layouts.app')

@section('title', 'View Event - Office Admin')

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

    /* Header */
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

    /* Meta Information */
    .event-meta {
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

    /* Event Content */
    .event-content {
        line-height: 1.8;
        color: var(--text-primary);
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    /* Action Buttons */
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

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #d97706);
    }

    /* Media Section */
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

    .media-item img,
    .media-item video {
        max-width: 100%;
        max-height: 300px;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
    }

    /* Responsive */
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

        .event-meta {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $office = $office ?? optional($event->admin)->office ?? optional(auth('admin')->user())->office ?? 'Office';
    $officeCode = strtoupper($office);
@endphp

<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>
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
            <li><a href="{{ route('office-admin.announcements.index') }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('office-admin.events.index') }}" class="active">
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
                <h1><i class="fas fa-eye"></i> View Event</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Event details and information</p>
            </div>
            <div class="header-actions">
               
                <a href="{{ route('office-admin.events.index') }}" class="btn btn-secondary">
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
            <!-- Event Meta -->
            <div class="event-meta">
                <div class="meta-item">
                    <i class="fas fa-heading"></i>
                    <div>
                        <div class="meta-label">Title</div>
                        <div class="meta-value">{{ $event->title }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <div>
                        <div class="meta-label">Created by</div>
                        <div class="meta-value">{{ optional($event->admin)->username ?? 'Unknown' }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-building"></i>
                    <div>
                        <div class="meta-label">Office</div>
                        <div class="meta-value">{{ optional($event->admin)->office ?? $office }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <div>
                        <div class="meta-label">Event Date</div>
                        <div class="meta-value">
                            {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('F d, Y') : 'Not set' }}
                            @if($event->event_time)
                                {{ \Carbon\Carbon::parse($event->event_time)->format('g:i A') }}
                            @endif
                        </div>
                    </div>
                </div>
                @if($event->location)
                <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <div class="meta-label">Location</div>
                        <div class="meta-value">{{ $event->location }}</div>
                    </div>
                </div>
                @endif
                <div class="meta-item">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <div class="meta-label">Status</div>
                        <div class="meta-value">
                            <span class="status-badge {{ $event->is_published ? 'published' : 'draft' }}">
                                <i class="fas fa-{{ $event->is_published ? 'check-circle' : 'clock' }}"></i>
                                {{ $event->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-plus"></i>
                    <div>
                        <div class="meta-label">Created</div>
                        <div class="meta-value">{{ $event->created_at->format('F d, Y g:i A') }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-hashtag"></i>
                    <div>
                        <div class="meta-label">ID</div>
                        <div class="meta-value">#{{ $event->id }}</div>
                    </div>
                </div>
            </div>

            @php
                // Get media paths
                $currentImagePaths = [];
                $currentVideoPaths = [];

                if ($event->image_path) {
                    $currentImagePaths[] = $event->image_path;
                }

                if ($event->image_paths) {
                    $imagePaths = is_string($event->image_paths) ? json_decode($event->image_paths, true) : $event->image_paths;
                    if (is_array($imagePaths)) {
                        $currentImagePaths = array_merge($currentImagePaths, array_filter($imagePaths));
                    }
                }

                if ($event->video_path) {
                    $currentVideoPaths[] = $event->video_path;
                }

                if ($event->video_paths) {
                    $videoPaths = is_string($event->video_paths) ? json_decode($event->video_paths, true) : $event->video_paths;
                    if (is_array($videoPaths)) {
                        $currentVideoPaths = array_merge($currentVideoPaths, array_filter($videoPaths));
                    }
                }

                $hasMedia = (!empty($currentImagePaths) || !empty($currentVideoPaths));
            @endphp

            @if($hasMedia)
                <div class="media-section">
                    <h3><i class="fas fa-paperclip"></i> Media Attachments</h3>
                    <div class="media-grid">
                        @foreach($currentImagePaths as $index => $imagePath)
                            <div class="media-item">
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="Event Image {{ $index + 1 }}" onclick="openImageModal(this.src)">
                            </div>
                        @endforeach
                        @foreach($currentVideoPaths as $index => $videoPath)
                            <div class="media-item">
                                <video controls>
                                    <source src="{{ asset('storage/' . $videoPath) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($event->csv_path)
                <div class="media-section">
                    <h3><i class="fas fa-file-csv"></i> CSV File</h3>
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <i class="fas fa-file-csv" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <div>
                            <p style="margin: 0; font-weight: 500;">{{ basename($event->csv_path) }}</p>
                            <a href="{{ asset('storage/' . $event->csv_path) }}" download class="btn" style="margin-top: 0.5rem;">
                                <i class="fas fa-download"></i> Download CSV
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Event Content -->
            <div class="event-content">
                {!! nl2br(e($event->description)) !!}
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); cursor: pointer;" onclick="closeImageModal()">
    <img id="modalImage" style="margin: auto; display: block; max-width: 90%; max-height: 90%; margin-top: 5%;">
    <span style="position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer;" onclick="closeImageModal()">×</span>
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
