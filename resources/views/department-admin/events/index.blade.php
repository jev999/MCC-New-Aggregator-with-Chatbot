@extends('layouts.app')

@section('title', 'Department Events - Department Admin')

@section('content')
<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-user-shield"></i> Department Admin</h3>
            <div class="dept-info">{{ auth('admin')->user()->department }} Department</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="{{ route('department-admin.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="{{ route('department-admin.announcements.index') }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('department-admin.events.index') }}" class="active">
                <i class="fas fa-calendar-alt"></i> Events
            </a></li>
            <li><a href="{{ route('department-admin.news.index') }}">
                <i class="fas fa-newspaper"></i> News
            </a></li>
            <li>
                
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="header-text">
                    <h1>Department Events</h1>
                    <p>Manage and organize department events</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('department-admin.events.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Event
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon published">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $events->where('is_published', true)->count() }}</h3>
                    <p>Published Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon draft">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $events->where('is_published', false)->count() }}</h3>
                    <p>Draft Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $events->count() }}</h3>
                    <p>Total Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon departments">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ auth('admin')->user()->department }}</h3>
                    <p>Department</p>
                </div>
            </div>
        </div>

        <!-- Enhanced Content Container -->
        <div class="content-container">
            <div class="content-header">
                <div class="content-title">
                    <h2><i class="fas fa-list"></i> All Events</h2>
                    <span class="content-count">{{ $events->count() }} events</span>
                </div>
                <div class="content-controls">
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search events..." class="search-input">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <div class="filter-container">
                        <select id="statusFilter" class="filter-select">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="filter-container">
                        <select id="sortFilter" class="filter-select">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="title">Title A-Z</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Table/Cards View -->
            <div class="data-view">
                <!-- Desktop Table View -->
                <div class="table-view">
                    <table class="enhanced-table" id="dataTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-heading"></i> Title</th>
                                <th><i class="fas fa-user"></i> Created By</th>
                                <th><i class="fas fa-map-marker-alt"></i> Location</th>
                                <th><i class="fas fa-toggle-on"></i> Status</th>
                                <th><i class="fas fa-calendar"></i> Event Date</th>
                                <th><i class="fas fa-calendar"></i> Created</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                <tr data-status="{{ $event->is_published ? 'published' : 'draft' }}" data-department="{{ auth('admin')->user()->department }}">
                                    <td>
                                        <span class="id-badge">#{{ $event->id }}</span>
                                    </td>
                                    <td>
                                        <div class="content-preview">
                                            <h4>{{ Str::limit($event->title, 50) }}</h4>
                                            <p>{{ Str::limit($event->description, 100) }}</p>
                                            @if($event->hasMedia)
                                                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                                                    @if(in_array($event->hasMedia, ['image', 'both']))
                                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #dcfce7; color: #166534; border-radius: 4px; font-size: 0.75rem;">
                                                            <i class="fas fa-image"></i> Image
                                                        </span>
                                                    @endif
                                                    @if(in_array($event->hasMedia, ['video', 'both']))
                                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #fecaca; color: #991b1b; border-radius: 4px; font-size: 0.75rem;">
                                                            <i class="fas fa-video"></i> Video
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span>{{ auth('admin')->user()->username }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($event->location)
                                            <span class="location-badge">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ Str::limit($event->location, 40) }}
                                            </span>
                                        @else
                                            <span class="text-muted">No location set</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $event->is_published ? 'published' : 'draft' }}">
                                            <i class="fas fa-{{ $event->is_published ? 'check' : 'eye-slash' }}"></i>
                                            {{ $event->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            @if($event->event_date)
                                                <div class="date">{{ $event->event_date->format('M d, Y') }}</div>
                                                @if($event->event_time)
                                                    <div class="time">{{ \Carbon\Carbon::parse($event->event_time)->format('g:i A') }}</div>
                                                @endif
                                            @else
                                                <div class="date text-muted">TBD</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div class="date">{{ $event->created_at->format('M d, Y') }}</div>
                                            <div class="time">{{ $event->created_at->format('g:i A') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('department-admin.events.show', $event) }}" class="btn-action-view" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('department-admin.events.edit', $event) }}" class="btn-action-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn-action-delete" title="Delete" onclick="deleteEvent({{ $event->id }}, '{{ addslashes($event->title) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards View -->
                <div class="cards-view">
                    @forelse($events as $event)
                        <div class="event-card" data-status="{{ $event->is_published ? 'published' : 'draft' }}" data-location="{{ $event->location ?? '' }}">
                            <div class="card-header">
                                <div class="card-id">#{{ $event->id }}</div>
                                <span class="status-badge {{ $event->is_published ? 'published' : 'draft' }}">
                                    <i class="fas fa-{{ $event->is_published ? 'check' : 'eye-slash' }}"></i>
                                    {{ $event->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </div>
                            <div class="card-content">
                                <h3>{{ $event->title }}</h3>
                                <p>{{ Str::limit($event->description, 120) }}</p>
                                @if($event->hasMedia)
                                    <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        @if(in_array($event->hasMedia, ['image', 'both']))
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #dcfce7; color: #166534; border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-image"></i> Image
                                            </span>
                                        @endif
                                        @if(in_array($event->hasMedia, ['video', 'both']))
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #fecaca; color: #991b1b; border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-video"></i> Video
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                @if($event->event_date)
                                    <div style="margin-top: 0.75rem; padding: 0.5rem; background: #f0f9ff; border-radius: 4px; border-left: 3px solid #0ea5e9;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #0c4a6e;">
                                            <i class="fas fa-calendar"></i>
                                            <span>{{ $event->event_date->format('M d, Y') }}</span>
                                            @if($event->event_time)
                                                <i class="fas fa-clock"></i>
                                                <span>{{ \Carbon\Carbon::parse($event->event_time)->format('g:i A') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if($event->location)
                                    <div style="margin-top: 0.5rem; padding: 0.5rem; background: #f0fdf4; border-radius: 4px; border-left: 3px solid #22c55e;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #166534;">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $event->location }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="card-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span>{{ auth('admin')->user()->username }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-building"></i>
                                    <span>{{ auth('admin')->user()->department }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $event->created_at->format('M d, Y \a\t g:i A') }}</span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <a href="{{ route('department-admin.events.show', $event) }}" class="btn-action-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('department-admin.events.edit', $event) }}" class="btn-action-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button" class="btn-action-delete" onclick="deleteEvent({{ $event->id }}, '{{ addslashes($event->title) }}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3>No events yet</h3>
                            <p>Create your first event to get started.</p>
                            <a href="{{ route('department-admin.events.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Event
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if(method_exists($events, 'hasPages') && $events->hasPages())
            <!-- Pagination -->
            <div class="pagination-container">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    /* Sidebar Styling */
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

    /* Enhanced Page Header */
    .page-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3), 0 0 40px rgba(16, 185, 129, 0.1);
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

    /* Enhanced Button Styling for All Buttons */
    .btn {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: 2px solid rgba(59, 130, 246, 0.8);
        color: white;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        font-weight: 600;
        letter-spacing: 0.3px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s ease;
    }

    .btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        opacity: 0;
        transform: scale(0);
        transition: all 0.5s ease;
        pointer-events: none;
    }

    .btn:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        border-color: rgba(37, 99, 235, 0.9);
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4),
                    0 0 30px rgba(59, 130, 246, 0.2);
        filter: brightness(1.05) saturate(1.1);
        text-decoration: none;
        color: white;
    }

    .btn:hover::before {
        left: 100%;
        animation: shimmer 0.6s ease-in-out;
    }

    .btn:hover::after {
        opacity: 1;
        transform: scale(1);
        animation: pulseGlow 2s ease-in-out infinite;
    }

    .btn:active {
        transform: translateY(0) scale(1.01);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        transition: all 0.1s ease;
    }

    .btn i {
        margin-right: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn:hover i {
        transform: scale(1.1);
    }

    /* Specific Button Variants */
    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-color: rgba(59, 130, 246, 0.8);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        border-color: rgba(37, 99, 235, 0.9);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4),
                    0 0 30px rgba(59, 130, 246, 0.2);
    }

    .btn-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        border-color: rgba(6, 182, 212, 0.8);
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        border-color: rgba(8, 145, 178, 0.9);
        box-shadow: 0 8px 25px rgba(6, 182, 212, 0.4),
                    0 0 30px rgba(6, 182, 212, 0.2);
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-color: rgba(245, 158, 11, 0.8);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        border-color: rgba(217, 119, 6, 0.9);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4),
                    0 0 30px rgba(245, 158, 11, 0.2);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border-color: rgba(239, 68, 68, 0.8);
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border-color: rgba(220, 38, 38, 0.9);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4),
                    0 0 30px rgba(239, 68, 68, 0.2);
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: rgba(16, 185, 129, 0.8);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border-color: rgba(5, 150, 105, 0.9);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4),
                    0 0 30px rgba(16, 185, 129, 0.2);
    }

    /* Button Sizes */
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        border-radius: 6px;
    }

    .btn-lg {
        padding: 1rem 2rem;
        font-size: 1rem;
        border-radius: 10px;
    }

    /* Header Actions Specific Styling */
    .header-actions .btn {
        text-transform: uppercase;
        font-size: 0.875rem;
        padding: 0.875rem 1.5rem;
        letter-spacing: 0.5px;
    }

    .header-actions .btn:hover i {
        transform: rotate(90deg) scale(1.1);
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
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

    .stat-icon.departments {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .stat-content h3 {
        font-size: 1.875rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-primary);
    }

    .stat-content p {
        margin: 0;
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 500;
    }

    /* Enhanced Content Container */
    .content-container {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .content-header {
        padding: 2rem;
        border-bottom: 1px solid var(--border-color);
        background: #f8fafc;
    }

    .content-title {
        margin-bottom: 1.5rem;
    }

    .content-title h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .content-count {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .content-controls {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .search-container {
        position: relative;
        flex: 1;
        min-width: 300px;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
    }

    .filter-container {
        min-width: 150px;
    }

    .filter-select {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        background: white;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    /* Enhanced Table */
    .data-view {
        padding: 2rem;
    }

    .table-view {
        display: block;
        overflow-x: auto;
    }

    .enhanced-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .enhanced-table th {
        background: #f8fafc;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 2px solid var(--border-color);
        white-space: nowrap;
    }

    .enhanced-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: top;
    }

    .enhanced-table tr:hover {
        background: #f8fafc;
    }

    .id-badge {
        background: var(--primary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .content-preview h4 {
        margin: 0 0 0.5rem 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1.4;
    }

    .content-preview p {
        margin: 0;
        font-size: 0.75rem;
        color: var(--text-secondary);
        line-height: 1.4;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }

    .location-badge {
        background: #e0e7ff;
        color: #3730a3;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .date-info .date {
        font-weight: 500;
        color: var(--text-primary);
    }

    .date-info .time {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }

    .text-muted {
        color: #9ca3af !important;
        font-style: italic;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
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

    /* Mobile Cards View */
    .cards-view {
        display: none;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .event-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .event-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .card-id {
        background: var(--primary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .card-content h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
        line-height: 1.4;
    }

    .card-content p {
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0 0 1rem 0;
    }

    .card-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .card-actions .btn {
        flex: 1;
        min-width: auto;
        justify-content: center;
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }

    /* Mobile Cards Enhanced Action Buttons */
    .card-actions .btn-action-view,
    .card-actions .btn-action-edit,
    .card-actions .btn-action-delete {
        flex: 1;
        min-width: auto;
        justify-content: center;
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }

    .search-container {
        position: relative;
        flex: 1;
        min-width: 300px;
    }

    .search-box {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        color: var(--text-secondary);
        z-index: 2;
    }

    .search-box input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 2.5rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .search-box input:focus {
        outline: none;
        border-color: #10b981;
        background: white;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .search-shortcut {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: #e5e7eb;
        color: #6b7280;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.625rem;
        font-weight: 600;
        pointer-events: none;
    }

    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--border-color);
        border-top: none;
        border-radius: 0 0 var(--radius-md) var(--radius-md);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10;
        display: none;
    }

    .suggestion-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.2s ease;
    }

    .suggestion-item:hover {
        background: #f8fafc;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .filter-container {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .view-controls {
        display: flex;
        gap: 0.25rem;
        background: #f1f5f9;
        padding: 0.25rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
    }

    .view-toggle {
        padding: 0.5rem 0.75rem;
        border: none;
        background: transparent;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .view-toggle:hover {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .view-toggle.active {
        background: #10b981;
        color: white;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
    }

    /* Results Summary */
    .results-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-radius: var(--radius-md);
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .results-count {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .results-count strong {
        color: var(--text-primary);
        font-weight: 600;
    }

    .clear-filters {
        background: #ef4444;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .clear-filters:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
    }

    .filter-select {
        padding: 0.875rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 150px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #10b981;
        background: white;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    /* Enhanced Events Container */
    .events-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .events-container.list-view {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .events-container.list-view .event-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1rem 1.5rem;
    }

    .events-container.list-view .card-header {
        margin-bottom: 0;
        flex-direction: row;
        align-items: center;
        min-width: 200px;
    }

    .events-container.list-view .card-content {
        flex: 1;
        margin-bottom: 0;
    }

    .events-container.list-view .card-title {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .events-container.list-view .card-description {
        font-size: 0.8rem;
        -webkit-line-clamp: 1;
        margin-bottom: 0;
    }

    .events-container.list-view .event-date,
    .events-container.list-view .event-location {
        display: inline-flex;
        margin-right: 1rem;
        margin-bottom: 0;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .events-container.list-view .card-actions {
        margin-left: auto;
    }

    /* Enhanced Event Cards */
    .event-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .event-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #10b981, #059669);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .event-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        border-color: #10b981;
    }

    .event-card:hover::before {
        opacity: 1;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .card-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.3s ease;
    }

    .status-badge.published {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
        border: 1px solid #10b981;
    }

    .status-badge.draft {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        border: 1px solid #f59e0b;
    }

    .card-meta .date {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 500;
        padding: 0.25rem 0.5rem;
        background: #f1f5f9;
        border-radius: var(--radius-sm);
        display: inline-block;
        width: fit-content;
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
    }

    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .btn-icon i {
        font-size: 14px;
        line-height: 1;
    }

    .btn-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        color: white;
    }

    .btn-icon[title="View"]:hover {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border-color: #3b82f6;
    }

    .btn-icon[title="Edit"]:hover {
        background: linear-gradient(135deg, #10b981, #059669);
        border-color: #10b981;
    }

    .btn-icon.delete:hover {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border-color: #ef4444;
    }

    .card-content {
        margin-bottom: 1rem;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.75rem 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .card-description {
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0 0 1rem 0;
        font-size: 0.875rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .event-date, .event-location {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        padding: 0.5rem;
        background: #f8fafc;
        border-radius: var(--radius-sm);
        border-left: 3px solid #10b981;
    }

    .event-date i, .event-location i {
        color: #10b981;
        width: 16px;
        text-align: center;
    }

    .event-date span, .event-location span {
        font-weight: 500;
    }

    /* Empty State Enhancement */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: var(--radius-lg);
        border: 2px dashed var(--border-color);
        margin: 2rem 0;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: var(--text-secondary);
    }

    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
    }

    .empty-state p {
        color: var(--text-secondary);
        margin: 0 0 2rem 0;
        font-size: 1rem;
    }

    .empty-state .btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 0.875rem 1.5rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: none;
    }

    .empty-state .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
    }

    /* Pagination Enhancement */
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color);
    }

    /* Loading States */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-lg);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid #10b981;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .mobile-menu-btn {
            display: block !important;
        }

        .content-controls {
            flex-direction: column;
        }

        .search-container,
        .filter-container {
            min-width: auto;
        }
    }

    @media (max-width: 768px) {
        .table-view {
            display: none;
        }

        .cards-view {
            display: grid;
        }

        .content-header {
            padding: 1.5rem;
        }

        .data-view {
            padding: 1.5rem;
        }

        .card-actions {
            flex-direction: column;
        }

        .card-actions .btn {
            flex: none;
        }

        .card-meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .mobile-menu-btn {
            display: block !important;
        }
    }

    @media (max-width: 480px) {
        .page-header {
            padding: 1.5rem;
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .header-actions {
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .event-card {
            padding: 1rem;
        }

        .card-title {
            font-size: 1rem;
        }

        .card-description {
            font-size: 0.8rem;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .status-badge {
            font-size: 0.625rem;
            padding: 0.25rem 0.5rem;
        }

        .event-date, .event-location {
            font-size: 0.8rem;
            padding: 0.375rem;
        }

        .empty-state {
            padding: 2rem 1rem;
        }

        .empty-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
        }

        .empty-state p {
            font-size: 0.875rem;
        }
    }

    /* Animation Enhancements */
    .event-card {
        animation: fadeInUp 0.6s ease-out;
    }

    .event-card:nth-child(1) { animation-delay: 0.1s; }
    .event-card:nth-child(2) { animation-delay: 0.2s; }
    .event-card:nth-child(3) { animation-delay: 0.3s; }
    .event-card:nth-child(4) { animation-delay: 0.4s; }
    .event-card:nth-child(5) { animation-delay: 0.5s; }
    .event-card:nth-child(6) { animation-delay: 0.6s; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Focus States for Accessibility */
    .btn-icon:focus,
    .search-box input:focus,
    .filter-select:focus {
        outline: 2px solid #10b981;
        outline-offset: 2px;
    }

    /* Print Styles */
    @media print {
        .sidebar,
        .header-actions,
        .content-controls,
        .card-actions,
        .pagination-container {
            display: none !important;
        }

        .event-card {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #000;
        }

        .events-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Mobile menu toggle function
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
    }

    // Enhanced filter and search functionality for table and cards
    function filterEvents() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const sortFilter = document.getElementById('sortFilter').value;
        
        // Filter table rows
        const tableRows = document.querySelectorAll('.enhanced-table tbody tr');
        const eventCards = document.querySelectorAll('.cards-view .event-card');

        // Filter table rows
        tableRows.forEach(row => {
            const title = row.querySelector('.content-preview h4')?.textContent.toLowerCase() || '';
            const description = row.querySelector('.content-preview p')?.textContent.toLowerCase() || '';
            const status = row.dataset.status;
            const location = row.querySelector('.content-preview')?.textContent.toLowerCase() || '';

            const matchesSearch = title.includes(searchTerm) ||
                                description.includes(searchTerm) ||
                                location.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Filter mobile cards
        eventCards.forEach(card => {
            const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
            const description = card.querySelector('p')?.textContent.toLowerCase() || '';
            const status = card.dataset.status;
            const location = card.textContent.toLowerCase();

            const matchesSearch = title.includes(searchTerm) ||
                                description.includes(searchTerm) ||
                                location.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;

            if (matchesSearch && matchesStatus) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        // Update visible count
        const visibleTableRows = Array.from(tableRows).filter(row => row.style.display !== 'none').length;
        const visibleCards = Array.from(eventCards).filter(card => card.style.display !== 'none').length;
        const totalCount = Math.max(tableRows.length, eventCards.length);
        const visibleCount = Math.max(visibleTableRows, visibleCards);
        
        updateResultsSummary(visibleCount, totalCount);
    }

    // Loading state functions
    function showLoadingState() {
        const container = document.querySelector('.events-container');
        if (!container.querySelector('.loading-overlay')) {
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'loading-overlay';
            loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
            container.style.position = 'relative';
            container.appendChild(loadingOverlay);
        }
        container.querySelector('.loading-overlay').classList.add('active');
    }

    function hideLoadingState() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }

    // No results message
    function showNoResultsMessage() {
        const container = document.querySelector('.events-container');
        if (!container.querySelector('.no-results')) {
            const noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No Events Found</h3>
                    <p>No events match your current search criteria. Try adjusting your filters or search terms.</p>
                </div>
            `;
            container.appendChild(noResults);
        }
        container.querySelector('.no-results').style.display = 'block';
    }

    function hideNoResultsMessage() {
        const noResults = document.querySelector('.no-results');
        if (noResults) {
            noResults.style.display = 'none';
        }
    }

    // Enhanced search with debouncing and suggestions
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const value = this.value.toLowerCase();

        if (value.length > 0) {
            showSearchSuggestions(value);
        } else {
            hideSearchSuggestions();
        }

        searchTimeout = setTimeout(filterEvents, 300);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.length > 0) {
            showSearchSuggestions(this.value.toLowerCase());
        }
    });

    searchInput.addEventListener('blur', function() {
        setTimeout(hideSearchSuggestions, 200);
    });

    function showSearchSuggestions(query) {
        const eventCards = document.querySelectorAll('.event-card');
        const suggestions = new Set();

        eventCards.forEach(card => {
            const title = card.dataset.title;
            const description = card.querySelector('.card-description')?.textContent.toLowerCase() || '';
            const location = card.querySelector('.event-location span')?.textContent.toLowerCase() || '';

            if (title.includes(query)) suggestions.add(card.querySelector('.card-title').textContent);
            if (location.includes(query)) suggestions.add(card.querySelector('.event-location span')?.textContent);
        });

        if (suggestions.size > 0) {
            searchSuggestions.innerHTML = Array.from(suggestions).slice(0, 5).map(suggestion =>
                `<div class="suggestion-item" onclick="selectSuggestion('${suggestion}')">${suggestion}</div>`
            ).join('');
            searchSuggestions.style.display = 'block';
        } else {
            hideSearchSuggestions();
        }
    }

    function hideSearchSuggestions() {
        searchSuggestions.style.display = 'none';
    }

    function selectSuggestion(suggestion) {
        searchInput.value = suggestion;
        hideSearchSuggestions();
        filterEvents();
    }

    // Real-time filter updates
    document.getElementById('statusFilter').addEventListener('change', filterEvents);
    document.getElementById('sortFilter').addEventListener('change', filterEvents);

    // View toggle functionality
    document.querySelectorAll('.view-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const view = this.dataset.view;
            const container = document.querySelector('.events-container');

            // Update active state
            document.querySelectorAll('.view-toggle').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Update container class
            if (view === 'list') {
                container.classList.add('list-view');
            } else {
                container.classList.remove('list-view');
            }

            // Save preference
            localStorage.setItem('eventsViewPreference', view);
        });
    });

    // Load saved view preference
    const savedView = localStorage.getItem('eventsViewPreference');
    if (savedView) {
        const toggle = document.querySelector(`[data-view="${savedView}"]`);
        if (toggle) {
            toggle.click();
        }
    }

    // Clear filters functionality
    document.getElementById('clearFilters').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('sortFilter').value = 'newest';
        hideSearchSuggestions();
        filterEvents();
    });

    // Update results summary
    function updateResultsSummary(visibleCount, totalCount) {
        // Update content count in header
        const contentCount = document.querySelector('.content-count');
        if (contentCount) {
            contentCount.textContent = `${visibleCount} events`;
        }

        const hasFilters = document.getElementById('searchInput').value ||
                          document.getElementById('statusFilter').value;
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }

        // Escape to clear search
        if (e.key === 'Escape') {
            const searchInput = document.getElementById('searchInput');
            if (searchInput.value) {
                searchInput.value = '';
                filterEvents();
            }
        }
    });

    // Card interaction enhancements
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to cards
        const eventCards = document.querySelectorAll('.event-card');
        eventCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Add click to expand functionality
        eventCards.forEach(card => {
            const description = card.querySelector('.card-description');
            if (description && description.scrollHeight > description.clientHeight) {
                const expandBtn = document.createElement('button');
                expandBtn.className = 'expand-btn';
                expandBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Show more';
                expandBtn.style.cssText = `
                    background: none;
                    border: none;
                    color: #10b981;
                    font-size: 0.75rem;
                    cursor: pointer;
                    margin-top: 0.5rem;
                    display: flex;
                    align-items: center;
                    gap: 0.25rem;
                `;

                expandBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (description.style.webkitLineClamp === 'none') {
                        description.style.webkitLineClamp = '3';
                        this.innerHTML = '<i class="fas fa-chevron-down"></i> Show more';
                    } else {
                        description.style.webkitLineClamp = 'none';
                        this.innerHTML = '<i class="fas fa-chevron-up"></i> Show less';
                    }
                });

                description.parentNode.insertBefore(expandBtn, description.nextSibling);
            }
        });

        // Add tooltips to action buttons
        const actionButtons = document.querySelectorAll('.btn-icon');
        actionButtons.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                const title = this.getAttribute('title');
                if (title) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = title;
                    tooltip.style.cssText = `
                        position: absolute;
                        background: #1f2937;
                        color: white;
                        padding: 0.5rem;
                        border-radius: 0.375rem;
                        font-size: 0.75rem;
                        white-space: nowrap;
                        z-index: 1000;
                        bottom: 100%;
                        left: 50%;
                        transform: translateX(-50%);
                        margin-bottom: 0.5rem;
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    `;

                    this.style.position = 'relative';
                    this.appendChild(tooltip);

                    setTimeout(() => {
                        tooltip.style.opacity = '1';
                    }, 100);
                }
            });

            btn.addEventListener('mouseleave', function() {
                const tooltip = this.querySelector('.tooltip');
                if (tooltip) {
                    tooltip.remove();
                }
            });
        });
    });

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

    // Delete event with SweetAlert
    async function deleteEvent(id, title) {
        const result = await Swal.fire({
            title: 'Delete Event?',
            text: `Are you sure you want to delete "${title}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        });
        
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Deleting Event...',
                text: 'Please wait while we delete the event.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/department-admin/events/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

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
</script>
@endsection
