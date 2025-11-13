<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;

class FixOfficeAdminPermissions extends Command
{
    protected $signature = 'fix:office-admin-permissions';
    protected $description = 'Fix office admin permissions for events, announcements, and news';

    public function handle()
    {
        $this->info('Fixing office admin permissions...');

        try {
            // Create permissions if they don't exist
            $permissions = [
                'view-events',
                'create-events',
                'edit-events',
                'delete-events',
                'view-announcements',
                'create-announcements',
                'edit-announcements',
                'delete-announcements',
                'view-news',
                'create-news',
                'edit-news',
                'delete-news',
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'admin'
                ]);
                $this->line("✓ Permission: {$permission}");
            }

            // Get or create office admin role for admin guard
            $officeAdminRole = Role::firstOrCreate([
                'name' => 'office_admin',
                'guard_name' => 'admin'
            ]);

            // Assign all permissions to office admin role
            $officeAdminRole->syncPermissions($permissions);
            $this->info('✓ Office admin role permissions updated');

            // Assign role to all office admin users
            $officeAdmins = Admin::where('role', 'office_admin')->get();
            
            foreach ($officeAdmins as $admin) {
                $admin->assignRole($officeAdminRole);
                $this->line("✓ Role assigned to: {$admin->name} ({$admin->email})");
            }

            $this->info("✅ Successfully fixed permissions for {$officeAdmins->count()} office admin(s)");
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
