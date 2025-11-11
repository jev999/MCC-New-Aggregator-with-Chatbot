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
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('create-announcements', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-announcements', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-announcements', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        // Content Management - Events
        Gate::define('view-events', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('create-events', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-events', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-events', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        // Content Management - News
        Gate::define('view-news', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('create-news', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-news', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
            if ($admin && method_exists($admin, 'isSuperAdmin')) {
                return $admin->isSuperAdmin() || $admin->isDepartmentAdmin() || $admin->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-news', function ($user = null) use ($getAdmin) {
            $admin = $user ?? $getAdmin();
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
    }
}
