<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeAdminDashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $office = $admin->office;

        // Get counts for content created by this office admin only
        $counts = [
            'announcements' => Announcement::where('is_published', true)
                                         ->where('admin_id', $admin->id)
                                         ->count(),
            'events' => Event::where('is_published', true)
                            ->where('admin_id', $admin->id)
                            ->count(),
            'news' => News::where('is_published', true)
                         ->where('admin_id', $admin->id)
                         ->count(),
            'draft_announcements' => Announcement::where('is_published', false)
                                                ->where('admin_id', $admin->id)
                                                ->count(),
            'draft_events' => Event::where('is_published', false)
                                   ->where('admin_id', $admin->id)
                                   ->count(),
            'draft_news' => News::where('is_published', false)
                               ->where('admin_id', $admin->id)
                               ->count(),
            'total_announcements' => Announcement::where('admin_id', $admin->id)->count(),
            'total_events' => Event::where('admin_id', $admin->id)->count(),
            'total_news' => News::where('admin_id', $admin->id)->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_faculty' => User::where('role', 'faculty')->count(),
        ];

        // For NSTP office, add specific 1st year student count
        if ($office === 'NSTP') {
            $counts['first_year_students'] = User::where('role', 'student')
                                                ->where('year_level', '1st Year')
                                                ->count();
        }

        // Generate chart data for the last 7 days
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(Carbon::now()->subDays($i));
        }

        $chartData = [
            'labels' => $dates->map(function($date) {
                return $date->format('M d');
            })->toArray(),
            'announcements' => $dates->map(function($date) use ($admin) {
                return Announcement::where('is_published', true)
                                  ->where('admin_id', $admin->id)
                                  ->whereDate('created_at', $date)
                                  ->count();
            })->toArray(),
            'events' => $dates->map(function($date) use ($admin) {
                return Event::where('is_published', true)
                           ->where('admin_id', $admin->id)
                           ->whereDate('created_at', $date)
                           ->count();
            })->toArray(),
            'news' => $dates->map(function($date) use ($admin) {
                return News::where('is_published', true)
                          ->where('admin_id', $admin->id)
                          ->whereDate('created_at', $date)
                          ->count();
            })->toArray(),
        ];

        // Recent activities created by this office admin only
        $recentActivities = [
            'announcements' => Announcement::where('admin_id', $admin->id)
                                         ->with('admin')
                                         ->latest()
                                         ->take(5)
                                         ->get(),
            'events' => Event::where('admin_id', $admin->id)
                            ->with('admin')
                            ->latest()
                            ->take(5)
                            ->get(),
            'news' => News::where('admin_id', $admin->id)
                          ->with('admin')
                          ->latest()
                          ->take(5)
                          ->get(),
        ];

        // Office statistics
        $officeStats = [
            'total_content' => $counts['total_announcements'] + $counts['total_events'] + $counts['total_news'],
            'published_content' => $counts['announcements'] + $counts['events'] + $counts['news'],
            'draft_content' => $counts['draft_announcements'] + $counts['draft_events'] + $counts['draft_news'],
            'content_this_month' => Announcement::where('admin_id', $admin->id)
                                                ->whereMonth('created_at', Carbon::now()->month)
                                                ->count() +
                                   Event::where('admin_id', $admin->id)
                                        ->whereMonth('created_at', Carbon::now()->month)
                                        ->count() +
                                   News::where('admin_id', $admin->id)
                                        ->whereMonth('created_at', Carbon::now()->month)
                                        ->count(),
            'published_this_month' => Announcement::where('is_published', true)
                                                   ->where('admin_id', $admin->id)
                                                   ->whereMonth('created_at', Carbon::now()->month)
                                                   ->count() +
                                     Event::where('is_published', true)
                                          ->where('admin_id', $admin->id)
                                          ->whereMonth('created_at', Carbon::now()->month)
                                          ->count() +
                                     News::where('is_published', true)
                                          ->where('admin_id', $admin->id)
                                          ->whereMonth('created_at', Carbon::now()->month)
                                          ->count(),
            'total_users' => $counts['total_students'] + $counts['total_faculty'],
        ];

        return view('office-admin.dashboard', compact('counts', 'chartData', 'recentActivities', 'officeStats', 'admin', 'office'));
    }
}
