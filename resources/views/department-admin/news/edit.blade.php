@extends('layouts.app')


@section('title', 'Edit News Article - Department Admin')


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
            <h3><i class="fas fa-building"></i> Department Admin</h3>
            <div class="dept-info">{{ auth('admin')->user()->department }} Department</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="{{ route('department-admin.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="{{ route('department-admin.announcements.index') }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('department-admin.events.index') }}">
                <i class="fas fa-calendar-alt"></i> Events
            </a></li>
            <li><a href="{{ route('department-admin.news.index') }}" class="active">
                <i class="fas fa-newspaper"></i> News
            </a></li>
            <li>
                
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div>
                <h1><i class="fas fa-edit"></i> Edit News Article</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Update article information</p>
            </div>
            <div class="header-actions">
                
                <a href="{{ route('department-admin.news.index') }}" class="btn btn-info">
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
            <!-- News Info Header -->
            <div class="news-info">
                <div class="info-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="info-content">
                    <h2>{{ $news->title }}</h2>
                    <div class="info-meta">
                        <span>Created {{ $news->created_at->format('M d, Y') }}</span>
                        <span class="separator">•</span>
                        <span class="status-badge {{ $news->is_published ? 'published' : 'draft' }}">
                            <i class="fas fa-{{ $news->is_published ? 'check' : 'eye-slash' }}"></i>
                            {{ $news->is_published ? 'Published' : 'Draft' }}
                        </span>
                        <span class="separator">•</span>
                        <span>By {{ $news->admin->username }}</span>
                        @if($news->admin->department)
                        <span class="separator">•</span>
                        <span class="department-badge">{{ $news->admin->department }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('department-admin.news.update', $news) }}" enctype="multipart/form-data" class="news-form">
                @csrf
                @method('PUT')
                
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Article Information</h3>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i> Article Title *
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               class="form-input @error('title') error @enderror" 
                               value="{{ old('title', $news->title) }}" 
                               placeholder="Enter article title..."
                               required>
                        @error('title')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label">
                            <i class="fas fa-align-left"></i> Article Content *
                        </label>
                        <textarea id="content" 
                                  name="content" 
                                  class="form-textarea @error('content') error @enderror" 
                                  rows="10" 
                                  placeholder="Write your news article content here..."
                                  required>{{ old('content', $news->content) }}</textarea>
                        @error('content')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> Settings</h3>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       name="is_published" 
                                       value="1" 
                                       {{ old('is_published', $news->is_published) ? 'checked' : '' }}
                                       class="checkbox-input">
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">
                                    <i class="fas fa-eye"></i> Published
                                </span>
                            </label>
                            <small class="form-help">Check to make this article visible to users</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Media Files</h3>
                    
                    <!-- Current Single Image -->
                    @if($news->image_path)
                    <div class="current-media-section">
                        <label class="form-label">Current Image</label>
                        <div class="current-media-grid">
                            <div class="current-media-item">
                                <img src="{{ asset('storage/' . $news->image_path) }}" alt="Current image" class="current-image-display">
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
                    @if($news->image_paths && count($news->image_paths) > 0)
                    <div class="current-media-section">
                        <label class="form-label">Current Images ({{ count($news->image_paths) }})</label>
                        <div class="current-media-grid">
                            @foreach($news->image_paths as $index => $imagePath)
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
                    @if($news->video_path)
                    <div class="current-media-section">
                        <label class="form-label">Current Video</label>
                        <div class="current-media-grid">
                            <div class="current-media-item video-media-item">
                                <div class="video-container">
                                    <video controls preload="metadata" class="current-video">
                                        <source src="{{ asset('storage/' . $news->video_path) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="video-overlay">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                </div>
                                <div class="video-info">
                                    <span class="video-filename">{{ basename($news->video_path) }}</span>
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
                    @if($news->video_paths && count($news->video_paths) > 0)
                    <div class="current-media-section">
                        <label class="form-label">Current Videos ({{ count($news->video_paths) }})</label>
                        <div class="current-media-grid">
                            @foreach($news->video_paths as $index => $videoPath)
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


                    <!-- New Video Upload -->
                    <div class="form-group">
                        <label for="videos" class="form-label">
                            <i class="fas fa-video"></i> Upload Video (Max: 1)
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
                                <small>MP4 only - Max 1 file, 50MB</small>
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
                        <i class="fas fa-save"></i> Update Article
                    </button>
                    @if(!$news->is_published)
                    <button type="submit" name="action" value="update_and_publish" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Update & Publish
                    </button>
                    @endif
                   
                    <a href="{{ route('department-admin.news.index') }}" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Sidebar Styling */
    .sidebar {
        width: 280px;
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
        text-align: center !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
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
        text-align: center !important;
        width: 100%;
        display: block;
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

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: var(--radius-md);
        cursor: pointer;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
    }

    .mobile-menu-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        .mobile-menu-btn {
            display: block !important;
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
        color: #3b82f6;
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

    .news-info {
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
        background: linear-gradient(135deg, #3b82f6, #2563eb);
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
        background: #dbeafe;
        color: #1e40af;
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

    .news-form {
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
        color: #3b82f6;
    }

    .form-group {
        margin-bottom: 1.5rem;
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
        color: #3b82f6;
        width: 16px;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 200px;
        font-family: inherit;
    }

    .form-input.error,
    .form-textarea.error {
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
        background: #3b82f6;
        border-color: #3b82f6;
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

    .current-video {
        margin-bottom: 1.5rem;
    }

    .video-preview {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .current-video-display {
        max-width: 300px;
        max-height: 200px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
    }

    .video-actions {
        display: flex;
        align-items: center;
    }

    .file-preview-item {
        margin-bottom: 0.75rem;
        padding: 1rem;
        background: rgba(248, 250, 252, 0.5);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
    }

    .preview-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .preview-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .file-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .file-size {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }

    .remove-file {
        background: #ef4444;
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .remove-file:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .file-previews {
        margin-top: 1rem;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
    }

    .btn-outline {
        background: transparent;
        color: #10b981;
        border: 1px solid #10b981;
    }

    .btn-outline:hover {
        background: #10b981;
        color: white;
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
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.05);
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
        color: #3b82f6;
        margin-bottom: 1rem;
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
        background: white;
        transition: all 0.2s ease;
    }

    .current-media-item:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .current-image-display, .current-video-display {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    
    .media-actions {
        padding: 0.5rem;
        background: white;
        border-top: 1px solid var(--border-color);
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
        height: 180px; /* Match video container height */
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

    .file-upload-content p {
        margin: 0 0 0.5rem 0;
        font-weight: 500;
        color: var(--text-primary);
    }

    .file-upload-content small {
        color: var(--text-secondary);
    }

    .file-preview {
        text-align: center;
        padding: 1.5rem;
        background: rgba(16, 185, 129, 0.05);
        border: 1px solid rgba(16, 185, 129, 0.1);
        border-radius: var(--radius-md);
        margin-top: 1rem;
    }

    .file-upload-area.dragover {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
        transform: scale(1.01);
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

    .form-actions {
        display: flex;
        gap: 1rem;
        padding: 2rem;
        border-top: 1px solid var(--border-color);
        background: rgba(248, 250, 252, 0.3);
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        text-align: center;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #16a34a, #15803d);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
    }

    .btn-info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .btn-secondary {
        background: white;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .btn-info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
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

        .news-info {
            flex-direction: column;
            text-align: center;
        }

        .form-actions {
            flex-direction: column;
        }

        .news-form {
            padding: 1.5rem;
        }

        .info-meta {
            justify-content: center;
        }
    }
</style>

<script>
    // Multiple Images Upload Preview
    let selectedImages = [];
    const maxImages = 2;
    
    // Track removed media checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.type === 'checkbox' && (e.target.name === 'remove_images[]' || e.target.name === 'remove_videos[]' || e.target.name === 'remove_image' || e.target.name === 'remove_video')) {
            updateAvailableSlots();
        }
    });
    
    function updateAvailableSlots() {
        // Get all checked removal checkboxes
        const removedImageIndexes = Array.from(document.querySelectorAll('input[name="remove_images[]"]:checked')).map(cb => cb.value);
        const removedVideoIndexes = Array.from(document.querySelectorAll('input[name="remove_videos[]"]:checked')).map(cb => cb.value);
        const removedSingleImage = document.querySelector('input[name="remove_image"]:checked') !== null;
        const removedSingleVideo = document.querySelector('input[name="remove_video"]:checked') !== null;
        
        const currentImageCount = {{ count($news->allImagePaths) }};
        const currentVideoCount = {{ count($news->allVideoPaths) }};
        
        const removedImagesCount = removedImageIndexes.length + (removedSingleImage ? 1 : 0);
        const removedVideosCount = removedVideoIndexes.length + (removedSingleVideo ? 1 : 0);
        
        const availableImageSlots = Math.min(maxImages, currentImageCount - removedImagesCount + maxImages);
        const availableVideoSlots = Math.min(maxVideos, currentVideoCount - removedVideosCount + maxVideos);
        
        // Update upload area labels
        const imageLabel = document.querySelector('label[for="images"]');
        const videoLabel = document.querySelector('label[for="videos"]');
        
        if (imageLabel) {
            imageLabel.innerHTML = `<i class="fas fa-camera"></i> Upload Images (Max: ${availableImageSlots} available)`;
        }
        
        if (videoLabel) {
            videoLabel.innerHTML = `<i class="fas fa-video"></i> Upload Video (Max: ${availableVideoSlots} available)`;
        }
    }
    
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const container = document.getElementById('imagePreviewContainer');
        
        // Calculate available slots
        const currentImageCount = {{ count($news->allImagePaths) }};
        const removedImageIndexes = Array.from(document.querySelectorAll('input[name="remove_images[]"]:checked')).map(cb => cb.value);
        const removedSingleImage = document.querySelector('input[name="remove_image"]:checked') !== null;
        const removedImagesCount = removedImageIndexes.length + (removedSingleImage ? 1 : 0);
        const availableSlots = Math.min(maxImages, currentImageCount - removedImagesCount + maxImages);
        
        // Validate file count against available slots
        if (files.length > availableSlots) {
            Swal.fire({
                icon: 'error',
                title: 'Upload Limit Exceeded',
                text: `You can only upload ${availableSlots} more images. You selected ${files.length} files.`,
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
                }).then(() => {
                    e.target.value = '';
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
                }).then(() => {
                    e.target.value = '';
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
        const currentVideoCount = {{ count($news->allVideoPaths) }};
        const removedVideoIndexes = Array.from(document.querySelectorAll('input[name="remove_videos[]"]:checked')).map(cb => cb.value);
        const removedSingleVideo = document.querySelector('input[name="remove_video"]:checked') !== null;
        const removedVideosCount = removedVideoIndexes.length + (removedSingleVideo ? 1 : 0);
        const availableSlots = Math.min(maxVideos, currentVideoCount - removedVideosCount + maxVideos);
        
        // Validate file count against available slots
        if (files.length > availableSlots) {
            Swal.fire({
                icon: 'error',
                title: 'Upload Limit Exceeded',
                text: `You can only upload ${availableSlots} more videos. You selected ${files.length} files.`,
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
    
    // Form submission validation
    document.querySelector('.news-form').addEventListener('submit', function(e) {
        const currentImageCount = {{ count($news->allImagePaths) }};
        const currentVideoCount = {{ count($news->allVideoPaths) }};
        
        const removedImageIndexes = Array.from(document.querySelectorAll('input[name="remove_images[]"]:checked')).map(cb => cb.value);
        const removedVideoIndexes = Array.from(document.querySelectorAll('input[name="remove_videos[]"]:checked')).map(cb => cb.value);
        const removedSingleImage = document.querySelector('input[name="remove_image"]:checked') !== null;
        const removedSingleVideo = document.querySelector('input[name="remove_video"]:checked') !== null;
        
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
                confirmButtonText: 'OK',
                confirmButtonColor: '#ef4444'
            });
            return false;
        }
    });
    
    // Initialize available slots on page load
    updateAvailableSlots();

    // Mobile sidebar toggle
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
