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
            $table->json('image_paths')->nullable();
            $table->json('video_paths')->nullable();
        });
        
        Schema::table('events', function (Blueprint $table) {
            $table->json('image_paths')->nullable();
            $table->json('video_paths')->nullable();
        });
        
        Schema::table('news', function (Blueprint $table) {
            $table->json('image_paths')->nullable();
            $table->json('video_paths')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['image_paths', 'video_paths']);
        });
        
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['image_paths', 'video_paths']);
        });
        
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['image_paths', 'video_paths']);
        });
    }
};
