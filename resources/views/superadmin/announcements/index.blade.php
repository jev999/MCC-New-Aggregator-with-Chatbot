@extends('layouts.app')

@section('title', 'Announcements Management - Super Admin')

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                <i class="fas fa-users-cog"></i> DepartmentAdmin Management
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
            <li><a href="{{ route('superadmin.admin-access') }}">
                <i class="fas fa-clipboard-list"></i> Admin Access Logs
            </a></li>
            <li><a href="{{ route('superadmin.backup') }}">
                <i class="fas fa-database"></i> Database Backup
            </a></li>
                
            
        </ul>
    </div>
    
    <div class="main-content">
        <!-- Enhanced Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="header-text">
                    <h1>Announcements Management</h1>
                    <p>Manage all campus announcements across departments</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('superadmin.announcements.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>New Announcement</span>
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
        
        <!-- Enhanced Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon published">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $announcements->where('is_published', true)->count() }}</h3>
                    <p>Published</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon draft">
                    <i class="fas fa-eye-slash"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $announcements->where('is_published', false)->count() }}</h3>
                    <p>Drafts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $announcements->count() }}</h3>
                    <p>Total</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon departments">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $announcements->groupBy('admin.department')->count() }}</h3>
                    <p>Departments</p>
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
                        <select id="departmentFilter" class="filter-select">
                            <option value="">All Departments</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSHM">BSHM</option>
                            <option value="BSBA">BSBA</option>
                            <option value="BEED">BEED</option>
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
                                <tr data-status="{{ $announcement->is_published ? 'published' : 'draft' }}" data-department="{{ $announcement->admin->department ?? 'N/A' }}">
                                    <td>
                                        <span class="id-badge">#{{ $announcement->id }}</span>
                                    </td>
                                    <td>
                                        <div class="content-preview">
                                            <h4>{{ Str::limit($announcement->title, 50) }}</h4>
                                            <p>{{ Str::limit($announcement->content, 100) }}</p>
                                            @if($announcement->image_path || $announcement->video_path || $announcement->csv_path)
                                                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                                                    @if($announcement->image_path)
                                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #dcfce7; color: #166534; border-radius: 4px; font-size: 0.75rem;">
                                                            <i class="fas fa-image"></i> Image
                                                        </span>
                                                    @endif
                                                    @if($announcement->video_path)
                                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #fecaca; color: #991b1b; border-radius: 4px; font-size: 0.75rem;">
                                                            <i class="fas fa-video"></i> Video
                                                        </span>
                                                    @endif
                                                    @if($announcement->csv_path)
                                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #fef3c7; color: #92400e; border-radius: 4px; font-size: 0.75rem;">
                                                            <i class="fas fa-file-csv"></i> CSV
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
                                            <span>{{ $announcement->admin->username }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="department-badge">{{ $announcement->admin->department ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $announcement->is_published ? 'published' : 'draft' }}">
                                            <i class="fas fa-{{ $announcement->is_published ? 'check' : 'eye-slash' }}"></i>
                                            {{ $announcement->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div class="date">{{ $announcement->created_at->setTimezone(config('app.timezone', 'UTC'))->format('M d, Y') }}</div>
                                            <div class="time">{{ $announcement->created_at->setTimezone(config('app.timezone', 'UTC'))->format('g:i A') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('superadmin.announcements.show', $announcement) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('superadmin.announcements.edit', $announcement) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('superadmin.announcements.destroy', $announcement) }}" style="display: inline;" onsubmit="return handleAnnouncementDelete(event, '{{ $announcement->title }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-bullhorn"></i>
                                            </div>
                                            <h3>No announcements yet</h3>
                                            <p>Create your first announcement to get started.</p>
                                            <a href="{{ route('superadmin.announcements.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create Announcement
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards View -->
                <div class="cards-view">
                    @forelse($announcements as $announcement)
                        <div class="announcement-card" data-status="{{ $announcement->is_published ? 'published' : 'draft' }}" data-department="{{ $announcement->admin->department ?? 'N/A' }}">
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
                                @if($announcement->image_path || $announcement->video_path || $announcement->csv_path)
                                    <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        @if($announcement->image_path)
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #dcfce7; color: #166534; border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-image"></i> Image
                                            </span>
                                        @endif
                                        @if($announcement->video_path)
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #fecaca; color: #991b1b; border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-video"></i> Video
                                            </span>
                                        @endif
                                        @if($announcement->csv_path)
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #fef3c7; color: #92400e; border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-file-csv"></i> CSV
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="card-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span>{{ $announcement->admin->username }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-building"></i>
                                    <span>{{ $announcement->admin->department ?? 'N/A' }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $announcement->created_at->setTimezone(config('app.timezone', 'UTC'))->format('M d, Y \a\t g:i A') }}</span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button onclick="openShowModal({{ $announcement->id }})" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <a href="{{ route('superadmin.announcements.edit', $announcement) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('superadmin.announcements.destroy', $announcement) }}" style="display: inline;" onsubmit="return handleAnnouncementDelete(event, '{{ $announcement->title }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h3>No announcements yet</h3>
                            <p>Create your first announcement to get started.</p>
                            <a href="{{ route('superadmin.announcements.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Announcement
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Show Announcement Modal -->
<div id="showAnnouncementModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="showAnnouncementTitle">Announcement</h3>
            <button onclick="closeShowModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-content">
            <div id="showAnnouncementContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div id="editAnnouncementModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="editAnnouncementTitle">Edit Announcement</h3>
            <button onclick="closeEditModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-content">
            <div id="editAnnouncementContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Create Announcement Modal -->
<div id="createAnnouncementModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Create New Announcement</h3>
            <button onclick="closeCreateAnnouncementModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-content">
            <div id="createAnnouncementContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
    /* Enhanced Page Header */
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
    }

    .header-text p {
        margin: 0;
        opacity: 0.9;
        font-size: 1rem;
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

    /* Enhanced Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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

    /* Enhanced Alert */
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

    /* Enhanced Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
    }

    .empty-state p {
        color: var(--text-secondary);
        margin: 0 0 2rem 0;
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

        /* Checkbox Styling */
        .modal-edit .checkbox-group {
            margin: 1rem 0;
        }

        .modal-edit .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .modal-edit .checkbox-label:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .modal-edit .checkbox-input {
            display: none;
        }

        .modal-edit .checkbox-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s ease;
            background: white;
        }

        .modal-edit .checkbox-input:checked + .checkbox-custom {
            background: #3b82f6;
            border-color: #3b82f6;
        }

        .modal-edit .checkbox-input:checked + .checkbox-custom::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .modal-edit .checkbox-text {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .modal-edit .checkbox-text i {
            color: #3b82f6;
        }

        .modal-edit .form-help {
            display: block;
            margin-top: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .modal-edit .modal-footer {
            padding: 2rem;
            border-top: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        /* Enhanced Button Styles for Modal */
        .modal-edit .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
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
            min-width: 140px;
        }

        .modal-edit .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .modal-edit .btn:hover::before {
            left: 100%;
        }

        .modal-edit .btn:active {
            transform: translateY(1px);
        }

        .modal-edit .btn-enhanced {
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 2px solid transparent;
        }

        .modal-edit .btn-enhanced:hover {
            transform: translateY(-2px) scale(1.02);
        }

        .modal-edit .btn-enhanced:active {
            transform: translateY(0);
        }

        .modal-edit .btn-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: 2px solid transparent;
        }

        .modal-edit .btn-green:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4), 0 10px 10px -5px rgba(16, 185, 129, 0.04);
        }

        .modal-edit .btn-red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: 2px solid transparent;
        }

        .modal-edit .btn-red:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4);
        }

        /* Loading Button States */
        .modal-edit .btn-loading {
            position: relative;
        }

        .modal-edit .btn-loading.loading .btn-content {
            opacity: 0;
        }

        .modal-edit .btn-loading.loading .btn-loading-spinner {
            display: flex !important;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
        }

        .modal-edit .btn-loading.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .modal-edit .modal-dialog {
                margin: 1rem;
                max-width: calc(100% - 2rem);
            }

            .modal-edit .announcement-info {
                flex-direction: column;
                text-align: center;
            }

            .modal-edit .form-row {
                grid-template-columns: 1fr;
            }

            .modal-edit .modal-footer {
                flex-direction: column;
                gap: 0.75rem;
            }

            .modal-edit .btn {
                width: 100%;
                justify-content: center;
            }
        }
    }

    /* Modal Styles - Matching Events Show Page */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(12px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-container {
        background: white;
        border-radius: 24px;
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.25),
            0 0 0 1px rgba(255, 255, 255, 0.1);
        max-width: min(1400px, 90vw);
        width: 100%;
        max-height: min(95vh, 900px);
        min-height: 600px;
        overflow: hidden;
        transform: scale(0.9) rotateX(15deg);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .modal-overlay.active .modal-container {
        transform: scale(1) rotateX(0deg);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .modal-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #3b82f6, #8b5cf6, #ef4444, #f59e0b);
        background-size: 300% 100%;
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .modal-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .modal-content {
        padding: 2rem;
        max-height: calc(min(95vh, 900px) - 140px);
        min-height: 400px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #667eea #f1f5f9;
    }

    .modal-content::-webkit-scrollbar {
        width: 8px;
    }

    .modal-content::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .modal-content::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 4px;
    }

    .modal-content::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #764ba2, #667eea);
    }

    .loading-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        text-align: center;
        color: var(--text-secondary);
    }

    .loading-spinner {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }

    .loading-spinner i {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .error-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        text-align: center;
        color: #ef4444;
    }

    .error-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    /* Enhanced Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        border-radius: 12px;
        cursor: pointer;
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

    /* Primary Button */
    .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4), 0 10px 10px -5px rgba(16, 185, 129, 0.04);
    }

    /* Info Button */
    .btn-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
    }

    /* Warning Button */
    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4);
    }

    /* Danger Button */
    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4);
    }

    /* Small Button */
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        border-radius: 8px;
    }

    /* Action Buttons Styling */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .action-buttons .btn {
        min-width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-buttons .btn i {
        font-size: 0.875rem;
    }

    /* Card Action Buttons */
    .card-actions .btn {
        flex: 1;
        min-width: auto;
        justify-content: center;
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }

    /* Image Modal Styles */
    .image-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        cursor: pointer;
    }

    .image-modal-container {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .full-screen-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    }

    .image-modal-close {
        position: absolute;
        top: -50px;
        right: -50px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .image-modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    /* Create Modal Form Styles */
    .announcement-form {
        padding: 0;
    }

    .form-section {
        margin-bottom: 2.5rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .form-section h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section h3 i {
        color: var(--primary-color);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label i {
        color: var(--primary-color);
        width: 16px;
    }

    .form-input,
    .form-textarea,
    .form-select {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
    }

    .form-input.error,
    .form-textarea.error,
    .form-select.error {
        border-color: #ef4444;
    }

    .error-message {
        display: block;
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        font-weight: 500;
        color: var(--text-primary);
    }

    .checkbox-input {
        display: none;
    }

    .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-sm);
        position: relative;
        transition: all 0.3s ease;
    }

    .checkbox-input:checked + .checkbox-custom {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .checkbox-input:checked + .checkbox-custom::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
    }

    .checkbox-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-help {
        color: var(--text-secondary);
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Creation Info Styles */
    .creation-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: var(--radius-md);
        border: 1px solid #e2e8f0;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-label i {
        color: var(--primary-color);
        width: 14px;
    }

    .info-value {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
        padding: 0.5rem 0.75rem;
        background: white;
        border-radius: var(--radius-sm);
        border: 1px solid #e2e8f0;
    }

    #currentTime {
        font-family: 'Courier New', monospace;
        color: var(--primary-color);
        font-weight: 600;
    }

    .file-upload-area {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: rgba(79, 70, 229, 0.05);
    }

    .file-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .file-upload-content i {
        font-size: 2rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .file-upload-content p {
        margin: 0 0 0.5rem 0;
        font-weight: 500;
        color: var(--text-primary);
    }

    .file-upload-content small {
        color: var(--text-secondary);
    }

    .file-previews {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .file-preview-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 1rem;
        text-align: center;
        position: relative;
    }

    .file-preview-item img {
        max-width: 100%;
        max-height: 120px;
        border-radius: var(--radius-sm);
        margin-bottom: 0.5rem;
    }

    .file-preview-item .file-name {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        word-break: break-word;
    }

    .file-preview-item .file-size {
        font-size: 0.7rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
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

    .alert-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .alert-icon {
        font-size: 1.25rem;
        margin-top: 0.125rem;
    }

    .alert-content strong {
        display: block;
        margin-bottom: 0.25rem;
    }

    /* Responsive Modal */
    @media (max-width: 1200px) {
        .modal-container {
            max-width: min(95vw, 1000px);
            min-height: 550px;
        }
    }

    @media (max-width: 992px) {
        .modal-container {
            max-width: min(98vw, 800px);
            min-height: 500px;
        }
    }

    @media (max-width: 768px) {
        .modal-container {
            max-width: 100vw;
            max-height: 100vh;
            min-height: 100vh;
            border-radius: 0;
        }

        .modal-header {
            padding: 1rem 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.125rem;
        }

        .modal-close {
            width: 35px;
            height: 35px;
            font-size: 0.875rem;
        }
    }
</style>

<script>
    // Enhanced search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        filterContent();
    });

    // Enhanced filter functionality
    document.getElementById('statusFilter').addEventListener('change', function() {
        filterContent();
    });

    document.getElementById('departmentFilter').addEventListener('change', function() {
        filterContent();
    });

    function filterContent() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const departmentFilter = document.getElementById('departmentFilter').value;

        // Filter table rows
        const tableRows = document.querySelectorAll('#dataTable tbody tr');
        tableRows.forEach(row => {
            if (row.querySelector('.empty-state')) return;

            const text = row.textContent.toLowerCase();
            const status = row.getAttribute('data-status');
            const department = row.getAttribute('data-department');

            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesDepartment = !departmentFilter || department === departmentFilter;

            if (matchesSearch && matchesStatus && matchesDepartment) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Filter cards
        const cards = document.querySelectorAll('.announcement-card');
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const status = card.getAttribute('data-status');
            const department = card.getAttribute('data-department');

            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesDepartment = !departmentFilter || department === departmentFilter;

            if (matchesSearch && matchesStatus && matchesDepartment) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Mobile responsiveness
    function handleResize() {
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        if (window.innerWidth <= 1024) {
            mobileMenuBtn.style.display = 'block';
        } else {
            mobileMenuBtn.style.display = 'none';
            document.querySelector('.sidebar').classList.remove('open');
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize();

    // SweetAlert delete confirmation
    async function handleAnnouncementDelete(event, announcementTitle) {
        event.preventDefault();
        
        const result = await Swal.fire({
            title: 'Delete Announcement?',
            text: `Are you sure you want to delete "${announcementTitle}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        });
        
        if (result.isConfirmed) {
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
            
            event.target.submit();
        }
        
        return false;
    }

    // Show Announcement Modal Functions
    function openShowModal(announcementId) {
        const modal = document.getElementById('showAnnouncementModal');
        const content = document.getElementById('showAnnouncementContent');
        const title = document.getElementById('showAnnouncementTitle');
        
        // Show modal with loading state
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
        
        // Show loading spinner
        content.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading announcement details...</p>
            </div>
        `;
        
        // Load announcement content via AJAX using modal-specific route
        fetch(`/superadmin/announcements/${announcementId}/modal-show`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load announcement');
                }
                return response.text();
            })
            .then(html => {
                content.innerHTML = html;
                // Update modal title - extract from the loaded content
                const titleElement = content.querySelector('.content-header h2');
                if (titleElement) {
                    title.textContent = titleElement.textContent;
                }
            })
            .catch(error => {
                console.error('Error loading announcement:', error);
                content.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load announcement details. Please try again.</p>
                    </div>
                `;
            });
    }

    function closeShowModal() {
        const modal = document.getElementById('showAnnouncementModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // Edit Modal Functions
    function openEditModal(announcementId) {
        const modal = document.getElementById('editAnnouncementModal');
        const content = document.getElementById('editAnnouncementContent');
        const title = document.getElementById('editAnnouncementTitle');
        
        content.innerHTML = `
            <div class="loading-state">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <p>Loading edit form...</p>
            </div>
        `;
        
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        document.body.style.overflow = 'hidden';
        
        fetch(`/superadmin/announcements/${announcementId}/modal-edit`)
            .then(response => response.text())
            .then(html => {
                content.innerHTML = html;
                
                // Update modal title - extract from the loaded content
                const titleElement = content.querySelector('#edit_title');
                if (titleElement) {
                    title.textContent = `Edit: ${titleElement.value}`;
                }
                
                // Set up form submission handler
                const form = content.querySelector('#editAnnouncementForm');
                if (form) {
                    form.setAttribute('onsubmit', 'return handleEditFormSubmit(event)');
                }
                
                initializeEditFormScripts();
            })
            .catch(error => {
                console.error('Error loading edit form:', error);
                content.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading edit form</p></div>';
            });
    }

    function closeEditModal() {
        const modal = document.getElementById('editAnnouncementModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    function handleEditFormSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        submitBtn.disabled = true;
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                closeEditModal();
                location.reload();
            } else {
                throw new Error('Update failed');
            }
        })
        .catch(error => {
            console.error('Error updating announcement:', error);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            alert('Error updating announcement. Please try again.');
        });
        
        return false;
    }

    function initializeEditFormScripts() {
        // Reinitialize any form scripts that might be needed
        const fileInputs = document.querySelectorAll('#editAnnouncementContent input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Add file preview functionality if needed
            });
        });
    }

    function openImageModal(imageSrc) {
        // Create or show image modal
        let imageModal = document.getElementById('imageModal');
        if (!imageModal) {
            imageModal = document.createElement('div');
            imageModal.id = 'imageModal';
            imageModal.className = 'image-modal-overlay';
            imageModal.style.display = 'none';
            imageModal.onclick = closeImageModal;
            imageModal.innerHTML = `
                <div class="image-modal-container">
                    <button class="image-modal-close" onclick="closeImageModal()">
                        <i class="fas fa-times"></i>
                    </button>
                    <img id="fullScreenImage" src="" alt="Full Screen Image" class="full-screen-image">
                </div>
            `;
            document.body.appendChild(imageModal);
        }
        
        const image = document.getElementById('fullScreenImage');
        image.src = imageSrc;
        imageModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.stat-card, .announcement-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });

    // Create Announcement Modal Functions
    function openCreateAnnouncementModal() {
        const modal = document.getElementById('createAnnouncementModal');
        const content = document.getElementById('createAnnouncementContent');
        
        // Show modal with loading state
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
        document.body.style.overflow = 'hidden';
        
        // Show loading spinner
        content.innerHTML = `
            <div class="loading-state">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <p>Loading create form...</p>
            </div>
        `;
        
        // Load create form via AJAX
        fetch('{{ route('superadmin.announcements.create') }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load create form');
                }
                return response.text();
            })
            .then(html => {
                // Extract the form content from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const formContent = doc.querySelector('#createAnnouncementForm');
                
                if (formContent) {
                    content.innerHTML = formContent.outerHTML;
                    
                    // Initialize form scripts
                    initializeCreateFormScripts();
                } else {
                    throw new Error('Form not found in response');
                }
            })
            .catch(error => {
                console.error('Error loading create form:', error);
                content.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load create form. Please try again.</p>
                        <button onclick="closeCreateAnnouncementModal()" class="btn btn-secondary">Close</button>
                    </div>
                `;
            });
    }

    function closeCreateAnnouncementModal() {
        const modal = document.getElementById('createAnnouncementModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    function initializeCreateFormScripts() {
        // Initialize file upload previews
        const imagesInput = document.getElementById('images');
        const videosInput = document.getElementById('videos');
        
        if (imagesInput) {
            imagesInput.addEventListener('change', function(e) {
                const maxFiles = 2;
                if (e.target.files.length > maxFiles) {
                    alert(`Please select only ${maxFiles} image files maximum.`);
                    e.target.value = '';
                    return;
                }
                
                const previewContainer = document.getElementById('imagesPreview');
                previewContainer.innerHTML = '';
                
                Array.from(e.target.files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(readerEvent) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'file-preview-item';
                        previewItem.innerHTML = `
                            <img src="${readerEvent.target.result}" alt="Image ${index + 1}">
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</div>
                        `;
                        previewContainer.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        if (videosInput) {
            videosInput.addEventListener('change', function(e) {
                const maxFiles = 2;
                if (e.target.files.length > maxFiles) {
                    alert(`Please select only ${maxFiles} video files maximum.`);
                    e.target.value = '';
                    return;
                }
                
                const previewContainer = document.getElementById('videosPreview');
                previewContainer.innerHTML = '';
                
                Array.from(e.target.files).forEach((file, index) => {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'file-preview-item';
                    previewItem.innerHTML = `
                        <i class="fas fa-video" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</div>
                    `;
                    previewContainer.appendChild(previewItem);
                });
            });
        }

        // Initialize real-time clock update
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila'
            });

            const currentTimeElement = document.getElementById('currentTime');
            if (currentTimeElement) {
                currentTimeElement.textContent = timeString;
            }
        }

        // Update time every second
        const timeInterval = setInterval(updateCurrentTime, 1000);
        updateCurrentTime();

        // Set up form submission handler
        const form = document.getElementById('createAnnouncementForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                submitBtn.disabled = true;
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        closeCreateAnnouncementModal();
                        location.reload();
                    } else {
                        throw new Error('Creation failed');
                    }
                })
                .catch(error => {
                    console.error('Error creating announcement:', error);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    alert('Error creating announcement. Please try again.');
                });
            });
        }
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeShowModal();
            closeEditModal();
            closeImageModal();
            closeCreateAnnouncementModal();
        }
    });

    // Close modal when clicking outside
    document.getElementById('createAnnouncementModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateAnnouncementModal();
        }
    });
</script>
@endsection
