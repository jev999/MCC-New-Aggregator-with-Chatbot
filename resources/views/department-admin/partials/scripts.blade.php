<script>
// Mobile menu toggle function
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}

// Enhanced drag and drop functionality for all upload areas
document.addEventListener('DOMContentLoaded', function() {
    const uploadAreas = document.querySelectorAll('.file-upload-area');

    uploadAreas.forEach(area => {
        // Drag and drop functionality
        area.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        area.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        area.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const input = this.querySelector('input[type="file"]');
                input.files = files;

                // Trigger change event
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
    });

    // Form submission enhancement with SweetAlert
    const form = document.querySelector('form');
    const submitBtns = document.querySelectorAll('button[type="submit"]');

    if (form) {
        form.addEventListener('submit', function(e) {
            submitBtns.forEach(btn => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                btn.disabled = true;
            });
        });
    }

    // Initialize scroll animations
    initScrollAnimations();
    
    // Initialize file upload handlers
    initFileUploadHandlers();
});

// Scroll animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in-up').forEach(el => {
        observer.observe(el);
    });
}

// File upload handlers
function initFileUploadHandlers() {
    // Single image upload preview
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const uploadArea = document.getElementById('imageUploadArea');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    uploadArea.innerHTML = `
                        <div class="file-preview">
                            <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: var(--radius-md);">
                            <p style="margin: 1rem 0 0 0; font-weight: 500;">${file.name}</p>
                            <button type="button" onclick="clearImageUpload()" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; background: #ef4444; color: white; border: none; border-radius: var(--radius-sm); cursor: pointer;">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Single video upload preview
    const videoInput = document.getElementById('video');
    if (videoInput) {
        videoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const uploadArea = document.getElementById('videoUploadArea');
            
            if (file) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'file-preview';
                previewDiv.innerHTML = `
                    <i class="fas fa-video" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                    <p style="margin: 1rem 0 0 0; font-weight: 500;">${file.name}</p>
                    <small style="color: var(--text-secondary);">${(file.size / (1024 * 1024)).toFixed(2)} MB</small>
                    <button type="button" onclick="clearVideoUpload()" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; background: #ef4444; color: white; border: none; border-radius: var(--radius-sm); cursor: pointer;">
                        <i class="fas fa-times"></i> Remove
                    </button>
                `;
                uploadArea.appendChild(previewDiv);
                e.target.style.display = 'none';
            }
        });
    }

    // Multiple images upload handler
    const imagesInput = document.getElementById('images');
    const imagesPreview = document.getElementById('imagesPreview');
    let selectedImages = [];

    if (imagesInput) {
        imagesInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            // Limit to 5 images (updated from memories)
            if (files.length > 5) {
                Swal.fire({
                    title: 'Too Many Files',
                    text: 'You can only upload up to 5 images.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                return;
            }
            
            selectedImages = files;
            displayImagePreviews();
        });
    }

    // Multiple videos upload handler
    const videosInput = document.getElementById('videos');
    const videosPreview = document.getElementById('videosPreview');
    let selectedVideos = [];

    if (videosInput) {
        videosInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            // Limit to 3 videos
            if (files.length > 3) {
                Swal.fire({
                    title: 'Too Many Files',
                    text: 'You can only upload up to 3 videos.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                return;
            }
            
            selectedVideos = files;
            displayVideoPreviews();
        });
    }

    // Store references for global access
    window.selectedImages = selectedImages;
    window.selectedVideos = selectedVideos;
    window.imagesInput = imagesInput;
    window.videosInput = videosInput;
    window.imagesPreview = imagesPreview;
    window.videosPreview = videosPreview;
}

// Display image previews
function displayImagePreviews() {
    if (!window.imagesPreview) return;
    
    window.imagesPreview.innerHTML = '';
    
    window.selectedImages.forEach((file, index) => {
        const previewItem = document.createElement('div');
        previewItem.className = 'file-preview-item';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewItem.innerHTML = `
                <div class="preview-content">
                    <img src="${e.target.result}" alt="Preview" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                    <div class="preview-info">
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    </div>
                    <button type="button" class="remove-file" onclick="removeImage(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(file);
        
        window.imagesPreview.appendChild(previewItem);
    });
}

// Display video previews
function displayVideoPreviews() {
    if (!window.videosPreview) return;
    
    window.videosPreview.innerHTML = '';
    
    window.selectedVideos.forEach((file, index) => {
        const previewItem = document.createElement('div');
        previewItem.className = 'file-preview-item';
        
        previewItem.innerHTML = `
            <div class="preview-content">
                <div class="video-icon" style="width: 60px; height: 60px; background: #ef4444; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-video"></i>
                </div>
                <div class="preview-info">
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                </div>
                <button type="button" class="remove-file" onclick="removeVideo(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        window.videosPreview.appendChild(previewItem);
    });
}

// Clear single image upload
function clearImageUpload() {
    const imageInput = document.getElementById('image');
    const uploadArea = document.getElementById('imageUploadArea');
    
    if (imageInput) imageInput.value = '';
    if (uploadArea) {
        uploadArea.innerHTML = `
            <input type="file" id="image" name="image" class="file-input" accept="image/*">
            <div class="file-upload-content">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to upload or drag and drop</p>
                <small>JPEG, PNG, JPG up to 5MB</small>
            </div>
        `;
        // Re-attach event listener
        initFileUploadHandlers();
    }
}

// Clear single video upload
function clearVideoUpload() {
    const videoInput = document.getElementById('video');
    const uploadArea = document.getElementById('videoUploadArea');
    const preview = uploadArea ? uploadArea.querySelector('.file-preview') : null;

    if (videoInput) {
        videoInput.value = '';
        videoInput.style.display = '';
    }
    if (preview) {
        preview.remove();
    }
}

// Remove image from multiple selection
function removeImage(index) {
    if (window.selectedImages) {
        window.selectedImages.splice(index, 1);
        updateImageInput();
        displayImagePreviews();
    }
}

// Remove video from multiple selection
function removeVideo(index) {
    if (window.selectedVideos) {
        window.selectedVideos.splice(index, 1);
        updateVideoInput();
        displayVideoPreviews();
    }
}

// Update image input files
function updateImageInput() {
    if (window.imagesInput && window.selectedImages) {
        const dt = new DataTransfer();
        window.selectedImages.forEach(file => dt.items.add(file));
        window.imagesInput.files = dt.files;
    }
}

// Update video input files
function updateVideoInput() {
    if (window.videosInput && window.selectedVideos) {
        const dt = new DataTransfer();
        window.selectedVideos.forEach(file => dt.items.add(file));
        window.videosInput.files = dt.files;
    }
}

// Professional logout handler with SweetAlert
function handleLogout() {
    Swal.fire({
        title: 'Logout Confirmation',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Logging out...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit logout form
            document.getElementById('logout-form').submit();
        }
    });
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    
    if (window.innerWidth <= 1024 && 
        sidebar && !sidebar.contains(event.target) && 
        mobileBtn && !mobileBtn.contains(event.target) && 
        sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth > 1024 && sidebar) {
        sidebar.classList.remove('open');
    }
});

// Mobile sidebar toggle functionality
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Enhanced sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });

    // Add smooth scrolling to sidebar
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.style.scrollBehavior = 'smooth';
    }

    // Highlight active menu item based on current URL
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.split('/').pop())) {
            link.classList.add('active');
        }
    });
});

// Loading overlay functions
function showLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.add('active');
    }
}

function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Form validation enhancement
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// File size validation
function validateFileSize(file, maxSizeMB, type = 'file') {
    const maxSize = maxSizeMB * 1024 * 1024; // Convert to bytes
    
    if (file.size > maxSize) {
        Swal.fire({
            title: 'File Too Large',
            text: `The ${type} size must be less than ${maxSizeMB}MB. Current size: ${(file.size / 1024 / 1024).toFixed(2)}MB`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    return true;
}

// Professional notification system
function showNotification(message, type = 'success', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type} notification-slide`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Auto remove
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }
    }, duration);
}

// Initialize tooltips if needed
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
});
</script>
