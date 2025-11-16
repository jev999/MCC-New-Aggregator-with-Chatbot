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
            $table->string('street')->nullable()->after('longitude');
            $table->string('barangay')->nullable()->after('street');
            $table->string('municipality')->nullable()->after('barangay');
            $table->string('province')->nullable()->after('municipality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_access_logs', function (Blueprint $table) {
            $table->dropColumn(['street', 'barangay', 'municipality', 'province']);
        });
    }
};
