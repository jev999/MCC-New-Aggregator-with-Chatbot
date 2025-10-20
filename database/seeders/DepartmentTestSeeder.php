<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;

class DepartmentTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'];
        
        // Create department admins
        foreach ($departments as $dept) {
            $admin = Admin::firstOrCreate(
                ['username' => strtolower($dept) . '_admin'],
                [
                    'password' => bcrypt('password123'),
                    'role' => 'department_admin',
                    'department' => $dept
                ]
            );
            
            // Create test students for each department
            User::firstOrCreate(
                ['ms365_account' => strtolower($dept) . '_student@example.com'],
                [
                    'first_name' => $dept,
                    'surname' => 'Student',
                    'password' => bcrypt('password123'),
                    'role' => 'student',
                    'department' => $dept,
                    'year_level' => '1st Year'
                ]
            );
            
            // Create department-specific content
            Announcement::firstOrCreate(
                ['title' => $dept . ' Department Only Announcement'],
                [
                    'content' => 'This announcement is only visible to ' . $dept . ' students.',
                    'is_published' => true,
                    'visibility_scope' => 'department',
                    'target_department' => $dept,
                    'admin_id' => $admin->id
                ]
            );
            
            Event::firstOrCreate(
                ['title' => $dept . ' Department Only Event'],
                [
                    'description' => 'This event is only visible to ' . $dept . ' students.',
                    'event_date' => now()->addDays(7),
                    'event_time' => '10:00',
                    'location' => $dept . ' Building',
                    'is_published' => true,
                    'visibility_scope' => 'department',
                    'target_department' => $dept,
                    'admin_id' => $admin->id
                ]
            );
            
            News::firstOrCreate(
                ['title' => $dept . ' Department Only News'],
                [
                    'content' => 'This news article is only visible to ' . $dept . ' students.',
                    'is_published' => true,
                    'visibility_scope' => 'department',
                    'target_department' => $dept,
                    'admin_id' => $admin->id
                ]
            );
            
            // Create all-department content from each admin
            Announcement::firstOrCreate(
                ['title' => 'All Departments Announcement from ' . $dept],
                [
                    'content' => 'This announcement from ' . $dept . ' admin is visible to all students.',
                    'is_published' => true,
                    'visibility_scope' => 'all',
                    'admin_id' => $admin->id
                ]
            );
        }
        
        // Create superadmin content
        $superadmin = Admin::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'password' => bcrypt('password123'),
                'role' => 'superadmin'
            ]
        );
        
        Announcement::firstOrCreate(
            ['title' => 'Superadmin Announcement for All'],
            [
                'content' => 'This announcement from superadmin is visible to all students.',
                'is_published' => true,
                'visibility_scope' => 'all',
                'admin_id' => $superadmin->id
            ]
        );
    }
}
