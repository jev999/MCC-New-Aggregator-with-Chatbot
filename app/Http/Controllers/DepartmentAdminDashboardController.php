<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DepartmentAdminDashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        // Get counts for content created by this department admin only
        // Department admin dashboard shows only their own content
        
        // Department name mapping for student/faculty matching
        $departmentMap = [
            'BSIT' => 'Bachelor of Science in Information Technology',
            'BSBA' => 'Bachelor of Science in Business Administration',
            'EDUC' => 'College of Education',
            'BSHM' => 'Bachelor of Science in Hospitality Management',
            'BSED' => 'Bachelor of Secondary Education',
        ];
        
        $fullDepartmentName = $departmentMap[$admin->department] ?? $admin->department;
        
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
            'my_announcements' => Announcement::where('admin_id', $admin->id)->count(),
            'my_events' => Event::where('admin_id', $admin->id)->count(),
            'my_news' => News::where('admin_id', $admin->id)->count(),
            'department_students' => User::where('role', 'student')
                                        ->where(function($query) use ($admin, $fullDepartmentName) {
                                            $query->where('department', $admin->department)
                                                  ->orWhere('department', $fullDepartmentName);
                                        })
                                        ->count(),
            'department_faculty' => User::where('role', 'faculty')
                                       ->where(function($query) use ($admin, $fullDepartmentName) {
                                           $query->where('department', $admin->department)
                                                 ->orWhere('department', $fullDepartmentName);
                                       })
                                       ->count(),
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

        // Recent activities created by this department admin only
        $recentActivities = [
            'announcements' => Announcement::where('is_published', true)
                                         ->where('admin_id', $admin->id)
                                         ->with('admin')
                                         ->latest()
                                         ->take(5)
                                         ->get(),
            'events' => Event::where('is_published', true)
                            ->where('admin_id', $admin->id)
                            ->with('admin')
                            ->latest()
                            ->take(5)
                            ->get(),
            'news' => News::where('is_published', true)
                          ->where('admin_id', $admin->id)
                          ->with('admin')
                          ->latest()
                          ->take(5)
                          ->get(),
        ];

        // Department statistics
        $departmentStats = [
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
            'department_users' => $counts['department_students'] + $counts['department_faculty'],
        ];

        return view('department-admin.dashboard', compact('counts', 'chartData', 'recentActivities', 'departmentStats', 'admin'));
    }
}
