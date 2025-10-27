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
        Schema::table('admin_access_logs', function (Blueprint $table) {
            $table->string('status')->default('success')->after('role'); // 'success' or 'failed'
            $table->string('username_attempted')->nullable()->after('status'); // username used in failed attempts
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_access_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'username_attempted']);
        });
    }
};
