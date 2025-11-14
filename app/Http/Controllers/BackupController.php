<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\DatabaseBackupService;

class BackupController extends Controller
{
    protected $backupDisk;
    protected $backupPath;
    protected $storageBasePath;

    public function __construct()
    {
        try {
            // Use the disk configured in config/backup.php
            $this->backupDisk = 'local';
            // Get backup name from config, fallback to APP_NAME or default
            $this->backupPath = config('backup.backup.name') ?? config('app.name', 'Laravel');
            $diskPath = '';
            try {
                $diskPath = rtrim(Storage::disk($this->backupDisk)->path(''), DIRECTORY_SEPARATOR);
            } catch (\Exception $e) {
                $diskPath = '';
            }
            $this->storageBasePath = $diskPath ?: storage_path('app/private');

            if (!is_dir($this->storageBasePath)) {
                @mkdir($this->storageBasePath, 0775, true);
            }
            
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

            // Check if there's a newly created backup in the session
            if (session()->has('new_backup')) {
                $newBackup = session('new_backup');
                $filename = $newBackup['filename'];
                
                // Check if this backup is already in our list
                if (!isset($seen[$filename])) {
                    // Add the new backup to our list if it's not already there
                    // Use storage_path if available, otherwise construct a path
                    $storagePath = $newBackup['storage_path'] ?? '';
                    if (empty($storagePath)) {
                        $storagePath = $this->backupPath . '/' . $filename;
                    }
                    
                    $seen[$filename] = [
                        'name' => $filename,
                        'filename' => $filename,
                        'path' => $storagePath, // Use storage path for consistency
                        'size' => $newBackup['size'] ?? $this->formatBytes(0),
                        'size_bytes' => is_numeric($newBackup['size']) ? $newBackup['size'] : 0,
                        'last_modified' => $newBackup['created_at'] ?? now()->timestamp,
                        'created_at' => Carbon::createFromTimestamp($newBackup['created_at'] ?? now()->timestamp),
                        'created_at_human' => Carbon::createFromTimestamp($newBackup['created_at'] ?? now()->timestamp)->diffForHumans(),
                        'is_new' => true,
                    ];
                    
                    Log::info("Added new backup from session: {$filename}");
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
            
            // Clear any cached backup list to ensure fresh data
            Cache::forget('backup_list');
            
            // Always use PHP-based backup for better compatibility
            // This avoids issues with mysqldump not being available or accessible
            $result = $this->createPhpBackup($admin);
            
            // Add the newly created backup to the session for immediate display
            if ($result->original['success'] && isset($result->original['filename'])) {
                // Store the new backup info in session for immediate display
                session()->flash('new_backup', [
                    'filename' => $result->original['filename'],
                    'size' => $result->original['size'],
                    'path' => $result->original['path'] ?? '',
                    'storage_path' => $result->original['storage_path'] ?? '',
                    'created_at' => now()->timestamp
                ]);
            }
            
            return $result;

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
            
            // Get the actual path of the backup file
            $actualPath = $result['path'] ?? '';
            $actualFilename = $result['filename'] ?? '';
            $actualSize = $result['size'] ?? 0;
            $storagePath = $result['storage_path'] ?? '';
            
            // Verify the file exists in the storage
            $disk = Storage::disk($this->backupDisk);
            $fileExists = false;
            $filePath = '';
            
            // Try to find the file in storage
            if (!empty($actualPath) && file_exists($actualPath)) {
                // File exists at the specified path
                $fileExists = true;
                $filePath = $actualPath;
            } else {
                // Try to find the file in various directories
                $filePath = $this->findBackupFile($disk, $actualFilename);
                $fileExists = !empty($filePath);
            }
            
            if (!$fileExists) {
                Log::warning('Backup file not found after creation', [
                    'filename' => $actualFilename,
                    'expected_path' => $actualPath
                ]);
            } else {
                Log::info('Backup file verified after creation', [
                    'filename' => $actualFilename,
                    'path' => $filePath
                ]);
            }
            
            Log::info('PHP-based backup created successfully', [
                'user' => $admin->username,
                'filename' => $actualFilename,
                'size' => $actualSize,
                'path' => $filePath,
                'exists' => $fileExists
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Database backup created successfully!',
                'method' => 'php',
                'filename' => $actualFilename,
                'path' => $filePath,
                'storage_path' => $storagePath,
                'size' => $this->formatBytes($actualSize),
                'raw_size' => $actualSize,
                'exists' => $fileExists
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
            $this->storagePath($this->backupPath),
            $this->storagePath('backup-temp'),
            // Add fallback directories
            $this->storagePath('Laravel'),
            $this->storagePath('backups'),
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
            Log::info('Download request received', ['filename' => $filename]);
            
            // Decode URL if it's URL encoded
            $decodedFilename = urldecode($filename);
            
            $disk = Storage::disk($this->backupDisk);
            
            // Find the file in any possible location
            $filePath = $this->findBackupFile($disk, $decodedFilename);
            
            if (!$filePath) {
                // Try with original filename if decoded didn't work
                $filePath = $this->findBackupFile($disk, $filename);
            }
            
            if (!$filePath) {
                Log::warning('Backup file not found for download', [
                    'filename' => $filename, 
                    'decoded' => $decodedFilename
                ]);
                
                // List all available backup files for debugging
                $allFiles = [];
                $candidateDirs = array_values(array_unique([
                    $this->backupPath,
                    config('backup.backup.name', 'Laravel'),
                    config('app.name', 'Laravel'),
                    'backups',
                    'Laravel',
                    '', // Also scan root directory
                ]));
                
                foreach ($candidateDirs as $dir) {
                    try {
                        $dirFiles = $disk->files($dir);
                        foreach ($dirFiles as $file) {
                            if (preg_match('/\.(zip|sql)$/i', $file)) {
                                $allFiles[] = $file;
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignore errors for individual directories
                    }
                }
                
                Log::warning('Available backup files', ['files' => $allFiles]);
                
                abort(404, 'Backup file not found: ' . $filename);
            }

            Log::info('Backup download initiated', [
                'filename' => $filename,
                'path' => $filePath,
                'user' => auth('admin')->check() ? auth('admin')->user()->username : 'unknown'
            ]);

            // Get the actual filename from the path
            $actualFilename = basename($filePath);
            
            // Ensure we preserve the requested extension if possible
            $requestedExtension = pathinfo($filename, PATHINFO_EXTENSION);
            $actualExtension = pathinfo($actualFilename, PATHINFO_EXTENSION);
            
            // Use the requested filename but ensure it has the correct extension
            $downloadFilename = $filename;
            if (!empty($requestedExtension) && strtolower($requestedExtension) !== strtolower($actualExtension)) {
                // Replace the extension in the requested filename
                $downloadFilename = pathinfo($filename, PATHINFO_FILENAME) . '.' . $actualExtension;
            }
            
            Log::info('Downloading file', [
                'requested' => $filename,
                'actual_path' => $filePath,
                'actual_file' => $actualFilename,
                'download_as' => $downloadFilename
            ]);
            
            // Get the full path to the file
            $fullPath = $this->storagePath($filePath);
            
            // Check if the file exists
            if (!file_exists($fullPath)) {
                Log::error('File not found at full path', ['path' => $fullPath]);
                abort(404, 'File not found');
            }
            
            // Get the file's MIME type (safe detection with fallbacks)
            $mimeType = $this->getMimeType($fullPath, 'application/octet-stream');
            
            // Set appropriate headers for download
            $headers = [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
                'Content-Length' => filesize($fullPath),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];
            
            Log::info('Serving file for download', [
                'path' => $fullPath,
                'filename' => $downloadFilename,
                'mime' => $mimeType,
                'size' => filesize($fullPath)
            ]);
            
            // Return the file as a download
            return response()->download($fullPath, $downloadFilename, $headers);
            
        } catch (\Exception $e) {
            Log::error('Backup download failed', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Failed to download backup: ' . $e->getMessage());
        }
    }
    
    /**
     * Find a backup file in any possible location
     */
    protected function findBackupFile($disk, $filename)
    {
        Log::info('Looking for backup file', ['filename' => $filename]);
        
        // Clean up filename to handle URL encoding and special characters
        $cleanFilename = basename(trim($filename));
        
        // Check if file exists directly
        if ($disk->exists($cleanFilename)) {
            Log::info('Found file directly', ['path' => $cleanFilename]);
            return $cleanFilename;
        }
        
        // Try with the direct path first
        $filePath = $this->backupPath . '/' . $cleanFilename;
        if ($disk->exists($filePath)) {
            Log::info('Found file in primary path', ['path' => $filePath]);
            return $filePath;
        }
        
        // Prepare candidates with all possible locations and extensions
        $candidates = [];
        $alt = preg_match('/\.zip$/i', $cleanFilename) ? preg_replace('/\.zip$/i', '.sql', $cleanFilename) : preg_replace('/\.sql$/i', '.zip', $cleanFilename);
        
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
                $candidates[] = $dir . '/' . $cleanFilename;
                if ($alt) $candidates[] = $dir . '/' . $alt;
            }
        }
        
        // Check all candidates
        foreach ($candidates as $candidate) {
            if ($disk->exists($candidate)) {
                Log::info('Found file in candidate path', ['path' => $candidate]);
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
            if ($baseFilename === $cleanFilename || $baseFilename === $alt) {
                Log::info('Found file in exhaustive search', ['path' => $file]);
                return $file;
            }
        }
        
        // Try fuzzy matching as a last resort
        foreach ($allFiles as $file) {
            $baseFilename = basename($file);
            // Try to match by database name + date part of the filename (avoid undefined properties)
            $dbName = config('database.connections.mysql.database', '');
            if (!empty($dbName) && strpos($cleanFilename, $dbName) !== false && strpos($baseFilename, $dbName) !== false) {
                // Extract date parts for comparison
                $datePattern = '/\d{4}-\d{2}-\d{2}/'; // matches YYYY-MM-DD
                preg_match($datePattern, $cleanFilename, $requestedDateMatch);
                preg_match($datePattern, $baseFilename, $fileNameDateMatch);
                
                if (!empty($requestedDateMatch) && !empty($fileNameDateMatch) && 
                    $requestedDateMatch[0] === $fileNameDateMatch[0]) {
                    Log::info('Found file with fuzzy matching', [
                        'requested' => $cleanFilename,
                        'found' => $baseFilename,
                        'path' => $file
                    ]);
                    return $file;
                }
            }
        }
        
        // Not found anywhere
        Log::warning('File not found anywhere', ['filename' => $cleanFilename]);
        return null;
    }

    /**
     * Safely determine a file's MIME type with fallbacks to extension mapping.
     */
    protected function getMimeType(string $fullPath, string $default = 'application/octet-stream'): string
    {
        try {
            if (function_exists('mime_content_type')) {
                $mime = @mime_content_type($fullPath);
                if ($mime) return $mime;
            }
        } catch (\Throwable $e) {
            // ignore and fallback
        }
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $map = [
            'zip' => 'application/zip',
            'sql' => 'application/sql',
            'json' => 'application/json',
            'txt' => 'text/plain',
        ];
        return $map[$ext] ?? $default;
    }

    /**
     * Direct download backup file (fallback method)
     */
    public function downloadDirect($filename)
    {
        try {
            Log::info('Direct download request received', ['filename' => $filename]);
            
            // Decode URL if it's URL encoded
            $decodedFilename = urldecode($filename);
            
            // Get the database name from config
            $dbName = config('database.connections.mysql.database', '');
            
            // Try to find the most recent backup file with matching date
            $disk = Storage::disk($this->backupDisk);
            $candidateDirs = array_values(array_unique([
                $this->backupPath,
                config('backup.backup.name', 'Laravel'),
                config('app.name', 'Laravel'),
                'backups',
                'Laravel',
                '', // Root directory
            ]));
            
            $matchingFiles = [];
            foreach ($candidateDirs as $dir) {
                try {
                    $dirFiles = $disk->files($dir);
                    foreach ($dirFiles as $file) {
                        if (preg_match('/\.(zip|sql)$/i', $file)) {
                            // Check if this file matches the database name and date pattern
                            if (strpos($file, $dbName) !== false) {
                                // Extract date from filename
                                if (preg_match('/(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
                                    $fileDate = $matches[1];
                                    // Check if date in requested filename matches
                                    if (strpos($decodedFilename, $fileDate) !== false) {
                                        $matchingFiles[] = $file;
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore errors for individual directories
                }
            }
            
            // Sort files by modification time (newest first)
            usort($matchingFiles, function($a, $b) use ($disk) {
                return $disk->lastModified($b) - $disk->lastModified($a);
            });
            
            // Use the most recent matching file
            if (!empty($matchingFiles)) {
                $filePath = $matchingFiles[0];
                
                // Ensure we preserve the requested extension
                $requestedExtension = pathinfo($filename, PATHINFO_EXTENSION);
                $actualFilename = basename($filePath);
                $actualExtension = pathinfo($actualFilename, PATHINFO_EXTENSION);
                
                // If the requested file has a specific extension (zip/sql), try to find a file with that extension
                if (!empty($requestedExtension) && strtolower($requestedExtension) !== strtolower($actualExtension)) {
                    // Look for a file with the same name but different extension
                    foreach ($matchingFiles as $file) {
                        $fileExt = pathinfo($file, PATHINFO_EXTENSION);
                        if (strtolower($fileExt) === strtolower($requestedExtension)) {
                            $filePath = $file;
                            $actualFilename = basename($file);
                            break;
                        }
                    }
                }
                
                Log::info('Found matching backup file', [
                    'requested' => $filename,
                    'requested_extension' => $requestedExtension,
                    'found' => $actualFilename,
                    'found_extension' => pathinfo($actualFilename, PATHINFO_EXTENSION),
                    'path' => $filePath
                ]);
                
                // Get the full path to the file
                $fullPath = $this->storagePath($filePath);
                
                // Log detailed information about the file
                Log::info('File details before download', [
                    'filePath' => $filePath,
                    'fullPath' => $fullPath,
                    'exists' => file_exists($fullPath),
                    'readable' => is_readable($fullPath),
                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                    'permissions' => file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'N/A'
                ]);
                
                // Check if the file exists
                if (!file_exists($fullPath)) {
                    Log::error('File not found at full path', ['path' => $fullPath]);
                    
                    // Try a direct approach to find the file
                    $alternativePath = $this->findBackupFileByName($decodedFilename);
                    if ($alternativePath && file_exists($alternativePath)) {
                        Log::info('Found alternative path for file', ['path' => $alternativePath]);
                        $fullPath = $alternativePath;
                    } else {
                        abort(404, 'File not found: ' . $decodedFilename);
                    }
                }
                
                // Get the file's MIME type using safe helper
                $mimeType = $this->getMimeType($fullPath, 'application/octet-stream');
                
                // Set appropriate headers for download
                $headers = [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'attachment; filename="' . $actualFilename . '"',
                    'Content-Length' => filesize($fullPath),
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ];
                
                Log::info('Serving file for download', [
                    'path' => $fullPath,
                    'filename' => $actualFilename,
                    'mime' => $mimeType,
                    'size' => filesize($fullPath)
                ]);
                
                // Return the file as a download (streamed, memory-safe)
                return response()->download($fullPath, $actualFilename, $headers);
            }
            
            // If no matching file found, try the regular download method
            return $this->download($filename);
        } catch (\Exception $e) {
            Log::error('Direct download failed', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
     * Find a backup file by name using direct file system access
     */
    protected function findBackupFileByName($filename)
    {
        // Clean up the filename
        $cleanFilename = basename(trim($filename));
        
        // Get the storage path
        $storagePath = $this->storagePath();
        
        // Possible locations to search
        $possibleDirs = [
            $storagePath . '/' . trim($this->backupPath, '/'),
            $storagePath . '/' . trim(config('backup.backup.name', 'Laravel'), '/'),
            $storagePath . '/' . trim(config('app.name', 'Laravel'), '/'),
            $storagePath . '/backups',
            $storagePath . '/Laravel',
            $storagePath, // Root directory
        ];
        
        // Try to find the exact file
        foreach ($possibleDirs as $dir) {
            $path = $dir . '/' . $cleanFilename;
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Try with alternative extension
        $altFilename = preg_match('/\.zip$/i', $cleanFilename) 
            ? preg_replace('/\.zip$/i', '.sql', $cleanFilename)
            : preg_replace('/\.sql$/i', '.zip', $cleanFilename);
        
        foreach ($possibleDirs as $dir) {
            $path = $dir . '/' . $altFilename;
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Try to find any file with similar name pattern
        $fileBaseName = pathinfo($cleanFilename, PATHINFO_FILENAME);
        $fileBaseParts = explode('_', $fileBaseName);
        
        // If we have a database name and date pattern
        if (count($fileBaseParts) >= 3) {
            $dbName = $fileBaseParts[0];
            $datePattern = isset($fileBaseParts[1]) ? $fileBaseParts[1] : '';
            
            foreach ($possibleDirs as $dir) {
                if (is_dir($dir)) {
                    $files = scandir($dir);
                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..') continue;
                        
                        if (strpos($file, $dbName) !== false && 
                            (empty($datePattern) || strpos($file, $datePattern) !== false)) {
                            return $dir . '/' . $file;
                        }
                    }
                }
            }
        }
        
        // Not found
        return null;
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

    /**
     * Get absolute path inside the configured storage disk root.
     */
    protected function storagePath(string $relative = ''): string
    {
        $clean = trim($relative, '/\\');
        if ($clean === '') {
            return $this->storageBasePath;
        }
        return $this->storageBasePath . DIRECTORY_SEPARATOR . $clean;
    }
}
