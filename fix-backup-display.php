<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

echo "Backup Display Fix Tool\n";
echo "======================\n\n";

// Get the backup disk
$backupDisk = 'local';
$storage = Storage::disk($backupDisk);

// Define all possible backup directories
$backupPaths = [
    config('backup.backup.name', 'Laravel'),
    config('app.name', 'Laravel'),
    'backups',
    'Laravel',
    '', // Root directory
];

echo "Checking backup directories...\n";

// Check each directory
foreach ($backupPaths as $dir) {
    $dirPath = empty($dir) ? 'ROOT' : $dir;
    echo "Directory: {$dirPath}\n";
    
    try {
        // Create directory if it doesn't exist
        if (!empty($dir) && !$storage->exists($dir)) {
            echo "  - Creating directory...\n";
            $storage->makeDirectory($dir);
            echo "  ✓ Directory created\n";
        } else {
            echo "  ✓ Directory exists\n";
        }
        
        // List files in this directory
        $files = $storage->files($dir);
        $backupFiles = array_filter($files, function($file) {
            return preg_match('/\.(sql|zip)$/i', $file);
        });
        
        echo "  - Found " . count($backupFiles) . " backup files\n";
        
        // List the backup files
        foreach ($backupFiles as $file) {
            $size = $storage->size($file);
            $lastModified = Carbon::createFromTimestamp($storage->lastModified($file))->format('Y-m-d H:i:s');
            echo "    • " . basename($file) . " (" . formatBytes($size) . ") - {$lastModified}\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Check for any backup files in subdirectories
echo "Checking for backup files in subdirectories...\n";
$allBackupFiles = [];

foreach ($backupPaths as $dir) {
    if (empty($dir)) continue; // Skip root directory for subdirectory check
    
    try {
        $subdirs = $storage->directories($dir);
        foreach ($subdirs as $subdir) {
            echo "Subdirectory: {$subdir}\n";
            
            $files = $storage->files($subdir);
            $backupFiles = array_filter($files, function($file) {
                return preg_match('/\.(sql|zip)$/i', $file);
            });
            
            echo "  - Found " . count($backupFiles) . " backup files\n";
            
            // List the backup files
            foreach ($backupFiles as $file) {
                $size = $storage->size($file);
                $lastModified = Carbon::createFromTimestamp($storage->lastModified($file))->format('Y-m-d H:i:s');
                echo "    • " . basename($file) . " (" . formatBytes($size) . ") - {$lastModified}\n";
                $allBackupFiles[] = $file;
            }
        }
    } catch (Exception $e) {
        // Ignore errors for individual directories
    }
}

// Consolidate backup files to a single directory if needed
if (count($allBackupFiles) > 0) {
    echo "\nConsolidating backup files to primary backup directory...\n";
    
    // Use the first directory as the primary backup directory
    $primaryDir = !empty($backupPaths[0]) ? $backupPaths[0] : 'backups';
    
    // Create the primary directory if it doesn't exist
    if (!$storage->exists($primaryDir)) {
        $storage->makeDirectory($primaryDir);
        echo "Created primary backup directory: {$primaryDir}\n";
    }
    
    // Copy all backup files to the primary directory
    foreach ($allBackupFiles as $file) {
        $filename = basename($file);
        $targetPath = $primaryDir . '/' . $filename;
        
        // Skip if file already exists in primary directory
        if ($storage->exists($targetPath)) {
            echo "✓ {$filename} already exists in primary directory\n";
            continue;
        }
        
        try {
            // Read the file content
            $content = $storage->get($file);
            
            // Write to the primary directory
            $storage->put($targetPath, $content);
            
            echo "✓ Copied {$filename} to primary directory\n";
        } catch (Exception $e) {
            echo "✗ Failed to copy {$filename}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nBackup display fix completed.\n";
echo "Please refresh the backup page to see your backups.\n";

/**
 * Format bytes to human readable size
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}
