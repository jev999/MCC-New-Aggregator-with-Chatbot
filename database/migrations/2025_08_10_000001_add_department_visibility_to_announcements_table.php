<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->enum('visibility_scope', ['department', 'all'])->default('all')->after('is_published');
            $table->enum('target_department', ['BSIT', 'BSBA', 'BEED', 'BSHM'])->nullable()->after('visibility_scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['visibility_scope', 'target_department']);
        });
    }
};
