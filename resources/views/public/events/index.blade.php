@extends('layouts.public')

@section('title', 'Events - MCC Portal')

@section('content')
<div class="public-content">
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> Campus Events</h1>
            <p>Discover upcoming events and activities at Madridejos Community College</p>
        </div>

        <div class="content-grid">
            @forelse($events as $event)
                <div class="content-card">
                    @if($event->image)
                        <div class="card-image">
                            <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" loading="lazy">
                        </div>
                    @endif
                    
                    <div class="card-content">
                        <div class="card-meta">
                            <span class="date">{{ $event->event_date->format('M d, Y') }}</span>
                            <span class="badge event-badge">Event</span>
                        </div>
                        <h3 class="card-title">{{ $event->title }}</h3>
                        <p class="card-excerpt">{{ Str::limit($event->description, 150) }}</p>
                        
                        @if($event->location)
                            <div class="location">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $event->location }}
                            </div>
                        @endif
                        
                        <div class="event-time">
                            <i class="fas fa-clock"></i>
                            {{ $event->event_date->format('g:i A') }}
                        </div>
                        
                        <div class="media-indicators">
                            @if($event->image)
                                <span class="media-indicator"><i class="fas fa-image"></i></span>
                            @endif
                            @if($event->video)
                                <span class="media-indicator"><i class="fas fa-video"></i></span>
                            @endif
                            @if($event->csv_file)
                                <span class="media-indicator"><i class="fas fa-file-csv"></i></span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <a href="{{ route('public.events.show', $event) }}" class="btn btn-primary">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>No Events Available</h3>
                    <p>There are currently no published events.</p>
                </div>
            @endforelse
        </div>

        @if($events->hasPages())
            <div class="pagination-wrapper">
                {{ $events->links() }}
            </div>
        @endif
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
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
    color: white;
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.page-header p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.content-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.content-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.card-image {
    height: 200px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.content-card:hover .card-image img {
    transform: scale(1.05);
}

.card-content {
    padding: 1.5rem;
}

.card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.date {
    color: #6b7280;
    font-size: 0.875rem;
}

.event-badge {
    background: #f59e0b;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #1f2937;
    line-height: 1.4;
}

.card-excerpt {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.location, .event-time {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.location i, .event-time i {
    color: #f59e0b;
}

.media-indicators {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.media-indicator {
    background: #f3f4f6;
    color: #6b7280;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
}

.card-footer {
    padding: 0 1.5rem 1.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #f59e0b;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.btn:hover {
    background: #d97706;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: white;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.7;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
}
</style>
@endsection
