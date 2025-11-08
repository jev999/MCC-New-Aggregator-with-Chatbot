<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BackupController;
use Illuminate\Http\Request;

class TestBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the backup functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing backup functionality...');

        try {
            $controller = new BackupController();
            $request = new Request();

            // Call the create method
            $response = $controller->create($request);

            $this->info('Backup creation response:');
            $this->line($response->getContent());

        } catch (\Exception $e) {
            $this->error('Backup test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }

        return 0;
    }
}
