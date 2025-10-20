<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a super admin if it doesn't exist (but don't overwrite existing)
        $superadmin = Admin::where('username', 'superadmin')->first();
        
        if (!$superadmin) {
            $superadmin = Admin::create([
                'username' => 'superadmin',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
            ]);
            $this->command->info('Super Admin created - Username: superadmin, Password: password123');
        } else {
            // If superadmin exists but password is not working, fix it
            if (!Hash::check('password123', $superadmin->password)) {
                $superadmin->update([
                    'password' => Hash::make('password123')
                ]);
                $this->command->info('Super Admin password updated to: password123');
            } else {
                $this->command->info('Super Admin already exists with correct password');
            }
        }

        // Create a regular admin for testing (but don't overwrite existing)
        $admin = Admin::where('username', 'admin')->first();
        
        if (!$admin) {
            Admin::create([
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]);
            $this->command->info('Admin created - Username: admin, Password: password123');
        } else {
            $this->command->info('Admin already exists');
        }

        $this->command->info('Super Admin and Admin accounts are ready!');
    }
}
