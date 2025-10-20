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
        // Modify the department enum to include BSED
        DB::statement("ALTER TABLE users MODIFY COLUMN department ENUM('BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the department enum to original values
        DB::statement("ALTER TABLE users MODIFY COLUMN department ENUM('BSIT', 'BSBA', 'BEED', 'BSHM') NULL");
    }
};
