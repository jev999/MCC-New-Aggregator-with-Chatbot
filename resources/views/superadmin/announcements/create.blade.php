@extends('layouts.app')

@section('title', 'Create Announcement - Super Admin')

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
                <i class="fas fa-users-cog"></i> Department Admin Management
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
            @if(auth('admin')->check() && auth('admin')->user()->isSuperAdmin())
            <li><a href="{{ route('superadmin.admin-access') }}">
                <i class="fas fa-clipboard-list"></i> Admin Access Logs
            </a></li>
            <li><a href="{{ route('superadmin.backup') }}">
                <i class="fas fa-database"></i> Database Backup
            </a></li>
            @endif
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div>
                <h1><i class="fas fa-plus-circle"></i> Create New Announcement</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Share important information with the campus community</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('superadmin.announcements.index') }}" class="btn btn-back">
                    <div class="btn-shine"></div>
                    <span><i class="fas fa-arrow-left"></i> Back to List</span>
                </a>
            </div>
        </div>

        <!-- Create Announcement Form Container -->
        <div class="form-container">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.announcements.store') }}" enctype="multipart/form-data" class="announcement-form" id="createAnnouncementForm">
                @csrf
                
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i> Title *
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               class="form-input @error('title') error @enderror" 
                               value="{{ old('title') }}" 
                               placeholder="Enter announcement title..."
                               required>
                        @error('title')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label">
                            <i class="fas fa-align-left"></i> Content *
                        </label>
                        <textarea id="content" 
                                  name="content" 
                                  class="form-textarea @error('content') error @enderror" 
                                  rows="8" 
                                  placeholder="Write your announcement content here..."
                                  required>{{ old('content') }}</textarea>
                        @error('content')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Real-time Creation Info -->
                <div class="form-section">
                    <h3><i class="fas fa-clock"></i> Creation Information</h3>
                    <div class="creation-info">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-calendar"></i> Creation Date
                            </label>
                            <div class="info-value" id="currentDate">
                                {{ now()->setTimezone('Asia/Manila')->format('F d, Y') }}
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-clock"></i> Creation Time
                            </label>
                            <div class="info-value" id="currentTime">
                                {{ now()->setTimezone('Asia/Manila')->format('g:i:s A') }}
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-user"></i> Created By
                            </label>
                            <div class="info-value">
                                {{ Auth::guard('admin')->user()->username }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> Settings</h3>

                    <!-- Hidden input to automatically set visibility to 'all' -->
                    <input type="hidden" name="visibility_scope" value="all">

                    <div class="form-group">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox"
                                       name="is_published"
                                       value="1"
                                       {{ old('is_published') ? 'checked' : '' }}
                                       class="checkbox-input">
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">
                                    <i class="fas fa-eye"></i> Publish immediately
                                </span>
                            </label>
                            <small class="form-help">If unchecked, the announcement will be saved as a draft</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-image"></i> Media Attachments (Optional)</h3>
                    
                    <div class="form-group">
                        <label for="images" class="form-label">
                            <i class="fas fa-camera"></i> Images (Up to 2 files)
                        </label>
                        <div class="file-upload-area" id="imagesUploadArea">
                            <input type="file"
                                   id="images"
                                   name="images[]"
                                   class="file-input @error('images') error @enderror"
                                   accept=".jpg,.jpeg,.png"
                                   multiple
                                   data-max-files="2">
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>JPG, PNG only - up to 2MB each, maximum 2 files</small>
                            </div>
                        </div>
                        <div id="imagesPreview" class="file-previews"></div>
                        @error('images')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('images.*')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="videos" class="form-label">
                            <i class="fas fa-video"></i> Video (Up to 1 file)
                        </label>
                        <div class="file-upload-area" id="videosUploadArea">
                            <input type="file"
                                   id="videos"
                                   name="videos[]"
                                   class="file-input @error('videos') error @enderror"
                                   accept=".mp4"
                                   multiple
                                   data-max-files="1">
                            <div class="file-upload-content">
                                <i class="fas fa-video"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>MP4 only - up to 50MB, maximum 1 file</small>
                            </div>
                        </div>
                        <div id="videosPreview" class="file-previews"></div>
                        @error('videos')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('videos.*')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-create">
                        <div class="btn-shine"></div>
                        <span><i class="fas fa-save"></i> Create Announcement</span>
                    </button>
                    <a href="{{ route('superadmin.announcements.index') }}" class="btn btn-cancel">
                        <div class="btn-shine"></div>
                        <span><i class="fas fa-times"></i> Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }

    .dashboard {
        display: flex;
        min-height: 100vh;
        background: #f8fafc;
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
        color: #ffd700;
        font-size: 1.5rem;
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
        flex: 1;
        margin-left: 280px;
        padding: 2rem;
        background: #f8fafc;
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }

    /* Mobile responsiveness */
    @media (max-width: 1024px) {
        .mobile-menu-btn {
            display: block !important;
        }

        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            width: 100%;
        }
    }

    .header {
        background: white;
        padding: 2rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid var(--border-color);
    }

    .header h1 {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header h1 i {
        color: var(--primary-color);
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    /* Form Container Styles */
    .form-container {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }


    .announcement-form {
        padding: 2rem;
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

    /* Enhanced Button Styles */
    .btn {
        position: relative;
        overflow: hidden;
        border: none;
        border-radius: 12px;
        padding: 14px 28px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-width: 160px;
        z-index: 1;
    }

    .btn * {
        pointer-events: none; /* Prevent nested elements from blocking clicks */
    }

    .btn span {
        position: relative;
        z-index: 3;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn i {
        transition: all 0.3s ease;
    }

    .btn-shine {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s ease;
        z-index: 2;
    }

    /* Back Button (Blue) */
    .btn-back {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    }

    .btn-back:hover .btn-shine {
        left: 100%;
    }

    .btn-back:hover i {
        transform: scale(1.1) rotate(-5deg);
    }

    /* Create Button (Green) */
    .btn-create {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
        background: linear-gradient(135deg, #059669 0%, #065f46 100%);
    }

    .btn-create:hover .btn-shine {
        left: 100%;
    }

    .btn-create:hover i {
        transform: scale(1.1) rotate(5deg);
    }

    /* Cancel Button (Red) */
    .btn-cancel {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .btn-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5);
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    }

    .btn-cancel:hover .btn-shine {
        left: 100%;
    }

    .btn-cancel:hover i {
        transform: scale(1.1) rotate(-5deg);
    }

    /* Disabled State */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .btn:disabled:hover .btn-shine {
        left: -100% !important;
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

    /* Mobile Responsive Styles - Enhanced */
    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            padding: 1.5rem;
        }

        .header-actions {
            width: 100%;
            justify-content: center;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .announcement-form {
            padding: 1.5rem;
        }

        .btn {
            min-width: 100%;
            padding: 16px 28px;
            font-size: 0.9rem;
        }

        .form-input,
        .form-textarea,
        .form-select {
            padding: 1rem;
            font-size: 16px; /* Prevents zoom on iOS */
        }

        .file-upload-area {
            padding: 1.5rem;
        }

        .creation-info {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    /* Small screens */
    @media (max-width: 576px) {
        .header h1 {
            font-size: 1.5rem;
        }

        .announcement-form {
            padding: 1rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
        }

        .form-section h3 {
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .btn {
            padding: 14px 24px;
            font-size: 0.85rem;
        }

        .file-upload-area {
            padding: 1rem;
        }

        .file-upload-content i {
            font-size: 1.5rem;
        }
    }

    /* Extra small screens */
    @media (max-width: 480px) {
        .dashboard {
            padding: 0.5rem;
        }

        .header {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .header h1 {
            font-size: 1.25rem;
        }

        .announcement-form {
            padding: 0.75rem;
        }

        .form-section h3 {
            font-size: 0.95rem;
        }

        .btn {
            padding: 12px 20px;
            font-size: 0.8rem;
            min-width: 100%;
        }

        .mobile-menu-btn {
            display: block !important;
        }
    }

    /* Touch-friendly enhancements */
    @media (hover: none) and (pointer: coarse) {
        .btn {
            min-height: 44px;
            padding: 12px 24px;
        }

        .form-input,
        .form-textarea,
        .form-select {
            min-height: 44px;
            padding: 12px 16px;
        }

        .checkbox-custom {
            width: 24px;
            height: 24px;
        }

        .file-upload-area {
            min-height: 120px;
        }
    }

    /* High DPI displays */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .form-input,
        .form-textarea,
        .form-select {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    }
</style>

<script>

    // Multiple images upload with 2-file limit
    document.getElementById('images').addEventListener('change', function(e) {
        const maxFiles = 2;
        if (e.target.files.length > maxFiles) {
            Swal.fire({
                icon: 'warning',
                title: 'Too Many Images!',
                text: `Please select only ${maxFiles} image files maximum.`,
                confirmButtonText: 'Got it!',
                confirmButtonColor: '#3b82f6',
                background: '#ffffff',
                customClass: {
                    popup: 'swal-popup',
                    title: 'swal-title',
                    content: 'swal-content'
                }
            });
            e.target.value = '';
            return;
        }
        
        // Check file sizes
        const maxSize = 2 * 1024 * 1024; // 2MB
        const oversizedFiles = Array.from(e.target.files).filter(file => file.size > maxSize);
        if (oversizedFiles.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'File Size Too Large!',
                text: `Some images exceed the 2MB limit. Please choose smaller files.`,
                confirmButtonText: 'Choose Again',
                confirmButtonColor: '#ef4444',
                background: '#ffffff',
                customClass: {
                    popup: 'swal-popup',
                    title: 'swal-title',
                    content: 'swal-content'
                }
            });
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
                    <button type="button" class="file-remove-btn" onclick="removeImageFile(${index})">
                        <i class="fas fa-times"></i> Remove
                    </button>
                `;
                previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
    });

    // Single video upload with 1-file limit
    document.getElementById('videos').addEventListener('change', function(e) {
        const maxFiles = 1;
        if (e.target.files.length > maxFiles) {
            Swal.fire({
                icon: 'warning',
                title: 'Too Many Videos!',
                text: `Please select only ${maxFiles} video file maximum.`,
                confirmButtonText: 'Got it!',
                confirmButtonColor: '#3b82f6',
                background: '#ffffff',
                customClass: {
                    popup: 'swal-popup',
                    title: 'swal-title',
                    content: 'swal-content'
                }
            });
            e.target.value = '';
            return;
        }
        
        // Check file size and type
        const maxSize = 50 * 1024 * 1024; // 50MB
        const allowedTypes = ['video/mp4'];
        const file = e.target.files[0];
        
        if (file && !allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type!',
                text: `Invalid file type: ${file.name}. Only MP4 video files are allowed.`,
                confirmButtonText: 'Choose Again',
                confirmButtonColor: '#ef4444',
                background: '#ffffff',
                customClass: {
                    popup: 'swal-popup',
                    title: 'swal-title',
                    content: 'swal-content'
                }
            });
            e.target.value = '';
            return;
        }
        
        if (file && file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'File Size Too Large!',
                text: `File too large: ${file.name}. Maximum size is 50MB per video.`,
                confirmButtonText: 'Choose Again',
                confirmButtonColor: '#ef4444',
                background: '#ffffff',
                customClass: {
                    popup: 'swal-popup',
                    title: 'swal-title',
                    content: 'swal-content'
                }
            });
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
                <button type="button" class="file-remove-btn" onclick="removeVideoFile(${index})">
                    <i class="fas fa-times"></i> Remove
                </button>
            `;
            previewContainer.appendChild(previewItem);
        });
    });

    function removeImageFile(index) {
        const input = document.getElementById('images');
        const dt = new DataTransfer();
        const files = Array.from(input.files);
        
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    }

    function removeVideoFile(index) {
        const input = document.getElementById('videos');
        const dt = new DataTransfer();
        const files = Array.from(input.files);
        
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    }

    // Single file upload preview (legacy support) - enforce single file selection
    document.getElementById('image').addEventListener('change', function(e) {
        // Enforce single file selection for legacy inputs
        if (e.target.files.length > 1) {
            alert('Please select only one image file for the legacy single image upload.');
            e.target.value = '';
            return;
        }
        
        const file = e.target.files[0];
        const uploadArea = document.getElementById('imageUploadArea');
        const originalInput = e.target;

        if (file) {
            const reader = new FileReader();
            reader.onload = function(readerEvent) {
                // Hide the original input and show preview
                originalInput.style.display = 'none';

                // Create preview element
                const previewDiv = document.createElement('div');
                previewDiv.className = 'file-preview';
                previewDiv.innerHTML = `
                    <img src="${readerEvent.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: var(--radius-md);">
                    <p style="margin: 1rem 0 0 0; font-weight: 500;">${file.name}</p>
                    <button type="button" onclick="clearFileUpload()" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; background: #ef4444; color: white; border: none; border-radius: var(--radius-sm); cursor: pointer;">
                        <i class="fas fa-times"></i> Remove
                    </button>
                `;

                // Add preview after the input
                uploadArea.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        }
    });

    function clearFileUpload() {
        const imageInput = document.getElementById('image');
        const uploadArea = document.getElementById('imageUploadArea');
        const preview = uploadArea.querySelector('.file-preview');

        // Clear the file input
        imageInput.value = '';

        // Show the input again
        imageInput.style.display = '';

        // Remove the preview
        if (preview) {
            preview.remove();
        }
    }

    // Video upload preview - enforce single file selection
    document.getElementById('video').addEventListener('change', function(e) {
        // Enforce single file selection for legacy inputs
        if (e.target.files.length > 1) {
            alert('Please select only one video file for the legacy single video upload.');
            e.target.value = '';
            return;
        }
        
        const file = e.target.files[0];
        const uploadArea = document.getElementById('videoUploadArea');
        const originalInput = e.target;

        if (file) {
            // Hide the original input
            originalInput.style.display = 'none';

            // Create preview element
            const previewDiv = document.createElement('div');
            previewDiv.className = 'file-preview';
            previewDiv.innerHTML = `
                <i class="fas fa-video" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <p style="margin: 1rem 0 0 0; font-weight: 500;">${file.name}</p>
                <small style="color: var(--text-secondary);">${(file.size / (1024 * 1024)).toFixed(2)} MB</small>
                <button type="button" onclick="clearVideoUpload()" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; background: #ef4444; color: white; border: none; border-radius: var(--radius-sm); cursor: pointer;">
                    <i class="fas fa-times"></i> Remove
                </button>
            `;

            // Add preview after the input
            uploadArea.appendChild(previewDiv);
        }
    });

    function clearVideoUpload() {
        const videoInput = document.getElementById('video');
        const uploadArea = document.getElementById('videoUploadArea');
        const preview = uploadArea.querySelector('.file-preview');

        // Clear the file input
        videoInput.value = '';

        // Show the input again
        videoInput.style.display = '';

        // Remove the preview
        if (preview) {
            preview.remove();
        }
    }


    // Real-time clock update
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
    setInterval(updateCurrentTime, 1000);

    // Update immediately on page load
    updateCurrentTime();

    // Auto-save functionality (optional)
    let autoSaveTimer;
    const form = document.querySelector('.announcement-form');
    const inputs = form.querySelectorAll('input, textarea, select');

    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                // Auto-save logic here if needed
                console.log('Auto-saving...');
            }, 2000);
        });
    });

    // Sidebar toggle functionality for mobile
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
@endsection
