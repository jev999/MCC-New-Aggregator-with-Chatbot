<?php

/**
 * Backup System Diagnostic Tool
 * This script diagnoses specific issues with the backup system
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

echo "Database Backup System Diagnostic Tool\n";
echo "===================================\n\n";

// Function to check if a directory is writable and create it if it doesn't exist
function checkDirectory($path, $description) {
    echo "Checking {$description} directory: {$path}\n";
    
    if (!file_exists($path)) {
        echo "  Directory does not exist. Creating...\n";
        if (mkdir($path, 0775, true)) {
            echo "  ✓ Directory created successfully\n";
        } else {
            echo "  ✗ Failed to create directory\n";
            echo "    Possible causes:\n";
            echo "    - PHP does not have write permissions to the parent directory\n";
            echo "    - Disk is full\n";
            echo "    - Path is invalid\n";
            
            // Try to get more information
            $parentDir = dirname($path);
            echo "    Parent directory ({$parentDir}) exists: " . (file_exists($parentDir) ? "Yes" : "No") . "\n";
            if (file_exists($parentDir)) {
                echo "    Parent directory writable: " . (is_writable($parentDir) ? "Yes" : "No") . "\n";
                echo "    Parent directory permissions: " . substr(sprintf('%o', fileperms($parentDir)), -4) . "\n";
            }
            return false;
        }
    } else {
        echo "  ✓ Directory exists\n";
    }
    
    if (is_writable($path)) {
        echo "  ✓ Directory is writable\n";
        
        // Test file creation
        $testFile = $path . '/test_' . time() . '.txt';
        if (file_put_contents($testFile, 'Test content')) {
            echo "  ✓ Successfully created test file\n";
            unlink($testFile);
            echo "  ✓ Successfully deleted test file\n";
        } else {
            echo "  ✗ Failed to create test file despite directory being 'writable'\n";
            echo "    This suggests a deeper permission issue or disk problem\n";
            return false;
        }
        
        return true;
    } else {
        echo "  ✗ Directory is not writable\n";
        echo "    Current permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
        echo "    Attempting to fix permissions...\n";
        
        if (@chmod($path, 0775)) {
            echo "  ✓ Permissions updated\n";
            if (is_writable($path)) {
                echo "  ✓ Directory is now writable\n";
                return true;
            } else {
                echo "  ✗ Directory is still not writable despite permission change\n";
                echo "    This suggests a deeper permission issue\n";
                return false;
            }
        } else {
            echo "  ✗ Failed to update permissions\n";
            echo "    This suggests PHP does not have sufficient privileges\n";
            return false;
        }
    }
}

// Check database connection
echo "1. Testing database connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "  ✓ Database connection successful\n";
    echo "  Database: " . config('database.connections.mysql.database') . "\n";
    echo "  Host: " . config('database.connections.mysql.host') . "\n";
    echo "  Username: " . config('database.connections.mysql.username') . "\n\n";
} catch (Exception $e) {
    echo "  ✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "  Please check your .env file and database settings\n\n";
    exit(1);
}

// Check storage directories
echo "2. Checking storage directories...\n";
$storageApp = storage_path('app');
$tempPath = storage_path('app/backup-temp');
$backupPath = storage_path('app/' . config('backup.backup.name', 'Laravel'));
$fallbackPath = storage_path('app/Laravel');

$storageAppOk = checkDirectory($storageApp, 'Storage app');
$tempPathOk = checkDirectory($tempPath, 'Backup temp');
$backupPathOk = checkDirectory($backupPath, 'Primary backup');
$fallbackPathOk = checkDirectory($fallbackPath, 'Fallback backup');

echo "\n";

// Check disk space
echo "3. Checking disk space...\n";
$freeSpace = disk_free_space(storage_path());
$totalSpace = disk_total_space(storage_path());
$freeSpaceGB = round($freeSpace / 1024 / 1024 / 1024, 2);
$totalSpaceGB = round($totalSpace / 1024 / 1024 / 1024, 2);
$percentFree = round(($freeSpace / $totalSpace) * 100, 2);

echo "  Total disk space: {$totalSpaceGB} GB\n";
echo "  Free disk space: {$freeSpaceGB} GB ({$percentFree}%)\n";

if ($freeSpaceGB < 1) {
    echo "  ✗ Warning: Less than 1GB of free space available\n";
    echo "    Low disk space may cause backup failures\n";
} else {
    echo "  ✓ Sufficient disk space available\n";
}
echo "\n";

// Check PHP settings
echo "4. Checking PHP settings...\n";
$memoryLimit = ini_get('memory_limit');
$maxExecutionTime = ini_get('max_execution_time');
$postMaxSize = ini_get('post_max_size');
$uploadMaxFilesize = ini_get('upload_max_filesize');

echo "  Memory Limit: {$memoryLimit}\n";
echo "  Max Execution Time: {$maxExecutionTime} seconds\n";
echo "  Post Max Size: {$postMaxSize}\n";
echo "  Upload Max Filesize: {$uploadMaxFilesize}\n";

// Check if memory limit is sufficient
$memoryLimitBytes = getBytes($memoryLimit);
if ($memoryLimitBytes < 256 * 1024 * 1024) {
    echo "  ✗ Memory limit is less than 256MB, which may be insufficient for large databases\n";
    echo "    Recommended: Set memory_limit to at least 256M in php.ini\n";
} else {
    echo "  ✓ Memory limit is sufficient\n";
}

// Check if max execution time is sufficient
if ($maxExecutionTime < 300 && $maxExecutionTime != 0) {
    echo "  ✗ Max execution time is less than 300 seconds, which may be insufficient for large databases\n";
    echo "    Recommended: Set max_execution_time to at least 300 in php.ini\n";
} else {
    echo "  ✓ Max execution time is sufficient\n";
}
echo "\n";

// Check for ZipArchive
echo "5. Checking for ZipArchive...\n";
if (class_exists('ZipArchive')) {
    echo "  ✓ ZipArchive is available\n";
} else {
    echo "  ✗ ZipArchive is not available\n";
    echo "    Backup system will fall back to uncompressed SQL files\n";
    echo "    Recommended: Install the PHP zip extension\n";
}
echo "\n";

// Check Laravel storage configuration
echo "6. Checking Laravel storage configuration...\n";
$backupConfig = config('backup.backup.name');
if ($backupConfig) {
    echo "  ✓ Backup configuration found: {$backupConfig}\n";
} else {
    echo "  ✗ Backup configuration not found\n";
    echo "    Using default: 'Laravel'\n";
}

// Check if storage disk is configured
$storageDisk = config('filesystems.disks.local');
if ($storageDisk) {
    echo "  ✓ Local storage disk is configured\n";
    echo "    Root: " . $storageDisk['root'] . "\n";
    
    // Check if the root directory exists and is writable
    if (file_exists($storageDisk['root'])) {
        echo "  ✓ Storage root directory exists\n";
        if (is_writable($storageDisk['root'])) {
            echo "  ✓ Storage root directory is writable\n";
        } else {
            echo "  ✗ Storage root directory is not writable\n";
            echo "    Permissions: " . substr(sprintf('%o', fileperms($storageDisk['root'])), -4) . "\n";
        }
    } else {
        echo "  ✗ Storage root directory does not exist\n";
    }
} else {
    echo "  ✗ Local storage disk is not configured\n";
}
echo "\n";

// Check for existing backup files
echo "7. Checking for existing backup files...\n";
$disk = Storage::disk('local');
$backupFiles = [];

// Check in multiple locations
$locations = [
    config('backup.backup.name', 'Laravel'),
    'Laravel',
    'backups',
    ''
];

foreach ($locations as $location) {
    try {
        $files = $disk->files($location);
        foreach ($files as $file) {
            if (preg_match('/\.(sql|zip)$/i', $file)) {
                $backupFiles[] = [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => $disk->size($file),
                    'last_modified' => $disk->lastModified($file)
                ];
            }
        }
    } catch (Exception $e) {
        // Ignore errors for individual directories
    }
}

if (count($backupFiles) > 0) {
    echo "  ✓ Found " . count($backupFiles) . " existing backup files\n";
    echo "  Most recent backups:\n";
    
    // Sort by last modified (newest first)
    usort($backupFiles, function($a, $b) {
        return $b['last_modified'] - $a['last_modified'];
    });
    
    // Show the 3 most recent backups
    $recentBackups = array_slice($backupFiles, 0, 3);
    foreach ($recentBackups as $backup) {
        $date = date('Y-m-d H:i:s', $backup['last_modified']);
        $size = formatBytes($backup['size']);
        echo "    • {$backup['name']} ({$size}) - {$date}\n";
    }
} else {
    echo "  ✗ No existing backup files found\n";
}
echo "\n";

// Summary and recommendations
echo "SUMMARY AND RECOMMENDATIONS:\n";
echo "===========================\n";

$issues = [];

if (!$storageAppOk) $issues[] = "Storage app directory is not writable";
if (!$tempPathOk) $issues[] = "Backup temp directory is not writable";
if (!$backupPathOk) $issues[] = "Primary backup directory is not writable";
if (!$fallbackPathOk) $issues[] = "Fallback backup directory is not writable";
if ($freeSpaceGB < 1) $issues[] = "Low disk space (less than 1GB free)";
if ($memoryLimitBytes < 256 * 1024 * 1024) $issues[] = "PHP memory limit is too low";
if ($maxExecutionTime < 300 && $maxExecutionTime != 0) $issues[] = "PHP max execution time is too low";
if (!class_exists('ZipArchive')) $issues[] = "PHP zip extension is not installed";

if (count($issues) > 0) {
    echo "The following issues were detected:\n";
    foreach ($issues as $index => $issue) {
        echo ($index + 1) . ". {$issue}\n";
    }
    echo "\n";
    
    echo "RECOMMENDED ACTIONS:\n";
    if (!$storageAppOk || !$tempPathOk || !$backupPathOk || !$fallbackPathOk) {
        echo "1. Fix directory permissions:\n";
        echo "   - Run the following command in your terminal:\n";
        echo "     chmod -R 775 " . storage_path('app') . "\n";
        echo "   - Make sure the web server user (www-data, apache, etc.) has write access\n";
    }
    
    if ($freeSpaceGB < 1) {
        echo "2. Free up disk space:\n";
        echo "   - Remove unnecessary files\n";
        echo "   - Consider increasing disk space\n";
    }
    
    if ($memoryLimitBytes < 256 * 1024 * 1024 || ($maxExecutionTime < 300 && $maxExecutionTime != 0)) {
        echo "3. Update PHP settings in php.ini:\n";
        if ($memoryLimitBytes < 256 * 1024 * 1024) {
            echo "   - memory_limit = 256M\n";
        }
        if ($maxExecutionTime < 300 && $maxExecutionTime != 0) {
            echo "   - max_execution_time = 300\n";
        }
    }
    
    if (!class_exists('ZipArchive')) {
        echo "4. Install PHP zip extension:\n";
        echo "   - On Ubuntu/Debian: sudo apt-get install php-zip\n";
        echo "   - On CentOS/RHEL: sudo yum install php-zip\n";
        echo "   - On Windows with XAMPP: Enable the extension in php.ini\n";
    }
} else {
    echo "✓ No major issues detected. The backup system should work correctly.\n";
    echo "If you're still experiencing problems, please check the Laravel logs at:\n";
    echo storage_path('logs') . "\n";
}

echo "\n";
echo "After addressing these issues, try running the backup again at:\n";
echo "https://mcc-nac.com/superadmin/backup\n";

// Helper functions
function getBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    
    return $val;
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
