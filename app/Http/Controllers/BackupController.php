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
            
            // Scan multiple candidate directories to avoid APP_NAME mismatches
            $candidateDirs = array_values(array_unique([
                $this->backupPath,
                config('backup.backup.name', 'Laravel'),
                config('app.name', 'Laravel'),
                'backups',
                'Laravel',
                '', // Also scan root directory
            ]));

            // Log the directories we're scanning
            Log::info('Scanning backup directories', ['dirs' => $candidateDirs]);

            // Get all files from storage disk
            $allFiles = [];
            foreach ($candidateDirs as $dir) {
                try {
                    // Get files in this directory
                    $dirFiles = $disk->files($dir);
                    Log::info("Found {$dir} files", ['count' => count($dirFiles)]);
                    $allFiles = array_merge($allFiles, $dirFiles);
                    
                    // Also check subdirectories one level deep
                    $subdirs = $disk->directories($dir);
                    foreach ($subdirs as $subdir) {
                        try {
                            $subdirFiles = $disk->files($subdir);
                            Log::info("Found {$subdir} files", ['count' => count($subdirFiles)]);
                            $allFiles = array_merge($allFiles, $subdirFiles);
                        } catch (\Exception $e) {
                            Log::warning("Error scanning subdir {$subdir}", ['error' => $e->getMessage()]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Error scanning dir {$dir}", ['error' => $e->getMessage()]);
                }
            }
            
            // Deduplicate files
            $allFiles = array_unique($allFiles);
            Log::info('Total files found', ['count' => count($allFiles)]);

            $seen = [];
            foreach ($allFiles as $file) {
                // Check if file is a backup (SQL or ZIP)
                if (preg_match('/\.(zip|sql)$/i', $file)) {
                    try {
                        $basename = basename($file);
                        $sizeBytes = $disk->size($file);
                        $lastMod = $disk->lastModified($file);
                        
                        // Deduplicate by filename, keep the latest
                        if (!isset($seen[$basename]) || $lastMod > $seen[$basename]['last_modified']) {
                            $seen[$basename] = [
                                'name' => $basename,
                                'filename' => $basename,
                                'path' => $file,
                                'size' => $this->formatBytes($sizeBytes),
                                'size_bytes' => $sizeBytes,
                                'last_modified' => $lastMod,
                                'created_at' => Carbon::createFromTimestamp($lastMod),
                                'created_at_human' => Carbon::createFromTimestamp($lastMod)->diffForHumans(),
                            ];
                            Log::info("Found backup file: {$basename}", [
                                'path' => $file,
                                'size' => $this->formatBytes($sizeBytes),
                                'date' => date('Y-m-d H:i:s', $lastMod)
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning("Error processing file {$file}", ['error' => $e->getMessage()]);
                    }
                }
            }

            $backups = collect(array_values($seen))->sortByDesc('last_modified')->values();
            
            // Get database statistics
            $dbStats = [
                'database_name' => config('database.connections.mysql.database', 'N/A'),
                'backup_disk' => $this->backupDisk,
                'backup_path' => $this->backupPath,
                'backup_schedule' => 'Every 5 hours',
            ];

            // Log the number of backups found
            Log::info('Backups found', ['count' => $backups->count()]);

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
     * Create new database backup - uses PHP-based backup method for better compatibility
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
            
            // Always use PHP-based backup for better compatibility
            // This avoids issues with mysqldump not being available or accessible
            return $this->createPhpBackup($admin);

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
            // Check if directories exist and are writable
            $this->ensureBackupDirectoriesExist();
            
            // Create backup service instance and generate backup
            $backupService = new DatabaseBackupService();
            $result = $backupService->createBackup();
            
            if (!isset($result['success']) || !$result['success']) {
                throw new \Exception('Backup creation failed: ' . ($result['message'] ?? 'Unknown error'));
            }
            
            Log::info('PHP-based backup created successfully', [
                'user' => $admin->username,
                'filename' => $result['filename'],
                'size' => $result['size']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Database backup created successfully!',
                'method' => 'php',
                'filename' => $result['filename'],
                'size' => $this->formatBytes($result['size'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('PHP-based backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ensure backup directories exist and are writable
     */
    protected function ensureBackupDirectoriesExist()
    {
        $directories = [
            storage_path('app/' . $this->backupPath),
            storage_path('app/backup-temp'),
            // Add fallback directories
            storage_path('app/Laravel'),
            storage_path('app/backups'),
        ];
        
        $success = false;
        $errors = [];
        
        foreach ($directories as $directory) {
            try {
                if (!file_exists($directory)) {
                    if (!@mkdir($directory, 0775, true)) {
                        $errors[] = "Failed to create directory: {$directory}";
                        continue;
                    }
                    Log::info("Created backup directory: {$directory}");
                }
                
                // Try to make writable if it exists but isn't writable
                if (!is_writable($directory)) {
                    @chmod($directory, 0775);
                    if (!is_writable($directory)) {
                        $errors[] = "Directory exists but is not writable: {$directory}";
                        continue;
                    }
                }
                
                // At least one directory is writable
                $success = true;
                
            } catch (\Exception $e) {
                $errors[] = "Error with directory {$directory}: " . $e->getMessage();
            }
        }
        
        if (!$success) {
            throw new \Exception("Failed to create or access backup directories. Please check permissions. Errors: " . implode(", ", $errors));
        }
        
        return $success;
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
     * Check if database connection is remote
     * Remote databases require PHP-based backup instead of mysqldump
     */
    protected function isRemoteDatabase()
    {
        try {
            $host = config('database.connections.mysql.host');
            $username = config('database.connections.mysql.username');
            $database = config('database.connections.mysql.database');
            
            // Check if using a remote database based on common patterns:
            // 1. Database name contains hosting provider prefixes (u123456_xxx format)
            // 2. Username contains hosting provider prefixes
            // 3. Host is not localhost/127.0.0.1
            
            $isRemoteByNaming = (
                // Check for cPanel/hosting provider naming convention (user_database format)
                preg_match('/^[a-z]\d+_/', $database) || 
                preg_match('/^[a-z]\d+_/', $username)
            );
            
            $isRemoteByHost = !in_array($host, ['localhost', '127.0.0.1', '::1', 'local']);
            
            $isRemote = $isRemoteByNaming || $isRemoteByHost;
            
            if ($isRemote) {
                Log::info('Remote database detected', [
                    'host' => $host,
                    'database' => $database,
                    'username' => $username,
                    'reason' => $isRemoteByNaming ? 'naming convention' : 'remote host'
                ]);
            }
            
            return $isRemote;
            
        } catch (\Exception $e) {
            Log::warning('Failed to detect remote database', ['error' => $e->getMessage()]);
            // Default to assuming remote for safety
            return true;
        }
    }


    /**
     * Download backup file
     */
    public function download($filename)
    {
        try {
            $disk = Storage::disk($this->backupDisk);
            
            // Find the file in any possible location
            $filePath = $this->findBackupFile($disk, $filename);
            
            if (!$filePath) {
                Log::warning('Backup file not found for download', ['filename' => $filename]);
                abort(404, 'Backup file not found');
            }

            Log::info('Backup download initiated', [
                'filename' => $filename,
                'path' => $filePath,
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
     * Find a backup file in any possible location
     */
    protected function findBackupFile($disk, $filename)
    {
        // Check if file exists directly
        if ($disk->exists($filename)) {
            return $filename;
        }
        
        // Try with the direct path first
        $filePath = $this->backupPath . '/' . $filename;
        if ($disk->exists($filePath)) {
            return $filePath;
        }
        
        // Prepare candidates with all possible locations and extensions
        $candidates = [];
        $alt = preg_match('/\.zip$/i', $filename) ? preg_replace('/\.zip$/i', '.sql', $filename) : preg_replace('/\.sql$/i', '.zip', $filename);
        
        $candidateDirs = array_values(array_unique([
            $this->backupPath,
            config('backup.backup.name', 'Laravel'),
            config('app.name', 'Laravel'),
            'backups',
            'Laravel',
            '', // Root directory
        ]));
        
        // Build all possible paths
        foreach ($candidateDirs as $dir) {
            // Skip empty directory for direct filename check (already done above)
            if (!empty($dir)) {
                $candidates[] = $dir . '/' . $filename;
                if ($alt) $candidates[] = $dir . '/' . $alt;
            }
        }
        
        // Check all candidates
        foreach ($candidates as $candidate) {
            if ($disk->exists($candidate)) {
                return $candidate;
            }
        }
        
        // If still not found, try a more exhaustive search
        $allFiles = [];
        foreach ($candidateDirs as $dir) {
            try {
                // Get files in this directory
                if (!empty($dir)) {
                    $dirFiles = $disk->files($dir);
                    $allFiles = array_merge($allFiles, $dirFiles);
                    
                    // Also check subdirectories one level deep
                    $subdirs = $disk->directories($dir);
                    foreach ($subdirs as $subdir) {
                        try {
                            $subdirFiles = $disk->files($subdir);
                            $allFiles = array_merge($allFiles, $subdirFiles);
                        } catch (\Exception $e) {
                            // Ignore errors for individual subdirectories
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors for individual directories
            }
        }
        
        // Look for exact filename match or alternate extension
        foreach ($allFiles as $file) {
            $baseFilename = basename($file);
            if ($baseFilename === $filename || $baseFilename === $alt) {
                return $file;
            }
        }
        
        // Not found anywhere
        return null;
    }

    /**
     * Delete backup file
     */
    public function delete($filename)
    {
        try {
            $disk = Storage::disk($this->backupDisk);
            
            // Find the file in any possible location
            $filePath = $this->findBackupFile($disk, $filename);
            
            if (!$filePath) {
                Log::warning('Backup file not found for deletion', ['filename' => $filename]);
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            // Delete the file
            $disk->delete($filePath);

            Log::info('Backup deleted', [
                'filename' => $filename,
                'path' => $filePath,
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
     * Check for backup files in root directory
     */
    public function checkBackupsInRoot()
    {
        try {
            $disk = Storage::disk($this->backupDisk);
            $count = 0;
            
            // Check root directory for backup files
            $rootFiles = $disk->files();
            foreach ($rootFiles as $file) {
                if (preg_match('/\.(zip|sql)$/i', $file)) {
                    $count++;
                }
            }
            
            // Check other potential directories
            $otherDirs = ['backups', 'Laravel', config('app.name', 'Laravel')];
            foreach ($otherDirs as $dir) {
                if ($dir !== $this->backupPath && $disk->exists($dir)) {
                    $dirFiles = $disk->files($dir);
                    foreach ($dirFiles as $file) {
                        if (preg_match('/\.(zip|sql)$/i', $file)) {
                            $count++;
                        }
                    }
                }
            }
            
            return response()->json([
                'found' => $count > 0,
                'count' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking for backup files', ['error' => $e->getMessage()]);
            return response()->json([
                'found' => false,
                'count' => 0,
                'error' => $e->getMessage()
            ]);
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
