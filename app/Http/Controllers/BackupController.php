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

            // Increase memory and execution limits for large databases
            @ini_set('memory_limit', '512M');
            @set_time_limit(300); // 5 minutes

            // Log the start of backup creation
            \Log::info('Backup creation started', [
                'user' => $admin->username ?? 'unknown',
                'ip' => $request->ip(),
                'timestamp' => now(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
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
                \Log::info('Backup directory does not exist, attempting to create', ['path' => $this->backupPath]);
                try {
                    if (!File::makeDirectory($this->backupPath, 0755, true)) {
                        \Log::error('Failed to create backup directory', [
                            'path' => $this->backupPath,
                            'parent_writable' => is_writable(dirname($this->backupPath)),
                            'parent_exists' => File::exists(dirname($this->backupPath))
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to create backup directory. Please ensure storage/app has write permissions (chmod 775 or 777).'
                        ], 500);
                    }
                    \Log::info('Backup directory created successfully', ['path' => $this->backupPath]);
                } catch (\Exception $e) {
                    \Log::error('Exception while creating backup directory', [
                        'path' => $this->backupPath,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create backup directory: ' . $e->getMessage()
                    ], 500);
                }
            }

            if (!is_writable($this->backupPath)) {
                \Log::error('Backup directory is not writable', [
                    'path' => $this->backupPath,
                    'permissions' => substr(sprintf('%o', fileperms($this->backupPath)), -4),
                    'owner' => function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($this->backupPath)) : 'unknown',
                    'php_user' => function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid()) : 'unknown'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Backup directory is not writable. Please run: chmod -R 775 storage/app/backups or contact your server administrator.'
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

    /**
     * Test backup system - diagnostic endpoint
     * This helps identify issues in production
     */
    public function test()
    {
        try {
            $diagnostics = [];
            
            // Check authentication
            $diagnostics['authenticated'] = auth('admin')->check();
            $diagnostics['is_superadmin'] = auth('admin')->check() && auth('admin')->user()->isSuperAdmin();
            
            // Check PHP configuration
            $diagnostics['php'] = [
                'version' => PHP_VERSION,
                'os' => PHP_OS,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ];
            
            // Check directory permissions
            $diagnostics['directories'] = [
                'backup_path' => $this->backupPath,
                'exists' => File::exists($this->backupPath),
                'is_writable' => File::exists($this->backupPath) ? is_writable($this->backupPath) : false,
                'permissions' => File::exists($this->backupPath) ? substr(sprintf('%o', fileperms($this->backupPath)), -4) : 'N/A',
                'parent_exists' => File::exists(dirname($this->backupPath)),
                'parent_writable' => File::exists(dirname($this->backupPath)) ? is_writable(dirname($this->backupPath)) : false,
            ];
            
            // Check database connection
            try {
                DB::connection()->getPdo();
                $diagnostics['database'] = [
                    'connected' => true,
                    'connection' => config('database.default'),
                    'host' => config('database.connections.mysql.host'),
                    'database' => config('database.connections.mysql.database'),
                    'username' => config('database.connections.mysql.username'),
                    'port' => config('database.connections.mysql.port'),
                ];
                
                // Try to get table count
                $tables = $this->getAllTables();
                $diagnostics['database']['table_count'] = count($tables);
                $diagnostics['database']['tables_sample'] = array_slice($tables, 0, 5);
            } catch (\Exception $e) {
                $diagnostics['database'] = [
                    'connected' => false,
                    'error' => $e->getMessage(),
                ];
            }
            
            // Check mysqldump availability
            $diagnostics['mysqldump_available'] = $this->isMysqldumpAvailable();
            
            // Check disk space
            $diagnostics['disk_space'] = [
                'free_space' => disk_free_space(storage_path()),
                'total_space' => disk_total_space(storage_path()),
                'free_space_formatted' => $this->formatBytes(disk_free_space(storage_path())),
                'total_space_formatted' => $this->formatBytes(disk_total_space(storage_path())),
            ];
            
            // Overall health check
            $diagnostics['status'] = 'OK';
            $diagnostics['issues'] = [];
            
            if (!$diagnostics['authenticated']) {
                $diagnostics['issues'][] = 'User not authenticated';
            }
            if (!$diagnostics['is_superadmin']) {
                $diagnostics['issues'][] = 'User is not a superadmin';
            }
            if (!$diagnostics['directories']['exists']) {
                $diagnostics['issues'][] = 'Backup directory does not exist';
            }
            if ($diagnostics['directories']['exists'] && !$diagnostics['directories']['is_writable']) {
                $diagnostics['issues'][] = 'Backup directory is not writable';
            }
            if (!$diagnostics['database']['connected']) {
                $diagnostics['issues'][] = 'Database connection failed';
            }
            if ($diagnostics['disk_space']['free_space'] < 100 * 1024 * 1024) { // Less than 100MB
                $diagnostics['issues'][] = 'Low disk space (less than 100MB available)';
            }
            
            if (count($diagnostics['issues']) > 0) {
                $diagnostics['status'] = 'ISSUES_FOUND';
            }
            
            return response()->json($diagnostics);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode to see trace'
            ], 500);
        }
    }
}
