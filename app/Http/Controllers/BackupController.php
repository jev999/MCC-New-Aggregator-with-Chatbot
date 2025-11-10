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
            
            \Log::info('Starting backup for ' . count($tables) . ' tables');
            $sql = '';

            foreach ($tables as $table) {
                $sql .= $this->getTableStructure($table);
                $sql .= $this->getTableData($table);
            }

            File::put($filepath, $sql);

            if (File::exists($filepath) && File::size($filepath) > 0) {
                \Log::info('Backup created successfully', [
                    'filename' => $filename,
                    'size' => $this->formatBytes(File::size($filepath)),
                    'tables' => count($tables)
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Database backup created successfully (Laravel method)!',
                    'filename' => $filename,
                    'size' => $this->formatBytes(File::size($filepath))
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
     * Restore database from backup file
     */
    public function restore(Request $request)
    {
        try {
            // Verify authentication
            if (!auth('admin')->check()) {
                \Log::warning('Backup restore attempted without authentication');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login again.'
                ], 401);
            }

            // Verify superadmin role
            $admin = auth('admin')->user();
            if (!$admin->isSuperAdmin()) {
                \Log::warning('Backup restore attempted by non-superadmin', [
                    'user' => $admin->username,
                    'role' => $admin->role
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only superadmins can restore backups.'
                ], 403);
            }

            $filename = $request->input('filename');
            
            if (empty($filename)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup filename is required.'
                ], 400);
            }

            $filepath = $this->backupPath . '/' . $filename;

            if (!File::exists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found.'
                ], 404);
            }

            // Log the start of restore
            \Log::info('Database restore started', [
                'user' => $admin->username,
                'filename' => $filename,
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            // Use config() instead of env() for better production compatibility
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', '3306');

            // Check if mysql command is available
            $mysqlAvailable = $this->isMysqlAvailable();

            if ($mysqlAvailable) {
                // Use mysql command to restore
                $command = sprintf(
                    'mysql --user=%s --password=%s --host=%s --port=%s %s < %s 2>&1',
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($database),
                    escapeshellarg($filepath)
                );

                exec($command, $output, $returnVar);

                if ($returnVar === 0) {
                    \Log::info('Database restored successfully using mysql command', [
                        'filename' => $filename,
                        'user' => $admin->username
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Database restored successfully!'
                    ]);
                } else {
                    \Log::warning('MySQL command restore failed, falling back to Laravel method', [
                        'return_var' => $returnVar,
                        'output' => $output
                    ]);
                }
            }

            // Fallback: Use Laravel DB to execute SQL statements
            return $this->restoreLaravelMethod($filepath);

        } catch (\Exception $e) {
            \Log::error('Database restore failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => auth('admin')->check() ? auth('admin')->user()->username : 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore database using Laravel DB (fallback method)
     */
    protected function restoreLaravelMethod($filepath)
    {
        try {
            // Read SQL file
            $sql = File::get($filepath);
            
            if (empty($sql)) {
                throw new \Exception('Backup file is empty or unreadable');
            }

            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Split SQL into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($statement) {
                    return !empty($statement) && !preg_match('/^--/', $statement);
                }
            );

            $admin = auth('admin')->user();
            \Log::info('Executing ' . count($statements) . ' SQL statements for restore', [
                'user' => $admin->username
            ]);

            // Execute each statement
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    DB::statement($statement);
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            \Log::info('Database restored successfully using Laravel method', [
                'statements_executed' => count($statements),
                'user' => $admin->username
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Database restored successfully (Laravel method)!'
            ]);

        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $ignored) {}

            \Log::error('Laravel restore method failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Failed to restore using Laravel method: ' . $e->getMessage());
        }
    }

    /**
     * Check if mysql command is available on the system
     */
    protected function isMysqlAvailable()
    {
        // Only check on Unix-like systems (Linux/Mac), not Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // On Windows, check if mysql is in PATH
            exec('where mysql 2>nul', $output, $returnVar);
            return $returnVar === 0 && !empty($output);
        }

        $command = 'which mysql 2>/dev/null';
        exec($command, $output, $returnVar);

        return $returnVar === 0 && !empty($output);
    }

    /**
     * Get all database tables
     */
    protected function getAllTables()
    {
        try {
            \Log::info('Getting all tables from database', [
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database')
            ]);

            $results = DB::select('SHOW TABLES');
            $tables = [];

            $dbName = 'Tables_in_' . config('database.connections.mysql.database');

            foreach ($results as $result) {
                $tables[] = $result->$dbName;
            }

            \Log::info('Successfully retrieved tables', ['count' => count($tables)]);
            return $tables;

        } catch (\Exception $e) {
            \Log::error('Failed to get tables', [
                'error' => $e->getMessage(),
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host')
            ]);
            throw $e;
        }
    }

    /**
     * Get table structure (CREATE TABLE statement)
     */
    protected function getTableStructure($table)
    {
        $structure = DB::select("SHOW CREATE TABLE `{$table}`");
        $createTable = $structure[0]->{'Create Table'};

        return "\n\n-- Table structure for table `{$table}`\n"
            . "DROP TABLE IF EXISTS `{$table}`;\n"
            . $createTable . ";\n\n";
    }

    /**
     * Get table data (INSERT statements)
     */
    protected function getTableData($table)
    {
        $data = DB::table($table)->get();
        $sql = "-- Dumping data for table `{$table}`\n";

        if ($data->count() > 0) {
            foreach ($data as $row) {
                $values = [];
                foreach ((array)$row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . addslashes($value) . "'";
                    }
                }
                
                $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
            }
        }

        return $sql . "\n";
    }

    /**
     * Get database statistics
     */
    protected function getDatabaseStats()
    {
        $tables = $this->getAllTables();
        $totalRecords = 0;
        $tableStats = [];

        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            $totalRecords += $count;
            
            $tableStats[] = [
                'name' => $table,
                'records' => $count
            ];
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
