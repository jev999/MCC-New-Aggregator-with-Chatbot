<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\DatabaseBackupService;

class BackupController extends Controller
{
    protected $backupDisk;
    protected $backupPath;

    public function __construct()
    {
        try {
            // Use the disk configured in config/backup.php
            $this->backupDisk = 'local';
            // Get backup name from config, fallback to APP_NAME or default
            $this->backupPath = config('backup.backup.name') ?? config('app.name', 'Laravel');
            
            Log::info('BackupController initialized', [
                'disk' => $this->backupDisk,
                'backup_name' => $this->backupPath
            ]);
            
        } catch (\Exception $e) {
            Log::error('BackupController constructor failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Display backup management page
     */
    public function index()
    {
        Log::info('BackupController@index called', [
            'user' => auth('admin')->check() ? auth('admin')->user()->username : 'not authenticated'
        ]);
        
        try {
            $disk = Storage::disk($this->backupDisk);
            $backups = collect();
            
            // Get all files from the backup directory
            $files = $disk->allFiles($this->backupPath);
            
            foreach ($files as $file) {
                // Only show .zip files (Spatie backup creates zip files)
                if (substr($file, -4) === '.zip') {
                    $backups->push([
                        'name' => basename($file),
                        'filename' => basename($file),
                        'path' => $file,
                        'size' => $this->formatBytes($disk->size($file)),
                        'size_bytes' => $disk->size($file),
                        'last_modified' => $disk->lastModified($file),
                        'created_at' => Carbon::createFromTimestamp($disk->lastModified($file)),
                        'created_at_human' => Carbon::createFromTimestamp($disk->lastModified($file))->diffForHumans(),
                    ]);
                }
            }
            
            // Sort by latest modified date
            $backups = $backups->sortByDesc('last_modified')->values();

            // Get database statistics
            $dbStats = [
                'database_name' => config('database.connections.mysql.database', 'N/A'),
                'backup_disk' => $this->backupDisk,
                'backup_path' => $this->backupPath,
                'backup_schedule' => 'Every 5 hours',
            ];

            return view('superadmin.backup.index', compact('backups', 'dbStats'));
            
        } catch (\Exception $e) {
            Log::error('Backup index page error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to load backup page: ' . $e->getMessage());
        }
    }

    /**
     * Create new database backup - tries Spatie first, falls back to PHP-based backup
     */
    public function create(Request $request)
    {
        try {
            // Verify authentication
            if (!auth('admin')->check()) {
                Log::warning('Backup creation attempted without authentication');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login again.'
                ], 401);
            }

            // Verify superadmin role
            $admin = auth('admin')->user();
            if (!$admin->isSuperAdmin()) {
                Log::warning('Backup creation attempted by non-superadmin', [
                    'user' => $admin->username,
                    'role' => $admin->role
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only superadmins can create backups.'
                ], 403);
            }

            // Log the start of backup creation
            Log::info('Manual backup creation started', [
                'user' => $admin->username,
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            // Check if we should use PHP-based backup (fallback mode)
            $usePhpBackup = config('backup.use_php_backup', false) || !$this->isMysqldumpAvailable();
            
            if ($usePhpBackup) {
                Log::info('Using PHP-based backup (mysqldump not available or configured)');
                return $this->createPhpBackup($admin);
            }

            // Try Spatie backup first
            try {
                // Set custom mysqldump path if configured
                $mysqldumpPath = config('backup.backup.database_dump_settings.mysql.dump_binary_path');
                if ($mysqldumpPath && file_exists($mysqldumpPath)) {
                    putenv("MYSQLDUMP_PATH={$mysqldumpPath}");
                    Log::info('Using custom mysqldump path', ['path' => $mysqldumpPath]);
                }

                // Run Spatie backup command
                Artisan::call('backup:run', [
                    '--only-db' => true,  // Only backup database, not files
                ]);

                $output = Artisan::output();
                
                // Check if backup failed due to mysqldump
                if (strpos($output, 'mysqldump') !== false && strpos($output, 'not recognized') !== false) {
                    Log::warning('Spatie backup failed due to missing mysqldump, falling back to PHP backup');
                    return $this->createPhpBackup($admin);
                }
                
                // Check if backup was successful
                if (strpos($output, 'Backup completed') !== false || strpos($output, 'successfully') !== false || strpos($output, 'Success') !== false) {
                    Log::info('Backup created successfully via Spatie', [
                        'user' => $admin->username,
                        'output' => $output
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Database backup created successfully!',
                        'method' => 'spatie',
                        'output' => $output
                    ]);
                } else {
                    Log::warning('Spatie backup output unclear, falling back to PHP backup', [
                        'output' => $output
                    ]);
                    return $this->createPhpBackup($admin);
                }
                
            } catch (\Exception $spatieException) {
                Log::warning('Spatie backup failed, falling back to PHP backup', [
                    'error' => $spatieException->getMessage()
                ]);
                return $this->createPhpBackup($admin);
            }

        } catch (\Exception $e) {
            Log::error('Backup creation failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => auth('admin')->check() ? auth('admin')->user()->username : 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create backup using PHP-based method (no mysqldump required)
     */
    protected function createPhpBackup($admin)
    {
        try {
            $backupService = new DatabaseBackupService();
            $result = $backupService->createBackup();
            
            Log::info('PHP-based backup created successfully', [
                'user' => $admin->username,
                'filename' => $result['filename'],
                'size' => $result['size']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Database backup created successfully using PHP method!',
                'method' => 'php',
                'filename' => $result['filename'],
                'size' => $this->formatBytes($result['size'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('PHP-based backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Check if mysqldump is available on the system
     */
    protected function isMysqldumpAvailable()
    {
        try {
            $mysqldumpPath = config('backup.backup.database_dump_settings.mysql.dump_binary_path');
            
            // If custom path is set and exists, return true
            if ($mysqldumpPath && file_exists($mysqldumpPath)) {
                return true;
            }
            
            // Try to find mysqldump in system PATH
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                exec('where mysqldump', $output, $returnCode);
            } else {
                // Linux/Mac
                exec('which mysqldump', $output, $returnCode);
            }
            
            return $returnCode === 0;
            
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Download backup file
     */
    public function download($filename)
    {
        try {
            $disk = Storage::disk($this->backupDisk);
            $filePath = $this->backupPath . '/' . $filename;

            if (!$disk->exists($filePath)) {
                abort(404, 'Backup file not found');
            }

            Log::info('Backup download initiated', [
                'filename' => $filename,
                'user' => auth('admin')->check() ? auth('admin')->user()->username : 'unknown'
            ]);

            // Download the file directly from storage
            return $disk->download($filePath, $filename);
            
        } catch (\Exception $e) {
            Log::error('Backup download failed', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Failed to download backup: ' . $e->getMessage());
        }
    }

    /**
     * Delete backup file
     */
    public function delete($filename)
    {
        try {
            $disk = Storage::disk($this->backupDisk);
            $filePath = $this->backupPath . '/' . $filename;

            if (!$disk->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            $disk->delete($filePath);

            Log::info('Backup deleted', [
                'filename' => $filename,
                'user' => auth('admin')->check() ? auth('admin')->user()->username : 'unknown'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Backup deletion failed', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage()
            ], 500);
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
