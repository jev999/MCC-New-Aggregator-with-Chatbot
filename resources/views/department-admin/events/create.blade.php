@extends('layouts.app')

@section('title', 'Create Event - Department Admin')

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
            <h3><i class="fas fa-user-shield"></i> Department Admin</h3>
            <div class="dept-info">{{ auth('admin')->user()->department }} Department</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="{{ route('department-admin.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="{{ route('department-admin.announcements.index') }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('department-admin.events.index') }}" class="active">
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
        <div class="header">
            <div>
                <h1><i class="fas fa-calendar-plus"></i> Create New Event</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Schedule a new department event</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('department-admin.events.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Events
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
            <form method="POST" action="{{ route('department-admin.events.store') }}" enctype="multipart/form-data" class="event-form">
                @csrf
                
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
                               value="{{ old('title') }}" 
                               placeholder="Enter event title..."
                               required>
                        @error('title')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left"></i> Event Description *
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea @error('description') error @enderror" 
                                  rows="6" 
                                  placeholder="Describe your event..."
                                  required>{{ old('description') }}</textarea>
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
                                <i class="fas fa-calendar"></i> Event Date & Time *
                            </label>
                            <input type="datetime-local"
                                   id="event_date"
                                   name="event_date"
                                   class="form-input @error('event_date') error @enderror"
                                   value="{{ old('event_date') }}"
                                   required>
                            @error('event_date')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="location" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Location *
                            </label>
                            <input type="text"
                                   id="location"
                                   name="location"
                                   class="form-input @error('location') error @enderror"
                                   value="{{ old('location') }}"
                                   placeholder="Event location..."
                                   required>
                            @error('location')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Event Images (Optional)</h3>
                    <p class="section-description">Upload up to 2 images for your event</p>

                    <div class="form-group">
                        <label for="images" class="form-label">
                            <i class="fas fa-camera"></i> Event Images (Max: 2)
                        </label>
                        <div class="file-upload-area" id="imagesUploadArea">
                            <input type="file"
                                   id="images"
                                   name="images[]"
                                   class="file-input @error('images') error @enderror"
                                   accept="image/jpeg,image/png,image/jpg"
                                   multiple
                                   data-max-files="2">
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>JPG, PNG only - Max 2 images, 5MB each</small>
                            </div>
                        </div>
                        <div id="imagesPreview" class="images-preview"></div>
                        @error('images')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('images.*')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-video"></i> Event Videos (Optional)</h3>
                    <p class="section-description">Upload up to 1 video for your event</p>

                    <div class="form-group">
                        <label for="videos" class="form-label">
                            <i class="fas fa-video"></i> Event Video (Max: 1)
                        </label>
                        <div class="file-upload-area" id="videosUploadArea">
                            <input type="file"
                                   id="videos"
                                   name="videos[]"
                                   class="file-input @error('videos') error @enderror"
                                   accept="video/mp4"
                                   multiple
                                   data-max-files="1">
                            <div class="file-upload-content">
                                <i class="fas fa-video"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>MP4 only - Max 1 video, 50MB each</small>
                            </div>
                        </div>
                        <div id="videosPreview" class="videos-preview"></div>
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
                                    <i class="fas fa-eye"></i> Publish Event
                                </span>
                            </label>
                            <small class="form-help">Check to make this event visible to students and faculty immediately</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-create-event">
                        <i class="fas fa-calendar-plus"></i> 
                        <span>Create Event</span>
                        <div class="btn-shine"></div>
                    </button>
                    <a href="{{ route('department-admin.events.index') }}" class="btn btn-cancel">
                        <i class="fas fa-times-circle"></i> 
                        <span>Cancel</span>
                        <div class="btn-shine"></div>
                    </a>
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

    .form-input:invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .error-message {
        display: block;
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.5rem;
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
        gap: 1.5rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    /* Enhanced Create Event Button (Green) */
    .btn-create-event {
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
        min-width: 160px;
        justify-content: center;
    }

    .btn-create-event:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 50%, #065f46 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .btn-create-event:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
    }

    .btn-create-event i {
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    .btn-create-event:hover i {
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

    /* Shine Effect for Both Buttons */
    .btn-shine {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s ease;
    }

    .btn-create-event:hover .btn-shine,
    .btn-cancel:hover .btn-shine {
        left: 100%;
    }

    /* Button Text Animation */
    .btn-create-event span,
    .btn-cancel span {
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .btn-create-event:hover span {
        text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
    }

    .btn-cancel:hover span {
        text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
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

    /* Shine Effect for Back Button */
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

    .btn-back:hover::before {
        left: 100%;
    }

    /* Loading State for Create Button */
    .btn-create-event:disabled {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-create-event:disabled:hover {
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

    /* Radio Button Styling */
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
        border-color: #3b82f6;
    }

    .radio-input:checked + .radio-custom::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background: #3b82f6;
        border-radius: 50%;
    }

    .radio-input:checked ~ .radio-text {
        color: #3b82f6;
    }

    .radio-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: #374151;
        transition: color 0.3s ease;
    }

    .radio-text i {
        color: #3b82f6;
    }

    .dept-info {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-top: 0.5rem;
    }

    .section-description {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin: 0 0 1rem 0;
        font-style: italic;
    }

    /* Multiple Images Preview Styling */
    .images-preview {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .image-preview-item {
        background: white;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
    }

    .image-preview-item:hover {
        border-color: #10b981;
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .image-preview-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: var(--radius-md);
        margin-bottom: 0.75rem;
    }

    .preview-info {
        text-align: center;
    }

    .file-name {
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.25rem 0;
        font-size: 0.875rem;
        word-break: break-word;
    }

    .file-size {
        color: var(--text-secondary);
        font-size: 0.75rem;
        margin: 0 0 0.75rem 0;
    }

    .remove-btn {
        background: #ef4444;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin: 0 auto;
    }

    .remove-btn:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
    }

    .add-more-images {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 200px;
    }

    .add-more-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        padding: 1rem 1.5rem;
        border-radius: var(--radius-lg);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 2px dashed transparent;
    }

    .add-more-btn:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
    }

    /* Multiple Videos Preview Styling */
    .videos-preview {
        margin-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .video-preview-item {
        background: white;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
    }

    .video-preview-item:hover {
        border-color: #10b981;
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .video-icon {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .video-preview-item .preview-info {
        flex: 1;
        text-align: left;
    }

    .video-preview-item .file-name {
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .video-preview-item .file-size {
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .video-preview-item .remove-btn {
        margin: 0;
    }

    .add-more-videos {
        margin-top: 0.5rem;
    }

    .add-more-item {
        border: 2px dashed var(--border-color);
        background: #f9fafb;
        justify-content: center;
        padding: 2rem;
    }

    .add-more-item:hover {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
    }

    .add-more-item .add-more-btn {
        background: none;
        border: none;
        color: #10b981;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .add-more-item .add-more-btn:hover {
        color: #059669;
        transform: scale(1.05);
    }

    /* Sidebar Styling - Enhanced to match events index */
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
        box-shadow: var(--shadow-md);
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

        .header {
            padding: 1.5rem;
        }

        .header h1 {
            font-size: 1.5rem;
        }

        .announcement-meta {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }
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

        .event-form {
            padding: 1.5rem;
        }

        .images-preview {
            grid-template-columns: 1fr;
        }

        .video-preview-item {
            flex-direction: column;
            text-align: center;
        }

        .video-preview-item .preview-info {
            text-align: center;
        }

        .videos-preview {
            gap: 0.75rem;
        }

        .add-more-item {
            padding: 1.5rem;
        }
    }
</style>

<script>
    let selectedImages = [];
    let selectedVideos = [];

    // Multiple images upload preview
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const maxFiles = parseInt(this.dataset.maxFiles) || 2;
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
            this.value = '';
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
                this.value = '';
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
                this.value = '';
                return;
            }
        }
        
        selectedImages = files;
        displayImagePreviews();
    });
    
    function displayImagePreviews() {
        const previewContainer = document.getElementById('imagesPreview');
        const uploadArea = document.getElementById('imagesUploadArea');
        const input = document.getElementById('images');
        
        previewContainer.innerHTML = '';
        
        if (selectedImages.length > 0) {
            input.style.display = 'none';
            
            selectedImages.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview-item';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <div class="preview-info">
                            <p class="file-name">${file.name}</p>
                            <p class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</p>
                            <button type="button" onclick="removeImage(${index})" class="remove-btn">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    `;
                    previewContainer.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            });
            
            // Add "Add More" button if under limit
            if (selectedImages.length < 2) {
                const addMoreDiv = document.createElement('div');
                addMoreDiv.className = 'add-more-images';
                addMoreDiv.innerHTML = `
                    <button type="button" onclick="addMoreImages()" class="add-more-btn">
                        <i class="fas fa-plus"></i> Add More Images (${selectedImages.length}/2)
                    </button>
                `;
                previewContainer.appendChild(addMoreDiv);
            }
        } else {
            input.style.display = '';
        }
    }
    
    function removeImage(index) {
        selectedImages.splice(index, 1);
        updateFileInput();
        displayImagePreviews();
    }
    
    function addMoreImages() {
        document.getElementById('images').click();
    }
    
    function updateFileInput() {
        const input = document.getElementById('images');
        const dt = new DataTransfer();
        
        selectedImages.forEach(file => {
            dt.items.add(file);
        });
        
        input.files = dt.files;
    }
    
    function clearAllImages() {
        selectedImages = [];
        document.getElementById('images').value = '';
        displayImagePreviews();
    }

    // Multiple videos upload preview
    document.getElementById('videos').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const maxFiles = parseInt(this.dataset.maxFiles) || 1;
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
            this.value = '';
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
                this.value = '';
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
                this.value = '';
                return;
            }
        }
        
        selectedVideos = files;
        displayVideoPreviews();
    });
    
    function displayVideoPreviews() {
        const previewContainer = document.getElementById('videosPreview');
        const input = document.getElementById('videos');
        
        previewContainer.innerHTML = '';
        
        if (selectedVideos.length > 0) {
            input.style.display = 'none';
            
            selectedVideos.forEach((file, index) => {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'video-preview-item';
                previewDiv.innerHTML = `
                    <div class="video-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="preview-info">
                        <p class="file-name">${file.name}</p>
                        <p class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</p>
                        <button type="button" onclick="removeVideo(${index})" class="remove-btn">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                `;
                previewContainer.appendChild(previewDiv);
            });
            
            // Add "Add More" button if under limit
            if (selectedVideos.length < 1) {
                const addMoreDiv = document.createElement('div');
                addMoreDiv.className = 'add-more-videos';
                addMoreDiv.innerHTML = `
                    <div class="video-preview-item add-more-item">
                        <button type="button" onclick="addMoreVideos()" class="add-more-btn">
                            <i class="fas fa-plus"></i> Add Video (${selectedVideos.length}/1)
                        </button>
                    </div>
                `;
                previewContainer.appendChild(addMoreDiv);
            }
        } else {
            input.style.display = '';
        }
    }
    
    function removeVideo(index) {
        selectedVideos.splice(index, 1);
        updateVideoInput();
        displayVideoPreviews();
    }
    
    function addMoreVideos() {
        document.getElementById('videos').click();
    }
    
    function updateVideoInput() {
        const input = document.getElementById('videos');
        const dt = new DataTransfer();
        
        selectedVideos.forEach(file => {
            dt.items.add(file);
        });
        
        input.files = dt.files;
    }
    
    function clearAllVideos() {
        selectedVideos = [];
        document.getElementById('videos').value = '';
        displayVideoPreviews();
    }


    // Set minimum date and time to current date and time
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

    // Set minimum date on page load
    setMinDateTime();

    // Add real-time validation for event date
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

    // Form validation before submission
    document.querySelector('.event-form').addEventListener('submit', function(e) {
        const eventDateInput = document.getElementById('event_date');
        const selectedDateTime = new Date(eventDateInput.value);
        const now = new Date();

        if (selectedDateTime < now) {
            e.preventDefault();
            alert('Please select a future date and time for the event.');
            eventDateInput.focus();
            return false;
        }
    });



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

    // Initialize on page load
    updateVisibilityScope();
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
</style>
@endsection
