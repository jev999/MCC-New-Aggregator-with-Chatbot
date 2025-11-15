@extends('layouts.app')

@section('title', 'My Announcements - Department Admin')

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
            <li><a href="{{ route('department-admin.announcements.index') }}" class="active">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('department-admin.events.index') }}">
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
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="header-text">
                    <h1>My Announcements</h1>
                    <p>Manage and publish announcements for {{ auth('admin')->user()->department }} Department</p>
                </div>
            </div>
            <div class="header-actions">
            <a href="{{ route('department-admin.announcements.create') }}" class="btn-create-announcement">
    <span class="btn-plus-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
    <span class="btn-text">Create Announcement</span>
    <span class="btn-hover-effect"></span>
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
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $announcements->where('is_published', true)->count() }}</h3>
                    <p>Published Announcements</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon draft">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $announcements->where('is_published', false)->count() }}</h3>
                    <p>Draft Announcements</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-copy"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $announcements->count() }}</h3>
                    <p>Total Announcements</p>
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
                    <h2><i class="fas fa-list"></i> All Announcements</h2>
                    <span class="content-count">{{ $announcements->count() }} announcements</span>
                </div>
                <div class="content-controls">
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search announcements..." class="search-input">
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
                                <th><i class="fas fa-building"></i> Department</th>
                                <th><i class="fas fa-toggle-on"></i> Status</th>
                                <th><i class="fas fa-calendar"></i> Created</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $announcement)
                                <tr data-status="{{ $announcement->is_published ? 'published' : 'draft' }}" data-department="{{ auth('admin')->user()->department }}">
                                    <td>
                                        <span class="id-badge">#{{ $announcement->id }}</span>
                                    </td>
                                    <td>
                                        <div class="content-preview">
                                            <h4>{{ Str::limit($announcement->title, 50) }}</h4>
                                            <p>{{ Str::limit($announcement->content, 100) }}</p>
                                            @if($announcement->hasMedia)
                                                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                                                    @if(in_array($announcement->hasMedia, ['image', 'both']))
                                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #dcfce7; color: #166534; border-radius: 4px; font-size: 0.75rem;">
                                                            <i class="fas fa-image"></i> Image
                                                        </span>
                                                    @endif
                                                    @if(in_array($announcement->hasMedia, ['video', 'both']))
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
                                        <span class="department-badge">{{ auth('admin')->user()->department }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $announcement->is_published ? 'published' : 'draft' }}">
                                            <i class="fas fa-{{ $announcement->is_published ? 'check' : 'eye-slash' }}"></i>
                                            {{ $announcement->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div class="date">{{ $announcement->created_at->format('M d, Y') }}</div>
                                            <div class="time">{{ $announcement->created_at->format('g:i A') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('department-admin.announcements.show', $announcement) }}" class="btn-action-view" title="View">
                                                <i class="fas fa-eye"></i>
                                                <span>View</span>
                                            </a>
                                            <a href="{{ route('department-admin.announcements.edit', $announcement) }}" class="btn-action-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                                <span>Edit</span>
                                            </a>
                                            <button type="button" class="btn-action-delete" title="Delete" onclick="deleteAnnouncement({{ $announcement->id }}, '{{ addslashes($announcement->title) }}')">
                                                <i class="fas fa-trash"></i>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards View -->
                <div class="cards-view">
                    @forelse($announcements as $announcement)
                        <div class="announcement-card" data-status="{{ $announcement->is_published ? 'published' : 'draft' }}" data-department="{{ auth('admin')->user()->department }}">
                            <div class="card-header">
                                <div class="card-id">#{{ $announcement->id }}</div>
                                <span class="status-badge {{ $announcement->is_published ? 'published' : 'draft' }}">
                                    <i class="fas fa-{{ $announcement->is_published ? 'check' : 'eye-slash' }}"></i>
                                    {{ $announcement->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </div>
                            <div class="card-content">
                                <h3>{{ $announcement->title }}</h3>
                                <p>{{ Str::limit($announcement->content, 120) }}</p>
                                @if($announcement->hasMedia)
                                    <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        @if(in_array($announcement->hasMedia, ['image', 'both']))
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #dcfce7; color: #166534; border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-image"></i> Image
                                            </span>
                                        @endif
                                        @if(in_array($announcement->hasMedia, ['video', 'both']))
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #fecaca; color: #991b1b; border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-video"></i> Video
                                            </span>
                                        @endif
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
                                    <span>{{ $announcement->created_at->format('M d, Y \a\t g:i A') }}</span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <a href="{{ route('department-admin.announcements.show', $announcement) }}" class="btn-action-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('department-admin.announcements.edit', $announcement) }}" class="btn-action-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button" class="btn-action-delete" onclick="deleteAnnouncement({{ $announcement->id }}, '{{ addslashes($announcement->title) }}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h3>No announcements yet</h3>
                            <p>Create your first announcement to get started.</p>
                            <a href="{{ route('department-admin.announcements.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Announcement
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
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
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
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

    .header-actions .btn {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        backdrop-filter: blur(10px);
    }

    .header-actions .btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }
/* Enhanced Create Announcement Button */
.btn-create-announcement {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
    border-radius: var(--radius-lg);
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3), 0 1px 3px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    border: none;
    cursor: pointer;
}

.btn-create-announcement:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.4), 0 3px 6px rgba(0, 0, 0, 0.1);
    color: white;
}

.btn-create-announcement:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.4);
}

.btn-plus-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.btn-create-announcement:hover .btn-plus-icon {
    transform: rotate(90deg);
}

.btn-hover-effect {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.7s ease;
}

.btn-create-announcement:hover .btn-hover-effect {
    left: 100%;
}

/* For the header actions area */
.header-actions .btn-create-announcement {
    margin-left: auto;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-create-announcement {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }
    
    .btn-plus-icon svg {
        width: 16px;
        height: 16px;
    }
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
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .stat-icon.draft {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #10b981, #059669);
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
        min-width: 250px;
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

    .department-badge {
        background: #e0e7ff;
        color: #3730a3;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
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

    .date-info .date {
        font-weight: 500;
        color: var(--text-primary);
    }

    .date-info .time {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .action-buttons .btn {
        padding: 0.5rem;
        min-width: auto;
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

    .announcement-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .announcement-card:hover {
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

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: white;
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
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Alerts */
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

    .alert-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .alert-icon {
        font-size: 1.25rem;
        margin-top: 0.125rem;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .mobile-menu-btn {
            display: block !important;
        }

        .page-header {
            flex-direction: column;
            gap: 1.5rem;
            text-align: center;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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

        .page-header {
            padding: 1.5rem;
        }

        .header-text h1 {
            font-size: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .stat-card {
            padding: 1rem;
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
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Delete announcement with SweetAlert
    async function deleteAnnouncement(id, title) {
        const result = await Swal.fire({
            title: 'Delete Announcement?',
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
                title: 'Deleting Announcement...',
                text: 'Please wait while we delete the announcement.',
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
            form.action = `/department-admin/announcements/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Mobile menu toggle with overlay + scroll lock
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        if (!sidebar) return;
        const willOpen = !sidebar.classList.contains('open');
        sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('active', willOpen);
        document.body.style.overflow = willOpen ? 'hidden' : '';
    }

    function closeSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
        
        // Filter table rows
        const tableRows = document.querySelectorAll('.enhanced-table tbody tr');
        const announcementCards = document.querySelectorAll('.announcement-card');
        
        // Filter table rows
        tableRows.forEach(row => {
            if (row.querySelector('.empty-state')) return; // Skip empty state row
            
            const titleElement = row.querySelector('.content-preview h4');
            const statusElement = row.querySelector('.status-badge');
            
            if (!titleElement || !statusElement) return;
            
            const title = titleElement.textContent.toLowerCase();
            const status = statusElement.classList.contains('published') ? 'published' : 'draft';
            
            const matchesSearch = title.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
        
        // Filter mobile cards
        announcementCards.forEach(card => {
            const titleElement = card.querySelector('h3');
            const statusElement = card.querySelector('.status-badge');
            
            if (!titleElement || !statusElement) return;
            
            const title = titleElement.textContent.toLowerCase();
            const status = statusElement.classList.contains('published') ? 'published' : 'draft';
            
            const matchesSearch = title.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            card.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }
    
    // Add event listeners for real-time filtering
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const sortFilter = document.getElementById('sortFilter');
        
        if (searchInput) {
            searchInput.addEventListener('input', filterAnnouncements);
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', filterAnnouncements);
        }
        
        if (sortFilter) {
            sortFilter.addEventListener('change', filterAnnouncements);
        }
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
        if (window.innerWidth > 1024) {
            closeSidebar();
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
</script>
@endsection

