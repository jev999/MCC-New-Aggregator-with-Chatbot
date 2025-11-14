<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

echo "Backup System Test\n";
echo "=================\n\n";

// Test database connection
echo "Testing database connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "✓ Connection successful!\n";
    
    // Test a simple query
    $result = DB::select('SELECT DATABASE() as db, USER() as user');
    echo "✓ Connected to database: " . $result[0]->db . "\n";
    echo "✓ Connected as user: " . $result[0]->user . "\n";
    
} catch (Exception $e) {
    echo "✗ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}

// Test backup directories
echo "\nTesting backup directories...\n";

$backupPath = config('backup.backup.name', 'Laravel');
$directories = [
    storage_path('app/' . $backupPath),
    storage_path('app/backup-temp'),
    storage_path('app/Laravel'),
    storage_path('app/backups'),
];

foreach ($directories as $directory) {
    echo "Directory: {$directory}\n";
    
    // Check if directory exists
    if (!file_exists($directory)) {
        echo "  - Does not exist, attempting to create...\n";
        if (mkdir($directory, 0775, true)) {
            echo "  ✓ Created successfully\n";
        } else {
            echo "  ✗ Failed to create directory\n";
        }
    } else {
        echo "  ✓ Directory exists\n";
    }
    
    // Check if directory is writable
    if (is_writable($directory)) {
        echo "  ✓ Directory is writable\n";
        
        // Try to write a test file
        $testFile = $directory . '/test-' . time() . '.txt';
        if (file_put_contents($testFile, 'Test file for backup system')) {
            echo "  ✓ Successfully wrote test file\n";
            // Clean up test file
            unlink($testFile);
        } else {
            echo "  ✗ Failed to write test file\n";
        }
    } else {
        echo "  ✗ Directory is not writable\n";
    }
    
    echo "\n";
}

// Test PHP functions needed for backup
echo "Testing required PHP functions...\n";
$requiredFunctions = [
    'file_put_contents',
    'file_get_contents',
    'fopen',
    'fwrite',
    'fclose',
    'mkdir',
    'unlink',
    'copy'
];

foreach ($requiredFunctions as $function) {
    if (function_exists($function)) {
        echo "✓ {$function} is available\n";
    } else {
        echo "✗ {$function} is NOT available\n";
    }
}

echo "\nTesting ZipArchive class...\n";
if (class_exists('ZipArchive')) {
    echo "✓ ZipArchive is available\n";
} else {
    echo "✗ ZipArchive is NOT available (SQL files will be used without compression)\n";
}

echo "\nTest completed.\n";
