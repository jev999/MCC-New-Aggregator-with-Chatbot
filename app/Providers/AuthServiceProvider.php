<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define gates for admin permissions
        // Note: We need to explicitly check the admin guard for these gates
        
        // Helper function to get admin user
        $getAdmin = function () {
            return auth()->guard('admin')->user();
        };
        
        // User Management
        Gate::define('manage-students', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            // Check if it's an admin (from admin guard)
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('manage-faculty', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin();
            }
            return false;
        });

        Gate::define('manage-admins', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin();
            }
            return false;
        });

        Gate::define('manage-admin-profiles', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin();
            }
            return false;
        });

        // Dashboard Access
        Gate::define('view-admin-dashboard', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('view-superadmin-dashboard', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin();
            }
            return false;
        });

        // Content Management - Announcements
        Gate::define('view-announcements', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('create-announcements', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-announcements', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-announcements', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        // Content Management - Events
        Gate::define('view-events', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('create-events', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-events', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-events', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        // Content Management - News
        Gate::define('view-news', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('create-news', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-news', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-news', function ($user = null) use ($getAdmin) {
            // Always use admin guard for these permissions
            $admin = $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        // General Permissions
        Gate::define('manage-content', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('view-reports', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        // User Dashboard and Notifications
        Gate::define('view-user-dashboard', function ($user) {
            // For regular users, check if they are authenticated and have a valid role
            return $user && ($user->role === 'student' || $user->role === 'faculty');
        });

        Gate::define('view-user-notifications', function ($user) {
            // Users can view their own notifications
            return $user && ($user->role === 'student' || $user->role === 'faculty');
        });

        Gate::define('mark-notifications-read', function ($user) {
            // Users can mark their own notifications as read
            return $user && ($user->role === 'student' || $user->role === 'faculty');
        });

        Gate::define('create-comments', function ($user) {
            // Users can create comments
            return $user && ($user->role === 'student' || $user->role === 'faculty');
        });

        Gate::define('update-own-comments', function ($user) {
            // Users can update their own comments
            return $user && ($user->role === 'student' || $user->role === 'faculty');
        });

        Gate::define('delete-own-comments', function ($user) {
            // Users can delete their own comments
            return $user && ($user->role === 'student' || $user->role === 'faculty');
        });

        Gate::define('update-user-profile', function ($user) {
            // Users can update their own profile
            return $user && ($user->role === 'student' || $user->role === 'faculty');
        });

        Gate::define('upload-user-profile-picture', function ($user) {
            // Users can upload their own profile picture
            return $user && ($user->role === 'student' || $user->role === 'faculty');
        });
    }
}
