<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'share_token')) {
                $table->string('share_token', 64)->nullable()->unique();
            }
        });

        DB::table('events')
            ->whereNull('share_token')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('events')
                        ->where('id', $row->id)
                        ->update(['share_token' => Str::random(48)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'share_token')) {
                $table->dropUnique('events_share_token_unique');
                $table->dropColumn('share_token');
            }
        });
    }
};
