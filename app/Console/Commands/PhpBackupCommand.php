<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Log;

class PhpBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:php-run {--force : Force PHP backup even if mysqldump is available}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database backup using PHP (no mysqldump required) - Works with remote databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting PHP-based database backup...');
        $this->info('This method works with remote databases and requires no system tools.');
        $this->newLine();
        
        try {
            $backupService = new DatabaseBackupService();
            
            $this->info('Connecting to database...');
            $result = $backupService->createBackup();
            
            $this->newLine();
            $this->info('✓ Backup completed successfully!');
            $this->info("  File: {$result['filename']}");
            $this->info("  Size: " . $this->formatBytes($result['size']));
            $this->info("  Path: {$result['path']}");
            $this->newLine();
            
            Log::info('Scheduled PHP backup completed', [
                'filename' => $result['filename'],
                'size' => $result['size']
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('✗ Backup failed!');
            $this->error("  Error: {$e->getMessage()}");
            $this->newLine();
            
            Log::error('Scheduled PHP backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Format bytes to human readable
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
