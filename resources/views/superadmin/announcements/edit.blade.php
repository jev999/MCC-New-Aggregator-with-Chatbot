@extends('layouts.app')

@section('title', 'Edit Announcement - Super Admin')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

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
            
               
            
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div>
                <h1><i class="fas fa-edit"></i> Edit Announcement</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Update announcement information</p>
            </div>
            <div class="header-actions">
                
                <a href="{{ route('superadmin.announcements.index') }}" class="btn btn-info">
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

        <div class="form-container">
            <!-- Announcement Info Header -->
            <div class="announcement-info">
                <div class="info-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="info-content">
                    <h2>{{ $announcement->title }}</h2>
                    <div class="info-meta">
                        <span>Created {{ $announcement->created_at->format('M d, Y') }}</span>
                        <span class="separator">•</span>
                        <span class="status-badge {{ $announcement->is_published ? 'published' : 'draft' }}">
                            <i class="fas fa-{{ $announcement->is_published ? 'check' : 'eye-slash' }}"></i>
                            {{ $announcement->is_published ? 'Published' : 'Draft' }}
                        </span>
                        <span class="separator">•</span>
                        <span>By {{ $announcement->admin->username }}</span>
                        @if($announcement->admin->department)
                        <span class="separator">•</span>
                        <span class="department-badge">{{ $announcement->admin->department }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('superadmin.announcements.update', $announcement) }}" enctype="multipart/form-data" class="announcement-form">
                @csrf
                @method('PUT')
                
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
                               value="{{ old('title', $announcement->title) }}" 
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
                                  required>{{ old('content', $announcement->content) }}</textarea>
                        @error('content')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
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
                                       {{ old('is_published', $announcement->is_published) ? 'checked' : '' }}
                                       class="checkbox-input">
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">
                                    <i class="fas fa-eye"></i> Published
                                </span>
                            </label>
                            <small class="form-help">Check to make this announcement visible to users</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Media Files</h3>
                    
                    <!-- Current Single Image -->
                    @if($announcement->image_path)
                    <div class="current-media-section">
                        <label class="form-label">Current Image</label>
                        <div class="current-media-grid">
                            <div class="current-media-item">
                                <img src="{{ asset('storage/' . $announcement->image_path) }}" alt="Current image" class="current-image-display">
                                <div class="media-actions">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="remove_image" value="1" class="checkbox-input">
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-text">Remove</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Current Multiple Images -->
                    @if($announcement->image_paths && is_array($announcement->image_paths) && !empty($announcement->image_paths))
                    <div class="current-media-section">
                        <label class="form-label">Current Images ({{ is_array($announcement->image_paths) ? count($announcement->image_paths) : 0 }})</label>
                        <div class="current-media-grid">
                            @foreach($announcement->image_paths as $index => $imagePath)
                            <div class="current-media-item">
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="Current image {{ $index + 1 }}" class="current-image-display">
                                <div class="media-actions">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="remove_images[]" value="{{ $index }}" class="checkbox-input">
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-text">Remove</span>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Current Single Video -->
                    @if($announcement->video_path)
                    <div class="current-media-section">
                        <label class="form-label">Current Video</label>
                        <div class="current-media-grid">
                            <div class="current-media-item video-media-item">
                                <div class="video-container">
                                    <video controls preload="metadata" class="current-video">
                                        <source src="{{ asset('storage/' . $announcement->video_path) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="video-overlay">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                </div>
                                <div class="video-info">
                                    <span class="video-filename">{{ basename($announcement->video_path) }}</span>
                                </div>
                                <div class="remove-media-checkbox">
                                    <input type="checkbox" name="remove_video" value="1" id="remove_video_single">
                                    <label for="remove_video_single"><i class="fas fa-trash-alt"></i> Remove</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Current Multiple Videos -->
                    @if($announcement->video_paths && is_array($announcement->video_paths) && !empty($announcement->video_paths))
                    <div class="current-media-section">
                        <label class="form-label">Current Videos ({{ is_array($announcement->video_paths) ? count($announcement->video_paths) : 0 }})</label>
                        <div class="current-media-grid">
                            @foreach($announcement->video_paths as $index => $videoPath)
                            <div class="current-media-item video-media-item">
                                <div class="video-container">
                                    <video controls preload="metadata" class="current-video">
                                        <source src="{{ asset('storage/' . $videoPath) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="video-overlay">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                </div>
                                <div class="video-info">
                                    <span class="video-filename">{{ basename($videoPath) }}</span>
                                </div>
                                <div class="remove-media-checkbox">
                                    <input type="checkbox" name="remove_videos[]" value="{{ $index }}" id="remove_video_{{ $index }}">
                                    <label for="remove_video_{{ $index }}"><i class="fas fa-trash-alt"></i> Remove</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- New Images Upload -->
                    <div class="form-group">
                        <label for="images" class="form-label">
                            <i class="fas fa-camera"></i> Upload Images (Max: 2)
                        </label>
                        <div class="file-upload-area" id="imagesUploadArea">
                            <input type="file"
                                   id="images"
                                   name="images[]"
                                   class="file-input @error('images') error @enderror @error('images.*') error @enderror"
                                   accept="image/jpeg,image/png,image/jpg"
                                   multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>PNG, JPG only - Max 2 files, 5MB each</small>
                            </div>
                        </div>
                        <div id="imagePreviewContainer" class="file-preview-container"></div>
                        @error('images')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('images.*')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- New Videos Upload -->
                    <div class="form-group">
                        <label for="videos" class="form-label">
                            <i class="fas fa-video"></i> Upload Videos (Max: 1)
                        </label>
                        <div class="file-upload-area" id="videosUploadArea">
                            <input type="file"
                                   id="videos"
                                   name="videos[]"
                                   class="file-input @error('videos') error @enderror @error('videos.*') error @enderror"
                                   accept="video/mp4"
                                   multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-video"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>MP4 only - Max 1 file, 50MB each</small>
                            </div>
                        </div>
                        <div id="videoPreviewContainer" class="file-preview-container"></div>
                        @error('videos')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('videos.*')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" name="action" value="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Announcement
                    </button>
                    @if(!$announcement->is_published)
                    <button type="submit" name="action" value="update_and_publish" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Update & Publish
                    </button>
                    @endif
                  
                    <a href="{{ route('superadmin.announcements.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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

    .form-container {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .announcement-info {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 2rem;
        border-bottom: 1px solid var(--border-color);
        background: #f8fafc;
    }

    .info-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .info-content h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
        line-height: 1.3;
    }

    .info-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-secondary);
        flex-wrap: wrap;
    }

    .separator {
        color: var(--border-color);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
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

    .department-badge {
        background: #e0e7ff;
        color: #3730a3;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
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

    .current-image {
        margin-bottom: 1.5rem;
    }

    .image-preview {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .current-image-display {
        max-width: 300px;
        max-height: 200px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
    }

    .image-actions {
        display: flex;
        align-items: center;
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

    .form-actions {
        display: flex;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        justify-content: flex-start;
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

    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .header-actions {
            width: 100%;
            justify-content: center;
        }

        .announcement-info {
            flex-direction: column;
            text-align: center;
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

        .info-meta {
            justify-content: center;
        }
    }
    
    /* Multiple Media Upload Styles */
    .current-media-section {
        margin-bottom: 2rem;
    }
    
    .current-media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .current-media-item {
        position: relative;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        background: #f8fafc;
    }
    
    .current-image-display {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }
    
    .current-video-display {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }

    /* Enhanced Video Styling */
    .video-media-item {
        background: var(--bg-secondary);
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .video-media-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .video-container {
        position: relative;
        width: 100%;
        height: 180px;
        overflow: hidden;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }

    .current-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #000;
    }

    .video-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: rgba(255, 255, 255, 0.9);
        font-size: 2rem;
        pointer-events: none;
        opacity: 0.8;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        transition: opacity 0.3s ease;
    }

    .video-container:hover .video-overlay {
        opacity: 1;
    }

    .video-info {
        padding: 0.75rem;
        background: var(--bg-primary);
        border-top: 1px solid var(--border-color);
    }

    .video-filename {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-secondary);
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .remove-media-checkbox {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 180px;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 10;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }

    .video-media-item:hover .remove-media-checkbox {
        opacity: 1;
    }

    .remove-media-checkbox input[type="checkbox"] {
        display: none;
    }

    .remove-media-checkbox label {
        color: white;
        cursor: pointer;
        padding: 0.5rem 1rem;
        background: rgba(239, 68, 68, 0.8);
        border-radius: var(--radius-md);
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.2s ease;
    }

    .remove-media-checkbox label:hover {
        background: #dc2626;
    }

    .remove-media-checkbox input[type="checkbox"]:checked + label {
        background: #10b981;
    }
    
    .media-actions {
        padding: 0.5rem;
        background: white;
        border-top: 1px solid var(--border-color);
    }
    
    .file-preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .file-preview-item {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        background: white;
    }
    
    .preview-image {
        position: relative;
        height: 150px;
        overflow: hidden;
    }
    
    .preview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .preview-video {
        position: relative;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: var(--primary-color);
    }
    
    .preview-video i {
        font-size: 3rem;
    }
    
    .remove-file-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 24px;
        height: 24px;
        border: none;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }
    
    .remove-file-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    /* Video Preview Styles for Upload Section */
    .video-preview-item {
        background: var(--bg-secondary);
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .video-preview-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .video-preview-container {
        position: relative;
        width: 100%;
        height: 120px;
        overflow: hidden;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }

    .file-preview-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #000;
    }

    .video-preview-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: rgba(255, 255, 255, 0.9);
        font-size: 2rem;
        pointer-events: none;
        opacity: 0.8;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        transition: opacity 0.3s ease;
    }

    .video-preview-container:hover .video-preview-overlay {
        opacity: 1;
    }

    .file-preview-info {
        padding: 0.75rem;
        background: var(--bg-primary);
        border-top: 1px solid var(--border-color);
    }

    .file-preview-name {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-primary);
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-preview-size {
        font-size: 0.7rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }

    .file-preview-remove {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ef4444;
        color: white;
        border: 2px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
        z-index: 10;
    }

    .file-preview-remove:hover {
        background: #dc2626;
        transform: scale(1.1);
    }
    
    .file-info {
        padding: 0.75rem;
        border-top: 1px solid var(--border-color);
    }
    
    .file-name {
        margin: 0 0 0.25rem 0;
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--text-primary);
        word-break: break-word;
    }
    
    .file-size {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }
</style>

<script>
    // Track removed media for replacement logic
    let removedImageIndexes = [];
    let removedVideoIndexes = [];
    let removedSingleImage = false;
    let removedSingleVideo = false;
    
    // Debug: Log initial state
    console.log('Media removal tracking initialized');
    
    // Multiple Images Upload Preview
    let selectedImages = [];
    const maxImages = 2;
    
    // Track removed media checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.name === 'remove_images[]') {
            const index = parseInt(e.target.value);
            console.log('Image removal checkbox changed:', index, 'checked:', e.target.checked);
            if (e.target.checked) {
                if (!removedImageIndexes.includes(index)) {
                    removedImageIndexes.push(index);
                    console.log('Added image index to removal list:', index);
                }
            } else {
                removedImageIndexes = removedImageIndexes.filter(i => i !== index);
                console.log('Removed image index from removal list:', index);
            }
            console.log('Current removed image indexes:', removedImageIndexes);
            updateMediaLimits();
        }
        
        if (e.target.name === 'remove_videos[]') {
            const index = parseInt(e.target.value);
            console.log('Video removal checkbox changed:', index, 'checked:', e.target.checked);
            if (e.target.checked) {
                if (!removedVideoIndexes.includes(index)) {
                    removedVideoIndexes.push(index);
                    console.log('Added video index to removal list:', index);
                }
            } else {
                removedVideoIndexes = removedVideoIndexes.filter(i => i !== index);
                console.log('Removed video index from removal list:', index);
            }
            console.log('Current removed video indexes:', removedVideoIndexes);
            updateMediaLimits();
        }
        
        if (e.target.name === 'remove_image') {
            removedSingleImage = e.target.checked;
            updateMediaLimits();
        }
        
        if (e.target.name === 'remove_video') {
            removedSingleVideo = e.target.checked;
            updateMediaLimits();
        }
    });
    
    function updateMediaLimits() {
        // Calculate available slots for new media
        const currentImageCount = {{ count($announcement->allImagePaths) }};
        const currentVideoCount = {{ count($announcement->allVideoPaths) }};
        
        const removedImagesCount = removedImageIndexes.length + (removedSingleImage ? 1 : 0);
        const removedVideosCount = removedVideoIndexes.length + (removedSingleVideo ? 1 : 0);
        
        const availableImageSlots = Math.min(maxImages, currentImageCount - removedImagesCount + maxImages);
        const availableVideoSlots = Math.min(1, currentVideoCount - removedVideosCount + 1);
        
        // Update upload area labels
        const imageLabel = document.querySelector('label[for="images"]');
        const videoLabel = document.querySelector('label[for="videos"]');
        
        if (imageLabel) {
            imageLabel.innerHTML = `<i class="fas fa-camera"></i> Upload Images (Max: ${availableImageSlots} available)`;
        }
        
        if (videoLabel) {
            videoLabel.innerHTML = `<i class="fas fa-video"></i> Upload Videos (Max: ${availableVideoSlots} available)`;
        }
    }
    
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const container = document.getElementById('imagePreviewContainer');
        
        // Calculate available slots
        const currentImageCount = {{ count($announcement->allImagePaths) }};
        const removedImagesCount = removedImageIndexes.length + (removedSingleImage ? 1 : 0);
        const availableSlots = Math.min(maxImages, currentImageCount - removedImagesCount + maxImages);
        
        // Validate file count against available slots
        if (files.length > availableSlots) {
            Swal.fire({
                icon: 'error',
                title: 'Upload Limit Exceeded',
                text: `Maximum ${availableSlots} images allowed. You have ${removedImagesCount} removed images that can be replaced.`,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            e.target.value = '';
            return;
        }
        
        // Clear previous previews
        container.innerHTML = '';
        selectedImages = [];
        
        files.forEach((file, index) => {
            // Validate file type
            if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: `File ${file.name} is not a valid image format. Only JPG and PNG are allowed.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: `File ${file.name} is too large. Maximum size is 5MB.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
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
    
    function removeImagePreview(index) {
        const container = document.getElementById('imagePreviewContainer');
        const input = document.getElementById('images');
        
        // Remove from selected files array
        selectedImages.splice(index, 1);
        
        // Update file input
        const dt = new DataTransfer();
        selectedImages.forEach(file => dt.items.add(file));
        input.files = dt.files;
        
        // Refresh preview
        container.innerHTML = '';
        selectedImages.forEach((file, newIndex) => {
            const reader = new FileReader();
            reader.onload = function(readerEvent) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'file-preview-item';
                previewDiv.innerHTML = `
                    <div class="preview-image">
                        <img src="${readerEvent.target.result}" alt="Preview ${newIndex + 1}">
                        <button type="button" class="remove-file-btn" onclick="removeImagePreview(${newIndex})">
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
    }
    
    // Multiple Videos Upload Preview
    let selectedVideos = [];
    const maxVideos = 1;
    
    document.getElementById('videos').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const container = document.getElementById('videoPreviewContainer');
        
        // Calculate available slots
        const currentVideoCount = {{ count($announcement->allVideoPaths) }};
        const removedVideosCount = removedVideoIndexes.length + (removedSingleVideo ? 1 : 0);
        const availableSlots = Math.min(maxVideos, currentVideoCount - removedVideosCount + maxVideos);
        
        // Validate file count against available slots
        if (files.length > availableSlots) {
            Swal.fire({
                icon: 'error',
                title: 'Upload Limit Exceeded',
                text: `Maximum ${availableSlots} videos allowed. You have ${removedVideosCount} removed videos that can be replaced.`,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            e.target.value = '';
            return;
        }
        
        // Clear previous previews
        container.innerHTML = '';
        selectedVideos = [];
        
        files.forEach((file, index) => {
            // Validate file type
            if (file.type !== 'video/mp4') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: `File ${file.name} is not a valid video format. Only MP4 is allowed.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Validate file size (50MB)
            if (file.size > 50 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: `File ${file.name} is too large. Maximum size is 50MB.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            selectedVideos.push(file);
            
            // Create preview with actual video thumbnail
            const previewDiv = document.createElement('div');
            previewDiv.className = 'file-preview-item video-preview-item';
            
            // Create video container
            const videoContainer = document.createElement('div');
            videoContainer.className = 'video-preview-container';
            
            // Create video element for thumbnail
            const videoElement = document.createElement('video');
            videoElement.className = 'file-preview-video';
            videoElement.controls = true;
            videoElement.preload = 'metadata';
            
            // Create object URL for the video file
            const videoURL = URL.createObjectURL(file);
            videoElement.src = videoURL;
            
            // Add play overlay
            const playOverlay = document.createElement('div');
            playOverlay.className = 'video-preview-overlay';
            playOverlay.innerHTML = '<i class="fas fa-play-circle"></i>';
            
            // Add remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'file-preview-remove';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function() {
                // Revoke object URL to free memory
                URL.revokeObjectURL(videoURL);
                removeVideoPreview(index);
            };
            
            videoContainer.appendChild(videoElement);
            videoContainer.appendChild(playOverlay);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-preview-info';
            fileInfo.innerHTML = `
                <span class="file-preview-name">${file.name}</span>
                <span class="file-preview-size">${(file.size / 1024 / 1024).toFixed(1)} MB</span>
            `;
            
            previewDiv.appendChild(videoContainer);
            previewDiv.appendChild(fileInfo);
            previewDiv.appendChild(removeBtn);
            container.appendChild(previewDiv);
        });
    });
    
    function removeVideoPreview(index) {
        const container = document.getElementById('videoPreviewContainer');
        const input = document.getElementById('videos');
        
        // Remove from selected files array
        selectedVideos.splice(index, 1);
        
        // Update file input
        const dt = new DataTransfer();
        selectedVideos.forEach(file => dt.items.add(file));
        input.files = dt.files;
        
        // Refresh preview with enhanced video thumbnails
        container.innerHTML = '';
        selectedVideos.forEach((file, newIndex) => {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'file-preview-item video-preview-item';
            
            // Create video container
            const videoContainer = document.createElement('div');
            videoContainer.className = 'video-preview-container';
            
            // Create video element for thumbnail
            const videoElement = document.createElement('video');
            videoElement.className = 'file-preview-video';
            videoElement.controls = true;
            videoElement.preload = 'metadata';
            
            // Create object URL for the video file
            const videoURL = URL.createObjectURL(file);
            videoElement.src = videoURL;
            
            // Add play overlay
            const playOverlay = document.createElement('div');
            playOverlay.className = 'video-preview-overlay';
            playOverlay.innerHTML = '<i class="fas fa-play-circle"></i>';
            
            // Add remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'file-preview-remove';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function() {
                // Revoke object URL to free memory
                URL.revokeObjectURL(videoURL);
                removeVideoPreview(newIndex);
            };
            
            videoContainer.appendChild(videoElement);
            videoContainer.appendChild(playOverlay);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-preview-info';
            fileInfo.innerHTML = `
                <span class="file-preview-name">${file.name}</span>
                <span class="file-preview-size">${(file.size / 1024 / 1024).toFixed(1)} MB</span>
            `;
            
            previewDiv.appendChild(videoContainer);
            previewDiv.appendChild(fileInfo);
            previewDiv.appendChild(removeBtn);
            container.appendChild(previewDiv);
        });
    }


    // Form validation with media replacement logic
    document.querySelector('.announcement-form').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();

        if (!title || !content) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Missing Required Fields',
                text: 'Please fill in all required fields.',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        // Add hidden inputs for removed media tracking before submission
        if (removedImageIndexes.length > 0) {
            // Remove existing hidden inputs if any
            const existingInputs = this.querySelectorAll('input[name="remove_images[]"]');
            existingInputs.forEach(input => input.remove());
            
            // Create individual hidden inputs for each removed image index
            removedImageIndexes.forEach(index => {
                const removedImageInput = document.createElement('input');
                removedImageInput.type = 'hidden';
                removedImageInput.name = 'remove_images[]';
                removedImageInput.value = index;
                this.appendChild(removedImageInput);
            });
            
            // Debug log
            console.log('Sending removed image indexes:', removedImageIndexes);
        }
        
        if (removedVideoIndexes.length > 0) {
            // Remove existing hidden inputs if any
            const existingInputs = this.querySelectorAll('input[name="remove_videos[]"]');
            existingInputs.forEach(input => input.remove());
            
            // Create individual hidden inputs for each removed video index
            removedVideoIndexes.forEach(index => {
                const removedVideoInput = document.createElement('input');
                removedVideoInput.type = 'hidden';
                removedVideoInput.name = 'remove_videos[]';
                removedVideoInput.value = index;
                this.appendChild(removedVideoInput);
            });
            
            // Debug log
            console.log('Sending removed video indexes:', removedVideoIndexes);
        }
        
        // Handle single media removal
        if (removedSingleImage) {
            const existingInput = this.querySelector('input[name="remove_legacy_image"]');
            if (existingInput) existingInput.remove();
            
            const removedSingleImageInput = document.createElement('input');
            removedSingleImageInput.type = 'hidden';
            removedSingleImageInput.name = 'remove_legacy_image';
            removedSingleImageInput.value = '1';
            this.appendChild(removedSingleImageInput);
        }
        
        if (removedSingleVideo) {
            const existingInput = this.querySelector('input[name="remove_legacy_video"]');
            if (existingInput) existingInput.remove();
            
            const removedSingleVideoInput = document.createElement('input');
            removedSingleVideoInput.type = 'hidden';
            removedSingleVideoInput.name = 'remove_legacy_video';
            removedSingleVideoInput.value = '1';
            this.appendChild(removedSingleVideoInput);
        }
        
        // Validate media limits considering replacements
        const currentImageCount = {{ count($announcement->allImagePaths) }};
        const currentVideoCount = {{ count($announcement->allVideoPaths) }};
        
        const removedImagesCount = removedImageIndexes.length + (removedSingleImage ? 1 : 0);
        const removedVideosCount = removedVideoIndexes.length + (removedSingleVideo ? 1 : 0);
        
        const finalImageCount = (currentImageCount - removedImagesCount) + selectedImages.length;
        const finalVideoCount = (currentVideoCount - removedVideosCount) + selectedVideos.length;
        
        if (finalImageCount > maxImages) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Image Limit Exceeded',
                text: `Total images cannot exceed ${maxImages}. Current: ${currentImageCount}, Removing: ${removedImagesCount}, Adding: ${selectedImages.length}, Final: ${finalImageCount}`,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        if (finalVideoCount > maxVideos) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Video Limit Exceeded',
                text: `Total videos cannot exceed ${maxVideos}. Current: ${currentVideoCount}, Removing: ${removedVideosCount}, Adding: ${selectedVideos.length}, Final: ${finalVideoCount}`,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            return false;
        }
    });
    
    // Initialize media limits on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateMediaLimits();
    });

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

    // Toggle target options based on visibility scope
    function toggleTargetOptions() {
        const visibilityScope = document.querySelector('input[name="visibility_scope"]:checked').value;
        const departmentGroup = document.getElementById('target-department-group');
        const officeGroup = document.getElementById('target-office-group');

        // Hide all target groups first
        departmentGroup.style.display = 'none';
        officeGroup.style.display = 'none';

        // Show appropriate target group
        if (visibilityScope === 'department') {
            departmentGroup.style.display = 'block';
        } else if (visibilityScope === 'office') {
            officeGroup.style.display = 'block';
        }
    }

    // Initialize target options on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleTargetOptions();
    });
</script>
@endsection
