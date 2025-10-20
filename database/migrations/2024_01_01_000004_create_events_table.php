<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->datetime('event_date');
            $table->time('event_time')->nullable();
            $table->string('location')->nullable();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->string('csv_file')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};
