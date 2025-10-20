<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $counts = [
            'announcements' => Announcement::count(),
            'events' => Event::count(),
            'news' => News::count(),
            'faculty' => User::where('role', 'faculty')->count(),
            'students' => User::where('role', 'student')->count(),
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
        ];

        return view('admin.dashboard', compact('counts', 'chartData'));
    }
}
