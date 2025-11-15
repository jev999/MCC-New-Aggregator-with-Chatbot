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
        Schema::create('shareable_links', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('content_type'); // 'announcement', 'event', 'news'
            $table->unsignedBigInteger('content_id');
            $table->timestamp('expires_at')->nullable();
            $table->integer('access_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['content_type', 'content_id']);
            $table->index('token');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shareable_links');
    }
};
