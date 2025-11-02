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
        // Note: Gates automatically receive the authenticated user/admin based on the guard in use
        
        // User Management
        Gate::define('manage-students', function ($user) {
            // Check if it's an admin (from admin guard)
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
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

        // Content Management - Announcements
        Gate::define('create-announcements', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-announcements', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-announcements', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        // Content Management - Events
        Gate::define('create-events', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-events', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-events', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        // Content Management - News
        Gate::define('create-news', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('edit-news', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
            }
            return false;
        });

        Gate::define('delete-news', function ($user) {
            if (method_exists($user, 'isSuperAdmin')) {
                return $user->isSuperAdmin() || $user->isDepartmentAdmin() || $user->isOfficeAdmin();
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
