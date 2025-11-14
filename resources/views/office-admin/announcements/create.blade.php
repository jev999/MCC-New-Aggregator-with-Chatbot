@extends('layouts.app')

@section('title', 'Create Announcement - Office Admin')

@section('content')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
        <i class="fas fa-bars"></i>
    </button>

   <!-- Replace the current sidebar section with this updated version -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>
            @php
                $office = Auth::guard('admin')->user()->office ?? 'OFFICE';
            @endphp
            @if($office === 'NSTP')
                <i class="fas fa-flag"></i>
            @elseif($office === 'SSC')
                <i class="fas fa-users"></i>
            @elseif($office === 'GUIDANCE')
                <i class="fas fa-heart"></i>
            @elseif($office === 'REGISTRAR')
                <i class="fas fa-file-alt"></i>
            @elseif($office === 'CLINIC')
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
        <div class="header">
            <div>
                <h1><i class="fas fa-plus-circle"></i> Create New Announcement</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Share important information from {{ Auth::guard('admin')->user()->office_display }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('office-admin.announcements.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

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

        <!-- Info notice for all office admins -->
        <div class="visibility-notice">
            <div class="notice-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="notice-content">
                <h4><i class="fas fa-info-circle"></i> Announcement Visibility</h4>
                <p>All announcements created by {{ Auth::guard('admin')->user()->office_display }} will be visible to <strong>all students and faculty</strong> when published. The announcement will show "Posted by {{ Auth::guard('admin')->user()->office_display }}".</p>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" action="{{ route('office-admin.announcements.store') }}" enctype="multipart/form-data" class="announcement-form">
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
                            <div class="info-label">
                                <i class="fas fa-calendar"></i> Created On
                            </div>
                            <div class="info-value" id="current-date">
                                {{ now()->format('F d, Y') }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-clock"></i> Time
                            </div>
                            <div class="info-value" id="current-time">
                                {{ now()->format('g:i A') }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user"></i> Created By
                            </div>
                            <div class="info-value">
                                {{ Auth::guard('admin')->user()->username }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-briefcase"></i> Office
                            </div>
                            <div class="info-value">
                                {{ Auth::guard('admin')->user()->office_display }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> Publishing Settings</h3>

                    <!-- Hidden input to automatically set visibility to all users -->
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

                    <!-- Multiple Images Upload -->
                    <div class="form-group">
                        <label for="images" class="form-label">
                            <i class="fas fa-images"></i> Images (up to 2)
                        </label>
                        <div class="file-upload-area" id="imagesUploadArea">
                            <input type="file"
                                   id="images"
                                   name="images[]"
                                   class="file-input @error('images') error @enderror @error('images.*') error @enderror"
                                   accept=".jpg,.jpeg,.png"
                                   multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop multiple images</p>
                                <small>JPG, PNG up to 2MB each (maximum 2 images)</small>
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

                    <!-- Multiple Videos Upload -->
                    <div class="form-group">
                        <label for="videos" class="form-label">
                            <i class="fas fa-video"></i> Video (up to 1)
                        </label>
                        <div class="file-upload-area" id="videosUploadArea">
                            <input type="file"
                                   id="videos"
                                   name="videos[]"
                                   class="file-input @error('videos') error @enderror @error('videos.*') error @enderror"
                                   accept=".mp4"
                                   multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-video"></i>
                                <p>Click to upload or drag and drop video</p>
                                <small>MP4 up to 50MB (maximum 1 video)</small>
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

                    <button type="submit" name="action" value="save_and_publish" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Create Announcement
                    </button>
                    <a href="{{ route('office-admin.announcements.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
   :root {
        /* Keep your existing variables and add these */
        --sidebar-width: 280px;
        --header-height: 80px;
        --transition-speed: 0.3s;
        --border-radius: 12px;
        --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        --card-padding: 1.5rem;
        
        /* Dynamic theming based on office */
        @php $office = auth('admin')->user()->office; @endphp
        @if($office === 'NSTP')
            --primary-color: #10b981;
            --primary-light: #d1fae5;
            --primary-dark: #059669;
            --primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        @elseif($office === 'SSC')
            --primary-color: #3b82f6;
            --primary-light: #dbeafe;
            --primary-dark: #2563eb;
            --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        @elseif($office === 'GUIDANCE')
            --primary-color: #8b5cf6;
            --primary-light: #ede9fe;
            --primary-dark: #7c3aed;
            --primary-gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        @elseif($office === 'REGISTRAR')
            --primary-color: #f59e0b;
            --primary-light: #fef3c7;
            --primary-dark: #d97706;
            --primary-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        @elseif($office === 'CLINIC')
            --primary-color: #ef4444;
            --primary-light: #fee2e2;
            --primary-dark: #dc2626;
            --primary-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        @else
            --primary-color: #667eea;
            --primary-light: #e0e7ff;
            --primary-dark: #5b67d7;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        @endif
        
        --bg-sidebar: #1f2937;
        --bg-sidebar-hover: rgba(255, 255, 255, 0.08);
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

    .office-info {
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

    .logout-btn {
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 500;
        width: calc(100% - 1rem);
        text-align: left;
        padding: 0.75rem 1.25rem;
        cursor: pointer;
        transition: all var(--transition-speed) ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        margin: 0.5rem;
        border-radius: 6px;
    }

    .logout-btn:hover {
        background: var(--bg-sidebar-hover);
        color: white;
    }

    .logout-btn i {
        width: 20px;
        text-align: center;
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            width: 100%;
        }
    }
    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 2rem;
        background: var(--bg-secondary);
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
        background: var(--bg-primary);
        padding: 2rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }

    .header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
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

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        line-height: 1;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background: var(--bg-tertiary);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--border-color);
        color: var(--text-primary);
    }

    .btn-success {
        background: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .alert {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: var(--radius-lg);
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

    .alert-content {
        flex: 1;
    }

    .alert-content strong {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .form-container {
        background: var(--bg-primary);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .form-section {
        padding: 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .form-section h3 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
    }

    .form-section h3 i {
        color: var(--primary-color);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-label i {
        color: var(--primary-color);
        width: 16px;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: var(--bg-primary);
        color: var(--text-primary);
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-input.error,
    .form-textarea.error {
        border-color: var(--danger-color);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-help {
        display: block;
        margin-top: 0.5rem;
        color: var(--text-muted);
        font-size: 0.75rem;
    }

    .error-message {
        display: block;
        margin-top: 0.5rem;
        color: var(--danger-color);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .creation-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        background: var(--bg-tertiary);
        padding: 1.5rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .info-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
    }



    /* Checkbox Group Styling */
    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        transition: all 0.3s ease;
        background: var(--bg-secondary);
    }

    .checkbox-label:hover {
        border-color: var(--primary-color);
        background: #eff6ff;
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
        background: white;
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
        font-size: 12px;
        font-weight: bold;
    }

    .checkbox-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: var(--text-primary);
    }

    .checkbox-text i {
        color: var(--primary-color);
    }

    /* File Upload Styling */
    .file-upload-area {
        position: relative;
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-lg);
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        background: var(--bg-secondary);
        cursor: pointer;
    }

    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: #eff6ff;
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

    .file-upload-content {
        pointer-events: none;
    }

    .file-upload-content i {
        font-size: 2rem;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .file-upload-content p {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .file-upload-content small {
        color: var(--text-muted);
        font-size: 0.75rem;
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
        padding: 2rem;
        background: var(--bg-tertiary);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    /* NSTP Visibility Notice */
    .nstp-visibility-notice {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 1px solid #3b82f6;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .nstp-visibility-notice .notice-icon {
        background: #3b82f6;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.25rem;
    }

    .nstp-visibility-notice .notice-content h4 {
        color: #1e40af;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .nstp-visibility-notice .notice-content p {
        color: #1d4ed8;
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0;
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.active {
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
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .header-actions {
            justify-content: flex-start;
        }

        .creation-info {
            grid-template-columns: 1fr;
        }

        .form-section {
            padding: 1.5rem;
        }

        .form-actions {
            padding: 1.5rem;
            flex-direction: column;
        }

        .btn {
            justify-content: center;
        }
    }

    /* NSTP Auto-visibility Card */
    .nstp-auto-visibility {
        margin-top: 0.5rem;
    }

    .auto-visibility-card {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        background: linear-gradient(135deg, var(--primary-light) 0%, #e0f2fe 100%);
        border: 2px solid var(--primary-color);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .auto-visibility-icon {
        background: var(--primary-color);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.5rem;
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.2);
    }

    .auto-visibility-content {
        flex: 1;
    }

    .auto-visibility-content h4 {
        color: var(--primary-dark);
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 0.75rem 0;
    }

    .auto-visibility-content p {
        color: var(--primary-dark);
        font-size: 0.95rem;
        line-height: 1.5;
        margin: 0 0 1rem 0;
    }

    .departments-list {
        background: rgba(255, 255, 255, 0.8);
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid rgba(59, 130, 246, 0.2);
        margin: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .department-item {
        display: flex;
        align-items: center;
        color: var(--primary-dark);
        font-size: 0.85rem;
        line-height: 1.4;
        padding: 0.25rem 0;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .department-item:not(:last-child) {
        margin-bottom: 0.25rem;
    }

    .department-item i {
        color: var(--success-color);
        margin-right: 0.75rem;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
</style>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
        
        // Add overlay when sidebar is active
        if (sidebar.classList.contains('active')) {
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.background = 'rgba(0,0,0,0.5)';
            overlay.style.zIndex = '999';
            overlay.style.backdropFilter = 'blur(2px)';
            overlay.onclick = toggleSidebar;
            document.body.appendChild(overlay);
        } else {
            document.querySelector('.sidebar-overlay')?.remove();
        }
    }
    // Update time every second
    function updateTime() {
        const now = new Date();
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }
    }

    // Update time immediately and then every second
    updateTime();
    setInterval(updateTime, 1000);

    // Multiple images upload with 2-file limit
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const maxFiles = 2;
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        
        // Validate file count
        if (files.length > maxFiles) {
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
        
        // Validate each file
        for (let file of files) {
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type!',
                    text: `Invalid file type: ${file.name}. Only JPG and PNG files are allowed.`,
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
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Size Too Large!',
                    text: `File too large: ${file.name}. Maximum size is 5MB per image.`,
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

    // Multiple videos upload with 1-file limit
    document.getElementById('videos').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const maxFiles = 1;
        const maxSize = 50 * 1024 * 1024; // 50MB
        const allowedTypes = ['video/mp4'];
        
        // Validate file count
        if (files.length > maxFiles) {
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
        
        // Validate each file
        for (let file of files) {
            if (!allowedTypes.includes(file.type)) {
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
            if (file.size > maxSize) {
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

    // File upload preview functionality
    document.addEventListener('DOMContentLoaded', function() {
        const fileInputs = document.querySelectorAll('.file-input');

        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const uploadArea = this.closest('.file-upload-area');
                const content = uploadArea.querySelector('.file-upload-content');

                if (this.files && this.files[0]) {
                    const fileName = this.files[0].name;
                    const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);

                    content.innerHTML = `
                        <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                        <p style="color: var(--success-color);">${fileName}</p>
                        <small>Size: ${fileSize} MB</small>
                    `;

                    uploadArea.style.borderColor = 'var(--success-color)';
                    uploadArea.style.background = '#f0fdf4';
                }
            });
        });
    });
</script>

<style>
    /* SweetAlert2 Custom Styling */
    .swal-popup {
        border-radius: 16px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
    }

    .swal-title {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        color: #1f2937 !important;
        margin-bottom: 0.75rem !important;
    }

    .swal-content {
        font-size: 1rem !important;
        color: #4b5563 !important;
        line-height: 1.5 !important;
    }

    .swal2-popup {
        border-radius: 16px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }

    .swal2-title {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        color: #1f2937 !important;
    }

    .swal2-html-container {
        font-size: 1rem !important;
        color: #4b5563 !important;
        line-height: 1.5 !important;
    }

    .swal2-confirm {
        border-radius: 8px !important;
        padding: 0.75rem 1.5rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
    }

    .swal2-confirm:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .swal2-icon {
        border: none !important;
        margin: 1rem auto 1.5rem !important;
    }

    .swal2-icon.swal2-warning {
        border-color: #f59e0b !important;
        color: #f59e0b !important;
    }

    .swal2-icon.swal2-error {
        border-color: #ef4444 !important;
        color: #ef4444 !important;
    }

    /* Visibility Notice */
    .visibility-notice {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
        border: 1px solid #93c5fd;
        border-radius: var(--radius-lg);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .visibility-notice .notice-icon {
        background: #3b82f6;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.25rem;
    }

    .visibility-notice .notice-content h4 {
        color: #1e40af;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .visibility-notice .notice-content p {
        color: #1d4ed8;
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0;
    }
</style>

@endsection
