@extends('layouts.app')

@section('title', 'Create News Article - Department Admin')

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
                <h1><i class="fas fa-plus-circle"></i> Create News Article</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Share news and updates with the {{ auth('admin')->user()->department }} department community</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('department-admin.news.index') }}" class="btn-back">
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
            <form method="POST" action="{{ route('department-admin.news.store') }}" enctype="multipart/form-data" class="announcement-form">
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
                               placeholder="Enter news article title..."
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
                                  placeholder="Write your news article content here..."
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
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-building"></i> Department
                            </label>
                            <div class="info-value">
                                {{ Auth::guard('admin')->user()->department }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> Settings</h3>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-users"></i> Visibility
                        </label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio"
                                       name="visibility_scope"
                                       value="department"
                                       {{ old('visibility_scope', 'department') === 'department' ? 'checked' : '' }}
                                       class="radio-input">
                                <span class="radio-custom"></span>
                                <span class="radio-text">
                                    <i class="fas fa-building"></i> {{ Auth::guard('admin')->user()->department }} Department (only your students)
                                </span>
                            </label>
                            <label class="radio-label">
                                <input type="radio"
                                       name="visibility_scope"
                                       value="all"
                                       {{ old('visibility_scope') === 'all' ? 'checked' : '' }}
                                       class="radio-input">
                                <span class="radio-custom"></span>
                                <span class="radio-text">
                                    <i class="fas fa-globe"></i> All Departments (will show "Posted by {{ Auth::guard('admin')->user()->department }} Department")
                                </span>
                            </label>
                        </div>
                        <small class="form-help" style="margin-top: 0.5rem; color: #666;">
                            Select exactly one option.
                        </small>
                    </div>

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
                            <small class="form-help">If unchecked, the article will be saved as a draft</small>
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
                                <small>JPG, PNG up to 5MB each (maximum 2 images)</small>
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
                    
                    <button type="submit" name="action" value="save_and_publish" class="btn-create-news">
                        <i class="fas fa-paper-plane"></i> Create News Article
                    </button>
                    <a href="{{ route('department-admin.news.index') }}" class="btn-cancel">
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

    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .radio-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        font-weight: 500;
        color: var(--text-primary);
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        transition: all 0.3s ease;
    }

    .radio-label:hover {
        border-color: var(--primary-color);
        background: rgba(79, 70, 229, 0.05);
    }

    .radio-input {
        display: none;
    }

    .radio-custom {
        width: 20px;
        height: 20px;
        border: 2px solid var(--border-color);
        border-radius: 50%;
        position: relative;
        transition: all 0.3s ease;
    }

    .radio-input:checked + .radio-custom {
        border-color: var(--primary-color);
    }

    .radio-input:checked + .radio-custom::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background: var(--primary-color);
        border-radius: 50%;
    }

    .radio-input:checked ~ .radio-text {
        color: var(--primary-color);
    }

    .radio-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.3s ease;
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

    /* Enhanced Create News Button (Green) */
    .btn-create-news {
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        text-decoration: none;
        min-width: 180px;
        justify-content: center;
    }

    .btn-create-news:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 50%, #065f46 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .btn-create-news:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
    }

    .btn-create-news i {
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    .btn-create-news:hover i {
        transform: scale(1.1) rotate(5deg);
    }

    /* Enhanced Cancel Button (Red) */
    .btn-cancel {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        text-decoration: none;
        min-width: 140px;
        justify-content: center;
    }

    .btn-cancel:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        color: white;
    }

    .btn-cancel:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
    }

    .btn-cancel i {
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    .btn-cancel:hover i {
        transform: scale(1.1) rotate(-5deg);
    }

    /* Enhanced Back Button (Blue) */
    .btn-back {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
        color: white;
        border: none;
        padding: 0.875rem 1.5rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        text-decoration: none;
        min-width: 140px;
        justify-content: center;
    }

    .btn-back:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .btn-back:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3);
    }

    .btn-back i {
        font-size: 1rem;
        transition: transform 0.3s ease;
    }

    .btn-back:hover i {
        transform: translateX(-3px);
    }

    /* Shine Effect for All Buttons */
    .btn-create-news::before,
    .btn-cancel::before,
    .btn-back::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-create-news:hover::before,
    .btn-cancel:hover::before,
    .btn-back:hover::before {
        left: 100%;
    }

    /* Text Animation for All Buttons */
    .btn-create-news span,
    .btn-cancel span,
    .btn-back span {
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .btn-create-news:hover span,
    .btn-cancel:hover span,
    .btn-back:hover span {
        text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
    }

    /* Loading State for Create Button */
    .btn-create-news:disabled {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-create-news:disabled:hover {
        transform: none;
        box-shadow: none;
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

    .dept-info {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-top: 0.5rem;
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

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .announcement-form {
            padding: 1.5rem;
        }
    }
</style>

<script>
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

    // Mobile menu toggle function
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
    }
</script>

</style>

<script>
    // Debug logging
    console.log('Department admin news creation form loaded');

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

    // Image upload preview (using announcements pattern)
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const uploadArea = document.getElementById('imageUploadArea');
        const originalInput = e.target;

        console.log('Image file selected:', file ? file.name : 'none');

        if (file) {
            console.log('Image file details:', {
                name: file.name,
                size: file.size,
                type: file.type
            });

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
                    <small style="color: var(--text-secondary);">${(file.size / 1024).toFixed(2)} KB</small>
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

        console.log('Clearing image upload');

        // Clear the file input
        imageInput.value = '';

        // Show the input again
        imageInput.style.display = '';

        // Remove the preview
        if (preview) {
            preview.remove();
        }
    }

    // Video upload preview (using announcements pattern)
    document.getElementById('video').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const uploadArea = document.getElementById('videoUploadArea');
        const originalInput = e.target;

        console.log('Video file selected:', file ? file.name : 'none');

        if (file) {
            console.log('Video file details:', {
                name: file.name,
                size: file.size,
                type: file.type
            });

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

        console.log('Clearing video upload');

        // Clear the file input
        videoInput.value = '';

        // Show the input again
        videoInput.style.display = '';

        // Remove the preview
        if (preview) {
            preview.remove();
        }
    }

    // CSV upload preview (using announcements pattern)
    document.getElementById('csv_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const uploadArea = document.getElementById('csvUploadArea');
        const originalInput = e.target;

        console.log('CSV file selected:', file ? file.name : 'none');

        if (file) {
            console.log('CSV file details:', {
                name: file.name,
                size: file.size,
                type: file.type
            });

            // Hide the original input
            originalInput.style.display = 'none';

            // Create preview element
            const previewDiv = document.createElement('div');
            previewDiv.className = 'file-preview';
            previewDiv.innerHTML = `
                <i class="fas fa-file-csv" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <p style="margin: 1rem 0 0 0; font-weight: 500;">${file.name}</p>
                <small style="color: var(--text-secondary);">${(file.size / 1024).toFixed(2)} KB</small>
                <button type="button" onclick="clearCsvUpload()" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; background: #ef4444; color: white; border: none; border-radius: var(--radius-sm); cursor: pointer;">
                    <i class="fas fa-times"></i> Remove
                </button>
            `;

            // Add preview after the input
            uploadArea.appendChild(previewDiv);
        }
    });

    function clearCsvUpload() {
        const csvInput = document.getElementById('csv_file');
        const uploadArea = document.getElementById('csvUploadArea');
        const preview = uploadArea.querySelector('.file-preview');

        console.log('Clearing CSV upload');

        // Clear the file input
        csvInput.value = '';

        // Show the input again
        csvInput.style.display = '';

        // Remove the preview
        if (preview) {
            preview.remove();
        }
    }

    // Form submission debugging
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('Form submission started');

        const imageFile = document.getElementById('image').files[0];
        const videoFile = document.getElementById('video').files[0];
        const csvFile = document.getElementById('csv_file').files[0];

        console.log('Files being submitted:', {
            image: imageFile ? imageFile.name : 'none',
            video: videoFile ? videoFile.name : 'none',
            csv: csvFile ? csvFile.name : 'none'
        });

        if (!imageFile && !videoFile && !csvFile) {
            console.warn('⚠️ WARNING: No files selected for upload! The article will be created without media.');

            // Optional: Show a confirmation dialog
            if (!confirm('No media files selected. Do you want to create the article without any images or videos?')) {
                e.preventDefault();
                return false;
            }
        } else {
            console.log('✅ Files detected, proceeding with upload');
        }
    });

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

    // Mobile menu toggle function
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
    }



    departmentCheckbox.addEventListener('change', function() {
        if (!this.checked && !allDepartmentsCheckbox.checked) {
            // Ensure at least one is always selected
            this.checked = true;
        }
        updateVisibilityScope();
    });

    allDepartmentsCheckbox.addEventListener('change', function() {
        if (!this.checked && !departmentCheckbox.checked) {
            // Ensure at least one is always selected
            departmentCheckbox.checked = true;
        }
        updateVisibilityScope();
    });

        </div>
    </div>

    @include('department-admin.partials.scripts')

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
</style>

<script>
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
</body>
</html>
