<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update announcements table
        DB::statement("ALTER TABLE announcements MODIFY COLUMN visibility_scope ENUM('department', 'office', 'all') DEFAULT 'all'");
        
        // Update events table
        DB::statement("ALTER TABLE events MODIFY COLUMN visibility_scope ENUM('department', 'office', 'all') DEFAULT 'all'");
        
        // Update news table
        DB::statement("ALTER TABLE news MODIFY COLUMN visibility_scope ENUM('department', 'office', 'all') DEFAULT 'all'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert announcements table
        DB::statement("ALTER TABLE announcements MODIFY COLUMN visibility_scope ENUM('department', 'all') DEFAULT 'all'");
        
        // Revert events table
        DB::statement("ALTER TABLE events MODIFY COLUMN visibility_scope ENUM('department', 'all') DEFAULT 'all'");
        
        // Revert news table
        DB::statement("ALTER TABLE news MODIFY COLUMN visibility_scope ENUM('department', 'all') DEFAULT 'all'");
    }
};
