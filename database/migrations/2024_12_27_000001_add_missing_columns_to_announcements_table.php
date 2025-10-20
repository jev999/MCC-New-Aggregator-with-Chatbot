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
            // Add multiple media support
            $table->json('image_paths')->nullable()->after('image_path');
            $table->json('video_paths')->nullable()->after('video_path');
            
            // Add visibility and targeting columns
            $table->enum('visibility_scope', ['department', 'office', 'all'])->default('all')->after('is_published');
            $table->string('target_department')->nullable()->after('visibility_scope');
            $table->string('target_office')->nullable()->after('target_department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn([
                'image_paths',
                'video_paths', 
                'visibility_scope',
                'target_department',
                'target_office'
            ]);
        });
    }
};
