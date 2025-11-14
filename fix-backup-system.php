<?php

/**
 * Database Backup System Fix Tool
 * This script diagnoses and fixes common issues with the backup system
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

echo "Database Backup System Fix Tool\n";
echo "=============================\n\n";

// Step 1: Check database connection
echo "1. Testing database connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "   ✓ Database connection successful\n";
    echo "   Database: " . config('database.connections.mysql.database') . "\n";
    echo "   Host: " . config('database.connections.mysql.host') . "\n";
    echo "   Username: " . config('database.connections.mysql.username') . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "   Please check your .env file and database settings\n\n";
}

// Step 2: Check and fix storage directories
echo "2. Checking and fixing storage directories...\n";

// Define all backup directories that need to be checked
$directories = [
    storage_path('app/backup-temp'),
    storage_path('app/Laravel'),
    storage_path('app/backups'),
    storage_path('app/' . config('backup.backup.name', 'Laravel')),
    storage_path('app/' . config('app.name', 'Laravel')),
    storage_path('app')
];

foreach ($directories as $dir) {
    echo "   Checking: " . basename($dir) . "\n";
    
    // Create directory if it doesn't exist
    if (!file_exists($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "   ✓ Created directory: " . basename($dir) . "\n";
        } else {
            echo "   ✗ Failed to create directory: " . basename($dir) . "\n";
            echo "     Trying to fix permissions...\n";
            
            // Try to fix parent directory permissions
            $parentDir = dirname($dir);
            if (file_exists($parentDir)) {
                chmod($parentDir, 0775);
                
                // Try creating again
                if (mkdir($dir, 0775, true)) {
                    echo "   ✓ Created directory after permission fix: " . basename($dir) . "\n";
                } else {
                    echo "   ✗ Still cannot create directory. Please check manually.\n";
                }
            }
        }
    } else {
        echo "   ✓ Directory exists: " . basename($dir) . "\n";
    }
    
    // Check if directory is writable
    if (file_exists($dir) && !is_writable($dir)) {
        echo "   ✗ Directory not writable: " . basename($dir) . "\n";
        echo "     Fixing permissions...\n";
        
        if (chmod($dir, 0775)) {
            echo "   ✓ Fixed permissions for: " . basename($dir) . "\n";
        } else {
            echo "   ✗ Failed to fix permissions. Please fix manually.\n";
            echo "     Command: chmod -R 775 " . $dir . "\n";
        }
    } else if (file_exists($dir)) {
        echo "   ✓ Directory is writable: " . basename($dir) . "\n";
    }
    
    echo "\n";
}

// Step 3: Test file writing
echo "3. Testing file writing capability...\n";
$testFile = storage_path('app/backup-temp/test_write.txt');
$testContent = 'Backup system test file - ' . date('Y-m-d H:i:s');

try {
    if (file_put_contents($testFile, $testContent)) {
        echo "   ✓ Successfully wrote test file\n";
        
        // Verify content
        $readContent = file_get_contents($testFile);
        if ($readContent === $testContent) {
            echo "   ✓ Successfully verified file content\n";
        } else {
            echo "   ✗ File content verification failed\n";
        }
        
        // Clean up
        unlink($testFile);
        echo "   ✓ Test file removed\n";
    } else {
        echo "   ✗ Failed to write test file\n";
        echo "     Please check PHP write permissions\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error during file write test: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 4: Check disk space
echo "4. Checking disk space...\n";
$freeSpace = disk_free_space(storage_path());
$totalSpace = disk_total_space(storage_path());
$freeSpaceGB = round($freeSpace / 1024 / 1024 / 1024, 2);
$totalSpaceGB = round($totalSpace / 1024 / 1024 / 1024, 2);
$percentFree = round(($freeSpace / $totalSpace) * 100, 2);

echo "   Total disk space: {$totalSpaceGB} GB\n";
echo "   Free disk space: {$freeSpaceGB} GB ({$percentFree}%)\n";

if ($freeSpaceGB < 1) {
    echo "   ✗ Warning: Less than 1GB of free space available\n";
    echo "     Low disk space may cause backup failures\n";
} else {
    echo "   ✓ Sufficient disk space available\n";
}
echo "\n";

// Step 5: Check PHP settings
echo "5. Checking PHP settings...\n";
$memoryLimit = ini_get('memory_limit');
$maxExecutionTime = ini_get('max_execution_time');
$postMaxSize = ini_get('post_max_size');
$uploadMaxFilesize = ini_get('upload_max_filesize');

echo "   Memory Limit: {$memoryLimit}\n";
echo "   Max Execution Time: {$maxExecutionTime} seconds\n";
echo "   Post Max Size: {$postMaxSize}\n";
echo "   Upload Max Filesize: {$uploadMaxFilesize}\n";

// Try to increase limits if needed
if (intval($memoryLimit) < 256) {
    echo "   Attempting to increase memory limit...\n";
    if (ini_set('memory_limit', '256M')) {
        echo "   ✓ Memory limit increased to 256M\n";
    } else {
        echo "   ✗ Failed to increase memory limit\n";
    }
}

if (intval($maxExecutionTime) < 300) {
    echo "   Attempting to increase max execution time...\n";
    if (ini_set('max_execution_time', '300')) {
        echo "   ✓ Max execution time increased to 300 seconds\n";
    } else {
        echo "   ✗ Failed to increase max execution time\n";
    }
}
echo "\n";

// Step 6: Test backup service
echo "6. Testing backup service components...\n";

// Check if ZipArchive is available
if (class_exists('ZipArchive')) {
    echo "   ✓ ZipArchive is available\n";
} else {
    echo "   ✗ ZipArchive is not available\n";
    echo "     Backup system will fall back to uncompressed SQL files\n";
}

// Check if mysqldump is available
$mysqldumpAvailable = false;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Windows
    exec('where mysqldump', $output, $returnCode);
} else {
    // Linux/Mac
    exec('which mysqldump', $output, $returnCode);
}

if ($returnCode === 0) {
    echo "   ✓ mysqldump is available: " . $output[0] . "\n";
    $mysqldumpAvailable = true;
} else {
    echo "   ✗ mysqldump is not available\n";
    echo "     Backup system will use PHP-based backup method\n";
}
echo "\n";

// Step 7: Fix common issues
echo "7. Applying fixes for common issues...\n";

// Fix 1: Ensure backup config exists
if (!config('backup.backup.name')) {
    echo "   ✗ Backup configuration not found\n";
    echo "     Creating default backup configuration...\n";
    
    // Create a backup config file if it doesn't exist
    $configPath = config_path('backup.php');
    if (!file_exists($configPath)) {
        $defaultConfig = <<<'EOD'
<?php
return [
    'backup' => [
        'name' => env('APP_NAME', 'Laravel'),
        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('app/backup-temp'),
                ],
            ],
            'databases' => [
                'mysql',
            ],
        ],
        'database_dump_settings' => [
            'mysql' => [
                'dump_binary_path' => '',
                'use_single_transaction' => true,
                'timeout' => 300,
            ],
        ],
        'destination' => [
            'disks' => [
                'local',
            ],
        ],
    ],
];
EOD;
        
        if (file_put_contents($configPath, $defaultConfig)) {
            echo "   ✓ Created default backup configuration\n";
        } else {
            echo "   ✗ Failed to create backup configuration\n";
        }
    }
}

// Fix 2: Create .htaccess to protect backup directory
$htaccessPath = storage_path('app/Laravel/.htaccess');
if (!file_exists($htaccessPath)) {
    $htaccessContent = <<<'EOD'
# Deny access to all files
<FilesMatch ".*">
    Order Allow,Deny
    Deny from all
</FilesMatch>
EOD;
    
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        echo "   ✓ Created .htaccess to protect backup directory\n";
    } else {
        echo "   ✗ Failed to create .htaccess protection\n";
    }
}

// Fix 3: Create index.php to protect backup directory
$indexPath = storage_path('app/Laravel/index.php');
if (!file_exists($indexPath)) {
    $indexContent = <<<'EOD'
<?php
// Silence is golden.
// This file prevents directory listing.
header("HTTP/1.0 403 Forbidden");
exit;
EOD;
    
    if (file_put_contents($indexPath, $indexContent)) {
        echo "   ✓ Created index.php to protect backup directory\n";
    } else {
        echo "   ✗ Failed to create index.php protection\n";
    }
}

echo "\n";
echo "=============================\n";
echo "✓ Backup system fix completed\n";
echo "=============================\n\n";
echo "You can now try creating a backup at:\n";
echo "https://mcc-nac.com/superadmin/backup\n\n";
echo "If you still encounter issues, please check the Laravel logs at:\n";
echo storage_path('logs') . "\n";
