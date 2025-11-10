<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class BackupController extends Controller
{
    protected $backupPath;

    public function __construct()
    {
        // Set backup directory in storage/app/backups
        $this->backupPath = storage_path('app/backups');
        
        // Create backups directory if it doesn't exist
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Display backup management page
     */
    public function index()
    {
        // Get all backup files
        $backups = collect(File::files($this->backupPath))
            ->map(function ($file) {
                return [
                    'filename' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                    'created_at_human' => Carbon::createFromTimestamp($file->getMTime())->diffForHumans(),
                ];
            })
            ->sortByDesc('created_at');

        // Get database statistics
        $dbStats = $this->getDatabaseStats();

        return view('superadmin.backup.index', compact('backups', 'dbStats'));
    }

    /**
     * Create new database backup
     */
    public function create(Request $request)
    {
        try {
            // Verify authentication
            if (!auth('admin')->check()) {
                \Log::warning('Backup creation attempted without authentication');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login again.'
                ], 401);
            }

            // Verify superadmin role
            $admin = auth('admin')->user();
            if (!$admin->isSuperAdmin()) {
                \Log::warning('Backup creation attempted by non-superadmin', [
                    'user' => $admin->username,
                    'role' => $admin->role
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only superadmins can create backups.'
                ], 403);
            }

            // Log the start of backup creation
            \Log::info('Backup creation started', [
                'user' => $admin->username ?? 'unknown',
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            // Use config() instead of env() for better production compatibility
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', '3306');

            // Validate database credentials
            if (empty($database) || empty($username) || empty($host)) {
                \Log::error('Database configuration is incomplete');
                return response()->json([
                    'success' => false,
                    'message' => 'Database configuration error. Please check your .env file.'
                ], 500);
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupPath . '/' . $filename;

            // Ensure backup directory exists and is writable
            if (!File::exists($this->backupPath)) {
                if (!File::makeDirectory($this->backupPath, 0755, true)) {
                    \Log::error('Failed to create backup directory', ['path' => $this->backupPath]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create backup directory. Please check file permissions.'
                    ], 500);
                }
            }

            if (!is_writable($this->backupPath)) {
                \Log::error('Backup directory is not writable', ['path' => $this->backupPath]);
                return response()->json([
                    'success' => false,
                    'message' => 'Backup directory is not writable. Please check file permissions.'
                ], 500);
            }

            // Check if mysqldump is available (only on Unix-like systems)
            $mysqldumpAvailable = $this->isMysqldumpAvailable();

            if ($mysqldumpAvailable) {
                // Create mysqldump command
                $command = sprintf(
                    'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s 2>&1',
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($database),
                    escapeshellarg($filepath)
                );

                // Execute backup
                exec($command, $output, $returnVar);

                if ($returnVar === 0 && File::exists($filepath) && File::size($filepath) > 0) {
                    \Log::info('Backup created successfully using mysqldump', [
                        'filename' => $filename,
                        'size' => $this->formatBytes(File::size($filepath))
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Database backup created successfully!',
                        'filename' => $filename,
                        'size' => $this->formatBytes(File::size($filepath))
                    ]);
                } else {
                    \Log::warning('Mysqldump failed, falling back to Laravel method', [
                        'return_var' => $returnVar,
                        'output' => $output,
                        'filepath_exists' => File::exists($filepath),
                        'file_size' => File::exists($filepath) ? File::size($filepath) : 0
                    ]);
                    
                    // Clean up failed mysqldump file
                    if (File::exists($filepath)) {
                        File::delete($filepath);
                    }
                }
            } else {
                \Log::info('Mysqldump not available, using Laravel backup method');
            }

            // Fallback: Use Laravel DB backup
            return $this->createLaravelBackup($filename);

        } catch (\Exception $e) {
            \Log::error('Backup creation failed with exception', [
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
     * Fallback Laravel-based backup method
     */
    protected function createLaravelBackup($filename)
    {
        try {
            // Increase memory and execution time for large remote databases
            ini_set('memory_limit', '512M');
            set_time_limit(600); // 10 minutes
            
            // Test database connection first
            try {
                DB::connection()->getPdo();
                \Log::info('Database connection successful');
            } catch (\Exception $e) {
                \Log::error('Database connection failed', [
                    'error' => $e->getMessage(),
                    'host' => config('database.connections.mysql.host'),
                    'database' => config('database.connections.mysql.database'),
                    'username' => config('database.connections.mysql.username')
                ]);
                throw new \Exception('Cannot connect to database. Please check your database credentials and connection. Error: ' . $e->getMessage());
            }

            $filepath = $this->backupPath . '/' . $filename;
            $tables = $this->getAllTables();
            
            if (empty($tables)) {
                throw new \Exception('No tables found in database');
            }
            
            \Log::info('Starting backup for ' . count($tables) . ' tables', ['tables' => $tables]);
            
            // Add SQL header
            $sql = "-- MySQL Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Host: " . config('database.connections.mysql.host') . "\n";
            $sql .= "-- Database: " . config('database.connections.mysql.database') . "\n";
            $sql .= "-- ------------------------------------------------------\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $sql .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sql .= "SET time_zone = \"+00:00\";\n\n";

            // Backup each table
            foreach ($tables as $index => $table) {
                \Log::info("Backing up table {$table}", ['progress' => ($index + 1) . '/' . count($tables)]);
                
                try {
                    $sql .= $this->getTableStructure($table);
                    $sql .= $this->getTableData($table);
                } catch (\Exception $e) {
                    \Log::error("Failed to backup table {$table}", ['error' => $e->getMessage()]);
                    $sql .= "-- ERROR backing up table {$table}: " . $e->getMessage() . "\n\n";
                }
            }
            
            // Add SQL footer
            $sql .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

            File::put($filepath, $sql);

            if (File::exists($filepath) && File::size($filepath) > 0) {
                \Log::info('Backup created successfully', [
                    'filename' => $filename,
                    'size' => $this->formatBytes(File::size($filepath)),
                    'tables' => count($tables)
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Database backup created successfully!',
                    'filename' => $filename,
                    'size' => $this->formatBytes(File::size($filepath)),
                    'tables' => count($tables)
                ]);
            }

            throw new \Exception('Backup file was not created or is empty');
        } catch (\Exception $e) {
            \Log::error('Laravel backup method failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download($filename)
    {
        $filepath = $this->backupPath . '/' . $filename;

        if (!File::exists($filepath)) {
            abort(404, 'Backup file not found');
        }

        return Response::download($filepath, $filename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Delete backup file
     */
    public function delete($filename)
    {
        try {
            $filepath = $this->backupPath . '/' . $filename;

            if (!File::exists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            File::delete($filepath);

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all database tables
     */
    protected function getAllTables()
    {
        try {
            $database = config('database.connections.mysql.database');
            
            \Log::info('Getting all tables from database', [
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'database' => $database
            ]);

            // Set longer timeout for remote connections
            DB::connection()->getPdo()->setAttribute(\PDO::ATTR_TIMEOUT, 300);
            
            $results = DB::select('SHOW TABLES');
            $tables = [];

            // Handle different result formats
            if (empty($results)) {
                \Log::error('No tables found in SHOW TABLES result');
                throw new \Exception('No tables found in database');
            }

            // Try different property names that MySQL might return
            $possibleKeys = [
                'Tables_in_' . $database,
                'Tables_in_' . strtolower($database),
                'TABLE_NAME',
                'Table'
            ];

            foreach ($results as $result) {
                $resultArray = (array) $result;
                $tableName = null;
                
                // Try to find the table name with different possible keys
                foreach ($possibleKeys as $key) {
                    if (isset($resultArray[$key])) {
                        $tableName = $resultArray[$key];
                        break;
                    }
                }
                
                // If still not found, take the first value
                if ($tableName === null && !empty($resultArray)) {
                    $tableName = reset($resultArray);
                }
                
                if ($tableName) {
                    $tables[] = $tableName;
                }
            }

            if (empty($tables)) {
                \Log::error('Could not parse table names from results', [
                    'sample_result' => json_encode($results[0] ?? null),
                    'database' => $database
                ]);
                throw new \Exception('Could not retrieve table names from database');
            }

            \Log::info('Successfully retrieved tables', ['count' => count($tables), 'tables' => $tables]);
            return $tables;

        } catch (\Exception $e) {
            \Log::error('Failed to get tables', [
                'error' => $e->getMessage(),
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get table structure (CREATE TABLE statement)
     */
    protected function getTableStructure($table)
    {
        try {
            $structure = DB::select("SHOW CREATE TABLE `{$table}`");
            
            if (empty($structure)) {
                \Log::error('Empty structure result for table', ['table' => $table]);
                throw new \Exception("Could not get structure for table: {$table}");
            }
            
            // Handle different property names
            $createTable = null;
            $structureObj = $structure[0];
            
            if (isset($structureObj->{'Create Table'})) {
                $createTable = $structureObj->{'Create Table'};
            } elseif (isset($structureObj->{'CREATE TABLE'})) {
                $createTable = $structureObj->{'CREATE TABLE'};
            } else {
                // Try to get the second property (first is table name, second is CREATE statement)
                $structureArray = (array) $structureObj;
                $values = array_values($structureArray);
                if (isset($values[1])) {
                    $createTable = $values[1];
                }
            }
            
            if (!$createTable) {
                \Log::error('Could not find CREATE TABLE statement', [
                    'table' => $table,
                    'structure_keys' => array_keys((array) $structureObj)
                ]);
                throw new \Exception("Could not parse CREATE TABLE for: {$table}");
            }

            return "\n\n-- Table structure for table `{$table}`\n"
                . "DROP TABLE IF EXISTS `{$table}`;\n"
                . $createTable . ";\n\n";
                
        } catch (\Exception $e) {
            \Log::error('Failed to get table structure', [
                'table' => $table,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get table data (INSERT statements)
     */
    protected function getTableData($table)
    {
        try {
            // Use chunk to handle large tables better on remote connections
            $sql = "-- Dumping data for table `{$table}`\n";
            $rowCount = 0;
            
            DB::table($table)->orderBy(DB::raw('1'))->chunk(1000, function ($rows) use (&$sql, &$rowCount) {
                foreach ($rows as $row) {
                    $values = [];
                    foreach ((array)$row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            // Better escaping for special characters
                            $values[] = "'" . str_replace(
                                ["'", "\n", "\r", "\0", "\x1a"],
                                ["\\'", "\\n", "\\r", "\\0", "\\Z"],
                                $value
                            ) . "'";
                        }
                    }
                    
                    $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    $rowCount++;
                }
            });
            
            \Log::info("Backed up table data", ['table' => $table, 'rows' => $rowCount]);

            return $sql . "\n";
            
        } catch (\Exception $e) {
            \Log::error('Failed to get table data', [
                'table' => $table,
                'error' => $e->getMessage()
            ]);
            // Return comment instead of failing completely
            return "-- Error dumping data for table `{$table}`: " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * Get database statistics
     */
    protected function getDatabaseStats()
    {
        try {
            $tables = $this->getAllTables();
            $totalRecords = 0;
            $tableStats = [];

            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    $totalRecords += $count;
                    
                    $tableStats[] = [
                        'name' => $table,
                        'records' => $count
                    ];
                } catch (\Exception $e) {
                    \Log::warning('Could not count records for table', [
                        'table' => $table,
                        'error' => $e->getMessage()
                    ]);
                    $tableStats[] = [
                        'name' => $table,
                        'records' => 0
                    ];
                }
            }

            // Sort tables by record count
            usort($tableStats, function($a, $b) {
                return $b['records'] - $a['records'];
            });

            return [
                'total_tables' => count($tables),
                'total_records' => $totalRecords,
                'tables' => collect($tableStats)->take(10), // Top 10 tables
                'database_name' => config('database.connections.mysql.database'),
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to get database stats', ['error' => $e->getMessage()]);
            return [
                'total_tables' => 0,
                'total_records' => 0,
                'tables' => collect([]),
                'database_name' => config('database.connections.mysql.database'),
            ];
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

    /**
     * Check if mysqldump command is available on the system
     */
    protected function isMysqldumpAvailable()
    {
        // Only check on Unix-like systems (Linux/Mac), not Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return false;
        }

        $command = 'which mysqldump 2>/dev/null';
        exec($command, $output, $returnVar);

        return $returnVar === 0 && !empty($output);
    }
}
