<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // List of essential tables
        $essentialTables = [
            'users',
            'admins',
            'password_resets',
            'failed_jobs',
            'personal_access_tokens',
            'sessions',
            'cache',
            'jobs',
            'notifications',
            'events',
            'news',
            'announcements',
            'comments',
            'registration_tokens',
            'ms365_accounts',
            'pending_registrations'
        ];

        foreach ($essentialTables as $table) {
            if (!Schema::hasTable($table)) {
                echo "Missing table: {$table}\n";
            }
        }
    }

    public function down()
    {
        // Not needed
    }
};
