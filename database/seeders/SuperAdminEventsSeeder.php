<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, let's check if we have any admins
        $adminCount = DB::table('admins')->count();
        $this->command->info("Total admins in database: {$adminCount}");

        // Find or create a superadmin
        $superAdmin = DB::table('admins')->where('role', 'superadmin')->first();

        if (!$superAdmin) {
            $this->command->info('Creating superadmin...');
            $adminId = DB::table('admins')->insertGetId([
                'username' => 'superadmin',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'department' => 'Administration',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->command->info("Superadmin created with ID: {$adminId}");
        } else {
            $adminId = $superAdmin->id;
            $this->command->info("Superadmin found with ID: {$adminId}");
        }

        // Create a simple test event
        $eventId = DB::table('events')->insertGetId([
            'admin_id' => $adminId,
            'title' => 'Test Event from SuperAdmin',
            'description' => 'This is a test event created by superadmin to verify the user dashboard integration.',
            'event_date' => Carbon::now()->addDays(7),
            'event_time' => '10:00:00',
            'location' => 'MCC Main Campus',
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->command->info("Test event created with ID: {$eventId}");

        // Check if event was created
        $eventCount = DB::table('events')->where('is_published', true)->count();
        $this->command->info("Total published events: {$eventCount}");

        $this->command->info('SuperAdmin events seeded successfully!');
    }
}
