@extends('layouts.app')

@section('title', 'Edit Announcement - Office Admin')

@section('content')
<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
        <i class="fas fa-bars"></i>
    </button>

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
                <h1><i class="fas fa-edit"></i> Edit Announcement</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Update announcement from {{ Auth::guard('admin')->user()->office_display }}</p>
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

        <div class="form-container">
            <form method="POST" action="{{ route('office-admin.announcements.update', $announcement->id) }}" enctype="multipart/form-data" class="announcement-form">
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

                    <div class="form-group">
                        <label for="expires_at" class="form-label">
                            <i class="fas fa-calendar-times"></i> Expiration Date (Optional)
                        </label>
                        <input type="datetime-local" 
                               id="expires_at" 
                               name="expires_at" 
                               class="form-input @error('expires_at') error @enderror" 
                               value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('expires_at')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <small class="form-help">Leave empty for permanent announcement</small>
                    </div>
                </div>

                <!-- Real-time Creation Info -->
                <div class="form-section">
                    <h3><i class="fas fa-clock"></i> Announcement Information</h3>
                    <div class="creation-info">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar"></i> Created On
                            </div>
                            <div class="info-value">
                                {{ $announcement->created_at->format('F d, Y') }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-clock"></i> Created At
                            </div>
                            <div class="info-value">
                                {{ $announcement->created_at->format('g:i A') }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user"></i> Created By
                            </div>
                            <div class="info-value">
                                {{ $announcement->admin->username ?? 'Unknown' }}
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
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-edit"></i> Last Updated
                            </div>
                            <div class="info-value">
                                {{ $announcement->updated_at->format('M d, Y g:i A') }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-eye"></i> Status
                            </div>
                            <div class="info-value">
                                @if($announcement->is_published)
                                    <span style="color: var(--success-color); font-weight: 600;">Published</span>
                                @else
                                    <span style="color: var(--warning-color); font-weight: 600;">Draft</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-image"></i> Media Files (Optional)</h3>
                    
                    @php
                        $allImagePaths = $announcement->allImagePaths ?? [];
                        $allVideoPaths = $announcement->allVideoPaths ?? [];
                    @endphp

                    @if(count($allImagePaths) > 0)
                        <div class="current-media-section">
                            <h4><i class="fas fa-image"></i> Current Images</h4>
                            <div class="current-media-grid">
                                @foreach($announcement->allImagePaths as $index => $imagePath)
                                    <div class="current-media-item">
                                        <img src="{{ storage_asset($imagePath) }}" alt="Current Image">
                                        <div class="remove-media-checkbox">
                                            <input type="checkbox" name="remove_images[]" value="{{ $index }}" id="remove_image_{{ $index }}">
                                            <label for="remove_image_{{ $index }}"><i class="fas fa-trash-alt"></i> Remove</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(count($allVideoPaths) > 0)
                        <div class="current-media-section">
                            <h4><i class="fas fa-video"></i> Current Videos</h4>
                            <div class="current-media-grid">
                                @foreach($announcement->allVideoPaths as $index => $videoPath)
                                    <div class="current-media-item video-media-item">
                                        <div class="video-container">
                                            <video controls preload="metadata" class="current-video">
                                                <source src="{{ storage_asset($videoPath) }}" type="video/mp4">
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

                    <!-- Multiple Images Upload -->
                    <div class="form-group">
                        <label for="images" class="form-label">
                            <i class="fas fa-images"></i> Add New Images (Up to 2)
                        </label>
                        <div class="file-upload-area" id="imagesUploadArea">
                            <input type="file" id="images" name="images[]" class="file-input" accept="image/jpeg,image/png,image/jpg" multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>JPG, PNG up to 2MB each</small>
                            </div>
                        </div>
                        <div id="images-preview" class="file-preview-container"></div>
                        @error('images') <span class="error-message">{{ $message }}</span> @enderror
                        @error('images.*') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    <!-- Multiple Videos Upload -->
                    <div class="form-group">
                        <label for="videos" class="form-label">
                            <i class="fas fa-video"></i> Add New Video (Up to 1)
                        </label>
                        <div class="file-upload-area" id="videosUploadArea">
                            <input type="file" id="videos" name="videos[]" class="file-input" accept="video/mp4" multiple>
                            <div class="file-upload-content">
                                <i class="fas fa-video"></i>
                                <p>Click to upload or drag and drop</p>
                                <small>MP4 up to 50MB, maximum 1 video</small>
                            </div>
                        </div>
                        <div id="videos-preview" class="file-preview-container"></div>
                        @error('videos') <span class="error-message">{{ $message }}</span> @enderror
                        @error('videos.*') <span class="error-message">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> Publishing Settings</h3>

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
                                    <i class="fas fa-eye"></i> Publish immediately
                                </span>
                            </label>
                            <small class="form-help">If checked, the announcement will appear in all departments. If unchecked, the announcement will be saved as a draft</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Announcement
                    </button>
                    <a href="{{ route('office-admin.announcements.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // CSRF Token Refresh System to prevent 419 errors
    function refreshCSRFToken() {
        fetch('{{ route("csrf-token") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                // Update CSRF token in the form
                const csrfInput = document.querySelector('input[name="_token"]');
                if (csrfInput) {
                    csrfInput.value = data.csrf_token;
                }
                // Update meta tag if exists
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    csrfMeta.setAttribute('content', data.csrf_token);
                }
                console.log('CSRF token refreshed successfully');
            }
        })
        .catch(error => {
            console.warn('Failed to refresh CSRF token:', error);
        });
    }

    // Refresh CSRF token every 25 minutes (before 30-minute expiry)
    setInterval(refreshCSRFToken, 25 * 60 * 1000);

    // Refresh token when user becomes active after being idle
    let isIdle = false;
    let idleTimer;

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        if (isIdle) {
            refreshCSRFToken();
            isIdle = false;
        }
        idleTimer = setTimeout(() => {
            isIdle = true;
        }, 15 * 60 * 1000); // 15 minutes idle
    }

    // Listen for user activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetIdleTimer, true);
    });

    // Initial setup
    resetIdleTimer();

    // Handle form submission with token validation
    document.querySelector('.announcement-form').addEventListener('submit', function(e) {
        const csrfToken = document.querySelector('input[name="_token"]').value;
        if (!csrfToken || csrfToken.length < 40) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Session Expired',
                text: 'Your session has expired. Please refresh the page and try again.',
                confirmButtonText: 'Refresh Page',
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal-custom-popup',
                    confirmButton: 'swal-custom-button'
                }
            }).then(() => {
                window.location.reload();
            });
            return false;
        }
    });
    // Multiple Images Upload Preview
    let selectedImages = [];
    const maxImages = 2;
    let selectedVideos = [];
    const maxVideos = 1;
    
    // Track removed media checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.type === 'checkbox' && (e.target.name === 'remove_images[]' || e.target.name === 'remove_videos[]')) {
            updateAvailableSlots();
        }
    });
    
    function updateAvailableSlots() {
        // Get all checked removal checkboxes
        const removedImageIndexes = Array.from(document.querySelectorAll('input[name="remove_images[]"]:checked')).map(cb => cb.value);
        const removedVideoIndexes = Array.from(document.querySelectorAll('input[name="remove_videos[]"]:checked')).map(cb => cb.value);
        
        const currentImageCount = {{ count($announcement->allImagePaths) }};
        const currentVideoCount = {{ count($announcement->allVideoPaths) }};
        
        const removedImagesCount = removedImageIndexes.length;
        const removedVideosCount = removedVideoIndexes.length;
        
        // Calculate remaining slots after removal
        const remainingImages = currentImageCount - removedImagesCount;
        const remainingVideos = currentVideoCount - removedVideosCount;
        
        // Available slots = max allowed - remaining after removal
        const availableImageSlots = Math.max(0, maxImages - remainingImages);
        const availableVideoSlots = Math.max(0, maxVideos - remainingVideos);
        
        // Update upload area labels
        const imageLabel = document.querySelector('label[for="images"]');
        const videoLabel = document.querySelector('label[for="videos"]');
        
        if (imageLabel) {
            if (availableImageSlots === 0) {
                imageLabel.innerHTML = `<i class="fas fa-images"></i> Add New Images (Limit reached - remove existing images first)`;
            } else {
                imageLabel.innerHTML = `<i class="fas fa-images"></i> Add New Images (${availableImageSlots} available)`;
            }
        }
        
        if (videoLabel) {
            if (availableVideoSlots === 0) {
                videoLabel.innerHTML = `<i class="fas fa-video"></i> Add New Video (Limit reached - remove existing video first)`;
            } else {
                videoLabel.innerHTML = `<i class="fas fa-video"></i> Add New Video (${availableVideoSlots} available)`;
            }
        }
    }
    
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const container = document.getElementById('images-preview');
        
        // Calculate available slots
        const currentImageCount = {{ count($announcement->allImagePaths) }};
        const removedImageIndexes = Array.from(document.querySelectorAll('input[name="remove_images[]"]:checked')).map(cb => cb.value);
        const removedImagesCount = removedImageIndexes.length;
        const remainingImages = currentImageCount - removedImagesCount;
        const availableSlots = Math.max(0, maxImages - remainingImages);
        
        // Validate file count against available slots
        if (files.length > availableSlots) {
            let errorMessage;
            if (availableSlots === 0) {
                errorMessage = `You have reached the maximum limit of ${maxImages} images. Please remove existing images first before uploading new ones.`;
            } else {
                errorMessage = `You can only upload ${availableSlots} more images. You selected ${files.length} files.`;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Upload Limit Exceeded',
                text: errorMessage,
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'swal-custom-popup',
                    confirmButton: 'swal-custom-button'
                }
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
                    customClass: {
                        popup: 'swal-custom-popup',
                        confirmButton: 'swal-custom-button'
                    }
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
                    customClass: {
                        popup: 'swal-custom-popup',
                        confirmButton: 'swal-custom-button'
                    }
                }).then(() => {
                    e.target.value = '';
                });
                return;
            }
            
            selectedImages.push(file);
            
            // Create preview
            const previewItem = document.createElement('div');
            previewItem.className = 'file-preview-item';
            
            const img = document.createElement('img');
            img.className = 'file-preview-thumbnail';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-preview-info';
            fileInfo.innerHTML = `
                <span class="file-preview-name">${file.name}</span>
                <span class="file-preview-size">${(file.size / 1024).toFixed(1)} KB</span>
            `;
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'file-preview-remove';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function() {
                removeFilePreview(this, index, 'images');
            };
            
            previewItem.appendChild(img);
            previewItem.appendChild(fileInfo);
            previewItem.appendChild(removeBtn);
            container.appendChild(previewItem);
        });
    });
    document.getElementById('videos').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const container = document.getElementById('videos-preview');
        
        // Calculate available slots
        const currentVideoCount = {{ count($announcement->allVideoPaths ?? []) }};
        const removedVideoIndexes = Array.from(document.querySelectorAll('input[name="remove_videos[]"]:checked')).map(cb => cb.value);
        const removedVideosCount = removedVideoIndexes.length;
        const remainingVideos = currentVideoCount - removedVideosCount;
        const availableSlots = Math.max(0, maxVideos - remainingVideos);
        
        // Validate file count against available slots
        if (files.length > availableSlots) {
            let errorMessage;
            if (availableSlots === 0) {
                errorMessage = `You have reached the maximum limit of ${maxVideos} video. Please remove the existing video first before uploading a new one.`;
            } else {
                errorMessage = `You can only upload ${availableSlots} more video. You selected ${files.length} files.`;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Upload Limit Exceeded',
                text: errorMessage,
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'swal-custom-popup',
                    confirmButton: 'swal-custom-button'
                }
            });
            e.target.value = '';
            return;
        }
        
        // Clear previous previews
        container.innerHTML = '';
        selectedVideos = [];
        
        files.forEach((file, index) => {
            // Validate file type
            if (!['video/mp4'].includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: `File ${file.name} is not a valid video format. Only MP4 is allowed.`,
                    confirmButtonColor: '#ef4444',
                    customClass: {
                        popup: 'swal-custom-popup',
                        confirmButton: 'swal-custom-button'
                    }
                }).then(() => {
                    e.target.value = '';
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
                    customClass: {
                        popup: 'swal-custom-popup',
                        confirmButton: 'swal-custom-button'
                    }
                }).then(() => {
                    e.target.value = '';
                });
                return;
            }
            
            selectedVideos.push(file);
            
            // Create preview with actual video thumbnail
            const previewItem = document.createElement('div');
            previewItem.className = 'file-preview-item video-preview-item';
            
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
            
            videoContainer.appendChild(videoElement);
            videoContainer.appendChild(playOverlay);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-preview-info';
            fileInfo.innerHTML = `
                <span class="file-preview-name">${file.name}</span>
                <span class="file-preview-size">${(file.size / 1024 / 1024).toFixed(1)} MB</span>
            `;
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'file-preview-remove';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function() {
                // Revoke object URL to free memory
                URL.revokeObjectURL(videoURL);
                removeFilePreview(this, index, 'videos');
            };
            
            previewItem.appendChild(videoContainer);
            previewItem.appendChild(fileInfo);
            previewItem.appendChild(removeBtn);
            container.appendChild(previewItem);
            
            console.log('Video preview created:', {
                fileName: file.name,
                containerChildren: container.children.length,
                previewItemClasses: previewItem.className
            });
        });
    });

    function removeFilePreview(button, index, type) {
        const previewItem = button.closest('.file-preview-item');
        previewItem.remove();
        
        if (type === 'images') {
            selectedImages.splice(index, 1);
            // Update the file input
            const dt = new DataTransfer();
            selectedImages.forEach(file => dt.items.add(file));
            document.getElementById('images').files = dt.files;
        } else {
            selectedVideos.splice(index, 1);
            // Update the file input
            const dt = new DataTransfer();
            selectedVideos.forEach(file => dt.items.add(file));
            document.getElementById('videos').files = dt.files;
        }
    }

    // Initialize available slots on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateAvailableSlots();
    });
</script>
@endsection

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
        --text-light: #9ca3af;
    }

    /* Update the sidebar styles */
    .sidebar {
        width: var(--sidebar-width);
        background: var(--bg-sidebar);
        color: white;
        position: fixed;
        height: 100vh;
        left: 0;
        top: 0;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: var(--box-shadow);
        transition: transform var(--transition-speed) ease;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        margin-bottom: 0.5rem;
    }

    .sidebar-header h3 {
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .sidebar-header h3 i {
        color: var(--primary-color);
        font-size: 1.3rem;
    }

    .office-info {
        color: var(--text-light);
        font-size: 0.8rem;
        margin-top: 0.5rem;
        font-weight: 400;
        opacity: 0.8;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0.5rem 0;
        flex-grow: 1;
    }

    .sidebar-menu li {
        margin: 0.25rem 0;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.25rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        font-weight: 500;
        transition: all var(--transition-speed) ease;
        gap: 0.75rem;
        font-size: 0.9rem;
        border-left: 3px solid transparent;
        margin: 0 0.5rem;
        border-radius: 6px;
    }

    .sidebar-menu a:hover {
        background: var(--bg-sidebar-hover);
        color: white;
        border-left-color: var(--primary-color);
    }

    .sidebar-menu a.active {
        background: var(--bg-sidebar-hover);
        color: white;
        border-left-color: var(--primary-color);
        font-weight: 600;
    }

    .sidebar-menu a i {
        width: 20px;
        text-align: center;
        font-size: 0.95rem;
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

    .current-file {
        background: var(--bg-tertiary);
        padding: 1rem;
        border-radius: var(--radius-md);
        margin-bottom: 1rem;
        border: 1px solid var(--border-color);
    }

    .current-file h4 {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .current-file h4 i {
        color: var(--primary-color);
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

    .current-media-section {
        margin-bottom: 2rem;
    }

    .current-media-section h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .current-media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }

    .current-media-item {
        position: relative;
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }

    .current-media-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        display: block;
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
        height: 120px;
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
        height: 120px; /* Match video container height */
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 10;
    }

    .current-media-item:hover .remove-media-checkbox {
        opacity: 1;
    }

    /* Special positioning for video items */
    .video-media-item .remove-media-checkbox {
        border-radius: var(--radius-md) var(--radius-md) 0 0;
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
        background: #ef4444;
    }

    .remove-media-checkbox input[type="checkbox"]:checked + label {
        background: #10b981;
    }

    .remove-media-checkbox input[type="checkbox"]:checked + label::before {
        content: "\f00c"; /* FontAwesome check icon */
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-right: 0.5rem;
    }
    
    .file-preview-container {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }

    .file-preview-item {
        position: relative;
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }

    .file-preview-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }

    .file-preview-item .file-info {
        padding: 0.75rem;
        background: var(--bg-secondary);
        font-size: 0.75rem;
    }

    .file-preview-item .file-name {
        display: block;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .remove-file-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(0,0,0,0.6);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    .form-actions {
        padding: 2rem;
        background: var(--bg-tertiary);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    /* File preview styles */
    .file-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1rem;
    }

    .file-preview-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        min-width: 200px;
    }

    .file-preview-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
        margin-right: 12px;
    }

    .video-thumbnail {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e3f2fd !important;
        color: #1976d2 !important;
        font-size: 1.5rem;
        border: 2px solid #1976d2 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .file-preview-info {
        flex: 1;
        min-width: 0;
    }

    .file-preview-name {
        display: block;
        font-weight: 500;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.875rem;
    }

    .file-preview-size {
        display: block;
        color: var(--text-muted);
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .file-preview-remove {
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
        margin-left: 8px;
        flex-shrink: 0;
    }

    .file-preview-remove:hover {
        background: var(--danger-dark);
        transform: scale(1.1);
    }

    /* Video Preview Styles */
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

    /* Current files display */
    .current-files {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
    }

    .current-files h4 {
        margin: 0 0 1rem 0;
        color: var(--text-primary);
        font-size: 1rem;
        font-weight: 600;
    }

    .current-files-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .current-file-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-primary);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius);
    }

    .current-file-preview {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .current-file-info {
        flex: 1;
        min-width: 0;
    }

    .current-file-name {
        display: block;
        font-weight: 500;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.875rem;
    }

    .current-file-size {
        display: block;
        color: var(--text-muted);
        font-size: 0.75rem;
        margin-top: 0.25rem;
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

    /* SweetAlert2 Custom Styling */
    .swal-custom-popup {
        border-radius: 16px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }

    .swal-custom-button {
        border-radius: 8px !important;
        font-weight: 600 !important;
        padding: 10px 24px !important;
        transition: all 0.2s ease !important;
    }

    .swal-custom-button:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
</style>

@section('additional-scripts')
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

    // Multiple file upload functionality
    document.addEventListener('DOMContentLoaded', function() {
</script>

@endsection
