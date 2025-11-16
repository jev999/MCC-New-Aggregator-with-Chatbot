<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\News;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('id');
        });

        News::whereNull('share_token')->chunkById(100, function ($items) {
            foreach ($items as $news) {
                $news->share_token = bin2hex(random_bytes(16));
                $news->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique('news_share_token_unique');
            $table->dropColumn('share_token');
        });
    }
};
