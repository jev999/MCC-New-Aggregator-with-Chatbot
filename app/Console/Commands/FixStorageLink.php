<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixStorageLink extends Command
{
    protected $signature = 'storage:fix-link {--force : Force the operation to run even if link exists}';
    protected $description = 'Create storage symbolic link and fix permissions for production';

    public function handle()
    {
        $this->info('🔧 Fixing storage link and permissions...');

        // Check if we're in production environment
        $isProduction = app()->environment('production');
        $this->info("Environment: " . app()->environment());

        // Define paths
        $publicStoragePath = public_path('storage');
        $storagePath = storage_path('app/public');

        // Remove existing link/directory if --force is used
        if ($this->option('force') && (is_link($publicStoragePath) || is_dir($publicStoragePath))) {
            if (is_link($publicStoragePath)) {
                unlink($publicStoragePath);
                $this->info('✅ Removed existing symbolic link');
            } elseif (is_dir($publicStoragePath)) {
                File::deleteDirectory($publicStoragePath);
                $this->info('✅ Removed existing directory');
            }
        }

        // Create the symbolic link
        if (!file_exists($publicStoragePath)) {
            try {
                if (function_exists('symlink')) {
                    symlink($storagePath, $publicStoragePath);
                    $this->info('✅ Created symbolic link: public/storage -> storage/app/public');
                } else {
                    // Fallback: create a hard copy (not recommended but works)
                    File::copyDirectory($storagePath, $publicStoragePath);
                    $this->warn('⚠️ Created directory copy (symlink not available)');
                }
            } catch (\Exception $e) {
                $this->error('❌ Failed to create storage link: ' . $e->getMessage());
                return 1;
            }
        } else {
            $this->warn('⚠️ Storage link already exists');
        }

        // Fix permissions for uploaded files
        $this->fixPermissions();

        // Test the storage access
        $this->testStorageAccess();

        $this->info('🎉 Storage link setup completed!');
        return 0;
    }

    private function fixPermissions()
    {
        $this->info('📁 Fixing storage permissions...');

        $storagePaths = [
            storage_path('app/public'),
            storage_path('app/public/announcement-images'),
            storage_path('app/public/announcement-videos'),
            storage_path('app/public/event-images'),
            storage_path('app/public/event-videos'),
            storage_path('app/public/news-images'),
            storage_path('app/public/news-videos'),
            public_path('storage'),
        ];

        foreach ($storagePaths as $path) {
            if (file_exists($path)) {
                try {
                    // Set directory permissions to 755
                    if (is_dir($path)) {
                        chmod($path, 0755);
                        $this->info("✅ Set permissions for directory: {$path}");
                    }

                    // Set file permissions to 644 for all files in the directory
                    if (is_dir($path)) {
                        $files = File::allFiles($path);
                        foreach ($files as $file) {
                            chmod($file->getPathname(), 0644);
                        }
                        if (count($files) > 0) {
                            $this->info("✅ Set permissions for " . count($files) . " files in {$path}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->warn("⚠️ Could not set permissions for {$path}: " . $e->getMessage());
                }
            }
        }
    }

    private function testStorageAccess()
    {
        $this->info('🧪 Testing storage access...');

        // Test file creation
        $testDir = storage_path('app/public/test');
        $testFile = $testDir . '/test.txt';
        $testContent = 'Storage test - ' . now()->toDateTimeString();

        try {
            // Create test directory
            if (!is_dir($testDir)) {
                mkdir($testDir, 0755, true);
            }

            // Create test file
            file_put_contents($testFile, $testContent);
            
            // Test if file is accessible via public/storage
            $publicTestFile = public_path('storage/test/test.txt');
            
            if (file_exists($publicTestFile)) {
                $this->info('✅ Storage link is working correctly');
                
                // Clean up test files
                unlink($testFile);
                rmdir($testDir);
                $this->info('✅ Test files cleaned up');
            } else {
                $this->error('❌ Storage link is not working - public file not accessible');
                $this->line('Public path checked: ' . $publicTestFile);
                $this->line('Storage path: ' . $testFile);
            }

        } catch (\Exception $e) {
            $this->error('❌ Storage test failed: ' . $e->getMessage());
        }
    }
}
