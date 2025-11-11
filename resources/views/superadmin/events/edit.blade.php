@extends('layouts.app')

@section('title', 'Edit Event - Super Admin')

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
                <i class="fas fa-users-cog"></i> Admin Management
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
            <li>

            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div>
                <h1><i class="fas fa-edit"></i> Edit Event</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Update event information</p>
            </div>
            <div class="header-actions">
               
                <a href="{{ route('superadmin.events.index') }}" class="btn btn-green btn-enhanced">
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
            <!-- Event Info Header -->
            <div class="event-info">
                <div class="info-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="info-content">
                    <h2>{{ $event->title }}</h2>
                    <div class="info-meta">
                        <span>Created {{ $event->created_at->format('M d, Y') }}</span>
                        <span class="separator">•</span>
                        @php
                            $eventStatus = $event->getEventStatus();
                            $status = $eventStatus['status'];
                            $statusText = $eventStatus['text'] . ' Event';
                            $statusIcon = $eventStatus['icon'];
                        @endphp
                        <span class="status-badge {{ $status }}">
                            <i class="fas fa-{{ $statusIcon }}"></i>
                            {{ $statusText }}
                        </span>
                        <span class="separator">•</span>
                        <span>By {{ $event->admin->username }}</span>
                        @if($event->admin->department)
                        <span class="separator">•</span>
                        <span class="department-badge">{{ $event->admin->department }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('superadmin.events.update', $event) }}" enctype="multipart/form-data" class="event-form">
                @csrf
                @method('PUT')
                
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Event Information</h3>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i> Event Title *
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               class="form-input @error('title') error @enderror" 
                               value="{{ old('title', $event->title) }}" 
                               placeholder="Enter event title..."
                               >
                        @error('title')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left"></i> Description *
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea @error('description') error @enderror" 
                                  rows="6" 
                                  placeholder="Describe the event details..."
                                  required>{{ old('description', $event->description) }}</textarea>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-calendar-alt"></i> Date & Location</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date" class="form-label">
                                <i class="fas fa-calendar"></i> Event Date & Time{{ Auth::guard('admin')->user()->isSuperAdmin() ? ' (Optional)' : ' *' }}
                            </label>
                            <input type="datetime-local" 
                                   id="event_date" 
                                   name="event_date" 
                                   class="form-input @error('event_date') error @enderror" 
                                   value="{{ old('event_date', $event->event_date ? $event->event_date->format('Y-m-d\TH:i') : '') }}"
                                   {{ Auth::guard('admin')->user()->isSuperAdmin() ? '' : 'required' }}>
                            @error('event_date')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="location" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Location
                            </label>
                            <input type="text" 
                                   id="location" 
                                   name="location" 
                                   class="form-input @error('location') error @enderror" 
                                   value="{{ old('location', $event->location) }}" 
                                   placeholder="Event location...">
                            @error('location')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Media Files</h3>
                    
                    <!-- Current Single Image -->
                    @if($event->image)
                    <div class="current-media-section">
                        <label class="form-label">Current Image</label>
                        <div class="current-media-grid">
                            <div class="current-media-item">
                                <img src="{{ asset('storage/' . $event->image) }}" alt="Current image" class="current-image-display">
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
                    @if($event->image_paths && is_array($event->image_paths) && !empty($event->image_paths))
                    <div class="current-media-section">
                        <label class="form-label">Current Images ({{ is_array($event->image_paths) ? count($event->image_paths) : 0 }})</label>
                        <div class="current-media-grid">
                            @foreach($event->image_paths as $index => $imagePath)
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
                    @if($event->video)
                    <div class="current-media-section">
                        <label class="form-label">Current Video</label>
                        <div class="current-media-grid">
                            <div class="current-media-item video-media-item">
                                <div class="video-container">
                                    <video controls preload="metadata" class="current-video">
                                        <source src="{{ asset('storage/' . $event->video) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="video-overlay">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                </div>
                                <div class="video-info">
                                    <span class="video-filename">{{ basename($event->video) }}</span>
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
                    @if($event->video_paths && is_array($event->video_paths) && !empty($event->video_paths))
                    <div class="current-media-section">
                        <label class="form-label">Current Videos ({{ is_array($event->video_paths) ? count($event->video_paths) : 0 }})</label>
                        <div class="current-media-grid">
                            @foreach($event->video_paths as $index => $videoPath)
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
                                <small>PNG, JPG only - Max 2 files, 2MB each</small>
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
                                       {{ old('is_published', $event->is_published) ? 'checked' : '' }}
                                       class="checkbox-input">
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">
                                    <i class="fas fa-eye"></i> Publish Event
                                </span>
                            </label>
                            <small class="form-help">Check to make this event visible to students and faculty</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-green btn-enhanced btn-loading" data-loading-text="Updating...">
                        <span class="btn-content">
                            <i class="fas fa-save"></i> Update Event
                        </span>
                        <span class="btn-loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Updating...
                        </span>
                    </button>
                    
                    <a href="{{ route('superadmin.events.index') }}" class="btn btn-red btn-enhanced">
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
        color: #10b981;
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

    .event-info {
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
        background: linear-gradient(135deg, #10b981, #059669);
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

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    .department-badge {
        background: #e0e7ff;
        color: #3730a3;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .event-form {
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
        color: #10b981;
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
        color: #10b981;
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
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
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

    .current-image {
        margin-bottom: 1.5rem;
    }

    .image-preview {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .current-image-display {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
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

    .video-actions {
        display: flex;
        align-items: center;
    }

    .current-csv {
        margin-bottom: 1.5rem;
    }

    .csv-preview {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .csv-file-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: #f8fafc;
        text-align: center;
    }

    .csv-actions {
        display: flex;
        align-items: center;
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
        background: #10b981;
        border-color: #10b981;
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
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
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
        color: #10b981;
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

    /* Checkbox Styling */
    .checkbox-group {
        margin: 1rem 0;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: var(--radius-lg);
        transition: all 0.3s ease;
        background: #f9fafb;
    }

    .checkbox-label:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .checkbox-input {
        display: none;
    }

    .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        position: relative;
        transition: all 0.3s ease;
        background: white;
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
        font-size: 12px;
        font-weight: bold;
    }

    .checkbox-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: #374151;
    }

    .checkbox-text i {
        color: #3b82f6;
    }

    .form-help {
        display: block;
        margin-top: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
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
        padding: 0.75rem;
        background: white;
        text-align: center;
    }
    
    .media-actions .checkbox-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        color: var(--text-primary);
        margin: 0;
        padding: 0;
        font-size: 0.875rem;
    }
    
    .media-actions .checkbox-custom {
        width: 16px;
        height: 16px;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-sm);
        position: relative;
        transition: all 0.3s ease;
    }
    
    .media-actions .checkbox-input:checked + .checkbox-custom {
        background: #10b981;
        border-color: #10b981;
    }
    
    .media-actions .checkbox-input:checked + .checkbox-custom::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.65rem;
        font-weight: bold;
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

    /* Radio Group Styling */
    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin: 1rem 0;
    }

    .radio-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: var(--radius-lg);
        transition: all 0.3s ease;
        background: #f9fafb;
    }

    .radio-label:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .radio-input {
        display: none;
    }

    .radio-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        position: relative;
        transition: all 0.3s ease;
        background: white;
    }

    .radio-input:checked + .radio-custom {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    .radio-input:checked + .radio-custom::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
    }

    .radio-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: #374151;
    }

    .radio-text i {
        color: #3b82f6;
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

        .event-info {
            flex-direction: column;
            text-align: center;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .event-form {
            padding: 1.5rem;
        }

        .info-meta {
            justify-content: center;
        }
    }

    /* Enhanced Button Styles - Matching Index Page */
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

    /* Enhanced Button Classes */
    .btn-enhanced {
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        border: 2px solid transparent;
    }

    .btn-enhanced:hover {
        transform: translateY(-2px);
    }

    .btn-enhanced:active {
        transform: translateY(0);
    }

    /* Green Button (Back to List) */
    .btn-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-green:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4), 0 10px 10px -5px rgba(16, 185, 129, 0.04);
    }

    .btn-green:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
    }

    /* Blue Button (Update Event) */
    .btn-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-blue:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
    }

    .btn-blue:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }

    /* Red Button (Cancel) */
    .btn-red {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: 2px solid transparent;
    }

    .btn-red:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4);
    }

    .btn-red:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3);
    }

    /* Loading Button States */
    .btn-loading {
        position: relative;
    }

    .btn-loading.loading .btn-content {
        opacity: 0;
    }

    .btn-loading.loading .btn-loading-spinner {
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

    .btn-loading.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-loading.loading::after {
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

    /* Form Action Buttons */
    .form-actions .btn {
        min-width: 140px;
        padding: 0.875rem 1.75rem;
        font-size: 0.9rem;
        border-radius: 12px;
        font-weight: 600;
        letter-spacing: 0.025em;
    }

    .form-actions .btn:hover {
        transform: translateY(-2px) scale(1.02);
    }

    /* Ripple Effect */
    .btn-enhanced::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
        z-index: 0;
    }

    .btn-enhanced:active::after {
        width: 300px;
        height: 300px;
    }

    .btn-enhanced > * {
        position: relative;
        z-index: 2;
    }

    /* Enhanced Form Actions Layout */
    .form-actions {
        display: flex;
        gap: 1.5rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
    }

    /* Enhanced Header Actions Layout */
    .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    /* Responsive Button Adjustments */
    @media (max-width: 768px) {
        .btn {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
        }

        .header-actions .btn {
            padding: 0.875rem 1.5rem;
            font-size: 0.875rem;
            text-transform: none;
        }

        .form-actions {
            gap: 1rem;
            flex-direction: column;
            align-items: stretch;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .header-actions {
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: center;
        }
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
        const currentImageCount = {{ count($event->allImagePaths) }};
        const currentVideoCount = {{ count($event->allVideoPaths) }};
        
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
        const currentImageCount = {{ count($event->allImagePaths) }};
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
            
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: `File ${file.name} is too large. Maximum size is 2MB.`,
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
        const currentVideoCount = {{ count($event->allVideoPaths) }};
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


    // Set minimum date and time to current date and time (only for non-superadmin)
    function setMinDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('event_date').min = currentDateTime;
    }

    // Set minimum date on page load (only for non-superadmin)
    @if(!Auth::guard('admin')->user()->isSuperAdmin())
    setMinDateTime();
    @endif

    // Add real-time validation for event date (only for non-superadmin)
    @if(!Auth::guard('admin')->user()->isSuperAdmin())
    document.getElementById('event_date').addEventListener('change', function() {
        const selectedDateTime = new Date(this.value);
        const now = new Date();

        if (selectedDateTime < now) {
            this.setCustomValidity('Event date and time must be in the future');
            this.reportValidity();

            // Show error message
            const errorElement = this.parentNode.querySelector('.error-message');
            if (!errorElement) {
                const errorSpan = document.createElement('span');
                errorSpan.className = 'error-message';
                errorSpan.textContent = 'Event date and time must be in the future';
                this.parentNode.appendChild(errorSpan);
            }
        } else {
            this.setCustomValidity('');

            // Remove error message
            const errorElement = this.parentNode.querySelector('.error-message');
            if (errorElement && errorElement.textContent === 'Event date and time must be in the future') {
                errorElement.remove();
            }
        }
    });
    @endif

    // Form validation before submission (only for non-superadmin)
    @if(!Auth::guard('admin')->user()->isSuperAdmin())
    document.querySelector('.event-form').addEventListener('submit', function(e) {
        const eventDateInput = document.getElementById('event_date');
        const selectedDateTime = new Date(eventDateInput.value);
        const now = new Date();

        if (selectedDateTime < now) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date',
                text: 'Please select a future date and time for the event.',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            eventDateInput.focus();
            return false;
        }

        // Show loading state
        const submitBtn = this.querySelector('.btn-loading');
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
    });
    @else
    // Form validation for superadmin (allow empty date/time)
    document.querySelector('.event-form').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();

        if (!title || !description) {
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
        const currentImageCount = {{ count($event->allImagePaths) }};
        const currentVideoCount = {{ count($event->allVideoPaths) }};
        
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
        
        // Show loading state
        const submitBtn = this.querySelector('.btn-loading');
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
    });
    @endif

    // Enhanced Button Interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Add ripple effect to all enhanced buttons
        const enhancedButtons = document.querySelectorAll('.btn-enhanced');
        
        enhancedButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Create ripple element
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple-effect');
                
                // Add ripple styles
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple-animation 0.6s linear';
                ripple.style.pointerEvents = 'none';
                
                this.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    if (ripple.parentNode) {
                        ripple.parentNode.removeChild(ripple);
                    }
                }, 600);
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
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

    // Initialize media limits on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateMediaLimits();
        toggleTargetOptions();
    });
</script>
@endsection
