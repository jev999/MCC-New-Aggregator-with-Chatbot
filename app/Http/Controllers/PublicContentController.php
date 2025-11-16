<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use Illuminate\Http\Request;

class PublicContentController extends Controller
{
    /**
     * Display a listing of published announcements
     */
    public function announcements()
    {
        $user = auth()->user();
        $userDepartment = $user ? $user->department : null;

        $query = Announcement::where('is_published', true)->with('admin');

        // If user is logged in, filter by full user visibility (department + NSTP 1st-year rule)
        if ($user) {
            $query->visibleToUser($user);
        } else {
            // For non-logged in users, show only 'all' visibility
            $query->where('visibility_scope', 'all');
        }

        $announcements = $query->latest()->paginate(12);

        return view('public.announcements.index', compact('announcements'));
    }

    /**
     * Display a specific published announcement
     */
    public function showAnnouncement(Announcement $announcement)
    {
        // Only show published announcements
        if (!$announcement->is_published) {
            abort(404);
        }

        // Check if user can view this announcement based on visibility rules (department + NSTP 1st-year)
        $user = auth()->user();

        if ($user) {
            if (!$announcement->isVisibleToUser($user)) {
                abort(404);
            }
        } else {
            // For non-logged in users, only show 'all' visibility announcements
            if ($announcement->visibility_scope !== 'all') {
                abort(404);
            }
        }

        // Load admin and comments relationships for display
        $announcement->load(['admin', 'comments.user', 'comments.replies.user']);

        return view('public.announcements.show', compact('announcement'));
    }

    public function shareAnnouncement(string $token)
    {
        $announcement = Announcement::where('share_token', $token)
            ->with('admin')
            ->firstOrFail();

        if (!$announcement->is_published) {
            abort(404);
        }

        if ($announcement->visibility_scope !== 'all' && $announcement->visibility_scope !== null && $announcement->visibility_scope !== '') {
            abort(404);
        }

        $announcement->load(['comments.user', 'comments.replies.user']);

        return view('public.announcements.show', compact('announcement'));
    }

    /**
     * Display a listing of published events
     */
    public function events()
    {
        $user = auth()->user();
        $userDepartment = $user ? $user->department : null;

        $query = Event::where('is_published', true)->with('admin');

        // If user is logged in, filter by full user visibility (department + NSTP 1st-year rule)
        if ($user) {
            $query->visibleToUser($user);
        } else {
            // For non-logged in users, show only 'all' visibility
            $query->where('visibility_scope', 'all');
        }

        $events = $query->orderBy('event_date', 'asc')->paginate(12);

        return view('public.events.index', compact('events'));
    }

    /**
     * Display a specific published event
     */
    public function showEvent(Event $event)
    {
        // Only show published events
        if (!$event->is_published) {
            abort(404);
        }

        // Check if user can view this event based on visibility rules (department + NSTP 1st-year)
        $user = auth()->user();

        if ($user) {
            if (!$event->isVisibleToUser($user)) {
                abort(404);
            }
        } else {
            // For non-logged in users, only show 'all' visibility events
            if ($event->visibility_scope !== 'all') {
                abort(404);
            }
        }

        // Load admin and comments relationships for display
        $event->load(['admin', 'comments.user', 'comments.replies.user']);

        return view('public.events.show', compact('event'));
    }

    public function shareEvent(string $token)
    {
        $event = Event::where('share_token', $token)
            ->with('admin')
            ->firstOrFail();

        if (!$event->is_published) {
            abort(404);
        }

        if ($event->visibility_scope !== 'all' && $event->visibility_scope !== null && $event->visibility_scope !== '') {
            abort(404);
        }

        $event->load(['comments.user', 'comments.replies.user']);

        return view('public.events.show', compact('event'));
    }

    /**
     * Display a listing of published news
     */
    public function news()
    {
        $user = auth()->user();
        $userDepartment = $user ? $user->department : null;

        $query = News::where('is_published', true)->with('admin');

        // If user is logged in, filter by full user visibility (department + NSTP 1st-year rule)
        if ($user) {
            $query->visibleToUser($user);
        } else {
            // For non-logged in users, show only 'all' visibility
            $query->where('visibility_scope', 'all');
        }

        $news = $query->latest()->paginate(12);

        return view('public.news.index', compact('news'));
    }

    /**
     * Display a specific published news article
     */
    public function showNews(News $news)
    {
        // Only show published news
        if (!$news->is_published) {
            abort(404);
        }

        // Check if user can view this news based on visibility rules (department + NSTP 1st-year)
        $user = auth()->user();

        if ($user) {
            if (!$news->isVisibleToUser($user)) {
                abort(404);
            }
        } else {
            // For non-logged in users, only show 'all' visibility news
            if ($news->visibility_scope !== 'all') {
                abort(404);
            }
        }

        // Load admin and comments relationships for display
        $news->load(['admin', 'comments.user', 'comments.replies.user']);

        return view('public.news.show', compact('news'));
    }

    public function shareNews(string $token)
    {
        $news = News::where('share_token', $token)
            ->with('admin')
            ->firstOrFail();

        if (!$news->is_published) {
            abort(404);
        }

        if ($news->visibility_scope !== 'all' && $news->visibility_scope !== null && $news->visibility_scope !== '') {
            abort(404);
        }

        $news->load(['comments.user', 'comments.replies.user']);

        return view('public.news.show', compact('news'));
    }
}
