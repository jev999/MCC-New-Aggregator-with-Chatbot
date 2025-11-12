<?php
/**
 * Backup Directories Fix Script
 * Run this on your live server to create and fix backup directories
 * Access via: https://mcc-nac.com/fix-backup-directories.php
 * 
 * SECURITY: DELETE THIS FILE AFTER USE!
 */

// Prevent unauthorized access
$SECRET_KEY = 'mcc-fix-2024'; // Change this!
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die('Unauthorized access. Add ?key=mcc-fix-2024 to the URL');
}

echo "<h1>Backup Directories Fix</h1>";
echo "<style>body{font-family:Arial;padding:20px}.pass{color:green}.fail{color:red}.info{color:blue}</style>";

$app_name = getenv('APP_NAME') ?: 'MCC-News-Aggregator';
$directories = [
    'storage',
    'storage/app',
    'storage/app/' . $app_name,
    'storage/app/backup-temp',
    'storage/logs',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
];

echo "<h2>Creating and Fixing Directories</h2>";

foreach ($directories as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    
    echo "<p><strong>{$dir}</strong></p>";
    
    // Check if directory exists
    if (!is_dir($full_path)) {
        // Try to create it
        if (@mkdir($full_path, 0775, true)) {
            echo "<p class='pass'>✓ Created directory</p>";
        } else {
            echo "<p class='fail'>✗ Failed to create directory</p>";
            echo "<p class='info'>Try creating manually with: mkdir -p {$full_path}</p>";
            continue;
        }
    } else {
        echo "<p class='pass'>✓ Directory exists</p>";
    }
    
    // Check if writable
    if (is_writable($full_path)) {
        echo "<p class='pass'>✓ Directory is writable</p>";
    } else {
        echo "<p class='fail'>✗ Directory is not writable</p>";
        
        // Try to fix permissions
        if (@chmod($full_path, 0775)) {
            echo "<p class='pass'>✓ Fixed permissions to 775</p>";
        } else {
            echo "<p class='fail'>✗ Failed to fix permissions</p>";
            echo "<p class='info'>Try manually: chmod 775 {$full_path}</p>";
        }
    }
    
    // Show current permissions
    if (is_dir($full_path)) {
        $perms = substr(sprintf('%o', fileperms($full_path)), -4);
        echo "<p class='info'>Current permissions: {$perms}</p>";
    }
    
    echo "<hr>";
}

echo "<h2>Testing Backup Directory Write Access</h2>";

$backup_dir = __DIR__ . '/storage/app/' . $app_name;
$test_file = $backup_dir . '/test_' . time() . '.txt';

try {
    file_put_contents($test_file, 'This is a test backup file created at ' . date('Y-m-d H:i:s'));
    
    if (file_exists($test_file)) {
        echo "<p class='pass'>✓ Successfully wrote test file to backup directory</p>";
        echo "<p>File: {$test_file}</p>";
        
        // Clean up
        unlink($test_file);
        echo "<p class='pass'>✓ Successfully deleted test file</p>";
    } else {
        echo "<p class='fail'>✗ Test file was not created</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Error writing test file: " . $e->getMessage() . "</p>";
}

echo "<h2>Testing Temp Directory Write Access</h2>";

$temp_dir = __DIR__ . '/storage/app/backup-temp';
$test_file = $temp_dir . '/test_' . time() . '.txt';

try {
    file_put_contents($test_file, 'This is a test temp file created at ' . date('Y-m-d H:i:s'));
    
    if (file_exists($test_file)) {
        echo "<p class='pass'>✓ Successfully wrote test file to temp directory</p>";
        echo "<p>File: {$test_file}</p>";
        
        // Clean up
        unlink($test_file);
        echo "<p class='pass'>✓ Successfully deleted test file</p>";
    } else {
        echo "<p class='fail'>✗ Test file was not created</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Error writing test file: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>All directories have been checked and fixed where possible.</p>";
echo "<p><strong>If you still see errors:</strong></p>";
echo "<ol>";
echo "<li>Contact your hosting provider to fix directory permissions</li>";
echo "<li>Or run these commands via SSH:<br>";
echo "<code>chmod -R 775 storage</code><br>";
echo "<code>chmod -R 775 bootstrap/cache</code></li>";
echo "</ol>";

echo "<p style='color:red;font-weight:bold'>⚠️ IMPORTANT: DELETE THIS FILE AFTER USE FOR SECURITY!</p>";
