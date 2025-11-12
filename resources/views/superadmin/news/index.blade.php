@extends('layouts.app')

@section('title', 'News Management - Super Admin')

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
                <i class="fas fa-users-cog"></i>Department Admin Management
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
                <i class="fas fa-user-graduate"></i> Students
            </a></li>
            <li><a href="{{ route('superadmin.admin-access') }}">
                <i class="fas fa-clipboard-list"></i> Admin Access Logs
            </a></li>
            <li><a href="{{ route('superadmin.backup') }}">
                <i class="fas fa-database"></i> Database Backup
            </a></li>
            <li>
                <form method="POST" action="{{ route('superadmin.logout') }}" style="display: inline; width: 100%;">
                    @csrf
                    
                </form>
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <!-- Enhanced Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="header-text">
                    <h1>News Management</h1>
                    <p>Manage all campus news across departments</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('superadmin.news.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>New Article</span>
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
                    <h3>{{ $news->where('is_published', true)->count() }}</h3>
                    <p>Published</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon draft">
                    <i class="fas fa-eye-slash"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $news->where('is_published', false)->count() }}</h3>
                    <p>Drafts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $news->count() }}</h3>
                    <p>Total</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon departments">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $news->groupBy('admin.department')->count() }}</h3>
                    <p>Departments</p>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Content Container -->
        <div class="content-container">
            <div class="content-header">
                <div class="content-title">
                    <h2><i class="fas fa-list"></i> All News Articles</h2>
                    <span class="content-count">{{ $news->count() }} articles</span>
                </div>
                <div class="content-controls">
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search news..." class="search-input">
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
                            @forelse($news as $article)
                                <tr data-status="{{ $article->is_published ? 'published' : 'draft' }}" data-department="{{ $article->admin->department ?? 'N/A' }}">
                                    <td>
                                        <span class="id-badge">#{{ $article->id }}</span>
                                    </td>
                                    <td>
                                        <div class="content-preview">
                                            <h4>{{ Str::limit($article->title, 50) }}</h4>
                                            <p>{{ Str::limit($article->content, 100) }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span>{{ $article->admin->username }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="department-badge">{{ $article->admin->department ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $article->is_published ? 'published' : 'draft' }}">
                                            <i class="fas fa-{{ $article->is_published ? 'check' : 'eye-slash' }}"></i>
                                            {{ $article->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div class="date">{{ $article->created_at->format('M d, Y') }}</div>
                                            <div class="time">{{ $article->created_at->format('H:i') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('superadmin.news.show', $article) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('superadmin.news.edit', $article) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('superadmin.news.destroy', $article) }}" style="display: inline;" onsubmit="return handleNewsDelete(event, '{{ $article->title }}')">
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
                                                <i class="fas fa-newspaper"></i>
                                            </div>
                                            <h3>No news articles yet</h3>
                                            <p>Create your first news article to get started.</p>
                                            <button onclick="openCreateNewsModal()" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create Article
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards View -->
                <div class="cards-view">
                    @forelse($news as $article)
                        <div class="news-card" data-status="{{ $article->is_published ? 'published' : 'draft' }}" data-department="{{ $article->admin->department ?? 'N/A' }}">
                            <div class="card-header">
                                <div class="card-id">#{{ $article->id }}</div>
                                <span class="status-badge {{ $article->is_published ? 'published' : 'draft' }}">
                                    <i class="fas fa-{{ $article->is_published ? 'check' : 'eye-slash' }}"></i>
                                    {{ $article->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </div>
                            <div class="card-content">
                                <h3>{{ $article->title }}</h3>
                                <p>{{ Str::limit($article->content, 120) }}</p>
                            </div>
                            <div class="card-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span>{{ $article->admin->username }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-building"></i>
                                    <span>{{ $article->admin->department ?? 'N/A' }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $article->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <a href="{{ route('superadmin.news.show', $article) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('superadmin.news.edit', $article) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('superadmin.news.destroy', $article) }}" style="display: inline;" onsubmit="return handleNewsDelete(event, '{{ $article->title }}')">
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
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <h3>No news articles yet</h3>
                            <p>Create your first news article to get started.</p>
                            <button onclick="openCreateNewsModal()" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Article
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create News Modal -->
<div id="createNewsModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Create New News Article</h3>
            <button onclick="closeCreateNewsModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-content" id="createNewsContent">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Show News Modal -->
<div id="showNewsModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-newspaper"></i> <span id="showNewsTitle">News Article Details</span></h3>
            <button onclick="closeShowNewsModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-content" id="showNewsContent">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Edit News Modal -->
<div id="editNewsModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> <span id="editNewsTitle">Edit News Article</span></h3>
            <button onclick="closeEditNewsModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-content" id="editNewsContent">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <button class="image-modal-close" onclick="closeImageModal()">
        <i class="fas fa-times"></i>
    </button>
    <img id="imageModalImg" src="" alt="Full Size Image">
</div>

<style>
    /* Enhanced Page Header */
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

    .news-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .news-card:hover {
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

        .page-header {
            padding: 1rem;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }

        .header-text h1 {
            font-size: 1.25rem;
        }

        .content-header {
            padding: 1rem;
        }

        .data-view {
            padding: 1rem;
        }

        .news-card {
            padding: 1rem;
        }
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
        border-color: #f59e0b;
        background: rgba(245, 158, 11, 0.05);
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
        color: #f59e0b;
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

    .loading-spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        text-align: center;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #f59e0b;
        border-radius: 50%;
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

    /* Modal responsive design */
    @media (max-width: 768px) {
        .modal-container {
            width: 95%;
            max-height: 95vh;
            border-radius: 15px;
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

    .btn-primary:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
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

    /* Modal Styles - Matching Announcements Page */
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
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
        background: linear-gradient(90deg, #f59e0b, #3b82f6, #8b5cf6, #ef4444, #10b981);
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
        scrollbar-color: #f59e0b #f1f5f9;
    }

    .modal-content::-webkit-scrollbar {
        width: 8px;
    }

    .modal-content::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .modal-content::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border-radius: 4px;
    }

    .modal-content::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #d97706, #f59e0b);
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

    /* Form Styling for Modal Content */
    .announcement-form {
        padding: 0;
        max-width: none;
    }

    /* Article Info Styling */
    .article-info {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
    }

    .article-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .created-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: var(--text-primary);
    }

    .separator {
        color: var(--text-secondary);
        font-weight: 300;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-badge.published {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .status-badge.draft {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .department-badge {
        background: var(--primary-color);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Current Media Styling */
    .current-media-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: var(--radius-lg);
        border: 1px solid #e2e8f0;
    }

    .current-media-section .form-label {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.5rem;
    }

    .current-media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .current-media-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 1rem;
        text-align: center;
        position: relative;
    }

    .current-image-display {
        width: 100%;
        max-height: 150px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        margin-bottom: 1rem;
    }

    .current-video-display {
        width: 100%;
        max-height: 150px;
        border-radius: var(--radius-sm);
        margin-bottom: 1rem;
    }

    .media-actions {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .media-actions .checkbox-label {
        font-size: 0.875rem;
        color: #ef4444;
        font-weight: 500;
    }

    .media-actions .checkbox-input:checked + .checkbox-custom {
        background: #ef4444;
        border-color: #ef4444;
    }

    /* File Preview Container */
    .file-preview-container {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .file-preview-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 1rem;
        text-align: center;
        position: relative;
    }

    .preview-image,
    .preview-video {
        position: relative;
        margin-bottom: 0.75rem;
    }

    .preview-image img {
        width: 100%;
        max-height: 120px;
        object-fit: cover;
        border-radius: var(--radius-sm);
    }

    .preview-video i {
        font-size: 3rem;
        color: var(--primary-color);
        margin: 1rem 0;
    }

    .remove-file-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .remove-file-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .file-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .file-name {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-primary);
        word-break: break-word;
    }

    .file-size {
        font-size: 0.7rem;
        color: var(--text-secondary);
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

    .file-remove-btn {
        background: #ef4444;
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .file-remove-btn:hover {
        background: #dc2626;
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

    /* News Details Styling */
    .news-details {
        padding: 0;
    }

    .detail-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .detail-section h4 {
        margin: 0 0 1rem 0;
        color: #1e293b;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-section h4 i {
        color: #f59e0b;
        font-size: 1rem;
    }

    .detail-section h5 {
        margin: 0 0 1rem 0;
        color: #475569;
        font-size: 1rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-section h5 i {
        color: #f59e0b;
        font-size: 0.9rem;
    }

    /* Status Grid */
    .status-grid {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .status-badge.published {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.draft {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(107, 114, 128, 0.3);
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .info-item {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .info-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .info-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .info-label i {
        color: #f59e0b;
        width: 16px;
    }

    .info-value {
        font-size: 0.95rem;
        color: #1e293b;
        font-weight: 500;
    }

    /* Content Display */
    .content-display {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        line-height: 1.6;
        color: #374151;
        font-size: 0.95rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Media Display */
    .media-display {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .images-section, .videos-section {
        margin-bottom: 1.5rem;
    }

    .images-section:last-child, .videos-section:last-child {
        margin-bottom: 0;
    }

    .images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .image-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .image-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .image-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }

    .videos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .video-item {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .video-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .video-item video {
        width: 100%;
        height: 200px;
        display: block;
    }

    .video-label {
        padding: 0.75rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        text-align: center;
    }

    /* Image Modal */
    .image-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .image-modal.active {
        opacity: 1;
        visibility: visible;
    }

    .image-modal img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }

    .image-modal-close {
        position: absolute;
        top: 2rem;
        right: 2rem;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 1.5rem;
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .image-modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
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
        const cards = document.querySelectorAll('.news-card');
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
    async function handleNewsDelete(event, newsTitle) {
        event.preventDefault();
        
        const result = await Swal.fire({
            title: 'Delete News Article?',
            text: `Are you sure you want to delete "${newsTitle}"? This action cannot be undone.`,
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
                title: 'Deleting News Article...',
                text: 'Please wait while we delete the news article.',
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



    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.stat-card, .news-card');
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

    // Create News Modal Functions
    function openCreateNewsModal() {
        const modal = document.getElementById('createNewsModal');
        const content = document.getElementById('createNewsContent');
        
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
        fetch('{{ route('superadmin.news.create') }}')
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
                const formContent = doc.querySelector('#createNewsForm');
                
                if (formContent) {
                    content.innerHTML = formContent.outerHTML;
                    
                    // Initialize form scripts
                    initializeCreateNewsFormScripts();
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
                        <button onclick="closeCreateNewsModal()" class="btn btn-secondary">Close</button>
                    </div>
                `;
            });
    }

    // Show News Modal Functions
    function openShowNewsModal(newsId) {
        const modal = document.getElementById('showNewsModal');
        const content = document.getElementById('showNewsContent');
        const title = document.getElementById('showNewsTitle');
        
        // Show modal with loading state
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
        document.body.style.overflow = 'hidden';
        
        // Reset title
        title.textContent = 'News Article Details';
        
        // Show loading spinner
        content.innerHTML = `
            <div class="loading-state">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <p>Loading news article...</p>
            </div>
        `;
        
        // Load news details via AJAX
        fetch(`/superadmin/news/${newsId}/show-data`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load news article');
                }
                return response.json();
            })
            .then(data => {
                // Update modal title
                title.textContent = data.title;
                
                // Create professional news display
                content.innerHTML = `
                    <div class="news-details">
                        <!-- Status Section -->
                        <div class="detail-section">
                            <h4><i class="fas fa-info-circle"></i> Article Status</h4>
                            <div class="status-grid">
                                <div class="status-item">
                                    <span class="status-badge ${data.is_published ? 'published' : 'draft'}">
                                        <i class="fas fa-${data.is_published ? 'check' : 'eye-slash'}"></i>
                                        ${data.is_published ? 'Published' : 'Draft'}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Article Information -->
                        <div class="detail-section">
                            <h4><i class="fas fa-newspaper"></i> Article Information</h4>
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-tag"></i> Category
                                    </div>
                                    <div class="info-value">news</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-user"></i> Created By
                                    </div>
                                    <div class="info-value">${data.author} (${data.role})</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-building"></i> Department
                                    </div>
                                    <div class="info-value">${data.department || 'N/A'}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-calendar"></i> Created
                                    </div>
                                    <div class="info-value">${data.created_at}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-hashtag"></i> ID
                                    </div>
                                    <div class="info-value">#${data.id}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="detail-section">
                            <h4><i class="fas fa-align-left"></i> Content</h4>
                            <div class="content-display">
                                ${data.content}
                            </div>
                        </div>

                        ${data.has_media ? `
                        <!-- Media Section -->
                        <div class="detail-section">
                            <h4><i class="fas fa-image"></i> Media Attachments</h4>
                            <div class="media-display">
                                ${data.images && data.images.length > 0 ? `
                                    <div class="images-section">
                                        <h5><i class="fas fa-camera"></i> Images (${data.images.length})</h5>
                                        <div class="images-grid">
                                            ${data.images.map(url => `
                                                <div class="image-item" onclick="openImageModal('${url}')">
                                                    <img src="${url}" alt="News Image" loading="lazy">
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                ` : ''}
                                
                                ${data.videos && data.videos.length > 0 ? `
                                    <div class="videos-section">
                                        <h5><i class="fas fa-video"></i> Videos (${data.videos.length})</h5>
                                        <div class="videos-grid">
                                            ${data.videos.map((url, index) => `
                                                <div class="video-item">
                                                    <video controls>
                                                        <source src="${url}" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                    <div class="video-label">Video ${index + 1}</div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error loading news article:', error);
                content.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load news article. Please try again.</p>
                        <button onclick="closeShowNewsModal()" class="btn btn-secondary">Close</button>
                    </div>
                `;
            });
    }

    function closeShowNewsModal() {
        const modal = document.getElementById('showNewsModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // Image Modal Functions
    function openImageModal(imageUrl) {
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('imageModalImg');
        
        img.src = imageUrl;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Close image modal when clicking outside the image
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

    // Edit News Modal Functions
    function openEditNewsModal(newsId) {
        const modal = document.getElementById('editNewsModal');
        const content = document.getElementById('editNewsContent');
        const title = document.getElementById('editNewsTitle');
        
        // Show modal with loading state
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
        document.body.style.overflow = 'hidden';
        
        // Reset title
        title.textContent = 'Edit News Article';
        
        // Show loading spinner
        content.innerHTML = `
            <div class="loading-state">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <p>Loading edit form...</p>
            </div>
        `;
        
        // Load edit form via AJAX
        fetch(`/superadmin/news/${newsId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                console.log('Received HTML length:', html.length);
                console.log('HTML preview:', html.substring(0, 500));
                // For AJAX requests, the response should contain just the form content
                // Let's try to extract the form or use the entire response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Try to find the form in different ways
                let formContent = doc.querySelector('#editNewsForm');
                
                if (!formContent) {
                    // If form not found, try to find any form
                    formContent = doc.querySelector('form');
                }
                
                if (!formContent) {
                    // If still no form, use the entire body content
                    const bodyContent = doc.querySelector('body');
                    if (bodyContent) {
                        content.innerHTML = bodyContent.innerHTML;
                    } else {
                        content.innerHTML = html;
                    }
                } else {
                    // Get the parent container that includes the form and metadata
                    const container = formContent.closest('.form-container') || formContent.parentElement;
                    if (container) {
                        content.innerHTML = container.outerHTML;
                    } else {
                        content.innerHTML = formContent.outerHTML;
                    }
                }
                
                // Update modal title
                const titleElement = doc.querySelector('h1');
                if (titleElement) {
                    title.textContent = titleElement.textContent.replace('Edit News Article', '').trim() || 'Edit News Article';
                }
                
                // Initialize form scripts
                initializeEditNewsFormScripts();
            })
            .catch(error => {
                console.error('Error loading edit form:', error);
                console.error('Error details:', error.message);
                content.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load edit form. Error: ${error.message}</p>
                        <button onclick="closeEditNewsModal()" class="btn btn-secondary">Close</button>
                    </div>
                `;
            });
    }

    function closeEditNewsModal() {
        const modal = document.getElementById('editNewsModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    function closeCreateNewsModal() {
        const modal = document.getElementById('createNewsModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    function initializeCreateNewsFormScripts() {
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
        const form = document.getElementById('createNewsForm');
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
                        closeCreateNewsModal();
                        location.reload();
                    } else {
                        throw new Error('Creation failed');
                    }
                })
                .catch(error => {
                    console.error('Error creating news:', error);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    alert('Error creating news article. Please try again.');
                });
            });
        }
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateNewsModal();
        }
    });

    // Close modal when clicking outside
    document.getElementById('createNewsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateNewsModal();
        }
    });

    function initializeEditNewsFormScripts() {
        // Handle form submission via AJAX
        const form = document.getElementById('editNewsForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                handleEditNewsFormSubmission(this);
            });
        }

        // Initialize file upload previews for edit form
        initializeEditFileUploads();
    }

    function handleEditNewsFormSubmission(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        
        // Create FormData object
        const formData = new FormData(form);
        
        // Submit form via AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Network response was not ok');
        })
        .then(data => {
            // Check if response contains validation errors
            if (data.includes('alert-danger') || data.includes('error-message')) {
                // Update form content with validation errors
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const newForm = doc.querySelector('#editNewsForm');
                
                if (newForm) {
                    document.getElementById('editNewsContent').innerHTML = newForm.outerHTML;
                    initializeEditNewsFormScripts();
                }
            } else {
                // Success - close modal and refresh page
                closeEditNewsModal();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'News article updated successfully!',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Refresh the page to show updated data
                    window.location.reload();
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to update news article. Please try again.',
                confirmButtonColor: '#ef4444'
            });
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        });
    }

    function initializeEditFileUploads() {
        // Initialize multiple file upload previews if they exist
        const imageInput = document.querySelector('#editNewsForm input[name="images[]"]');
        const videoInput = document.querySelector('#editNewsForm input[name="videos[]"]');
        
        if (imageInput) {
            initializeEditMultipleFilePreview(imageInput, 'images', 2);
        }
        
        if (videoInput) {
            initializeEditMultipleFilePreview(videoInput, 'videos', 3);
        }
    }

    function initializeEditMultipleFilePreview(input, type, maxFiles) {
        const previewContainer = document.getElementById(type + 'Preview');
        let selectedFiles = [];

        input.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            // Limit number of files
            if (files.length > maxFiles) {
                alert(`You can only select up to ${maxFiles} ${type}.`);
                input.value = '';
                return;
            }

            selectedFiles = files;
            updateEditFilePreview(type, selectedFiles, previewContainer);
        });
    }

    function updateEditFilePreview(type, files, container) {
        container.innerHTML = '';
        
        files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-preview-item';
            
            if (type === 'images') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fileItem.innerHTML = `
                        <div class="preview-image">
                            <img src="${e.target.result}" alt="Preview ${index + 1}">
                            <button type="button" class="remove-file-btn" onclick="removeEditFile('${type}', ${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="file-info">
                            <span class="file-name">${file.name}</span>
                            <span class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</span>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else if (type === 'videos') {
                fileItem.innerHTML = `
                    <div class="preview-video">
                        <i class="fas fa-video"></i>
                        <button type="button" class="remove-file-btn" onclick="removeEditFile('${type}', ${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="file-info">
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</span>
                    </div>
                `;
            }
            
            container.appendChild(fileItem);
        });
    }

    function removeEditFile(type, index) {
        const input = document.querySelector(`#editNewsForm input[name="${type}[]"]`);
        const previewContainer = document.getElementById(type + 'Preview');
        
        // Create new FileList without the removed file
        const dt = new DataTransfer();
        const files = Array.from(input.files);
        
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        input.files = dt.files;
        updateEditFilePreview(type, Array.from(dt.files), previewContainer);
    }
</script>



@endsection
