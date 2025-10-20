@extends('layouts.public')

@section('title', $event->title . ' - MCC Portal')

@section('content')
<div class="public-content">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('user.dashboard') }}"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <article class="content-article">
            <header class="article-header">
                <div class="article-meta">
                    <span class="badge">Event</span>
                    <span class="date">
                        @if($event->event_date)
                            {{ $event->event_date->format('F d, Y g:i A') }}
                        @else
                            Date TBD
                        @endif
                    </span>
                </div>
                <h1 class="article-title">{{ $event->title }}</h1>
                <div class="event-details">
                    @if($event->location)
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $event->location }}</span>
                        </div>
                    @endif
                    <div class="detail-item">
                        <i class="fas fa-user-tie"></i>
                        <span>
                            @if($event->admin->role === 'superadmin')
                                Organized by MCC Administration
                            @elseif($event->admin->role === 'department_admin')
                                Organized by {{ $event->admin->department_display }} Department - {{ $event->admin->username }}
                            @elseif($event->admin->role === 'office_admin')
                                Organized by {{ $event->admin->office_display }} - {{ $event->admin->username }}
                            @else
                                Organized by {{ $event->admin->username }}
                            @endif
                        </span>
                    </div>
                </div>
            </header>

            <div class="article-media">
                @if($event->image)
                    <div class="media-item">
                        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="article-image">
                    </div>
                @endif

                @if($event->video)
                    <div class="media-item">
                        <h4><i class="fas fa-video"></i> Event Video</h4>
                        <video controls class="article-video" preload="metadata">
                            <source src="{{ asset('storage/' . $event->video) }}" type="video/mp4">
                            <source src="{{ asset('storage/' . $event->video) }}" type="video/webm">
                            <source src="{{ asset('storage/' . $event->video) }}" type="video/ogg">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @endif

                @if($event->csv_file)
                    <div class="media-item">
                        <h4><i class="fas fa-file-csv"></i> Event Resources</h4>
                        <div class="file-download">
                            <div class="file-info">
                                <i class="fas fa-file-csv"></i>
                                <div>
                                    <p class="file-name">{{ basename($event->csv_file) }}</p>
                                    <a href="{{ asset('storage/' . $event->csv_file) }}" download class="btn btn-download">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="article-content">
                <h3>Event Description</h3>
                {!! nl2br(e($event->description)) !!}
            </div>
        </article>

        <!-- Comments Section -->
        @auth
        <section class="comments-section">
            <div class="comments-header">
                <h3><i class="fas fa-comments"></i> Comments & Feedback</h3>
                <p>Share your thoughts and questions about this event</p>
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
                                  placeholder="Write your comment or feedback about this event..."
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
                    <h4><span id="commentsCount">{{ $event->comments->count() }}</span> Comments</h4>
                </div>

                <div id="commentsList">
                    @forelse($event->comments as $comment)
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
@endsection

@section('styles')
<style>
.public-content {
    min-height: 100vh;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
    background: #f59e0b;
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

.event-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.detail-item i {
    color: #f59e0b;
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
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.article-video {
    width: 100%;
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
}

.article-content h3 {
    color: #1f2937;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.article-content {
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
    
    .event-details {
        gap: 0.75rem;
    }
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
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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
    background: #f59e0b;
    color: white;
}

.btn-primary:hover {
    background: #d97706;
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
    color: #f59e0b;
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
    color: #f59e0b;
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
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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

@section('scripts')
<script>
// Comment System JavaScript for Events
document.addEventListener('DOMContentLoaded', function() {
    const commentInput = document.getElementById('commentInput');
    const submitBtn = document.getElementById('submitComment');
    const charCount = document.querySelector('.char-count');

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
    const submitBtn = parentId ?
        document.querySelector(`[data-parent-id="${parentId}"] .btn-primary`) :
        document.getElementById('submitComment');

    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';

    // Submit comment via AJAX
    fetch('/user/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            content: content,
            commentable_type: 'event',
            commentable_id: {{ $event->id }},
            parent_id: parentId
        })
    })
    .then(response => response.json())
    .then(data => {
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
            alert('Error posting comment: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error posting comment. Please try again.');
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
</script>
