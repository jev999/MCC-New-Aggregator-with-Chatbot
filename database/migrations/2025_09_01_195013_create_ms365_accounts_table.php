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
        Schema::create('ms365_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->enum('role', ['student', 'faculty'])->nullable();
            $table->enum('department', ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'])->nullable();
            $table->string('year_level')->nullable(); // For students
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['email', 'is_active']);
            $table->index(['department', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms365_accounts');
    }
};
