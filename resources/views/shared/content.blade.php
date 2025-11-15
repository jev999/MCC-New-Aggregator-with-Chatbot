@extends('layouts.public')

@section('title', ($content->title ?? 'Shared Content') . ' - MCC Portal')

@section('content')
<div class="public-content">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('welcome') }}"><i class="fas fa-home"></i> Back to Home</a>
            <span style="color: white; margin: 0 0.5rem;">|</span>
            <span style="color: rgba(255,255,255,0.8); font-size: 0.875rem;">
                <i class="fas fa-share-alt"></i> Shared Content
            </span>
        </div>

        <article class="content-article">
            <header class="article-header">
                <div class="article-meta">
                    <span class="badge @if($contentType === 'announcement') announcement-badge @elseif($contentType === 'event') event-badge @else news-badge @endif">
                        @if($contentType === 'announcement')
                            <i class="fas fa-bullhorn"></i> Announcement
                        @elseif($contentType === 'event')
                            <i class="fas fa-calendar-alt"></i> Event
                        @else
                            <i class="fas fa-newspaper"></i> News
                        @endif
                    </span>
                    <span class="date">
                        @if($contentType === 'event' && $content->event_date)
                            {{ $content->event_date->format('F d, Y') }}
                            @if($content->event_time)
                                {{ $content->event_time }}
                            @endif
                        @else
                            {{ $content->created_at->format('F d, Y g:i A') }}
                        @endif
                    </span>
                </div>
                <h1 class="article-title">{{ $content->title }}</h1>
                <div class="article-author">
                    <i class="fas fa-user-shield"></i>
                    @if($content->admin->role === 'superadmin')
                        Published by MCC Administration
                    @elseif($content->admin->role === 'department_admin')
                        Published by {{ $content->admin->department_display ?? $content->admin->department }} Department - {{ $content->admin->username }}
                    @elseif($content->admin->role === 'office_admin')
                        Published by {{ $content->admin->office_display ?? $content->admin->office }} - {{ $content->admin->username }}
                    @else
                        Published by {{ $content->admin->username }}
                    @endif
                </div>
                @if($contentType === 'event' && $content->location)
                <div class="event-location" style="margin-top: 1rem; color: #10b981; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-map-marker-alt"></i>
                    <strong>Location:</strong> {{ $content->location }}
                </div>
                @endif
            </header>

            <div class="article-media">
                @php
                    $allImageUrls = $content->allImageUrls ?? [];
                    $allVideoUrls = $content->allVideoUrls ?? [];
                    $hasMedia = $content->hasMedia ?? 'none';
                @endphp

                @if($hasMedia === 'image' || $hasMedia === 'both')
                    @foreach($allImageUrls as $imageUrl)
                        <div class="media-item">
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $content->title }}" 
                                 class="article-image">
                        </div>
                    @endforeach
                @endif

                @if($hasMedia === 'video' || $hasMedia === 'both')
                    @foreach($allVideoUrls as $videoUrl)
                        <div class="media-item">
                            <h4><i class="fas fa-video"></i> Video</h4>
                            <div class="video-container">
                                <video controls class="article-video" preload="metadata" playsinline>
                                    <source src="{{ $videoUrl }}" type="video/mp4">
                                    <source src="{{ $videoUrl }}" type="video/webm">
                                    <p>Your browser does not support the video tag. <a href="{{ $videoUrl }}" target="_blank" download>Download the video</a> to watch it.</p>
                                </video>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($contentType === 'announcement' && $content->csv_path)
                    <div class="media-item">
                        <h4><i class="fas fa-file-csv"></i> Downloadable File</h4>
                        <div class="file-download">
                            <div class="file-info">
                                <i class="fas fa-file-csv"></i>
                                <div>
                                    <p class="file-name">{{ basename($content->csv_path) }}</p>
                                    <a href="{{ storage_asset($content->csv_path) }}" download class="btn btn-download">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="article-content">
                @if($contentType === 'announcement')
                    {!! nl2br(e($content->content)) !!}
                @elseif($contentType === 'event')
                    {!! nl2br(e($content->description)) !!}
                @else
                    {!! nl2br(e($content->content)) !!}
                @endif
            </div>
        </article>

        <div class="share-info" style="margin-top: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.95); border-radius: 12px; text-align: center; color: #6b7280;">
            <i class="fas fa-link" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; color: #3b82f6;"></i>
            <p style="margin: 0; font-size: 0.875rem;">
                This content was shared via a secure link.
                @if($shareableLink->expires_at)
                    Link expires: {{ $shareableLink->expires_at->format('M d, Y') }}
                @else
                    This link does not expire.
                @endif
            </p>
        </div>
    </div>
</div>
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
    flex-wrap: wrap;
}

.badge {
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.announcement-badge {
    background: #3b82f6;
}

.event-badge {
    background: #10b981;
}

.news-badge {
    background: #ef4444;
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

    .article-video {
        min-height: 200px;
    }

    .article-image {
        max-height: 300px;
    }
}
</style>
@endsection

