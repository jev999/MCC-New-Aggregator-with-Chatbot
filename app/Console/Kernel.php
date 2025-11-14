<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run database backup every 5 hours
        // Always use PHP-based backup for scheduled tasks (works with remote databases, creates .sql files)
        // PHP-based backup doesn't require mysqldump and works with all database configurations
        $schedule->command('backup:php-run')->everyFiveHours()
            ->sendOutputTo(storage_path('logs/backup.log'))
            ->emailOutputOnFailure(env('BACKUP_NOTIFICATION_EMAIL', 'admin@example.com'));
        
        // Run backup cleanup to manage old backups (daily at midnight)
        $schedule->command('backup:clean')->daily()->at('00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

