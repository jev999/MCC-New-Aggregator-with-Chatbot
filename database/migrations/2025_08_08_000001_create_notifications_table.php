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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'announcement', 'event', 'news'
            $table->string('title');
            $table->text('message');
            $table->unsignedBigInteger('content_id'); // ID of the announcement/event/news
            $table->string('content_type'); // 'App\Models\Announcement', 'App\Models\Event', 'App\Models\News'
            $table->foreignId('admin_id')->constrained()->onDelete('cascade'); // Who published it
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Index for better performance
            $table->index(['user_id', 'is_read']);
            $table->index(['content_id', 'content_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
