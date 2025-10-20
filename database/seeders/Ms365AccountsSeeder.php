<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Ms365AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample MS365 accounts for testing
        $accounts = [
            [
                'display_name' => 'John Doe',
                'user_principal_name' => 'student1@mcc-nac.edu.ph',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'created_at' => now(),
            ],
            [
                'display_name' => 'Jane Smith',
                'user_principal_name' => 'student2@mcc-nac.edu.ph',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'created_at' => now(),
            ],
            [
                'display_name' => 'Dr. Robert Johnson',
                'user_principal_name' => 'faculty1@mcc-nac.edu.ph',
                'first_name' => 'Robert',
                'last_name' => 'Johnson',
                'created_at' => now(),
            ],
            [
                'display_name' => 'Prof. Maria Garcia',
                'user_principal_name' => 'faculty2@mcc-nac.edu.ph',
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'created_at' => now(),
            ],
        ];

        // Insert accounts if they don't exist
        foreach ($accounts as $account) {
            DB::table('ms365_accounts')->updateOrInsert(
                ['user_principal_name' => $account['user_principal_name']],
                $account
            );
        }

        $this->command->info('MS365 accounts seeded successfully!');
    }
}
