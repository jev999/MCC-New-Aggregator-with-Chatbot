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
            $table->timestamp('password_changed_at')->nullable()->after('email_verified_at');
            $table->timestamp('password_expires_at')->nullable()->after('password_changed_at');
            $table->boolean('password_must_change')->default(false)->after('password_expires_at');
            $table->json('password_history')->nullable()->after('password_must_change');
            $table->integer('failed_login_attempts')->default(0)->after('password_history');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'password_changed_at',
                'password_expires_at', 
                'password_must_change',
                'password_history',
                'failed_login_attempts',
                'locked_until'
            ]);
        });
    }
};