<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;
use PDO;

class DatabaseBackupService
{
    protected $connection;
    protected $database;
    
    public function __construct()
    {
        $this->connection = config('database.default');
        $this->database = config("database.connections.{$this->connection}.database");
    }
    
    /**
     * Create a full database backup without using mysqldump
     * This works with remote databases and doesn't require system tools
     */
    public function createBackup()
    {
        try {
            // Get database connection info
            $host = config("database.connections.{$this->connection}.host");
            $username = config("database.connections.{$this->connection}.username");
            
            Log::info('Starting PHP-based database backup', [
                'database' => $this->database,
                'connection' => $this->connection,
                'host' => $host,
                'username' => $username
            ]);
            
            // Create necessary directories first
            $this->createBackupDirectories();
            
            // Test database connection first
            if (!$this->testDatabaseConnection()) {
                throw new Exception('Cannot connect to database. Please check your database credentials and connection settings.');
            }
            
            Log::info('Database connection verified successfully');
            
            // Get all tables
            $tables = $this->getTables();
            
            if (empty($tables)) {
                throw new Exception('No tables found in database');
            }
            
            Log::info('Found tables to backup', [
                'count' => count($tables),
                'tables' => implode(', ', array_slice($tables, 0, 5)) . (count($tables) > 5 ? '...' : '')
            ]);
            
            // Generate SQL dump
            $sqlDump = $this->generateSqlDump($tables);
            
            Log::info('SQL dump generated', [
                'size_bytes' => strlen($sqlDump),
                'size_mb' => round(strlen($sqlDump) / 1024 / 1024, 2)
            ]);
            
            // Create filename with timestamp
            $filename = $this->database . '_' . date('Y-m-d_His') . '.sql';
            $zipFilename = $this->database . '_' . date('Y-m-d_His') . '.zip';
            
            // Save SQL file temporarily
            $tempPath = storage_path('app/backup-temp');
            $sqlFilePath = $tempPath . '/' . $filename;
            
            // Write the SQL dump to file
            // Try multiple methods to ensure it works
            $bytesWritten = false;
            
            // Method 1: file_put_contents
            try {
                $bytesWritten = file_put_contents($sqlFilePath, $sqlDump);
            } catch (Exception $e) {
                Log::warning('file_put_contents failed: ' . $e->getMessage());
            }
            
            // Method 2: fopen/fwrite if method 1 failed
            if ($bytesWritten === false) {
                try {
                    $handle = fopen($sqlFilePath, 'w');
                    if ($handle) {
                        $bytesWritten = fwrite($handle, $sqlDump);
                        fclose($handle);
                    }
                } catch (Exception $e) {
                    Log::warning('fopen/fwrite failed: ' . $e->getMessage());
                }
            }
            
            if ($bytesWritten === false) {
                throw new Exception('Failed to write SQL dump to file. Check disk space and permissions.');
            }
            
            Log::info('SQL dump saved to temp file', [
                'file' => $sqlFilePath,
                'size' => $bytesWritten
            ]);
            
            // Always save to the primary backup directory for consistency
            $primaryBackupPath = config('backup.backup.name', 'Laravel');
            $backupPaths = [
                $primaryBackupPath,
                'backups',
                'Laravel',
                '' // Root directory as fallback
            ];
            
            // Try each backup path until one works
            $success = false;
            $actualPath = null;
            $actualFilename = null;
            $actualSize = 0;
            $storagePath = null;
            
            foreach ($backupPaths as $backupPath) {
                try {
                    // Create the directory path for this attempt
                    $backupDir = storage_path('app/' . $backupPath);
                    if (!empty($backupPath) && !file_exists($backupDir)) {
                        if (!@mkdir($backupDir, 0775, true)) {
                            Log::warning("Could not create directory: {$backupDir}");
                            continue; // Try next path
                        }
                    }
                    
                    // Set the zip path for this attempt
                    $zipPath = empty($backupPath) 
                        ? storage_path('app/' . $zipFilename) 
                        : storage_path('app/' . $backupPath . '/' . $zipFilename);
                    
                    // For storage path (relative to storage disk)
                    $storageRelativePath = empty($backupPath)
                        ? $zipFilename
                        : $backupPath . '/' . $zipFilename;
                    
                    // Try to create zip archive
                    if ($this->createZipArchive($sqlFilePath, $zipPath, $filename)) {
                        // Check if zip file was actually created
                        if (file_exists($zipPath)) {
                            $actualPath = $zipPath;
                        } else {
                            // Fall back to SQL file if ZIP creation failed
                            $sqlPath = str_replace('.zip', '.sql', $zipPath);
                            $actualPath = file_exists($sqlPath) ? $sqlPath : null;
                        }
                        
                        if ($actualPath && file_exists($actualPath)) {
                            $actualFilename = basename($actualPath);
                            $actualSize = filesize($actualPath);
                            $success = true;
                            $storagePath = str_replace('.zip', '', $storageRelativePath) . 
                                          (preg_match('/\.zip$/i', $actualFilename) ? '.zip' : '.sql');
                            
                            Log::info('Backup created successfully', [
                                'path' => $actualPath,
                                'storage_path' => $storagePath,
                                'directory' => $backupPath ?: 'root',
                                'size' => $this->formatBytes($actualSize)
                            ]);
                            break; // Success - exit the loop
                        }
                    }
                    
                    // If zip failed, try direct SQL file
                    $sqlBackupPath = empty($backupPath)
                        ? storage_path('app/' . $filename)
                        : storage_path('app/' . $backupPath . '/' . $filename);
                    
                    // For storage path (relative to storage disk)
                    $sqlStorageRelativePath = empty($backupPath)
                        ? $filename
                        : $backupPath . '/' . $filename;
                    
                    if (copy($sqlFilePath, $sqlBackupPath) || file_put_contents($sqlBackupPath, $sqlDump) !== false) {
                        $actualPath = $sqlBackupPath;
                        $actualFilename = $filename;
                        $actualSize = filesize($sqlBackupPath);
                        $success = true;
                        $storagePath = $sqlStorageRelativePath;
                        
                        Log::info('Backup saved as SQL file', [
                            'path' => $actualPath,
                            'storage_path' => $storagePath,
                            'directory' => $backupPath ?: 'root',
                            'size' => $this->formatBytes($actualSize)
                        ]);
                        break; // Success - exit the loop
                    }
                    
                } catch (Exception $e) {
                    Log::warning("Failed to save backup to {$backupPath}", ['error' => $e->getMessage()]);
                    // Continue to next path
                }
            }
            
            // Clean up temporary SQL file
            if (file_exists($sqlFilePath)) {
                @unlink($sqlFilePath);
            }
            
            if ($success) {
                Log::info('Backup completed successfully', [
                    'file' => $actualFilename,
                    'path' => $actualPath,
                    'size' => $actualSize,
                    'size_formatted' => $this->formatBytes($actualSize)
                ]);
                
                return [
                    'success' => true,
                    'filename' => $actualFilename,
                    'path' => $actualPath,
                    'storage_path' => $storagePath,
                    'size' => $actualSize
                ];
            } else {
                throw new Exception('Failed to create backup file in any location. Please check directory permissions.');
            }
            
        } catch (Exception $e) {
            Log::error('PHP-based backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }
    
    /**
     * Create all necessary backup directories
     */
    protected function createBackupDirectories()
    {
        $backupPath = config('backup.backup.name', 'Laravel');
        $directories = [
            storage_path('app/backup-temp'),
            storage_path('app/' . $backupPath),
            // Add fallback directories
            storage_path('app/Laravel'),
            storage_path('app/backups')
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (!@mkdir($dir, 0775, true)) {
                    Log::warning("Could not create directory: {$dir}");
                } else {
                    Log::info("Created directory: {$dir}");
                }
            }
            
            // Try to make writable if it exists but isn't writable
            if (file_exists($dir) && !is_writable($dir)) {
                @chmod($dir, 0775);
                if (!is_writable($dir)) {
                    Log::warning("Directory exists but is not writable: {$dir}");
                }
            }
        }
    }
    
    /**
     * Format bytes to human readable size
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Test database connection
     */
    protected function testDatabaseConnection()
    {
        try {
            // Try with PDO first
            DB::connection()->getPdo();
            
            // Try a simple query to verify full access
            $result = DB::select('SELECT 1 as test');
            if (empty($result) || !isset($result[0]->test) || $result[0]->test != 1) {
                throw new Exception('Database query test failed');
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Database connection test failed', [
                'error' => $e->getMessage(),
                'connection' => $this->connection,
                'database' => $this->database
            ]);
            return false;
        }
    }
    
    /**
     * Get all tables from the database
     */
    protected function getTables()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $this->database;
            
            $tableNames = [];
            foreach ($tables as $table) {
                $tableNames[] = $table->$tableKey;
            }
            
            return $tableNames;
        } catch (Exception $e) {
            Log::error('Failed to get tables', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * Generate SQL dump for all tables
     */
    protected function generateSqlDump($tables)
    {
        ini_set('memory_limit', '512M'); // Increase memory limit for large databases
        set_time_limit(300); // 5 minutes timeout
        
        $dump = "-- PHP-based Database Backup\n";
        $dump .= "-- Database: {$this->database}\n";
        $dump .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $dump .= "-- Generator: MCC News Aggregator Backup System\n\n";
        
        $dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $dump .= "SET time_zone = \"+00:00\";\n\n";
        
        $dump .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $dump .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $dump .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $dump .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
        
        // Backup each table
        foreach ($tables as $table) {
            try {
                Log::info("Backing up table: {$table}");
                
                $dump .= "\n\n-- --------------------------------------------------------\n";
                $dump .= "-- Table structure for table `{$table}`\n";
                $dump .= "-- --------------------------------------------------------\n\n";
                
                // Drop table if exists
                $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                
                // Get CREATE TABLE statement
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                if (isset($createTable[0]->{'Create Table'})) {
                    $dump .= $createTable[0]->{'Create Table'} . ";\n\n";
                } else if (isset($createTable[0]->{'Create View'})) {
                    $dump .= $createTable[0]->{'Create View'} . ";\n\n";
                    continue; // Skip data export for views
                } else {
                    $dump .= "-- Could not determine table structure for {$table}\n\n";
                    continue;
                }
                
                // Get table data
                $dump .= "-- Dumping data for table `{$table}`\n\n";
                
                // Get row count first to decide on strategy
                $count = DB::table($table)->count();
                
                // Skip empty tables
                if ($count === 0) {
                    $dump .= "-- Table is empty\n\n";
                    continue;
                }
                
                // For large tables, use pagination to avoid memory issues
                $batchSize = 100;
                $totalPages = ceil($count / $batchSize);
                
                for ($page = 0; $page < $totalPages; $page++) {
                    $rows = DB::table($table)
                        ->skip($page * $batchSize)
                        ->take($batchSize)
                        ->get();
                    
                    if ($rows->count() > 0) {
                        // Get column names from the first row
                        $columns = array_keys((array) $rows[0]);
                        $columnsList = '`' . implode('`, `', $columns) . '`';
                        
                        $dump .= "INSERT INTO `{$table}` ({$columnsList}) VALUES\n";
                        
                        $values = [];
                        foreach ($rows as $row) {
                            $rowData = (array) $row;
                            $escapedValues = array_map(function($value) {
                                if (is_null($value)) {
                                    return 'NULL';
                                } else if (is_numeric($value) && !is_string($value)) {
                                    return $value;
                                } else {
                                    return "'" . addslashes($value) . "'";
                                }
                            }, $rowData);
                            
                            $values[] = '(' . implode(', ', $escapedValues) . ')';
                        }
                        
                        $dump .= implode(",\n", $values) . ";\n";
                    }
                }
                
            } catch (Exception $e) {
                Log::warning("Failed to backup table {$table}", ['error' => $e->getMessage()]);
                $dump .= "-- ERROR backing up table {$table}: " . $e->getMessage() . "\n\n";
            }
        }
        
        $dump .= "\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $dump .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $dump .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
        
        return $dump;
    }
    
    /**
     * Create ZIP archive from SQL file
     */
    protected function createZipArchive($sqlFilePath, $zipPath, $filename)
    {
        try {
            if (!class_exists('ZipArchive')) {
                // Fallback: just save the SQL file without compression
                Log::warning('ZipArchive not available, saving uncompressed SQL');
                $sqlDestPath = str_replace('.zip', '.sql', $zipPath);
                
                // Make sure the directory exists
                $dir = dirname($sqlDestPath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0775, true);
                }
                
                // Copy the file
                if (!@copy($sqlFilePath, $sqlDestPath)) {
                    // If copy fails, try file_put_contents
                    $content = file_get_contents($sqlFilePath);
                    if ($content !== false) {
                        file_put_contents($sqlDestPath, $content);
                    } else {
                        throw new Exception("Failed to read source SQL file");
                    }
                }
                
                return true;
            }
            
            // Make sure the directory exists
            $dir = dirname($zipPath);
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            
            $zip = new \ZipArchive();
            $result = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            
            if ($result === true) {
                // Read file content and add it to zip
                $content = file_get_contents($sqlFilePath);
                if ($content !== false) {
                    // Make sure the filename has .sql extension
                    $sqlFilename = preg_match('/\.sql$/i', $filename) ? $filename : $filename . '.sql';
                    $zip->addFromString($sqlFilename, $content);
                    $zip->close();
                    
                    // Verify the zip file was created
                    if (file_exists($zipPath) && filesize($zipPath) > 0) {
                        Log::info('ZIP archive created successfully', [
                            'path' => $zipPath,
                            'size' => filesize($zipPath)
                        ]);
                        return true;
                    } else {
                        Log::warning('ZIP file creation failed or file is empty', [
                            'path' => $zipPath
                        ]);
                        return false;
                    }
                } else {
                    $zip->close();
                    throw new Exception("Failed to read SQL file content");
                }
            } else {
                // Handle ZipArchive error codes
                $errorMessages = [
                    \ZipArchive::ER_EXISTS => 'File already exists',
                    \ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                    \ZipArchive::ER_INVAL => 'Invalid argument',
                    \ZipArchive::ER_MEMORY => 'Memory allocation failure',
                    \ZipArchive::ER_NOENT => 'No such file',
                    \ZipArchive::ER_NOZIP => 'Not a zip archive',
                    \ZipArchive::ER_OPEN => 'Can\'t open file',
                    \ZipArchive::ER_READ => 'Read error',
                    \ZipArchive::ER_SEEK => 'Seek error'
                ];
                
                $errorMessage = isset($errorMessages[$result]) ? $errorMessages[$result] : 'Unknown error';
                Log::error('ZipArchive error', ['code' => $result, 'message' => $errorMessage]);
                
                // Fallback to SQL file
                $sqlDestPath = str_replace('.zip', '.sql', $zipPath);
                copy($sqlFilePath, $sqlDestPath);
                return true;
            }
        } catch (Exception $e) {
            Log::error('Failed to create ZIP', ['error' => $e->getMessage()]);
            
            // Last resort fallback - try to save the SQL file directly
            try {
                $sqlDestPath = str_replace('.zip', '.sql', $zipPath);
                copy($sqlFilePath, $sqlDestPath);
                return true;
            } catch (Exception $e2) {
                Log::error('Failed to save SQL file as fallback', ['error' => $e2->getMessage()]);
                return false;
            }
        }
    }
    
    /**
     * Get list of all backups
     */
    public function getBackups()
    {
        try {
            $backupPath = config('backup.backup.name', 'Laravel');
            $disk = Storage::disk('local');
            
            $files = $disk->allFiles($backupPath);
            $backups = [];
            
            foreach ($files as $file) {
                if (preg_match('/\.(zip|sql)$/', $file)) {
                    $backups[] = [
                        'name' => basename($file),
                        'path' => $file,
                        'size' => $disk->size($file),
                        'date' => $disk->lastModified($file)
                    ];
                }
            }
            
            return collect($backups)->sortByDesc('date')->values();
        } catch (Exception $e) {
            Log::error('Failed to get backups list', ['error' => $e->getMessage()]);
            return collect();
        }
    }
}
