@extends('layouts.app')

@section('title', 'Events Management - Super Admin')

@section('content')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <li><a href="{{ route('superadmin.admin-access') }}">
                <i class="fas fa-clipboard-list"></i> Admin Access Logs
            </a></li>
            <li>
               
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <!-- Enhanced Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="header-text">
                    <h1>Events Management</h1>
                    <p>Manage all campus events across departments</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('superadmin.events.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>New Event</span>
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
                    <h3>{{ $events->where('is_published', true)->count() }}</h3>
                    <p>Published</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon draft">
                    <i class="fas fa-eye-slash"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $events->where('is_published', false)->count() }}</h3>
                    <p>Drafts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $events->count() }}</h3>
                    <p>Total</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon departments">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $events->groupBy('admin.department')->count() }}</h3>
                    <p>Departments</p>
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
                        <select id="publishStatusFilter" class="filter-select">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="filter-container">
                        <select id="eventStatusFilter" class="filter-select">
                            <option value="">All Events</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="past">Past</option>
                            <option value="tbd">TBD</option>
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
                                <th><i class="fas fa-map-marker-alt"></i> Location</th>
                                <th><i class="fas fa-toggle-on"></i> Status</th>
                                <th><i class="fas fa-calendar"></i> Event Date</th>
                                <th><i class="fas fa-clock"></i> Event Status</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                @php
                                    $eventStatus = $event->getEventStatus();
                                    $status = $eventStatus['status'];
                                    $statusText = $eventStatus['text'];
                                    $statusIcon = $eventStatus['icon'];
                                @endphp
                                <tr data-status="{{ $status }}" data-department="{{ $event->admin->department ?? 'N/A' }}" data-published="{{ $event->is_published ? 'published' : 'draft' }}">
                                    <td>
                                        <span class="id-badge">#{{ $event->id }}</span>
                                    </td>
                                    <td>
                                        <div class="content-preview">
                                            <h4>{{ Str::limit($event->title, 50) }}</h4>
                                            <p>{{ Str::limit($event->description, 100) }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span>{{ $event->admin->username }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="department-badge">{{ $event->admin->department ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="location-info">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $event->location ?? 'Not specified' }}</span>
                                        </div>
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
                                                <div class="time">{{ $event->event_date->format('H:i') }}</div>
                                            @else
                                                <div class="date no-date">Date TBD</div>
                                                <div class="time">Time TBD</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $status }}">
                                            <i class="fas fa-{{ $statusIcon }}"></i>
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('superadmin.events.show', $event) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('superadmin.events.edit', $event) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('superadmin.events.destroy', $event) }}" style="display: inline;" onsubmit="return handleEventDelete(event, '{{ $event->title }}')">
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
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <h3>No events yet</h3>
                                            <p>Create your first event to get started.</p>
                                            <a href="{{ route('superadmin.events.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create Event
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
                    @forelse($events as $event)
                        @php
                            $eventStatus = $event->getEventStatus();
                            $status = $eventStatus['status'];
                            $statusText = $eventStatus['text'];
                            $statusIcon = $eventStatus['icon'];
                        @endphp
                        <div class="event-card" data-status="{{ $status }}" data-department="{{ $event->admin->department ?? 'N/A' }}" data-published="{{ $event->is_published ? 'published' : 'draft' }}">
                            <div class="card-header">
                                <div class="card-id">#{{ $event->id }}</div>
                                <div class="card-status-badges">
                                    <span class="status-badge {{ $event->is_published ? 'published' : 'draft' }}">
                                        <i class="fas fa-{{ $event->is_published ? 'check' : 'eye-slash' }}"></i>
                                        {{ $event->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                    <span class="status-badge {{ $status }}">
                                        <i class="fas fa-{{ $statusIcon }}"></i>
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-content">
                                <h3>{{ $event->title }}</h3>
                                <p>{{ Str::limit($event->description, 120) }}</p>
                            </div>
                            <div class="card-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span>{{ $event->admin->username }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-building"></i>
                                    <span>{{ $event->admin->department ?? 'N/A' }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $event->event_date->format('M d, Y H:i') }}</span>
                                </div>
                                @if($event->location)
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $event->location }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="card-actions">
                                <button onclick="openShowModal({{ $event->id }})" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button onclick="openEditModal({{ $event->id }})" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('superadmin.events.destroy', $event) }}" style="display: inline;" onsubmit="return handleEventDelete(event, '{{ $event->title }}')">
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
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3>No events yet</h3>
                            <p>Create your first event to get started.</p>
                            <button onclick="openCreateModal()" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Event
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Show Event Modal -->
<div id="showEventModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="fas fa-eye"></i> <span id="showEventTitle">Event Details</span></h2>
            <button type="button" class="modal-close" onclick="closeShowModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div id="showEventContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div id="editEventModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> <span id="editEventTitle">Edit Event</span></h2>
            <button type="button" class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div id="editEventContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Create Event Modal -->
<div id="createEventModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle"></i> Create New Event</h2>
            <button type="button" class="modal-close" onclick="closeCreateModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <form method="POST" action="{{ route('superadmin.events.store') }}" enctype="multipart/form-data" class="event-form" id="createEventForm">
                @csrf
                
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Event Information</h3>
                    
                    <div class="form-group">
                        <label for="modal_title" class="form-label">
                            <i class="fas fa-heading"></i> Event Title *
                        </label>
                        <input type="text" 
                               id="modal_title" 
                               name="title" 
                               class="form-input" 
                               placeholder="Enter event title..."
                               required>
                        <span class="error-message" id="title-error" style="display: none;"></span>
                    </div>

                    <div class="form-group">
                        <label for="modal_description" class="form-label">
                            <i class="fas fa-align-left"></i> Description *
                        </label>
                        <textarea id="modal_description" 
                                  name="description" 
                                  class="form-textarea" 
                                  rows="6" 
                                  placeholder="Describe the event details..."
                                  required></textarea>
                        <span class="error-message" id="description-error" style="display: none;"></span>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-calendar-alt"></i> Date & Location</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modal_event_date" class="form-label">
                                <i class="fas fa-calendar"></i> Event Date & Time *
                            </label>
                            <input type="datetime-local" 
                                   id="modal_event_date" 
                                   name="event_date" 
                                   class="form-input" 
                                   required>
                            <span class="error-message" id="event_date-error" style="display: none;"></span>
                        </div>

                        <div class="form-group">
                            <label for="modal_location" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Location *
                            </label>
                            <input type="text" 
                                   id="modal_location" 
                                   name="location" 
                                   class="form-input" 
                                   placeholder="Event location..."
                                   required>
                            <span class="error-message" id="location-error" style="display: none;"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Event Media (Optional)</h3>
                    
                    <div class="form-group">
                        <label for="modal_images" class="form-label">
                            <i class="fas fa-camera"></i> Multiple Images (up to 2)
                        </label>
                        <div class="file-upload-area" id="modalImagesUploadArea">
                            <input type="file" 
                                   id="modal_images" 
                                   name="images[]" 
                                   class="file-input" 
                                   accept=".jpg,.jpeg,.png"
                                   multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>JPG, PNG only - up to 5MB each (max 2 images)</small>
                            </div>
                        </div>
                        <div id="modalImagesPreview" class="file-previews"></div>
                        <span class="error-message" id="images-error" style="display: none;"></span>
                    </div>

                    <div class="form-group">
                        <label for="modal_videos" class="form-label">
                            <i class="fas fa-video"></i> Multiple Videos (up to 3)
                        </label>
                        <div class="file-upload-area" id="modalVideosUploadArea">
                            <input type="file"
                                   id="modal_videos"
                                   name="videos[]"
                                   class="file-input"
                                   accept=".mp4"
                                   multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-video"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>MP4 only - up to 50MB each (max 3 videos)</small>
                            </div>
                        </div>
                        <div id="modalVideosPreview" class="file-previews"></div>
                        <span class="error-message" id="videos-error" style="display: none;"></span>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> Publishing Settings</h3>

                    <!-- Hidden input to automatically set visibility to 'all' -->
                    <input type="hidden" name="visibility_scope" value="all">

                    <div class="form-group">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox"
                                       name="is_published"
                                       value="1"
                                       class="checkbox-input">
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">
                                    <i class="fas fa-eye"></i> Publish Event
                                </span>
                            </label>
                            <small class="form-help">Check to make this event visible to students and faculty immediately</small>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Event
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeCreateModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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

    .stat-icon.upcoming {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .stat-icon.ongoing {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .stat-icon.past {
        background: linear-gradient(135deg, #6b7280, #4b5563);
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

    .location-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .location-info i {
        color: #10b981;
        width: 16px;
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

    .status-badge.published {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.draft {
        background: #fef3c7;
        color: #92400e;
    }

    /* Card Status Badges Container */
    .card-status-badges {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
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

        .event-card {
            padding: 1rem;
        }
    }

    /* Modal Styles */
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
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6, #ef4444, #f59e0b);
        background-size: 300% 100%;
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .modal-header h2 {
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

    .modal-body {
        padding: 2rem;
        max-height: calc(min(95vh, 900px) - 140px);
        min-height: 400px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #10b981 #f1f5f9;
    }

    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #059669, #047857);
    }

    /* Form Styles for Modal */
    .modal-body .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-body .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .modal-body .form-section h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-body .form-section h3 i {
        color: #10b981;
    }

    .modal-body .form-group {
        margin-bottom: 1.5rem;
    }

    .modal-body .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .modal-body .form-label {
        display: block;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-body .form-label i {
        color: #10b981;
        width: 16px;
    }

    .modal-body .form-input,
    .modal-body .form-textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        background: white;
    }

    .modal-body .form-input:focus,
    .modal-body .form-textarea:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .modal-body .form-textarea {
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
    }

    .modal-body .file-upload-area {
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        background: #f8fafc;
    }

    .modal-body .file-upload-area:hover {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
    }

    .modal-body .file-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .modal-body .file-upload-content i {
        font-size: 2rem;
        color: #10b981;
        margin-bottom: 1rem;
    }

    .modal-body .file-upload-content p {
        margin: 0 0 0.5rem 0;
        font-weight: 500;
        color: var(--text-primary);
    }

    .modal-body .file-upload-content small {
        color: var(--text-secondary);
    }

    .modal-body .file-previews {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }

    .modal-body .file-preview-item {
        text-align: center;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
    }

    .modal-body .checkbox-group {
        margin: 1rem 0;
    }

    .modal-body .checkbox-label {
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

    .modal-body .checkbox-label:hover {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
    }

    .modal-body .checkbox-input {
        display: none;
    }

    .modal-body .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        position: relative;
        transition: all 0.3s ease;
        background: white;
    }

    .modal-body .checkbox-input:checked + .checkbox-custom {
        background: #10b981;
        border-color: #10b981;
    }

    .modal-body .checkbox-input:checked + .checkbox-custom::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .modal-body .checkbox-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: #374151;
    }

    .modal-body .checkbox-text i {
        color: #10b981;
    }

    .modal-body .form-help {
        display: block;
        margin-top: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }

    .modal-body .error-message {
        display: block;
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }

    /* Responsive Modal */
    @media (max-width: 1200px) {
        .modal-container {
            max-width: min(95vw, 1000px);
            min-height: 550px;
        }

        .modal-body {
            min-height: 350px;
        }
    }

    @media (max-width: 992px) {
        .modal-container {
            max-width: min(98vw, 800px);
            min-height: 500px;
        }

        .modal-body {
            padding: 1.75rem;
            min-height: 300px;
        }

        .modal-body .form-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    @media (max-width: 768px) {
        .modal-container {
            max-width: 100vw;
            max-height: 100vh;
            min-height: 100vh;
            border-radius: 0;
            margin: 0;
        }

        .modal-overlay {
            padding: 0;
        }

        .modal-header {
            padding: 1.5rem;
            border-radius: 0;
        }

        .modal-header h2 {
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 1.5rem;
            max-height: calc(100vh - 140px);
            min-height: calc(100vh - 140px);
        }

        .modal-body .form-row {
            grid-template-columns: 1fr;
        }

        .modal-actions {
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .modal-header {
            padding: 1rem;
        }

        .modal-header h2 {
            font-size: 1.125rem;
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-body .form-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
        }

        .modal-body .file-upload-area {
            padding: 1.5rem;
        }
    }

    @media (max-width: 360px) {
        .modal-body {
            padding: 0.75rem;
        }

        .modal-header {
            padding: 0.75rem;
        }

        .modal-body .form-input,
        .modal-body .form-textarea {
            padding: 0.75rem;
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

    /* Secondary Button */
    .btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(107, 114, 128, 0.4);
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

    /* Large Button */
    .btn-lg {
        padding: 1rem 2rem;
        font-size: 1rem;
        border-radius: 16px;
    }

    /* Button Loading State */
    .btn.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn.loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        margin: auto;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: button-loading-spinner 1s ease infinite;
    }

    @keyframes button-loading-spinner {
        from {
            transform: rotate(0turn);
        }
        to {
            transform: rotate(1turn);
        }
    }

    /* Enhanced Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .action-buttons .btn {
        min-width: 40px;
        height: 40px;
        padding: 0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-buttons .btn i {
        font-size: 0.875rem;
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

    /* Modal Action Buttons */
    .modal-actions .btn {
        min-width: 120px;
        padding: 0.875rem 1.75rem;
        font-weight: 700;
        border-radius: 12px;
    }

    .modal-actions .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.25);
    }

    .modal-actions .btn-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(16, 185, 129, 0.35);
    }

    /* Card Action Buttons */
    .card-actions .btn {
        flex: 1;
        min-width: auto;
        justify-content: center;
        font-size: 0.75rem;
        padding: 0.625rem 1rem;
        border-radius: 8px;
        font-weight: 600;
    }

    /* Pulse Animation for Important Buttons */
    .btn-pulse {
        animation: pulse-glow 2s infinite;
    }

    @keyframes pulse-glow {
        0%, 100% {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        50% {
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4), 0 4px 8px rgba(16, 185, 129, 0.2);
        }
    }

    /* Button Group */
    .btn-group {
        display: flex;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .btn-group .btn {
        border-radius: 0;
        border-right: 1px solid rgba(255, 255, 255, 0.2);
    }

    .btn-group .btn:first-child {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .btn-group .btn:last-child {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
        border-right: none;
    }

    /* Floating Action Button */
    .btn-fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        cursor: pointer;
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
    }

    .btn-fab:hover {
        transform: scale(1.1) translateY(-2px);
        box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
    }

    .btn-fab i {
        font-size: 1.25rem;
    }
</style>

<script>
    // Enhanced search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        filterContent();
    });

    // Enhanced filter functionality
    document.getElementById('publishStatusFilter').addEventListener('change', function() {
        filterContent();
    });

    document.getElementById('eventStatusFilter').addEventListener('change', function() {
        filterContent();
    });

    document.getElementById('departmentFilter').addEventListener('change', function() {
        filterContent();
    });

    function filterContent() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const publishStatusFilter = document.getElementById('publishStatusFilter').value;
        const eventStatusFilter = document.getElementById('eventStatusFilter').value;
        const departmentFilter = document.getElementById('departmentFilter').value;

        // Filter table rows
        const tableRows = document.querySelectorAll('#dataTable tbody tr:not(.empty-row)');
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const eventStatus = row.getAttribute('data-status');
            const publishStatus = row.getAttribute('data-published');
            const department = row.getAttribute('data-department');

            const matchesSearch = text.includes(searchTerm);
            const matchesPublishStatus = !publishStatusFilter || publishStatus === publishStatusFilter;
            const matchesEventStatus = !eventStatusFilter || eventStatus === eventStatusFilter;
            const matchesDepartment = !departmentFilter || department === departmentFilter;

            if (matchesSearch && matchesPublishStatus && matchesEventStatus && matchesDepartment) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Filter cards
        const cards = document.querySelectorAll('.event-card');
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const eventStatus = card.getAttribute('data-status');
            const publishStatus = card.getAttribute('data-published');
            const department = card.getAttribute('data-department');

            const matchesSearch = text.includes(searchTerm);
            const matchesPublishStatus = !publishStatusFilter || publishStatus === publishStatusFilter;
            const matchesEventStatus = !eventStatusFilter || eventStatus === eventStatusFilter;
            const matchesDepartment = !departmentFilter || department === departmentFilter;

            if (matchesSearch && matchesPublishStatus && matchesEventStatus && matchesDepartment) {
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

    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.stat-card, .event-card');
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

    // Modal functionality
    function openCreateModal() {
        const modal = document.getElementById('createEventModal');
        modal.style.display = 'flex';
        
        // Trigger animation
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        
        // Set minimum date and time
        setMinDateTime();
        
        // Focus on first input
        setTimeout(() => {
            document.getElementById('modal_title').focus();
        }, 400);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        const modal = document.getElementById('createEventModal');
        modal.classList.remove('active');
        
        setTimeout(() => {
            modal.style.display = 'none';
            // Reset form
            document.getElementById('createEventForm').reset();
            clearModalPreviews();
            clearModalErrors();
        }, 400);
        
        // Restore body scroll
        document.body.style.overflow = '';
    }

    function clearModalPreviews() {
        document.getElementById('modalImagesPreview').innerHTML = '';
        document.getElementById('modalVideosPreview').innerHTML = '';
    }

    function clearModalErrors() {
        const errorElements = document.querySelectorAll('.modal-body .error-message');
        errorElements.forEach(error => {
            error.style.display = 'none';
            error.textContent = '';
        });
        
        // Remove error classes
        const inputs = document.querySelectorAll('.modal-body .form-input, .modal-body .form-textarea');
        inputs.forEach(input => {
            input.classList.remove('error');
        });
    }

    // Close modal when clicking outside
    document.getElementById('createEventModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('createEventModal');
            if (modal.classList.contains('active')) {
                closeCreateModal();
            }
        }
    });

    // Set minimum date and time for modal
    function setMinDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('modal_event_date').min = currentDateTime;
    }

    // Modal form validation
    document.getElementById('modal_event_date').addEventListener('change', function() {
        const selectedDateTime = new Date(this.value);
        const now = new Date();
        const errorElement = document.getElementById('event_date-error');

        if (selectedDateTime < now) {
            this.classList.add('error');
            errorElement.textContent = 'Event date and time must be in the future';
            errorElement.style.display = 'block';
        } else {
            this.classList.remove('error');
            errorElement.style.display = 'none';
        }
    });

    // Modal Images upload preview
    document.getElementById('modal_images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const maxFiles = 2;
        
        if (files.length > maxFiles) {
            alert(`Please select only up to ${maxFiles} image files.`);
            e.target.value = '';
            return;
        }
        
        const previewContainer = document.getElementById('modalImagesPreview');
        previewContainer.innerHTML = '';
        
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(readerEvent) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'file-preview-item';
                previewDiv.innerHTML = `
                    <img src="${readerEvent.target.result}" alt="Preview ${index + 1}" style="max-width: 150px; max-height: 150px; border-radius: 8px; object-fit: cover;">
                    <p style="margin: 0.5rem 0; font-weight: 500; font-size: 0.9rem;">${file.name}</p>
                    <small style="color: #666;">${(file.size / (1024 * 1024)).toFixed(2)} MB</small>
                `;
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
        
        if (files.length > 0) {
            const clearButton = document.createElement('button');
            clearButton.type = 'button';
            clearButton.className = 'btn btn-sm btn-danger';
            clearButton.style.marginTop = '1rem';
            clearButton.innerHTML = '<i class="fas fa-times"></i> Clear All Images';
            clearButton.onclick = () => {
                document.getElementById('modal_images').value = '';
                document.getElementById('modalImagesPreview').innerHTML = '';
            };
            previewContainer.appendChild(clearButton);
        }
    });

    // Modal Videos upload preview
    document.getElementById('modal_videos').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const maxFiles = 3;
        
        if (files.length > maxFiles) {
            alert(`Please select only up to ${maxFiles} video files.`);
            e.target.value = '';
            return;
        }
        
        const previewContainer = document.getElementById('modalVideosPreview');
        previewContainer.innerHTML = '';
        
        files.forEach((file, index) => {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'file-preview-item';
            previewDiv.innerHTML = `
                <i class="fas fa-video" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;"></i>
                <p style="margin: 0.5rem 0; font-weight: 500; font-size: 0.9rem;">${file.name}</p>
                <small style="color: #666;">${(file.size / (1024 * 1024)).toFixed(2)} MB</small>
            `;
            previewContainer.appendChild(previewDiv);
        });
        
        if (files.length > 0) {
            const clearButton = document.createElement('button');
            clearButton.type = 'button';
            clearButton.className = 'btn btn-sm btn-danger';
            clearButton.style.marginTop = '1rem';
            clearButton.innerHTML = '<i class="fas fa-times"></i> Clear All Videos';
            clearButton.onclick = () => {
                document.getElementById('modal_videos').value = '';
                document.getElementById('modalVideosPreview').innerHTML = '';
            };
            previewContainer.appendChild(clearButton);
        }
    });

    // Form submission validation with enhanced button feedback
    document.getElementById('createEventForm').addEventListener('submit', function(e) {
        const eventDateInput = document.getElementById('modal_event_date');
        const selectedDateTime = new Date(eventDateInput.value);
        const now = new Date();

        if (selectedDateTime < now) {
            e.preventDefault();
            alert('Please select a future date and time for the event.');
            eventDateInput.focus();
            return false;
        }

        // Add loading state to submit button
        const submitBtn = this.querySelector('button[type="submit"]');
        addButtonLoading(submitBtn, 'Creating Event...');
    });

    // Enhanced button interactions
    function addButtonLoading(button, text = 'Loading...') {
        const originalText = button.innerHTML;
        button.classList.add('loading');
        button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
        button.disabled = true;
        
        // Store original text for restoration
        button.dataset.originalText = originalText;
    }

    function removeButtonLoading(button) {
        button.classList.remove('loading');
        button.innerHTML = button.dataset.originalText || button.innerHTML;
        button.disabled = false;
    }

    // Add click animations to all buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn') || e.target.closest('.btn')) {
            const btn = e.target.classList.contains('btn') ? e.target : e.target.closest('.btn');
            
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            btn.style.position = 'relative';
            btn.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }
    });

    // Add ripple animation CSS
    const rippleStyle = document.createElement('style');
    rippleStyle.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(rippleStyle);

    // Enhanced delete confirmation with better UX
    function confirmDelete(form, itemName = 'item') {
        const deleteBtn = form.querySelector('button[type="submit"]');
        
        // First click - show warning
        if (!deleteBtn.classList.contains('confirm-delete')) {
            deleteBtn.classList.add('confirm-delete', 'btn-pulse');
            deleteBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Confirm Delete';
            deleteBtn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            
            // Reset after 3 seconds
            setTimeout(() => {
                deleteBtn.classList.remove('confirm-delete', 'btn-pulse');
                deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                deleteBtn.style.background = '';
            }, 3000);
            
            return false;
        }
        
        // Second click - proceed with loading
        addButtonLoading(deleteBtn, 'Deleting...');
        return true;
    }

    // Apply enhanced delete to all delete forms
    document.querySelectorAll('form[action*="destroy"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirmDelete(this)) {
                this.submit();
            }
        });
    });

    // Add pulse animation to primary action buttons
    document.addEventListener('DOMContentLoaded', function() {
        const primaryButtons = document.querySelectorAll('.btn-primary');
        primaryButtons.forEach(btn => {
            if (btn.textContent.includes('New Event') || btn.textContent.includes('Create Event')) {
                btn.classList.add('btn-pulse');
            }
        });
    });

    // Enhanced hover effects for action buttons
    document.querySelectorAll('.action-buttons .btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.05)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    // Show Event Modal Functions
    function openShowModal(eventId) {
        const modal = document.getElementById('showEventModal');
        const content = document.getElementById('showEventContent');
        const title = document.getElementById('showEventTitle');
        
        // Show modal with same transition as create modal
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        document.body.style.overflow = 'hidden';
        
        // Fetch event data
        fetch(`/superadmin/events/${eventId}`)
            .then(response => response.text())
            .then(html => {
                // Parse the response to extract event details
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Extract event title
                const eventTitle = doc.querySelector('.page-header h1')?.textContent || 'Event Details';
                title.textContent = eventTitle;
                
                // Debug: Log the parsed HTML to see what we're working with
                console.log('Parsed HTML:', doc);
                console.log('Event container:', doc.querySelector('.event-container'));
                
                // Extract and format event content
                const eventContainer = doc.querySelector('.event-container');
                if (eventContainer) {
                    // Create modal-friendly content structure
                    const modalContent = createModalContent(eventContainer, doc);
                    content.innerHTML = modalContent;
                } else {
                    // Try alternative approach - get event data from current page
                    const eventData = getEventDataFromTable(eventId);
                    if (eventData) {
                        const modalContent = createModalContentFromEventData(eventData);
                        content.innerHTML = modalContent;
                    } else {
                        content.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading event details</p></div>';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading event:', error);
                // Fallback: get event data from current page
                const eventData = getEventDataFromTable(eventId);
                if (eventData) {
                    const modalContent = createModalContentFromEventData(eventData);
                    content.innerHTML = modalContent;
                } else {
                    content.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading event details</p></div>';
                }
            });
    }

    function closeShowModal() {
        const modal = document.getElementById('showEventModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 400);
    }

    // Edit Event Modal Functions
    function openEditModal(eventId) {
        const modal = document.getElementById('editEventModal');
        const content = document.getElementById('editEventContent');
        const title = document.getElementById('editEventTitle');
        
        // Show loading state
        content.innerHTML = `
            <div class="loading-state">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <p>Loading edit form...</p>
            </div>
        `;
        
        // Show modal with same transition as other modals
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        document.body.style.overflow = 'hidden';
        
        // Fetch edit page content
        fetch(`/superadmin/events/${eventId}/edit`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Extract event title for modal header
                const eventTitle = doc.querySelector('.event-info h2')?.textContent || 'Edit Event';
                title.textContent = eventTitle;
                
                // Extract the form container
                const formContainer = doc.querySelector('.form-container');
                if (formContainer) {
                    // Update form action to handle modal submission
                    const form = formContainer.querySelector('form');
                    if (form) {
                        form.setAttribute('onsubmit', 'return handleEditFormSubmit(event)');
                        form.setAttribute('id', 'editEventModalForm');
                    }
                    
                    // Remove the event-info header since we show it in modal header
                    const eventInfo = formContainer.querySelector('.event-info');
                    if (eventInfo) {
                        eventInfo.remove();
                    }
                    
                    content.innerHTML = formContainer.innerHTML;
                    
                    // Reinitialize form scripts for the modal
                    initializeEditFormScripts();
                } else {
                    content.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading edit form</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading edit form:', error);
                content.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading edit form</p></div>';
            });
    }

    function closeEditModal() {
        const modal = document.getElementById('editEventModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 400);
    }

    function handleEditFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('.btn-loading');
        
        // Show loading state
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
        
        // Submit form via AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                // Success - reload the page to show updated data
                window.location.reload();
            } else {
                throw new Error('Form submission failed');
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            alert('Error updating event. Please try again.');
            
            // Reset loading state
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        });
        
        return false;
    }

    function initializeEditFormScripts() {
        // Reinitialize file upload previews and other form functionality
        const imagesInput = document.getElementById('images');
        const videosInput = document.getElementById('videos');
        
        if (imagesInput) {
            // Reset the images upload handler
            let selectedImages = [];
            const maxImages = 5;
            
            imagesInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                const container = document.getElementById('imagePreviewContainer');
                
                if (files.length > maxImages) {
                    alert(`Maximum ${maxImages} images allowed`);
                    e.target.value = '';
                    return;
                }
                
                container.innerHTML = '';
                selectedImages = [];
                
                files.forEach((file, index) => {
                    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                        alert(`File ${file.name} is not a valid image format.`);
                        return;
                    }
                    
                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File ${file.name} is too large. Maximum size is 5MB.`);
                        return;
                    }
                    
                    selectedImages.push(file);
                    
                    const reader = new FileReader();
                    reader.onload = function(readerEvent) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'file-preview-item';
                        previewDiv.innerHTML = `
                            <div class="preview-image">
                                <img src="${readerEvent.target.result}" alt="Preview ${index + 1}">
                                <button type="button" class="remove-file-btn" onclick="removeImagePreview(${index})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="file-info">
                                <p class="file-name">${file.name}</p>
                                <small class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</small>
                            </div>
                        `;
                        container.appendChild(previewDiv);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }
        
        // Set minimum date for event date input
        const eventDateInput = document.getElementById('event_date');
        if (eventDateInput) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            eventDateInput.min = currentDateTime;
        }
    }

    // SweetAlert Delete Handler
    async function handleEventDelete(event, eventTitle) {
        event.preventDefault();
        
        const result = await Swal.fire({
            title: 'Delete Event?',
            text: `Are you sure you want to delete "${eventTitle}"? This action cannot be undone.`,
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
            
            // Submit the form
            event.target.submit();
        }
        
        return false;
    }

    function createModalContent(eventContainer, doc) {
        // Extract event meta information
        const metaItems = eventContainer.querySelectorAll('.meta-item');
        const eventContent = eventContainer.querySelector('.event-content');
        const contentText = eventContent?.querySelector('.content-text')?.innerHTML || '';
        
        // Extract all images from the entire document
        const allDocImages = doc.querySelectorAll('img');
        const allDocVideos = doc.querySelectorAll('video');
        
        // Also check for images in the content-image section
        const contentImageSection = eventContent?.querySelector('.content-image');
        const mainEventImage = contentImageSection?.querySelector('.event-image');
        
        console.log('Found images:', allDocImages.length);
        console.log('Found videos:', allDocVideos.length);
        console.log('Main event image:', mainEventImage);
        
        let modalHTML = '';
        
        // Event Status Section
        const statusBadge = Array.from(metaItems).find(item => item.querySelector('.meta-label')?.textContent.includes('Status'));
        if (statusBadge) {
            const statusValue = statusBadge.querySelector('.meta-value')?.innerHTML || '';
            modalHTML += `
                <div class="modal-section">
                    <h3><i class="fas fa-info-circle"></i> Event Status</h3>
                    <div class="status-display">${statusValue}</div>
                </div>
            `;
        }
        
        // Event Information Section
        modalHTML += '<div class="modal-section"><h3><i class="fas fa-calendar-alt"></i> Event Information</h3><div class="info-grid">';
        
        metaItems.forEach(item => {
            const label = item.querySelector('.meta-label');
            const value = item.querySelector('.meta-value');
            if (label && value && !label.textContent.includes('Status')) {
                const labelText = label.textContent.trim();
                const valueHTML = value.innerHTML.trim();
                modalHTML += `
                    <div class="info-item">
                        <span class="info-label">${labelText}</span>
                        <span class="info-value">${valueHTML}</span>
                    </div>
                `;
            }
        });
        
        modalHTML += '</div></div>';
        
        // Event Description Section
        if (contentText) {
            modalHTML += `
                <div class="modal-section">
                    <h3><i class="fas fa-align-left"></i> Description</h3>
                    <div class="description-content">${contentText}</div>
                </div>
            `;
        }
        
        // Event Media Section - Handle multiple images and videos
        const relevantImages = [];
        const relevantVideos = [];
        
        // Filter images that are likely event media (exclude icons, avatars, etc.)
        allDocImages.forEach(img => {
            const src = img.src || img.getAttribute('src') || '';
            const className = img.className || '';
            
            // Include images that are likely event media
            if (src.includes('storage/') || className.includes('event-image') || 
                src.includes('events/') || img.closest('.content-image') || 
                img.closest('.event-content')) {
                relevantImages.push(img);
            }
        });
        
        // Include all videos
        allDocVideos.forEach(video => {
            relevantVideos.push(video);
        });
        
        console.log('Relevant images:', relevantImages.length);
        console.log('Relevant videos:', relevantVideos.length);
        
        if (relevantImages.length > 0 || relevantVideos.length > 0) {
            modalHTML += `
                <div class="modal-section">
                    <h3><i class="fas fa-images"></i> Event Media</h3>
                    <div class="media-grid">
            `;
            
            // Add images
            relevantImages.forEach((img, index) => {
                const imgSrc = img.src || img.getAttribute('src');
                const imgAlt = img.alt || `Event Image ${index + 1}`;
                console.log('Adding image:', imgSrc);
                modalHTML += `
                    <div class="media-item image-item">
                        <img src="${imgSrc}" alt="${imgAlt}" class="modal-image" onclick="openImageModal('${imgSrc}')">
                        <p class="media-label">Image ${index + 1}</p>
                    </div>
                `;
            });
            
            // Add videos
            relevantVideos.forEach((video, index) => {
                const videoSrc = video.src || video.querySelector('source')?.src;
                if (videoSrc) {
                    console.log('Adding video:', videoSrc);
                    modalHTML += `
                        <div class="media-item video-item">
                            <video controls class="modal-video">
                                <source src="${videoSrc}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <p class="media-label">Video ${index + 1}</p>
                        </div>
                    `;
                }
            });
            
            modalHTML += `
                    </div>
                </div>
            `;
        }
        
        return modalHTML;
    }

    // Fallback function to get event data from the current table
    function getEventDataFromTable(eventId) {
        const eventRows = document.querySelectorAll('tbody tr');
        for (let row of eventRows) {
            const showButton = row.querySelector(`button[onclick*="${eventId}"]`);
            if (showButton) {
                const cells = row.querySelectorAll('td');
                return {
                    id: eventId,
                    title: cells[1]?.textContent?.trim() || 'Event',
                    description: cells[2]?.textContent?.trim() || 'No description available',
                    creator: cells[3]?.textContent?.trim() || 'Unknown',
                    department: cells[4]?.textContent?.trim() || 'N/A',
                    date: cells[5]?.textContent?.trim() || 'TBD',
                    status: cells[6]?.querySelector('.status-badge')?.textContent?.trim() || 'Unknown'
                };
            }
        }
        return null;
    }

    // Create modal content from table data
    function createModalContentFromEventData(eventData) {
        return `
            <div class="modal-section">
                <h3><i class="fas fa-info-circle"></i> Event Status</h3>
                <div class="status-display">
                    <span class="status-badge">${eventData.status}</span>
                </div>
            </div>
            
            <div class="modal-section">
                <h3><i class="fas fa-calendar-alt"></i> Event Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user"></i> Created By</span>
                        <span class="info-value">${eventData.creator}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-building"></i> Department</span>
                        <span class="info-value">${eventData.department}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar"></i> Event Date</span>
                        <span class="info-value">${eventData.date}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-hashtag"></i> ID</span>
                        <span class="info-value">#${eventData.id}</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-section">
                <h3><i class="fas fa-align-left"></i> Description</h3>
                <div class="description-content">${eventData.description}</div>
            </div>
            
            <div class="modal-section">
                <h3><i class="fas fa-info-circle"></i> Media Information</h3>
                <div class="description-content">
                    <p>Media files are available on the full event page. <a href="/superadmin/events/${eventData.id}" target="_blank">Click here to view full details</a>.</p>
                </div>
            </div>
        `;
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

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeShowModal();
            closeImageModal();
        }
    });

    // Close show modal when clicking outside
    document.getElementById('showEventModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeShowModal();
        }
    });
</script>

<style>
    .no-date {
        color: #6b7280;
        font-style: italic;
    }

    .text-muted {
        color: #6b7280 !important;
        font-style: italic;
    }

    /* Loading and Error States for Show Modal */
    .loading-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        text-align: center;
    }

    .loading-spinner {
        font-size: 2rem;
        color: #10b981;
        margin-bottom: 1rem;
    }

    .loading-state p {
        color: var(--text-secondary);
        margin: 0;
        font-size: 1rem;
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

    .error-state p {
        margin: 0;
        font-size: 1rem;
    }

    /* Show Modal Content Styles */
    .modal-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .modal-section h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-section h3 i {
        color: #10b981;
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
        color: #10b981;
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

    .modal-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .modal-image:hover {
        transform: scale(1.05);
    }

    .modal-video {
        width: 100%;
        height: 150px;
    }

    .media-label {
        padding: 0.75rem;
        margin: 0;
        font-weight: 500;
        color: var(--text-primary);
        text-align: center;
        background: #f8fafc;
        font-size: 0.875rem;
    }

    /* Full Screen Image Modal */
    .image-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        cursor: pointer;
    }

    .image-modal-container {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        cursor: default;
    }

    .image-modal-close {
        position: absolute;
        top: -50px;
        right: 0;
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

    .image-modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .full-screen-image {
        max-width: 100%;
        max-height: 100%;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    @media (max-width: 768px) {
        .info-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .info-value {
            text-align: left;
        }

        .media-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
