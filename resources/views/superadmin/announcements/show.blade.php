@extends('layouts.app')

@section('title', 'View Announcement - Super Admin')

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
            <li><a href="{{ route('superadmin.announcements.index') }}" class="active">
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
           
        </ul>
    </div>
    
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="header-text">
                    <h1>{{ $announcement->title }}</h1>
                    <p>Announcement Details</p>
                </div>
            </div>
            <div class="header-actions">
              
                <a href="{{ route('superadmin.announcements.index') }}" class="btn btn-info">
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

        <!-- Announcement Details -->
        <div class="announcement-container">
            <!-- Status and Meta Info -->
            <div class="announcement-meta">
                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-toggle-on"></i> Status
                        </div>
                        <div class="meta-value">
                            <span class="status-badge {{ $announcement->is_published ? 'published' : 'draft' }}">
                                <i class="fas fa-{{ $announcement->is_published ? 'check' : 'eye-slash' }}"></i>
                                {{ $announcement->is_published ? 'Published' : 'Draft' }}
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
                                    <span class="user-name">{{ $announcement->admin->username }}</span>
                                    <span class="user-role">{{ ucfirst(str_replace('_', ' ', $announcement->admin->role)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-building"></i> Department
                        </div>
                        <div class="meta-value">
                            <span class="department-badge">{{ $announcement->admin->department ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-calendar-plus"></i> Created
                        </div>
                        <div class="meta-value">
                            <div class="date-info">
                                <span class="date">{{ $announcement->created_at->format('F d, Y') }}</span>
                                <span class="time">{{ $announcement->created_at->format('g:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-calendar-edit"></i> Last Updated
                        </div>
                        <div class="meta-value">
                            <div class="date-info">
                                <span class="date">{{ $announcement->updated_at->format('F d, Y') }}</span>
                                <span class="time">{{ $announcement->updated_at->format('g:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    @if($announcement->expires_at)
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-calendar-times"></i> Expires
                        </div>
                        <div class="meta-value">
                            <div class="date-info">
                                <span class="date">{{ $announcement->expires_at->format('F d, Y') }}</span>
                                <span class="time">{{ $announcement->expires_at->format('g:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-hashtag"></i> ID
                        </div>
                        <div class="meta-value">
                            <span class="id-badge">#{{ $announcement->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="announcement-content">
                <div class="content-header">
                    <h2>{{ $announcement->title }}</h2>
                </div>
                
                @php
                    $images = [];
                    if ($announcement->image_path) {
                        $images[] = $announcement->image_path;
                    }
                    if ($announcement->image_paths && is_array($announcement->image_paths)) {
                        $images = array_merge($images, $announcement->image_paths);
                    }
                    $images = array_unique($images);

                    $videos = [];
                    if ($announcement->video_path) {
                        $videos[] = $announcement->video_path;
                    }
                    if ($announcement->video_paths && is_array($announcement->video_paths)) {
                        $videos = array_merge($videos, $announcement->video_paths);
                    }
                    $videos = array_unique($videos);
                @endphp

                @if(count($images) > 0 || count($videos) > 0)
                <div class="media-gallery">
                    @if(count($images) > 0)
                    <div class="media-section">
                        <h4><i class="fas fa-images"></i> Images ({{ count($images) }})</h4>
                        <div class="media-grid">
                            @foreach($images as $imagePath)
                            <div class="media-item">
                                <a href="{{ asset('storage/' . $imagePath) }}" data-lightbox="announcement-gallery">
                                    <img src="{{ asset('storage/' . $imagePath) }}" alt="Announcement Image">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(count($videos) > 0)
                    <div class="media-section">
                        <h4><i class="fas fa-video"></i> Videos ({{ count($videos) }})</h4>
                        <div class="media-grid">
                            @foreach($videos as $videoPath)
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
                </div>
                @endif

                @if($announcement->csv_path)
                <div class="content-csv">
                    <h4><i class="fas fa-file-csv"></i> CSV File</h4>
                    <div class="csv-download">
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <i class="fas fa-file-csv" style="font-size: 2rem; color: #10b981;"></i>
                            <div>
                                <p style="margin: 0; font-weight: 500;">{{ basename($announcement->csv_path) }}</p>
                                <a href="{{ asset('storage/' . $announcement->csv_path) }}" download class="btn btn-sm btn-primary" style="margin-top: 0.5rem;">
                                    <i class="fas fa-download"></i> Download CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="content-body">
                    <div class="content-text">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>
                </div>
            </div>

            <!-- Actions -->
           
        </div>
    </div>
</div>

<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
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

    .header-actions .btn {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        backdrop-filter: blur(10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        min-width: 140px;
    }

    .header-actions .btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .header-actions .btn i {
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .header-actions .btn:hover i {
        transform: rotate(360deg) scale(1.1);
    }

    /* Enhanced Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: var(--radius-lg);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        min-width: 140px;
    }

    .btn i {
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-primary:hover i {
        transform: rotate(360deg) scale(1.1);
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-success:hover i {
        transform: rotate(360deg) scale(1.1);
    }

    .btn-success::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-success:hover::before {
        left: 100%;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }

    .btn-secondary:hover i {
        transform: rotate(360deg) scale(1.1);
    }

    .btn-secondary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-secondary:hover::before {
        left: 100%;
    }

    .btn-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
    }

    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(6, 182, 212, 0.4);
    }

    .btn-info:hover i {
        transform: rotate(360deg) scale(1.1);
    }

    .btn-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-info:hover::before {
        left: 100%;
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }

    .btn-warning:hover i {
        transform: rotate(360deg) scale(1.1);
    }

    .btn-warning::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-warning:hover::before {
        left: 100%;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }

    .btn-danger:hover i {
        transform: rotate(360deg) scale(1.1);
    }

    .btn-danger::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-danger:hover::before {
        left: 100%;
    }

    /* Button active states */
    .btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    /* Disabled button state */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn:disabled:hover {
        transform: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .announcement-container {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .announcement-meta {
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
        background: var(--primary-color);
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
        background: var(--primary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .announcement-content {
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
    }

    .content-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.3;
    }

    .media-gallery {
        padding: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .media-section {
        margin-bottom: 2rem;
    }

    .media-section:last-child {
        margin-bottom: 0;
    }

    .media-section h4 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .media-section h4 i {
        color: var(--primary-color);
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .media-item {
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .media-item:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .media-item img, .media-item video {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }

    .media-item a {
        display: block;
        position: relative;
    }

    .media-item a::after {
        content: '\f00e'; /* Font Awesome search-plus icon */
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 2rem;
        background: rgba(0, 0, 0, 0.5);
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .media-item a:hover::after {
        opacity: 1;
    }

    .content-video {
        margin: 2rem 0;
    }

    .content-video h4 {
        margin-bottom: 1rem;
        color: var(--text-primary);
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .content-csv {
        margin: 2rem 0;
    }

    .content-csv h4 {
        margin-bottom: 1rem;
        color: var(--text-primary);
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .csv-download {
        margin-top: 1rem;
    }

    .content-body {
        padding: 2rem;
    }

    .content-text {
        font-size: 1rem;
        line-height: 1.7;
        color: var(--text-primary);
    }

    .announcement-actions {
        grid-column: 1 / -1;
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        border: 1px solid var(--border-color);
    }

    .action-group h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .action-group h3 i {
        color: var(--primary-color);
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
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
        .announcement-container {
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

        .action-buttons {
            flex-direction: column;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }

        .header-text h1 {
            font-size: 1.5rem;
        }

        .announcement-meta,
        .announcement-content,
        .announcement-actions {
            padding: 1.5rem;
        }

        .content-header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
        }

        .content-body {
            padding: 1.5rem;
        }
    }
</style>
@endsection
