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
        Schema::table('users', function (Blueprint $table) {
            // Make ms365_account nullable and remove unique constraint temporarily
            $table->string('ms365_account')->nullable()->change();
        });
        
        // Drop the unique index on ms365_account
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['ms365_account']);
        });
        
        // Add a new unique index that allows nulls
        Schema::table('users', function (Blueprint $table) {
            $table->unique('ms365_account', 'users_ms365_account_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the nullable constraint and make it required again
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_ms365_account_unique');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('ms365_account')->nullable(false)->change();
            $table->unique('ms365_account');
        });
    }
};
