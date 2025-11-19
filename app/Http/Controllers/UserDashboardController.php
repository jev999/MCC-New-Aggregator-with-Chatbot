<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use App\Services\NotificationService;
use Illuminate\Support\Str;

class UserDashboardController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        $search = trim((string) $request->input('search'));

        // Base queries: published + visible to user
        $announcementQuery = Announcement::where('is_published', true)
            ->visibleToUser($user)
            ->with('admin');

        $eventQuery = Event::where('is_published', true)
            ->visibleToUser($user)
            ->with('admin')
            ->where(function ($query) {
                // Show upcoming events and events from the last 90 days
                $query->where('event_date', '>=', now()->subDays(90))
                      ->orWhereNull('event_date'); // Include TBD events
            });

        $newsQuery = News::where('is_published', true)
            ->visibleToUser($user)
            ->with('admin');

        // Apply search filter across title and main text fields if provided
        if ($search !== '') {
            $searchLower = Str::lower($search);

            // When the user types a category name like "events" or "news",
            // keep that section visible even if the word is not in the title/content.
            $searchAnnouncementsByCategory = in_array($searchLower, ['announcement', 'announcements'], true);
            $searchEventsByCategory = in_array($searchLower, ['event', 'events'], true);
            $searchNewsByCategory = in_array($searchLower, ['news'], true);

            // Announcements: filter by text unless searching by category word
            if (! $searchAnnouncementsByCategory) {
                $announcementQuery->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                          ->orWhere('content', 'LIKE', "%{$search}%");
                });
            }
            // Prefer titles that start with the search term
            $announcementQuery->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["{$search}%"]);

            // Events: filter by text unless searching by category word
            if (! $searchEventsByCategory) {
                $eventQuery->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                          ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }
            $eventQuery->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["{$search}%"]);

            // News: filter by text unless searching by category word
            if (! $searchNewsByCategory) {
                $newsQuery->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                          ->orWhere('content', 'LIKE', "%{$search}%");
                });
            }
            $newsQuery->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["{$search}%"]);
        }

        $announcements = $announcementQuery->latest()->get();

        $events = $eventQuery
            ->orderByRaw('CASE WHEN event_date IS NULL THEN 1 ELSE 0 END') // TBD events last
            ->orderBy('event_date', 'asc')
            ->get();

        $news = $newsQuery->latest()->get();

        // Get statistics for the hero section (content visible to this user)
        $totalAnnouncements = Announcement::where('is_published', true)
            ->visibleToUser($user)
            ->count();

        $totalEvents = Event::where('is_published', true)
            ->visibleToUser($user)
            ->count();

        $totalNews = News::where('is_published', true)
            ->visibleToUser($user)
            ->count();

        // Get notification data for the current user
        $unreadNotificationsCount = $this->notificationService->getUnreadCount($user);
        $recentNotifications = $this->notificationService->getRecentNotifications($user, 5);

        return view('user.dashboard', compact(
            'announcements',
            'events',
            'news',
            'totalAnnouncements',
            'totalEvents',
            'totalNews',
            'unreadNotificationsCount',
            'recentNotifications',
            'search'
        ));
    }

    /**
     * AJAX endpoint for live search suggestions on the user dashboard.
     */
    public function ajaxSearch(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $term = trim((string) $request->input('query'));

        if ($term === '') {
            return response()->json([]);
        }

        $results = [];

        // Limit to a handful of suggestions per type, only published and visible to this user
        // Order so titles that start with the term appear first
        $announce = Announcement::where('is_published', true)
            ->visibleToUser($user)
            ->where('title', 'LIKE', "%{$term}%")
            ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["{$term}%"])
            ->latest()
            ->take(5)
            ->get(['id', 'title']);

        $event = Event::where('is_published', true)
            ->visibleToUser($user)
            ->where('title', 'LIKE', "%{$term}%")
            ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["{$term}%"])
            ->latest()
            ->take(5)
            ->get(['id', 'title']);

        $newz = News::where('is_published', true)
            ->visibleToUser($user)
            ->where('title', 'LIKE', "%{$term}%")
            ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["{$term}%"])
            ->latest()
            ->take(5)
            ->get(['id', 'title']);

        foreach ($announce as $a) {
            $results[] = [
                'type' => 'Announcement',
                'title' => $a->title,
                'id' => $a->id,
            ];
        }

        foreach ($event as $e) {
            $results[] = [
                'type' => 'Event',
                'title' => $e->title,
                'id' => $e->id,
            ];
        }

        foreach ($newz as $n) {
            $results[] = [
                'type' => 'News',
                'title' => $n->title,
                'id' => $n->id,
            ];
        }

        return response()->json($results);
    }

    /**
     * Get a specific announcement for notification click.
     */
    public function getAnnouncement($id)
    {
        $user = auth()->user();
        $announcement = Announcement::where('id', $id)
            ->where('is_published', true)
            ->visibleToUser($user)
            ->with('admin')
            ->first();

        if (!$announcement) {
            return response()->json([
                'success' => false,
                'error' => 'Announcement not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'content' => $announcement
        ]);
    }

    /**
     * Get a specific event for notification click.
     */
    public function getEvent($id)
    {
        $user = auth()->user();
        $event = Event::where('id', $id)
            ->where('is_published', true)
            ->visibleToUser($user)
            ->with('admin')
            ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'error' => 'Event not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'content' => $event
        ]);
    }

    /**
     * Get a specific news article for notification click.
     */
    public function getNews($id)
    {
        $user = auth()->user();
        $news = News::where('id', $id)
            ->where('is_published', true)
            ->visibleToUser($user)
            ->with('admin')
            ->first();

        if (!$news) {
            return response()->json([
                'success' => false,
                'error' => 'News article not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'content' => $news
        ]);
    }
}
