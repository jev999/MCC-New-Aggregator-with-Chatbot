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
        // For SQLite, we need to recreate the tables with updated enum values
        // For MySQL/PostgreSQL, we would use ALTER TABLE statements
        
        // Update users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('department');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('department', ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'])->nullable()->after('role');
        });
        
        // Update admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('department');
        });
        
        Schema::table('admins', function (Blueprint $table) {
            $table->enum('department', ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'])->nullable()->after('role');
        });
        
        // Update announcements table
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('target_department');
        });
        
        Schema::table('announcements', function (Blueprint $table) {
            $table->enum('target_department', ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'])->nullable()->after('visibility_scope');
        });
        
        // Update events table
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('target_department');
        });
        
        Schema::table('events', function (Blueprint $table) {
            $table->enum('target_department', ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'])->nullable()->after('visibility_scope');
        });
        
        // Update news table
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('target_department');
        });
        
        Schema::table('news', function (Blueprint $table) {
            $table->enum('target_department', ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'])->nullable()->after('visibility_scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the changes by removing BSED from enums
        // This is a simplified rollback - in production you might want more careful handling
        
        // Revert users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('department');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->enum('department', ['BSIT', 'BSBA', 'BEED', 'BSHM'])->nullable()->after('role');
        });
        
        // Revert admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('department');
        });
        Schema::table('admins', function (Blueprint $table) {
            $table->enum('department', ['BSIT', 'BSBA', 'BEED', 'BSHM'])->nullable()->after('role');
        });
        
        // Revert announcements table
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('target_department');
        });
        Schema::table('announcements', function (Blueprint $table) {
            $table->enum('target_department', ['BSIT', 'BSBA', 'BEED', 'BSHM'])->nullable()->after('visibility_scope');
        });
        
        // Revert events table
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('target_department');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->enum('target_department', ['BSIT', 'BSBA', 'BEED', 'BSHM'])->nullable()->after('visibility_scope');
        });
        
        // Revert news table
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('target_department');
        });
        Schema::table('news', function (Blueprint $table) {
            $table->enum('target_department', ['BSIT', 'BSBA', 'BEED', 'BSHM'])->nullable()->after('visibility_scope');
        });
    }
};
