<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Admin;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ====================================================================
        // Create Permissions
        // ====================================================================

        // Content Management Permissions
        $permissions = [
            // Announcements
            'view-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            
            // Events
            'view-events',
            'create-events',
            'edit-events',
            'delete-events',
            'publish-events',
            
            // News
            'view-news',
            'create-news',
            'edit-news',
            'delete-news',
            'publish-news',
            
            // User Management
            'view-students',
            'create-students',
            'edit-students',
            'delete-students',
            'bulk-delete-students',
            
            'view-faculty',
            'create-faculty',
            'edit-faculty',
            'delete-faculty',
            
            // Admin Management
            'view-admins',
            'create-admins',
            'edit-admins',
            'delete-admins',
            'assign-roles',
            'assign-permissions',
            
            // Department Management
            'view-department-admins',
            'create-department-admins',
            'edit-department-admins',
            'delete-department-admins',
            
            // Office Management
            'view-office-admins',
            'create-office-admins',
            'edit-office-admins',
            'delete-office-admins',
            
            // Profile Management
            'view-own-profile',
            'edit-own-profile',
            'view-admin-profiles',
            'edit-admin-profiles',
            'upload-profile-pictures',
            
            // System Settings
            'view-settings',
            'edit-settings',
            'view-logs',
            'view-admin-access-logs',
            'delete-admin-access-logs',
            'bulk-delete-access-logs',
            
            // Backup & Maintenance
            'create-backups',
            'download-backups',
            'delete-backups',
            'view-backups',
            
            // Dashboard Access
            'view-dashboard',
            'view-analytics',
            'view-statistics',
            
            // Department-Specific Permissions
            'manage-own-department',
            'view-own-department-students',
            'edit-own-department-students',
            
            // Office-Specific Permissions
            'manage-own-office',
            'view-own-office-content',
            'edit-own-office-content',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'admin']);
        }

        // ====================================================================
        // Create Roles and Assign Permissions
        // ====================================================================

        // Super Administrator Role - Full Access
        $superadmin = Role::create(['name' => 'Super Administrator', 'guard_name' => 'admin']);
        $superadmin->givePermissionTo(Permission::all());

        // Department Administrator Role - Department-specific access
        $departmentAdmin = Role::create(['name' => 'Department Administrator', 'guard_name' => 'admin']);
        $departmentAdmin->givePermissionTo([
            // Content Management (own department)
            'view-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            
            'view-events',
            'create-events',
            'edit-events',
            'delete-events',
            
            'view-news',
            'create-news',
            'edit-news',
            'delete-news',
            
            // Student Management (own department)
            'view-students',
            'view-own-department-students',
            'edit-own-department-students',
            
            // Faculty Management (own department)
            'view-faculty',
            'edit-faculty',
            
            // Department Management
            'manage-own-department',
            
            // Profile Management
            'view-own-profile',
            'edit-own-profile',
            
            // Dashboard
            'view-dashboard',
            'view-statistics',
        ]);

        // Office Administrator Role - Office-specific access
        $officeAdmin = Role::create(['name' => 'Office Administrator', 'guard_name' => 'admin']);
        $officeAdmin->givePermissionTo([
            // Content Management (own office)
            'view-announcements',
            'create-announcements',
            'edit-announcements',
            
            'view-events',
            'create-events',
            'edit-events',
            
            'view-news',
            'create-news',
            'edit-news',
            
            // Office Management
            'manage-own-office',
            'view-own-office-content',
            'edit-own-office-content',
            
            // Student Viewing
            'view-students',
            
            // Profile Management
            'view-own-profile',
            'edit-own-profile',
            
            // Dashboard
            'view-dashboard',
        ]);

        // Content Manager Role - Content-focused access
        $contentManager = Role::create(['name' => 'Content Manager', 'guard_name' => 'admin']);
        $contentManager->givePermissionTo([
            // Full Content Management
            'view-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            
            'view-events',
            'create-events',
            'edit-events',
            'delete-events',
            'publish-events',
            
            'view-news',
            'create-news',
            'edit-news',
            'delete-news',
            'publish-news',
            
            // Profile Management
            'view-own-profile',
            'edit-own-profile',
            
            // Dashboard
            'view-dashboard',
            'view-statistics',
        ]);

        // Student Affairs Manager Role - Student management focused
        $studentAffairs = Role::create(['name' => 'Student Affairs Manager', 'guard_name' => 'admin']);
        $studentAffairs->givePermissionTo([
            // Full Student Management
            'view-students',
            'create-students',
            'edit-students',
            'delete-students',
            
            // Faculty Viewing
            'view-faculty',
            
            // Announcements (viewing and creating)
            'view-announcements',
            'create-announcements',
            
            'view-events',
            'create-events',
            
            // Profile Management
            'view-own-profile',
            'edit-own-profile',
            
            // Dashboard
            'view-dashboard',
            'view-statistics',
        ]);

        // ====================================================================
        // Assign Roles to Existing Admins Based on Their 'role' Field
        // ====================================================================

        $admins = Admin::all();
        foreach ($admins as $admin) {
            switch ($admin->role) {
                case 'superadmin':
                    $admin->assignRole('Super Administrator');
                    break;
                case 'department_admin':
                    $admin->assignRole('Department Administrator');
                    break;
                case 'office_admin':
                    $admin->assignRole('Office Administrator');
                    break;
                default:
                    // Assign Content Manager as default for regular admins
                    $admin->assignRole('Content Manager');
                    break;
            }
        }

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Total Permissions: ' . Permission::count());
        $this->command->info('Total Roles: ' . Role::count());
        $this->command->info('Admins assigned to roles: ' . $admins->count());
    }
}
