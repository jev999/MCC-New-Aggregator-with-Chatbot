<?php

/**
 * Test script to manually verify backup functionality
 * This simulates the backup process without going through the web interface
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Log;

echo "====================================\n";
echo "DATABASE BACKUP TEST\n";
echo "====================================\n\n";

// Test database connection
echo "1. Testing database connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "   ✓ Database connection successful\n";
    echo "   Database: " . config('database.connections.mysql.database') . "\n";
    echo "   Host: " . config('database.connections.mysql.host') . "\n";
    echo "   Username: " . config('database.connections.mysql.username') . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if remote database
echo "2. Detecting database type...\n";
$host = config('database.connections.mysql.host');
$username = config('database.connections.mysql.username');
$database = config('database.connections.mysql.database');

$isRemoteByNaming = (
    preg_match('/^[a-z]\d+_/', $database) || 
    preg_match('/^[a-z]\d+_/', $username)
);

$isRemoteByHost = !in_array($host, ['localhost', '127.0.0.1', '::1', 'local']);
$isRemote = $isRemoteByNaming || $isRemoteByHost;

if ($isRemote) {
    echo "   ✓ Remote database detected\n";
    echo "   Reason: " . ($isRemoteByNaming ? 'Hosting provider naming convention' : 'Remote host') . "\n";
    echo "   Using PHP-based backup method\n\n";
} else {
    echo "   ✓ Local database detected\n";
    echo "   Can use mysqldump or PHP-based backup\n\n";
}

// Check directories
echo "3. Checking backup directories...\n";
$backupPath = storage_path('app/Laravel');
$tempPath = storage_path('app/backup-temp');

if (!file_exists($backupPath)) {
    mkdir($backupPath, 0775, true);
    echo "   ✓ Created backup directory: $backupPath\n";
} else {
    echo "   ✓ Backup directory exists: $backupPath\n";
}

if (!file_exists($tempPath)) {
    mkdir($tempPath, 0775, true);
    echo "   ✓ Created temp directory: $tempPath\n";
} else {
    echo "   ✓ Temp directory exists: $tempPath\n";
}

if (!is_writable($backupPath)) {
    echo "   ✗ Backup directory not writable!\n";
    exit(1);
}

if (!is_writable($tempPath)) {
    echo "   ✗ Temp directory not writable!\n";
    exit(1);
}

echo "   ✓ All directories are writable\n\n";

// Test backup creation
echo "4. Creating test backup using PHP method...\n";
echo "   This may take a few moments...\n\n";

try {
    $backupService = new DatabaseBackupService();
    $startTime = microtime(true);
    
    $result = $backupService->createBackup();
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "   ✓ Backup created successfully!\n";
    echo "   Filename: " . $result['filename'] . "\n";
    echo "   Size: " . formatBytes($result['size']) . "\n";
    echo "   Duration: {$duration} seconds\n";
    echo "   Path: " . $result['path'] . "\n\n";
    
    echo "====================================\n";
    echo "✓ BACKUP TEST COMPLETED SUCCESSFULLY\n";
    echo "====================================\n";
    echo "\nYou can now use the backup feature in the web interface at:\n";
    echo "https://mcc-nac.com/superadmin/backup\n\n";
    
} catch (Exception $e) {
    echo "   ✗ Backup failed: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}
