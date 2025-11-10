<?php
/**
 * Backup System Diagnostic Tool
 * Access: https://mcc-nac.com/test-backup-permissions.php
 * DELETE THIS FILE AFTER TESTING!
 */

echo "<h1>Backup System Diagnostics</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px; }
</style>";

// 1. Check PHP version
echo "<div class='section'>";
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: <span class='success'>" . phpversion() . "</span><br>";
echo "</div>";

// 2. Check storage path
echo "<div class='section'>";
echo "<h2>2. Storage Path Check</h2>";
$storagePath = __DIR__ . '/../storage/app/backups';
echo "Backup Path: <code>{$storagePath}</code><br>";

if (file_exists($storagePath)) {
    echo "Directory Exists: <span class='success'>✓ YES</span><br>";
    
    // Check if writable
    if (is_writable($storagePath)) {
        echo "Writable: <span class='success'>✓ YES</span><br>";
    } else {
        echo "Writable: <span class='error'>✗ NO - THIS IS THE PROBLEM!</span><br>";
        echo "<p class='error'>Fix: Run this command on your server:</p>";
        echo "<code>chmod -R 775 storage/app/backups</code><br>";
        echo "<code>chown -R www-data:www-data storage/app/backups</code><br>";
    }
    
    // Get permissions
    $perms = substr(sprintf('%o', fileperms($storagePath)), -4);
    echo "Permissions: <code>{$perms}</code><br>";
} else {
    echo "Directory Exists: <span class='error'>✗ NO - DIRECTORY NOT FOUND!</span><br>";
    echo "<p class='error'>Creating directory...</p>";
    
    if (mkdir($storagePath, 0775, true)) {
        echo "<span class='success'>✓ Directory created successfully!</span><br>";
    } else {
        echo "<span class='error'>✗ Failed to create directory!</span><br>";
    }
}
echo "</div>";

// 3. Check database connection
echo "<div class='section'>";
echo "<h2>3. Database Connection</h2>";
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $pdo = DB::connection()->getPdo();
    echo "Database Connection: <span class='success'>✓ Connected</span><br>";
    echo "Database: <span class='success'>" . config('database.connections.mysql.database') . "</span><br>";
    echo "Host: <span class='success'>" . config('database.connections.mysql.host') . "</span><br>";
} catch (Exception $e) {
    echo "Database Connection: <span class='error'>✗ FAILED</span><br>";
    echo "Error: <span class='error'>" . $e->getMessage() . "</span><br>";
}
echo "</div>";

// 4. Check mysqldump availability
echo "<div class='section'>";
echo "<h2>4. mysqldump Availability</h2>";
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "OS: <span class='warning'>Windows (mysqldump may not be in PATH)</span><br>";
} else {
    $command = 'which mysqldump 2>/dev/null';
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0 && !empty($output)) {
        echo "mysqldump: <span class='success'>✓ Available at " . $output[0] . "</span><br>";
    } else {
        echo "mysqldump: <span class='warning'>⚠ Not available (will use Laravel fallback)</span><br>";
    }
}
echo "</div>";

// 5. Test write permissions
echo "<div class='section'>";
echo "<h2>5. Write Test</h2>";
$testFile = $storagePath . '/test_' . time() . '.txt';
try {
    if (file_put_contents($testFile, 'test')) {
        echo "Write Test: <span class='success'>✓ SUCCESS</span><br>";
        unlink($testFile);
        echo "Delete Test: <span class='success'>✓ SUCCESS</span><br>";
    } else {
        echo "Write Test: <span class='error'>✗ FAILED</span><br>";
    }
} catch (Exception $e) {
    echo "Write Test: <span class='error'>✗ FAILED: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// 6. Check disk space
echo "<div class='section'>";
echo "<h2>6. Disk Space</h2>";
$freeSpace = disk_free_space($storagePath);
$totalSpace = disk_total_space($storagePath);
$usedSpace = $totalSpace - $freeSpace;

echo "Free Space: <span class='success'>" . formatBytes($freeSpace) . "</span><br>";
echo "Total Space: " . formatBytes($totalSpace) . "<br>";
echo "Used: " . formatBytes($usedSpace) . " (" . round(($usedSpace / $totalSpace) * 100, 2) . "%)<br>";

if ($freeSpace < 100 * 1024 * 1024) { // Less than 100MB
    echo "<span class='error'>⚠ Warning: Low disk space!</span><br>";
}
echo "</div>";

// 7. Recommendations
echo "<div class='section'>";
echo "<h2>7. Recommendations</h2>";
echo "<p><strong>If backup still fails:</strong></p>";
echo "<ol>";
echo "<li>Check Laravel logs: <code>storage/logs/laravel.log</code></li>";
echo "<li>Ensure SELinux is not blocking (if on Linux): <code>sudo setenforce 0</code></li>";
echo "<li>Check web server error logs</li>";
echo "<li>Verify database user has SELECT permissions on all tables</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p class='error'><strong>⚠ SECURITY WARNING: DELETE THIS FILE AFTER TESTING!</strong></p>";
echo "<p>Run: <code>rm public/test-backup-permissions.php</code></p>";

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
