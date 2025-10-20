<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LoginThrottleService;

class CleanupLoginAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'login:cleanup-attempts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired login attempt records';

    /**
     * Execute the console command.
     */
    public function handle(LoginThrottleService $throttleService)
    {
        $this->info('Cleaning up expired login attempts...');
        
        $deletedCount = $throttleService->cleanupExpiredAttempts();
        
        $this->info("Cleaned up {$deletedCount} expired login attempt records.");
        
        return Command::SUCCESS;
    }
}
