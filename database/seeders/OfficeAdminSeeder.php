<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OfficeAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create office admins if they don't exist
        $officeAdmins = [
            [
                'username' => 'jev.bautro@mcclawis.edu.ph',
                'password' => 'password123',
                'role' => 'office_admin',
                'office' => 'NSTP',
            ],
            [
                'username' => 'po.bautro@mccalumni.edu.ph',
                'password' => 'office123',
                'role' => 'office_admin',
                'office' => 'SSC',
            ],
            [
                'username' => 'office.admin@mcclawis.edu.ph',
                'password' => 'admin123',
                'role' => 'office_admin',
                'office' => 'GUIDANCE',
            ],
        ];

        foreach ($officeAdmins as $adminData) {
            $existingAdmin = Admin::where('username', $adminData['username'])->first();
            
            if (!$existingAdmin) {
                Admin::create([
                    'username' => $adminData['username'],
                    'password' => Hash::make($adminData['password']),
                    'role' => $adminData['role'],
                    'office' => $adminData['office'],
                ]);
                $this->command->info("Office Admin created - Username: {$adminData['username']}, Password: {$adminData['password']}, Office: {$adminData['office']}");
            } else {
                // Update password if it's not working
                if (!Hash::check($adminData['password'], $existingAdmin->password)) {
                    $existingAdmin->update([
                        'password' => Hash::make($adminData['password']),
                        'role' => $adminData['role'],
                        'office' => $adminData['office'],
                    ]);
                    $this->command->info("Office Admin password updated - Username: {$adminData['username']}, Password: {$adminData['password']}");
                } else {
                    $this->command->info("Office Admin already exists - Username: {$adminData['username']}");
                }
            }
        }

        $this->command->info('Office Admin accounts are ready!');
    }
}

