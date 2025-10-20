@extends('layouts.app')

@section('title', 'View Event - Super Admin')

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
            <li><a href="{{ route('superadmin.events.index') }}" class="active">
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
            <li>
                
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="header-text">
                    <h1>{{ $event->title }}</h1>
                    <p>Event Details</p>
                </div>
            </div>
            <div class="header-actions">
              
                <a href="{{ route('superadmin.events.index') }}" class="btn btn-green btn-enhanced">
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

        <!-- Event Details -->
        <div class="event-container">
            <!-- Status and Meta Info -->
            <div class="event-meta">
                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-clock"></i> Status
                        </div>
                        <div class="meta-value">
                            @php
                                $eventStatus = $event->getEventStatus();
                                $status = $eventStatus['status'];
                                $statusText = $eventStatus['text'] . ' Event';
                                $statusIcon = $eventStatus['icon'];
                            @endphp
                            <span class="status-badge {{ $status }}">
                                <i class="fas fa-{{ $statusIcon }}"></i>
                                {{ $statusText }}
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
                                    <span class="user-name">{{ $event->admin->username }}</span>
                                    <span class="user-role">{{ ucfirst(str_replace('_', ' ', $event->admin->role)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-building"></i> Department
                        </div>
                        <div class="meta-value">
                            <span class="department-badge">{{ $event->admin->department ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-calendar"></i> Event Date
                        </div>
                        <div class="meta-value">
                            <div class="date-info">
                                @if($event->event_date)
                                    <span class="date">{{ $event->event_date->format('F d, Y') }}</span>
                                    <span class="time">{{ $event->event_date->format('g:i A') }}</span>
                                @else
                                    <span class="date text-muted">Date to be determined</span>
                                    <span class="time text-muted">Time TBD</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($event->location)
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-map-marker-alt"></i> Location
                        </div>
                        <div class="meta-value">
                            <span class="location-info">{{ $event->location }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-calendar-plus"></i> Created
                        </div>
                        <div class="meta-value">
                            <div class="date-info">
                                <span class="date">{{ $event->created_at->format('F d, Y') }}</span>
                                <span class="time">{{ $event->created_at->format('g:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-hashtag"></i> ID
                        </div>
                        <div class="meta-value">
                            <span class="id-badge">#{{ $event->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="event-content">
                <div class="content-header">
                    <h2>{{ $event->title }}</h2>
                </div>
                
                @php
                    $images = [];
                    if ($event->image) {
                        $images[] = $event->image;
                    }
                    if ($event->image_paths && is_array($event->image_paths)) {
                        $images = array_merge($images, $event->image_paths);
                    }
                    $images = array_unique($images);

                    $videos = [];
                    if ($event->video) {
                        $videos[] = $event->video;
                    }
                    if ($event->video_paths && is_array($event->video_paths)) {
                        $videos = array_merge($videos, $event->video_paths);
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
                                <a href="{{ asset('storage/' . $imagePath) }}" data-lightbox="event-gallery">
                                    <img src="{{ asset('storage/' . $imagePath) }}" alt="Event Image">
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

                <div class="content-body">
                    <div class="content-text">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                </div>
            </div>

           
        </div>
    </div>
</div>


<style>
    .page-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
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

    /* Header Action Button Enhancement */
    .header-actions .btn {
        padding: 1rem 2rem;
        font-size: 1rem;
        border-radius: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
    }

    .header-actions .btn:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
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

    .event-container {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .event-meta {
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

    .status-badge.upcoming {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.ongoing {
        background: #fef3c7;
        color: #92400e;
        animation: pulse 2s infinite;
    }

    .status-badge.past {
        background: #f3f4f6;
        color: #374151;
    }

    .status-badge.tbd {
        background: #e5e7eb;
        color: #6b7280;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: #10b981;
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

    .location-info {
        color: var(--text-primary);
        font-weight: 500;
    }

    .id-badge {
        background: #10b981;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .event-content {
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

    .event-image {
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

    .event-actions {
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
        color: #10b981;
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
        .event-container {
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
        color: #10b981;
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

    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }

        .header-text h1 {
            font-size: 1.5rem;
        }

        .event-meta,
        .event-content,
        .event-actions {
            padding: 1.5rem;
        }

        .content-header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
        }

        .content-body {
            padding: 1.5rem;
        }

        .content-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .media-gallery {
            padding: 1.5rem;
        }

        .media-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }


</style>


@endsection
