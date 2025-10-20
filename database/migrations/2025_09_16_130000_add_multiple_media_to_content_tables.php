<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add multiple media columns to announcements table
        Schema::table('announcements', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('image_path');
            $table->json('video_paths')->nullable()->after('video_path');
        });

        // Add multiple media columns to news table
        Schema::table('news', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('image_path');
            $table->json('video_paths')->nullable()->after('video_path');
        });

        // Add multiple media columns to events table
        Schema::table('events', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('image');
            $table->json('video_paths')->nullable()->after('video');
        });
    }

    public function down()
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['image_paths', 'video_paths']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['image_paths', 'video_paths']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['image_paths', 'video_paths']);
        });
    }
};
