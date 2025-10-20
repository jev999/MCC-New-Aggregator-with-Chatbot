<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $counts = [
            'announcements' => Announcement::count(),
            'events' => Event::count(),
            'news' => News::count(),
            'faculty' => User::where('role', 'faculty')->count(),
            'students' => User::where('role', 'student')->count(),
            'admins' => Admin::where('role', 'admin')->count(),
            'super_admins' => Admin::where('role', 'super_admin')->count(),
            'department_admins' => Admin::where('role', 'department_admin')->count(),
            'total_admins' => Admin::count(),
        ];

        // Generate chart data for the last 7 days
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(Carbon::now()->subDays($i));
        }

        $chartData = [
            'labels' => $dates->map(function($date) {
                return $date->format('M d');
            })->toArray(),
            'announcements' => $dates->map(function($date) {
                return Announcement::whereDate('created_at', $date)->count();
            })->toArray(),
            'events' => $dates->map(function($date) {
                return Event::whereDate('created_at', $date)->count();
            })->toArray(),
            'news' => $dates->map(function($date) {
                return News::whereDate('created_at', $date)->count();
            })->toArray(),
            'students' => $dates->map(function($date) {
                return User::where('role', 'student')->whereDate('created_at', $date)->count();
            })->toArray(),
            'faculty' => $dates->map(function($date) {
                return User::where('role', 'faculty')->whereDate('created_at', $date)->count();
            })->toArray(),
            'admins' => $dates->map(function($date) {
                return Admin::whereDate('created_at', $date)->count();
            })->toArray(),
        ];

        // Recent activities
        $recentActivities = [
            'announcements' => Announcement::with('admin')->latest()->take(5)->get(),
            'events' => Event::with('admin')->latest()->take(5)->get(),
            'news' => News::with('admin')->latest()->take(5)->get(),
            'admins' => Admin::latest()->take(5)->get(),
        ];

        // System statistics
        $systemStats = [
            'total_content' => $counts['announcements'] + $counts['events'] + $counts['news'],
            'total_users' => $counts['faculty'] + $counts['students'],
            'content_this_month' => Announcement::whereMonth('created_at', Carbon::now()->month)->count() +
                                   Event::whereMonth('created_at', Carbon::now()->month)->count() +
                                   News::whereMonth('created_at', Carbon::now()->month)->count(),
            'users_this_month' => User::whereMonth('created_at', Carbon::now()->month)->count(),
        ];

        return view('superadmin.dashboard', compact('counts', 'chartData', 'recentActivities', 'systemStats'));
    }
}
