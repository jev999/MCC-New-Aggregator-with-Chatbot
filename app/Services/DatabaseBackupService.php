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
            Log::info('Starting PHP-based database backup', [
                'database' => $this->database,
                'connection' => $this->connection
            ]);
            
            // Get all tables
            $tables = $this->getTables();
            
            if (empty($tables)) {
                throw new Exception('No tables found in database');
            }
            
            Log::info('Found tables to backup', ['count' => count($tables)]);
            
            // Generate SQL dump
            $sqlDump = $this->generateSqlDump($tables);
            
            // Create filename with timestamp
            $filename = $this->database . '_' . date('Y-m-d_His') . '.sql';
            $zipFilename = $this->database . '_' . date('Y-m-d_His') . '.zip';
            
            // Save SQL file temporarily
            $tempPath = storage_path('app/backup-temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $sqlFilePath = $tempPath . '/' . $filename;
            file_put_contents($sqlFilePath, $sqlDump);
            
            Log::info('SQL dump created', [
                'file' => $sqlFilePath,
                'size' => filesize($sqlFilePath)
            ]);
            
            // Create ZIP archive
            $backupPath = config('backup.backup.name', 'Laravel');
            $zipPath = storage_path('app/' . $backupPath . '/' . $zipFilename);
            
            // Ensure directory exists
            $backupDir = storage_path('app/' . $backupPath);
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Create zip
            if ($this->createZipArchive($sqlFilePath, $zipPath, $filename)) {
                // Clean up temporary SQL file
                unlink($sqlFilePath);
                
                Log::info('Backup completed successfully', [
                    'zip_file' => $zipFilename,
                    'size' => filesize($zipPath)
                ]);
                
                return [
                    'success' => true,
                    'filename' => $zipFilename,
                    'path' => $zipPath,
                    'size' => filesize($zipPath)
                ];
            } else {
                throw new Exception('Failed to create ZIP archive');
            }
            
        } catch (Exception $e) {
            Log::error('PHP-based backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
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
                $dump .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                // Get table data
                $dump .= "-- Dumping data for table `{$table}`\n\n";
                
                $rows = DB::table($table)->get();
                
                if ($rows->count() > 0) {
                    $columns = array_keys((array) $rows[0]);
                    $columnsList = '`' . implode('`, `', $columns) . '`';
                    
                    // Insert data in batches to avoid memory issues
                    $batchSize = 100;
                    $batches = $rows->chunk($batchSize);
                    
                    foreach ($batches as $batch) {
                        $dump .= "INSERT INTO `{$table}` ({$columnsList}) VALUES\n";
                        
                        $values = [];
                        foreach ($batch as $row) {
                            $rowData = (array) $row;
                            $escapedValues = array_map(function($value) {
                                if (is_null($value)) {
                                    return 'NULL';
                                }
                                return "'" . addslashes($value) . "'";
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
                copy($sqlFilePath, str_replace('.zip', '.sql', $zipPath));
                return true;
            }
            
            $zip = new \ZipArchive();
            
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFile($sqlFilePath, $filename);
                $zip->close();
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Failed to create ZIP', ['error' => $e->getMessage()]);
            return false;
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
