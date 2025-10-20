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
                                         ->where('target_office', $office)
                                         ->count(),
            'events' => Event::where('is_published', true)
                            ->where('target_office', $office)
                            ->count(),
            'news' => News::where('is_published', true)
                         ->where('target_office', $office)
                         ->count(),
            'my_announcements' => Announcement::where('target_office', $office)->count(),
            'my_events' => Event::where('target_office', $office)->count(),
            'my_news' => News::where('target_office', $office)->count(),
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
            'announcements' => $dates->map(function($date) use ($office) {
                return Announcement::where('is_published', true)
                                  ->where('target_office', $office)
                                  ->whereDate('created_at', $date)
                                  ->count();
            })->toArray(),
            'events' => $dates->map(function($date) use ($office) {
                return Event::where('is_published', true)
                           ->where('target_office', $office)
                           ->whereDate('created_at', $date)
                           ->count();
            })->toArray(),
            'news' => $dates->map(function($date) use ($office) {
                return News::where('is_published', true)
                          ->where('target_office', $office)
                          ->whereDate('created_at', $date)
                          ->count();
            })->toArray(),
        ];

        // Recent activities created by this office admin only
        $recentActivities = [
            'announcements' => Announcement::where('is_published', true)
                                         ->where('target_office', $office)
                                         ->with('admin')
                                         ->latest()
                                         ->take(5)
                                         ->get(),
            'events' => Event::where('is_published', true)
                            ->where('target_office', $office)
                            ->with('admin')
                            ->latest()
                            ->take(5)
                            ->get(),
            'news' => News::where('is_published', true)
                          ->where('target_office', $office)
                          ->with('admin')
                          ->latest()
                          ->take(5)
                          ->get(),
        ];

        // Office statistics
        $officeStats = [
            'total_content' => $counts['announcements'] + $counts['events'] + $counts['news'],
            'my_content' => $counts['my_announcements'] + $counts['my_events'] + $counts['my_news'],
            'content_this_month' => Announcement::where('is_published', true)
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
            'my_content_this_month' => Announcement::where('admin_id', $admin->id)
                                                   ->whereMonth('created_at', Carbon::now()->month)
                                                   ->count() +
                                      Event::where('admin_id', $admin->id)
                                           ->whereMonth('created_at', Carbon::now()->month)
                                           ->count() +
                                      News::where('admin_id', $admin->id)
                                           ->whereMonth('created_at', Carbon::now()->month)
                                           ->count(),
            'total_users' => $counts['total_students'] + $counts['total_faculty'],
        ];

        return view('office-admin.dashboard', compact('counts', 'chartData', 'recentActivities', 'officeStats', 'admin', 'office'));
    }
}
