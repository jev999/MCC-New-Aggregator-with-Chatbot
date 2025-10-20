@extends('layouts.app')

@section('title', 'Create Event - Super Admin')

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
                <h1><i class="fas fa-plus-circle"></i> Create New Event</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Schedule a new campus event</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('superadmin.events.index') }}" class="btn btn-back">
                    <div class="btn-shine"></div>
                    <span><i class="fas fa-arrow-left"></i> Back to List</span>
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
            <form method="POST" action="{{ route('superadmin.events.store') }}" enctype="multipart/form-data" class="event-form">
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
                            <i class="fas fa-align-left"></i> Description *
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea @error('description') error @enderror" 
                                  rows="6" 
                                  placeholder="Describe the event details..."
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
                    <h3><i class="fas fa-images"></i> Event Media (Optional)</h3>
                    
                    <div class="form-group">
                        <label for="images" class="form-label">
                            <i class="fas fa-camera"></i> Multiple Images (up to 2)
                        </label>
                        <div class="file-upload-area" id="imagesUploadArea">
                            <input type="file" 
                                   id="images" 
                                   name="images[]" 
                                   class="file-input @error('images') error @enderror" 
                                   accept=".jpg,.jpeg,.png"
                                   multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>JPG, PNG only - up to 5MB each (max 2 images)</small>
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
                            <i class="fas fa-video"></i> Video (up to 1)
                        </label>
                        <div class="file-upload-area" id="videosUploadArea">
                            <input type="file"
                                   id="videos"
                                   name="videos[]"
                                   class="file-input @error('videos') error @enderror"
                                   accept=".mp4"
                                   multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-video"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>MP4 only - up to 50MB (max 1 video)</small>
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
                    <button type="submit" class="btn btn-create">
                        <div class="btn-shine"></div>
                        <span><i class="fas fa-save"></i> Create Event</span>
                    </button>
                    <a href="{{ route('superadmin.events.index') }}" class="btn btn-cancel">
                        <div class="btn-shine"></div>
                        <span><i class="fas fa-times"></i> Cancel</span>
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

    /* Custom SweetAlert Styling */
    .swal-popup {
        border-radius: 16px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }

    .swal-title {
        font-weight: 700 !important;
        font-size: 1.25rem !important;
        color: #1f2937 !important;
    }

    .swal-content {
        font-size: 0.875rem !important;
        color: #6b7280 !important;
        line-height: 1.5 !important;
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
    .btn-cancel {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.1);
}

.btn-cancel:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.2);
}
    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.1);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.2);
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
        
    }
</style>

<script>
    // Multiple Images upload preview - enforce file limit
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const maxFiles = 2;
        
        if (files.length > maxFiles) {
            Swal.fire({
                icon: 'warning',
                title: 'Too Many Images!',
                text: `Please select only up to ${maxFiles} image files.`,
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
        const maxSize = 5 * 1024 * 1024; // 5MB
        const oversizedFiles = files.filter(file => file.size > maxSize);
        if (oversizedFiles.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'File Size Too Large!',
                text: `Some images exceed the 5MB limit. Please choose smaller files.`,
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
            clearButton.onclick = clearImagesUpload;
            previewContainer.appendChild(clearButton);
        }
    });

    function clearImagesUpload() {
        document.getElementById('images').value = '';
        document.getElementById('imagesPreview').innerHTML = '';
    }

    // Single Video upload preview - enforce file limit
    document.getElementById('videos').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const maxFiles = 1;
        
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
        
        // Check file sizes
        const maxSize = 50 * 1024 * 1024; // 50MB
        const oversizedFiles = files.filter(file => file.size > maxSize);
        if (oversizedFiles.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Video File Too Large!',
                text: `The video file exceeds the 50MB limit. Please choose a smaller file.`,
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
        
        files.forEach((file, index) => {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'file-preview-item';
            previewDiv.innerHTML = `
                <i class="fas fa-video" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
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
            clearButton.onclick = clearVideosUpload;
            previewContainer.appendChild(clearButton);
        }
    });

    function clearVideosUpload() {
        document.getElementById('videos').value = '';
        document.getElementById('videosPreview').innerHTML = '';
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
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Date!',
                text: 'Please select a future date and time for the event.',
                confirmButtonText: 'Got it!',
                confirmButtonColor: '#3b82f6'
            });
            eventDateInput.focus();
            return false;
        }
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
