<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Event;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('id');
        });

        Event::whereNull('share_token')->chunkById(100, function ($events) {
            foreach ($events as $event) {
                $event->share_token = bin2hex(random_bytes(16));
                $event->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique('events_share_token_unique');
            $table->dropColumn('share_token');
        });
    }
};
