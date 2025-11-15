<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'share_token')) {
                $table->string('share_token', 64)->nullable()->unique();
            }
        });

        // Backfill tokens for existing records
        DB::table('announcements')
            ->whereNull('share_token')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('announcements')
                        ->where('id', $row->id)
                        ->update(['share_token' => Str::random(48)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'share_token')) {
                $table->dropUnique('announcements_share_token_unique');
                $table->dropColumn('share_token');
            }
        });
    }
};
