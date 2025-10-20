<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired sessions from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionLifetime = config('session.lifetime', 60) * 60; // Convert minutes to seconds
        $expiredTime = now()->timestamp - $sessionLifetime;

        try {
            // Count expired sessions
            $expiredCount = DB::table(config('session.table', 'sessions'))
                ->where('last_activity', '<', $expiredTime)
                ->count();

            if ($expiredCount === 0) {
                $this->info('No expired sessions found.');
                return 0;
            }

            $this->info("Found {$expiredCount} expired sessions.");

            if (!$this->option('force') && !$this->confirm('Do you want to delete these expired sessions?')) {
                $this->info('Session cleanup cancelled.');
                return 0;
            }

            // Delete expired sessions
            $deletedCount = DB::table(config('session.table', 'sessions'))
                ->where('last_activity', '<', $expiredTime)
                ->delete();

            $this->info("Successfully deleted {$deletedCount} expired sessions.");

            // Log the cleanup
            Log::info('Session cleanup completed', [
                'expired_sessions_deleted' => $deletedCount,
                'cleanup_time' => now()->toISOString(),
                'session_lifetime_minutes' => config('session.lifetime', 60)
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to cleanup expired sessions: ' . $e->getMessage());
            
            Log::error('Session cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cleanup_time' => now()->toISOString()
            ]);

            return 1;
        }
    }
}
