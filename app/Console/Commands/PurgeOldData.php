<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DataPurgingService;
use Illuminate\Support\Facades\Log;

class PurgeOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:purge {--dry-run : Show what would be purged without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old and unnecessary data based on retention policies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data purging process...');
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }

        try {
            // Get retention statistics
            $stats = DataPurgingService::getRetentionStats();
            
            $this->info('Current Data Statistics:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Users', $stats['total_users']],
                    ['Total Admins', $stats['total_admins']],
                    ['Total Notifications', $stats['total_notifications']],
                    ['Total Comments', $stats['total_comments']],
                    ['Old Notifications (>90 days)', $stats['old_notifications']],
                    ['Inactive Users (>2 years)', $stats['inactive_users']],
                    ['Old Sessions (>30 days)', $stats['old_sessions']],
                ]
            );

            if ($this->option('dry-run')) {
                $this->info('Dry run completed. No data was deleted.');
                return;
            }

            if ($this->confirm('Do you want to proceed with data purging?')) {
                $results = DataPurgingService::purgeOldData();
                
                $this->info('Data Purging Results:');
                $this->table(
                    ['Data Type', 'Records Purged'],
                    [
                        ['Old Notifications', $results['notifications']],
                        ['Orphaned Comments', $results['orphaned_comments']],
                        ['Inactive Users', $results['inactive_users']],
                        ['Password Reset Tokens', $results['password_reset_tokens']],
                        ['Old Sessions', $results['old_sessions']],
                    ]
                );

                $totalPurged = array_sum($results);
                $this->info("Total records purged: {$totalPurged}");
                
                Log::info('Data purging completed via console command', $results);
            } else {
                $this->info('Data purging cancelled.');
            }

        } catch (\Exception $e) {
            $this->error('Data purging failed: ' . $e->getMessage());
            Log::error('Data purging command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
