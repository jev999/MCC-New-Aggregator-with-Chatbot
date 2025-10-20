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
        // Update existing announcements to have 'all' visibility if they don't have visibility_scope set
        DB::table('announcements')
            ->whereNull('visibility_scope')
            ->orWhere('visibility_scope', '')
            ->update(['visibility_scope' => 'all']);

        // Update existing events to have 'all' visibility if they don't have visibility_scope set
        DB::table('events')
            ->whereNull('visibility_scope')
            ->orWhere('visibility_scope', '')
            ->update(['visibility_scope' => 'all']);

        // Update existing news to have 'all' visibility if they don't have visibility_scope set
        DB::table('news')
            ->whereNull('visibility_scope')
            ->orWhere('visibility_scope', '')
            ->update(['visibility_scope' => 'all']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this as it's just setting default values
    }
};
