
@extends('layouts.public')

@section('title', $announcement->title . ' - MCC Portal')

@section('content')
<div class="public-content">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('user.dashboard') }}"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <article class="content-article">
            <header class="article-header">
                <div class="article-meta">
                    <span class="badge">Announcement</span>
                    <span class="date">{{ $announcement->created_at->format('F d, Y g:i A') }}</span>
                </div>
                <h1 class="article-title">{{ $announcement->title }}</h1>
                <div class="article-author">
                    <i class="fas fa-user-shield"></i>
                    @if($announcement->admin->role === 'superadmin')
                        Published by MCC Administration
                    @elseif($announcement->admin->role === 'department_admin')
                        Published by {{ $announcement->admin->department_display }} Department - {{ $announcement->admin->username }}
                    @elseif($announcement->admin->role === 'office_admin')
                        Published by {{ $announcement->admin->office_display }} - {{ $announcement->admin->username }}
                    @else
                        Published by {{ $announcement->admin->username }}
                    @endif
                </div>
            </header>

            <div class="article-media">
                @if($announcement->image_path)
                    <div class="media-item">
                        @php
                            $imagePath = storage_asset($announcement->image_path);
                        @endphp

                        <img src="{{ $imagePath }}"
                             alt="{{ $announcement->title }}"
                             class="article-image"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<div class="image-error"><i class="fas fa-exclamation-triangle"></i><p>Image could not be loaded<br><small>Path: {{ $announcement->image_path }}</small></p></div>';">
                    </div>
                @endif

                @if($announcement->video_path)
                    <div class="media-item">
                        <h4><i class="fas fa-video"></i> Video</h4>
                        @php
                            $videoPath = storage_asset($announcement->video_path);
                            $extension = pathinfo($announcement->video_path, PATHINFO_EXTENSION);
                            $mimeType = match(strtolower($extension)) {
                                'mp4' => 'video/mp4',
                                'webm' => 'video/webm',
                                'avi' => 'video/x-msvideo',
                                'mov' => 'video/quicktime',
                                'wmv' => 'video/x-ms-wmv',
                                'flv' => 'video/x-flv',
                                default => 'video/mp4'
                            };
                        @endphp

                        <div class="video-container">
                            <video controls class="article-video" preload="metadata" playsinline onerror="this.style.display='none'; this.parentElement.innerHTML+='<div class="video-error"><i class="fas fa-exclamation-triangle"></i><p>Video could not be loaded<br><small>Path: {{ $announcement->video_path }}</small></p></div>';">
                                <source src="{{ $videoPath }}" type="{{ $mimeType }}">
                                <!-- Always add MP4 fallback -->
                                <source src="{{ $videoPath }}" type="video/mp4">
                                <p>Your browser does not support the video tag. <a href="{{ $videoPath }}" target="_blank" download>Download the video</a> to watch it.</p>
                            </video>

                            <!-- Debug info (remove in production) -->
                            <div style="font-size: 0.75rem; color: #666; margin-top: 0.5rem;">
                                <strong>Debug:</strong> {{ $announcement->video_path }} ({{ $mimeType }})
                            </div>
                        </div>
                    </div>
                @endif

                @if($announcement->csv_path)
                    <div class="media-item">
                        <h4><i class="fas fa-file-csv"></i> Downloadable File</h4>
                        <div class="file-download">
                            <div class="file-info">
                                <i class="fas fa-file-csv"></i>
                                <div>
                                    <p class="file-name">{{ basename($announcement->csv_path) }}</p>
                                    <a href="{{ storage_asset($announcement->csv_path) }}" download class="btn btn-download">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="article-content">
                {!! nl2br(e($announcement->content)) !!}
            </div>
        </article>

        <!-- Comments Section -->
        @auth
        <section class="comments-section">
            <div class="comments-header">
                <h3><i class="fas fa-comments"></i> Comments & Feedback</h3>
                <p>Share your thoughts and questions about this announcement</p>
            </div>

            <!-- Comment Form -->
            <div class="comment-form-container">
                <div class="comment-form">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-details">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-meta">{{ ucfirst(auth()->user()->role) }} - {{ auth()->user()->department ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="comment-input-group">
                        <textarea id="commentInput"
                                  placeholder="Write your comment or feedback about this announcement..."
                                  maxlength="1000"
                                  rows="4"></textarea>
                        <div class="comment-actions">
                            <span class="char-count">0/1000</span>
                            <button id="submitComment" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Post Comment
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments List -->
            <div class="comments-list">
                <div class="comments-count">
                    <h4><span id="commentsCount">{{ $announcement->comments->count() }}</span> Comments</h4>
                </div>

                <div id="commentsList">
                    @forelse($announcement->comments as $comment)
                        <div class="comment-item" data-comment-id="{{ $comment->id }}">
                            <div class="comment-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author">{{ $comment->user->name }}</span>
                                    <span class="comment-meta">
                                        {{ ucfirst($comment->user->role) }} - {{ $comment->user->department ?? 'N/A' }}
                                    </span>
                                    <span class="comment-time">{{ $comment->time_ago }}</span>
                                </div>
                                <div class="comment-text">{{ $comment->content }}</div>
                                <div class="comment-actions">
                                    <button class="reply-btn" onclick="showReplyForm({{ $comment->id }})">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                    @if($comment->canEdit(auth()->user()))
                                        <button class="edit-btn" onclick="editComment({{ $comment->id }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    @endif
                                    @if($comment->canDelete(auth()->user()))
                                        <button class="delete-btn" onclick="deleteComment({{ $comment->id }})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    @endif
                                </div>

                                <!-- Replies -->
                                @if($comment->replies->count() > 0)
                                    <div class="replies-list">
                                        @foreach($comment->replies as $reply)
                                            <div class="comment-item reply" data-comment-id="{{ $reply->id }}">
                                                <div class="comment-avatar">
                                                    <i class="fas fa-user-circle"></i>
                                                </div>
                                                <div class="comment-content">
                                                    <div class="comment-header">
                                                        <span class="comment-author">{{ $reply->user->name }}</span>
                                                        <span class="comment-meta">
                                                            {{ ucfirst($reply->user->role) }} - {{ $reply->user->department ?? 'N/A' }}
                                                        </span>
                                                        <span class="comment-time">{{ $reply->time_ago }}</span>
                                                    </div>
                                                    <div class="comment-text">{{ $reply->content }}</div>
                                                    <div class="comment-actions">
                                                        @if($reply->canEdit(auth()->user()))
                                                            <button class="edit-btn" onclick="editComment({{ $reply->id }})">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                        @endif
                                                        @if($reply->canDelete(auth()->user()))
                                                            <button class="delete-btn" onclick="deleteComment({{ $reply->id }})">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="no-comments">
                            <i class="fas fa-comments"></i>
                            <p>No comments yet. Be the first to share your thoughts!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
        @else
        <section class="comments-section">
            <div class="login-prompt">
                <i class="fas fa-sign-in-alt"></i>
                <h3>Join the Discussion</h3>
                <p>Please <a href="{{ route('login') }}">login</a> to comment and share your feedback</p>
            </div>
        </section>
        @endauth
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="close">&times;</span>
    <img id="modalImage" src="" alt="">
</div>

<script>
// Image modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const images = document.querySelectorAll('.article-image');
    const closeBtn = document.querySelector('.image-modal .close');

    // Add click event to all images
    images.forEach(img => {
        img.addEventListener('click', function() {
            modal.style.display = 'block';
            modalImg.src = this.src;
            modalImg.alt = this.alt;
        });
    });

    // Close modal when clicking the close button
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside the image
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });

    // Video error handling and fallback
    const videos = document.querySelectorAll('.article-video');
    videos.forEach(video => {
        video.addEventListener('error', function() {
            const container = this.parentElement;
            const errorMsg = document.createElement('div');
            errorMsg.className = 'video-error';
            errorMsg.innerHTML = `
                <div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; text-align: center; color: #991b1b;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                    Unable to play this video format.
                    <a href="${this.querySelector('source').src}" target="_blank" style="color: #dc2626; text-decoration: underline;">
                        Click here to download and play with your media player
                    </a>
                </div>
            `;
            container.appendChild(errorMsg);
            this.style.display = 'none';
        });

        // Add loading indicator
        video.addEventListener('loadstart', function() {
            const container = this.parentElement;
            const loader = document.createElement('div');
            loader.className = 'video-loader';
            loader.innerHTML = `
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; text-align: center;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <div>Loading video...</div>
                </div>
            `;
            container.appendChild(loader);
        });

        video.addEventListener('canplay', function() {
            const loader = this.parentElement.querySelector('.video-loader');
            if (loader) {
                loader.remove();
            }
        });
    });
});

// Comment System JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const commentInput = document.getElementById('commentInput');
    const submitBtn = document.getElementById('submitComment');
    const charCount = document.querySelector('.char-count');
    const commentsList = document.getElementById('commentsList');
    const commentsCount = document.getElementById('commentsCount');

    // Character counter
    if (commentInput) {
        commentInput.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/1000`;

            if (length > 900) {
                charCount.style.color = '#ef4444';
            } else if (length > 800) {
                charCount.style.color = '#f59e0b';
            } else {
                charCount.style.color = '#6b7280';
            }
        });

        // Submit comment
        submitBtn.addEventListener('click', function() {
            const content = commentInput.value.trim();

            if (!content) {
                alert('Please write a comment before submitting.');
                return;
            }

            console.log('Submitting comment:', content);
            submitComment(content);
        });

        // Allow Ctrl+Enter to submit
        commentInput.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                submitBtn.click();
            }
        });
    }
});

function submitComment(content, parentId = null) {
    console.log('submitComment called with:', { content, parentId });

    const submitBtn = parentId ?
        document.querySelector(`[data-parent-id="${parentId}"] .btn-primary`) :
        document.getElementById('submitComment');

    if (!submitBtn) {
        console.error('Submit button not found');
        return;
    }

    // Check for CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        alert('Security token not found. Please refresh the page and try again.');
        return;
    }

    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';

    const requestData = {
        content: content,
        commentable_type: 'announcement',
        commentable_id: {{ $announcement->id }},
        parent_id: parentId
    };

    console.log('Sending request:', requestData);

    // Create FormData object
    const formData = new FormData();
    formData.append('content', content);
    formData.append('commentable_type', 'announcement');
    formData.append('commentable_id', {{ $announcement->id }});
    if (parentId) {
        formData.append('parent_id', parentId);
    }

    // Submit comment via AJAX
    fetch('/user/comments', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('Response received:', response);
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
            return response.text().then(text => {
                console.error('Response text:', text);
                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);

        if (data.success) {
            // Clear input
            if (parentId) {
                // Remove reply form
                const replyForm = document.querySelector(`[data-parent-id="${parentId}"]`);
                if (replyForm) replyForm.remove();
            } else {
                document.getElementById('commentInput').value = '';
                document.querySelector('.char-count').textContent = '0/1000';
                document.querySelector('.char-count').style.color = '#6b7280';
            }

            // Add new comment to the list
            addCommentToList(data.comment, parentId);

            // Update comment count
            updateCommentCount();

            // Show success message
            showNotification('Comment posted successfully!', 'success');
        } else {
            console.error('Comment submission failed:', data);
            let errorMessage = 'Error posting comment: ';
            if (data.errors) {
                // Validation errors
                errorMessage += Object.values(data.errors).flat().join(', ');
            } else {
                errorMessage += (data.error || 'Unknown error');
            }
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Detailed error information:', {
            message: error.message,
            stack: error.stack,
            name: error.name
        });

        let errorMessage = 'Error posting comment: ';
        if (error.message.includes('HTTP error')) {
            errorMessage += 'Server error occurred. Please try again.';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage += 'Network connection failed. Please check your connection.';
        } else if (error.message.includes('JSON')) {
            errorMessage += 'Invalid server response. Please refresh the page and try again.';
        } else {
            errorMessage += error.message || 'Unknown error occurred.';
        }

        alert(errorMessage);
    })
    .finally(() => {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = parentId ?
            '<i class="fas fa-paper-plane"></i> Reply' :
            '<i class="fas fa-paper-plane"></i> Post Comment';
    });
}

function addCommentToList(comment, parentId) {
    const commentHtml = `
        <div class="comment-item ${parentId ? 'reply' : ''}" data-comment-id="${comment.id}">
            <div class="comment-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="comment-content">
                <div class="comment-header">
                    <span class="comment-author">${comment.user_name}</span>
                    <span class="comment-meta">${comment.user_role} - ${comment.user_department}</span>
                    <span class="comment-time">${comment.time_ago}</span>
                </div>
                <div class="comment-text">${comment.content}</div>
                <div class="comment-actions">
                    ${!parentId ? `<button onclick="showReplyForm(${comment.id})">
                        <i class="fas fa-reply"></i> Reply
                    </button>` : ''}
                    ${comment.can_edit ? `<button onclick="editComment(${comment.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>` : ''}
                    ${comment.can_delete ? `<button class="delete-btn" onclick="deleteComment(${comment.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>` : ''}
                </div>
                ${!parentId ? '<div class="replies-list"></div>' : ''}
            </div>
        </div>
    `;

    if (parentId) {
        // Add to replies list
        const parentComment = document.querySelector(`[data-comment-id="${parentId}"]`);
        const repliesList = parentComment.querySelector('.replies-list');
        repliesList.insertAdjacentHTML('beforeend', commentHtml);
    } else {
        // Add to main comments list
        const noComments = document.querySelector('.no-comments');
        if (noComments) {
            noComments.remove();
        }
        document.getElementById('commentsList').insertAdjacentHTML('beforeend', commentHtml);
    }
}

function updateCommentCount() {
    const currentCount = parseInt(document.getElementById('commentsCount').textContent);
    document.getElementById('commentsCount').textContent = currentCount + 1;
}

function showReplyForm(commentId) {
    // Remove any existing reply forms
    const existingForms = document.querySelectorAll('.reply-form');
    existingForms.forEach(form => form.remove());

    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const replyFormHtml = `
        <div class="reply-form" data-parent-id="${commentId}">
            <textarea placeholder="Write a reply..." maxlength="1000" rows="3"></textarea>
            <div class="comment-actions">
                <span class="char-count">0/1000</span>
                <div>
                    <button type="button" onclick="this.closest('.reply-form').remove()" style="background: #6b7280; color: white; margin-right: 0.5rem;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn-primary" onclick="submitReply(${commentId})">
                        <i class="fas fa-paper-plane"></i> Reply
                    </button>
                </div>
            </div>
        </div>
    `;

    commentItem.querySelector('.comment-content').insertAdjacentHTML('beforeend', replyFormHtml);

    // Focus on the reply textarea and add character counter
    const replyTextarea = commentItem.querySelector('.reply-form textarea');
    const replyCharCount = commentItem.querySelector('.reply-form .char-count');

    replyTextarea.focus();
    replyTextarea.addEventListener('input', function() {
        const length = this.value.length;
        replyCharCount.textContent = `${length}/1000`;

        if (length > 900) {
            replyCharCount.style.color = '#ef4444';
        } else if (length > 800) {
            replyCharCount.style.color = '#f59e0b';
        } else {
            replyCharCount.style.color = '#6b7280';
        }
    });
}

function submitReply(parentId) {
    const replyForm = document.querySelector(`[data-parent-id="${parentId}"]`);
    const content = replyForm.querySelector('textarea').value.trim();

    if (!content) {
        alert('Please write a reply before submitting.');
        return;
    }

    submitComment(content, parentId);
}

function editComment(commentId) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const commentText = commentItem.querySelector('.comment-text');
    const currentText = commentText.textContent;

    // Replace comment text with textarea
    commentText.innerHTML = `
        <div class="edit-form">
            <textarea maxlength="1000" rows="3">${currentText}</textarea>
            <div class="comment-actions">
                <span class="char-count">${currentText.length}/1000</span>
                <div>
                    <button type="button" onclick="cancelEdit(${commentId}, '${currentText.replace(/'/g, "\\'")}')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn-primary" onclick="saveEditedComment(${commentId})">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    `;

    // Focus on textarea and add character counter
    const textarea = commentText.querySelector('textarea');
    const charCount = commentText.querySelector('.char-count');

    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);

    textarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length}/1000`;

        if (length > 900) {
            charCount.style.color = '#ef4444';
        } else if (length > 800) {
            charCount.style.color = '#f59e0b';
        } else {
            charCount.style.color = '#6b7280';
        }
    });
}

function saveEditedComment(commentId) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const textarea = commentItem.querySelector('.edit-form textarea');
    const content = textarea.value.trim();
    const saveBtn = commentItem.querySelector('.edit-form .btn-primary');

    if (!content) {
        alert('Comment cannot be empty.');
        return;
    }

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    fetch(`/user/comments/${commentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ content: content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Replace edit form with updated comment text
            const commentText = commentItem.querySelector('.comment-text');
            commentText.innerHTML = data.comment.content;

            // Update time
            const timeSpan = commentItem.querySelector('.comment-time');
            timeSpan.textContent = data.comment.time_ago;

            showNotification('Comment updated successfully!', 'success');
        } else {
            alert('Error updating comment: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating comment. Please try again.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';
    });
}

function cancelEdit(commentId, originalText) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const commentText = commentItem.querySelector('.comment-text');
    commentText.innerHTML = originalText;
}

function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
        return;
    }

    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const deleteBtn = commentItem.querySelector('.delete-btn');

    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

    fetch(`/user/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove comment from DOM
            commentItem.remove();

            // Update comment count
            const currentCount = parseInt(document.getElementById('commentsCount').textContent);
            const newCount = Math.max(0, currentCount - 1);
            document.getElementById('commentsCount').textContent = newCount;

            // Show no comments message if no comments left
            if (newCount === 0) {
                document.getElementById('commentsList').innerHTML = `
                    <div class="no-comments">
                        <i class="fas fa-comments"></i>
                        <p>No comments yet. Be the first to share your thoughts!</p>
                    </div>
                `;
            }

            showNotification('Comment deleted successfully!', 'success');
        } else {
            alert('Error deleting comment: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting comment. Please try again.');
    })
    .finally(() => {
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>
@endsection

@section('styles')
<style>
.public-content {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem 0;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
}

.breadcrumb {
    margin-bottom: 2rem;
}

.breadcrumb a {
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.breadcrumb a:hover {
    background: rgba(255, 255, 255, 0.2);
}

.content-article {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.article-header {
    padding: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.article-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.badge {
    background: #3b82f6;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.date {
    color: #6b7280;
    font-size: 0.875rem;
}

.article-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.article-author {
    color: #6b7280;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.article-media {
    padding: 0 2rem;
}

.media-item {
    margin-bottom: 2rem;
}

.media-item h4 {
    color: #1f2937;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.article-image {
    width: 100%;
    height: auto;
    max-height: 500px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    object-fit: contain;
    object-position: center;
    background: #f8fafc;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.article-image:hover {
    transform: scale(1.02);
}

.video-container {
    position: relative;
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.article-video {
    width: 100%;
    height: auto;
    min-height: 300px;
    max-height: 500px;
    display: block;
    background: #000;
}

.article-video:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

.file-download {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.file-info > i {
    font-size: 2.5rem;
    color: #10b981;
}

.file-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.btn-download {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #10b981;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.btn-download:hover {
    background: #059669;
}

.article-content {
    padding: 2rem;
    font-size: 1.1rem;
    line-height: 1.7;
    color: #374151;
}

@media (max-width: 768px) {
    .article-title {
        font-size: 1.5rem;
    }

    .article-header,
    .article-media,
    .article-content {
        padding: 1.5rem;
    }

    .file-info {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .article-video {
        min-height: 200px;
    }

    .article-image {
        max-height: 300px;
    }
}

/* Image Modal Styles */
.image-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    cursor: pointer;
}

.image-modal img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

.image-modal .close {
    position: absolute;
    top: 20px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.image-modal .close:hover {
    color: #bbb;
}

.image-error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    color: #991b1b;
}

.image-error i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
}

.image-error p {
    margin: 0;
    font-weight: 500;
}

.video-error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    color: #991b1b;
}

.video-error i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
}

.video-error p {
    margin: 0 0 1rem 0;
    font-weight: 500;
}

.video-error small {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.75rem;
    opacity: 0.8;
}

/* Comments Section Styles */
.comments-section {
    margin-top: 3rem;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.comments-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 2rem;
    text-align: center;
}

.comments-header h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.comments-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1rem;
}

.comment-form-container {
    padding: 2rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
}

.comment-form .user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.user-avatar {
    width: 48px;
    height: 48px;
    background: #e5e7eb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-size: 1.5rem;
}

.user-details .user-name {
    display: block;
    font-weight: 600;
    color: #1f2937;
    font-size: 1rem;
}

.user-details .user-meta {
    display: block;
    color: #6b7280;
    font-size: 0.875rem;
}

.comment-input-group textarea {
    width: 100%;
    min-height: 100px;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    resize: vertical;
    transition: border-color 0.2s ease;
    font-family: inherit;
    line-height: 1.5;
}

.comment-input-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.comment-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

.char-count {
    color: #6b7280;
    font-size: 0.875rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-primary:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.comments-list {
    padding: 2rem;
}

.comments-count h4 {
    margin: 0 0 2rem 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.comment-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.comment-item.reply {
    margin-left: 3rem;
    margin-top: 1rem;
    padding-left: 1rem;
    border-left: 3px solid #e5e7eb;
}

.comment-item .comment-avatar {
    width: 40px;
    height: 40px;
    background: #e5e7eb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.comment-content {
    flex: 1;
    min-width: 0;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.comment-author {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.875rem;
}

.comment-meta {
    background: #e5e7eb;
    color: #6b7280;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.comment-time {
    color: #9ca3af;
    font-size: 0.75rem;
}

.comment-text {
    color: #374151;
    font-size: 0.875rem;
    line-height: 1.6;
    margin-bottom: 1rem;
    word-wrap: break-word;
}

.comment-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.comment-actions button {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.comment-actions button:hover {
    background: #f3f4f6;
    color: #374151;
}

.comment-actions .delete-btn:hover {
    color: #ef4444;
    background: #fef2f2;
}

.replies-list {
    margin-top: 1rem;
}

.no-comments {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.no-comments i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    opacity: 0.5;
}

.no-comments p {
    margin: 0;
    font-size: 1rem;
}

.login-prompt {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.login-prompt i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    color: #3b82f6;
}

.login-prompt h3 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 1.5rem;
}

.login-prompt p {
    margin: 0;
    font-size: 1rem;
}

.login-prompt a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.login-prompt a:hover {
    text-decoration: underline;
}

/* Reply form styles */
.reply-form {
    margin-top: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.reply-form textarea {
    width: 100%;
    min-height: 80px;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    resize: vertical;
    transition: border-color 0.2s ease;
    font-family: inherit;
}

.reply-form textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.reply-form .comment-actions {
    margin-top: 0.75rem;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .comments-section {
        margin-top: 2rem;
        border-radius: 12px;
    }

    .comments-header {
        padding: 1.5rem;
    }

    .comment-form-container {
        padding: 1.5rem;
    }

    .comments-list {
        padding: 1.5rem;
    }

    .comment-item.reply {
        margin-left: 1.5rem;
    }

    .comment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .comment-actions {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}
</style>
@endsection


