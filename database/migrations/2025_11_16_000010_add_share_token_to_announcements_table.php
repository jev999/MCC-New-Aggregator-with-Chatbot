<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Announcement;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('id');
        });

        Announcement::whereNull('share_token')->chunkById(100, function ($announcements) {
            foreach ($announcements as $announcement) {
                $announcement->share_token = bin2hex(random_bytes(16));
                $announcement->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropUnique('announcements_share_token_unique');
            $table->dropColumn('share_token');
        });
    }
};
