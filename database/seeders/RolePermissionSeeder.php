<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'view-user-dashboard',
            'update-user-profile',
            'upload-user-profile-picture',
            'view-user-notifications',
            'mark-notifications-read',
            'create-comments',
            'update-own-comments',
            'delete-own-comments',

            // Content permissions
            'view-announcements',
            'view-events',
            'view-news',
            'view-public-content',

            // Admin permissions
            'view-admin-dashboard',
            'manage-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            'manage-events',
            'create-events',
            'edit-events',
            'delete-events',
            'publish-events',
            'manage-news',
            'create-news',
            'edit-news',
            'delete-news',
            'publish-news',

            // Department Admin permissions
            'manage-department-content',
            'view-department-students',
            'edit-department-students',
            'delete-department-students',
            'view-department-faculty',
            'edit-department-faculty',
            'delete-department-faculty',

            // Office Admin permissions
            'manage-office-content',
            'view-office-students',
            'edit-office-students',

            // Super Admin permissions
            'view-superadmin-dashboard',
            'manage-all-content',
            'manage-admins',
            'create-admins',
            'edit-admins',
            'delete-admins',
            'manage-department-admins',
            'create-department-admins',
            'edit-department-admins',
            'delete-department-admins',
            'manage-office-admins',
            'create-office-admins',
            'edit-office-admins',
            'delete-office-admins',
            'manage-all-students',
            'create-students',
            'edit-students',
            'delete-students',
            'manage-all-faculty',
            'create-faculty',
            'edit-faculty',
            'delete-faculty',
            'view-system-logs',
            'manage-system-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        // Create roles and assign permissions for both web and admin guards

        // Student role (web guard)
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $studentRole->givePermissionTo([
            'view-user-dashboard',
            'update-user-profile',
            'upload-user-profile-picture',
            'view-user-notifications',
            'mark-notifications-read',
            'create-comments',
            'update-own-comments',
            'delete-own-comments',
            'view-announcements',
            'view-events',
            'view-news',
            'view-public-content',
        ]);

        // Faculty role (web guard)
        $facultyRole = Role::firstOrCreate(['name' => 'faculty', 'guard_name' => 'web']);
        $facultyRole->givePermissionTo([
            'view-user-dashboard',
            'update-user-profile',
            'upload-user-profile-picture',
            'view-user-notifications',
            'mark-notifications-read',
            'create-comments',
            'update-own-comments',
            'delete-own-comments',
            'view-announcements',
            'view-events',
            'view-news',
            'view-public-content',
        ]);

        // Department Admin role (web guard)
        $departmentAdminRole = Role::firstOrCreate(['name' => 'department_admin', 'guard_name' => 'web']);
        $departmentAdminRole->givePermissionTo([
            'view-admin-dashboard',
            'manage-department-content',
            'manage-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            'manage-events',
            'create-events',
            'edit-events',
            'delete-events',
            'publish-events',
            'manage-news',
            'create-news',
            'edit-news',
            'delete-news',
            'publish-news',
            'view-department-students',
            'edit-department-students',
            'delete-department-students',
            'view-department-faculty',
            'edit-department-faculty',
            'delete-department-faculty',
            'view-announcements',
            'view-events',
            'view-news',
            'view-public-content',
        ]);

        // Office Admin role (web guard)
        $officeAdminRole = Role::firstOrCreate(['name' => 'office_admin', 'guard_name' => 'web']);
        $officeAdminRole->givePermissionTo([
            'view-admin-dashboard',
            'manage-office-content',
            'manage-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            'manage-events',
            'create-events',
            'edit-events',
            'delete-events',
            'publish-events',
            'manage-news',
            'create-news',
            'edit-news',
            'delete-news',
            'publish-news',
            'view-office-students',
            'edit-office-students',
            'view-announcements',
            'view-events',
            'view-news',
            'view-public-content',
        ]);

        // Super Admin role (web guard)
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::where('guard_name', 'web')->get());

        // Create roles for admin guard
        $adminDepartmentAdminRole = Role::firstOrCreate(['name' => 'department_admin', 'guard_name' => 'admin']);
        $adminDepartmentAdminRole->givePermissionTo([
            'view-admin-dashboard',
            'manage-department-content',
            'manage-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            'manage-events',
            'create-events',
            'edit-events',
            'delete-events',
            'publish-events',
            'manage-news',
            'create-news',
            'edit-news',
            'delete-news',
            'publish-news',
            'view-department-students',
            'edit-department-students',
            'delete-department-students',
            'view-department-faculty',
            'edit-department-faculty',
            'delete-department-faculty',
            'view-announcements',
            'view-events',
            'view-news',
            'view-public-content',
        ]);

        $adminOfficeAdminRole = Role::firstOrCreate(['name' => 'office_admin', 'guard_name' => 'admin']);
        $adminOfficeAdminRole->givePermissionTo([
            'view-admin-dashboard',
            'manage-office-content',
            'manage-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            'manage-events',
            'create-events',
            'edit-events',
            'delete-events',
            'publish-events',
            'manage-news',
            'create-news',
            'edit-news',
            'delete-news',
            'publish-news',
            'view-office-students',
            'edit-office-students',
            'view-announcements',
            'view-events',
            'view-news',
            'view-public-content',
        ]);

        $adminSuperAdminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'admin']);
        $adminSuperAdminRole->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        // Assign roles to existing users based on their current role field
        $this->assignRolesToExistingUsers();
    }

    /**
     * Assign roles to existing users based on their current role field
     */
    private function assignRolesToExistingUsers(): void
    {
        // Assign roles to Users table
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            if ($user->role === 'student') {
                $user->assignRole('student');
            } elseif ($user->role === 'faculty') {
                $user->assignRole('faculty');
            }
        }

        // Assign roles to Admins table
        $admins = \App\Models\Admin::all();
        foreach ($admins as $admin) {
            try {
                switch ($admin->role) {
                    case 'superadmin':
                        $admin->assignRole('superadmin');
                        break;
                    case 'department_admin':
                        $admin->assignRole('department_admin');
                        break;
                    case 'office_admin':
                        $admin->assignRole('office_admin');
                        break;
                    case 'admin':
                        // For backward compatibility, assign department_admin role
                        $admin->assignRole('department_admin');
                        break;
                }
            } catch (\Exception $e) {
                // Skip if role assignment fails
                echo "Failed to assign role to admin {$admin->id}: " . $e->getMessage() . "\n";
            }
        }
    }
}