<?php
/**
 * Backup System Diagnostic Script
 * Run this on your live server to identify backup issues
 * Access via: https://mcc-nac.com/test-backup-diagnostic.php
 */

// Prevent unauthorized access
$SECRET_KEY = 'mcc-diagnostic-2024'; // Change this!
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die('Unauthorized access');
}

echo "<h1>Backup System Diagnostic</h1>";
echo "<style>body{font-family:Arial;padding:20px}pre{background:#f4f4f4;padding:10px;border-radius:5px}.pass{color:green}.fail{color:red}</style>";

// 1. Check PHP Version
echo "<h2>1. PHP Version</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<p class='pass'>✓ PHP version is compatible</p>";
} else {
    echo "<p class='fail'>✗ PHP version is too old (need 7.4+)</p>";
}

// 2. Check Required Extensions
echo "<h2>2. Required PHP Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'zip', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='pass'>✓ {$ext} extension is loaded</p>";
    } else {
        echo "<p class='fail'>✗ {$ext} extension is NOT loaded</p>";
    }
}

// 3. Check Directory Permissions
echo "<h2>3. Directory Permissions</h2>";
$directories = [
    __DIR__ . '/storage/app',
    __DIR__ . '/storage/logs',
    __DIR__ . '/storage/framework',
];

// Check if backup directory should exist
$backup_dir = __DIR__ . '/storage/app/' . (getenv('APP_NAME') ?: 'MCC-News-Aggregator');
$directories[] = $backup_dir;

// Check backup-temp directory
$temp_dir = __DIR__ . '/storage/app/backup-temp';
$directories[] = $temp_dir;

foreach ($directories as $dir) {
    $exists = is_dir($dir);
    $writable = $exists && is_writable($dir);
    
    echo "<p><strong>{$dir}</strong></p>";
    echo $exists ? "<p class='pass'>✓ Directory exists</p>" : "<p class='fail'>✗ Directory does NOT exist</p>";
    if ($exists) {
        echo $writable ? "<p class='pass'>✓ Directory is writable</p>" : "<p class='fail'>✗ Directory is NOT writable</p>";
        echo "<p>Permissions: " . substr(sprintf('%o', fileperms($dir)), -4) . "</p>";
    }
}

// 4. Check mysqldump availability
echo "<h2>4. mysqldump Availability</h2>";
$mysqldump_output = null;
$mysqldump_return = null;

// Try to execute mysqldump
exec('mysqldump --version 2>&1', $mysqldump_output, $mysqldump_return);

if ($mysqldump_return === 0) {
    echo "<p class='pass'>✓ mysqldump is available</p>";
    echo "<pre>" . implode("\n", $mysqldump_output) . "</pre>";
} else {
    echo "<p class='fail'>✗ mysqldump is NOT available (will use PHP fallback)</p>";
    echo "<pre>Error code: {$mysqldump_return}</pre>";
}

// 5. Check Database Connection
echo "<h2>5. Database Connection</h2>";
if (file_exists(__DIR__ . '/.env')) {
    // Parse .env file
    $env = parse_ini_file(__DIR__ . '/.env');
    
    if ($env) {
        $db_host = $env['DB_HOST'] ?? null;
        $db_name = $env['DB_DATABASE'] ?? null;
        $db_user = $env['DB_USERNAME'] ?? null;
        $db_pass = $env['DB_PASSWORD'] ?? null;
        
        if ($db_host && $db_name && $db_user) {
            try {
                $pdo = new PDO(
                    "mysql:host={$db_host};dbname={$db_name}",
                    $db_user,
                    $db_pass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                echo "<p class='pass'>✓ Database connection successful</p>";
                echo "<p>Database: {$db_name}</p>";
                
                // Count tables
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "<p>Total tables: " . count($tables) . "</p>";
                
            } catch (PDOException $e) {
                echo "<p class='fail'>✗ Database connection failed</p>";
                echo "<pre>Error: " . $e->getMessage() . "</pre>";
            }
        } else {
            echo "<p class='fail'>✗ Database credentials not found in .env</p>";
        }
    } else {
        echo "<p class='fail'>✗ Could not parse .env file</p>";
    }
} else {
    echo "<p class='fail'>✗ .env file not found</p>";
}

// 6. Check Disk Space
echo "<h2>6. Disk Space</h2>";
$free_space = disk_free_space(__DIR__);
$total_space = disk_total_space(__DIR__);
$used_space = $total_space - $free_space;

echo "<p>Total Space: " . formatBytes($total_space) . "</p>";
echo "<p>Used Space: " . formatBytes($used_space) . "</p>";
echo "<p>Free Space: " . formatBytes($free_space) . "</p>";

if ($free_space > 100 * 1024 * 1024) { // 100 MB
    echo "<p class='pass'>✓ Sufficient disk space available</p>";
} else {
    echo "<p class='fail'>✗ Low disk space (less than 100 MB)</p>";
}

// 7. Check Memory Limit
echo "<h2>7. PHP Memory Limit</h2>";
$memory_limit = ini_get('memory_limit');
echo "<p>Memory Limit: {$memory_limit}</p>";
$memory_bytes = convertToBytes($memory_limit);
if ($memory_bytes >= 128 * 1024 * 1024) { // 128 MB
    echo "<p class='pass'>✓ Memory limit is sufficient</p>";
} else {
    echo "<p class='fail'>✗ Memory limit is low (should be at least 128M)</p>";
}

// 8. Check Execution Time
echo "<h2>8. PHP Execution Time</h2>";
$max_execution_time = ini_get('max_execution_time');
echo "<p>Max Execution Time: {$max_execution_time} seconds</p>";
if ($max_execution_time == 0 || $max_execution_time >= 300) {
    echo "<p class='pass'>✓ Execution time is sufficient</p>";
} else {
    echo "<p class='fail'>✗ Execution time might be too short for large databases</p>";
}

// 9. Test Backup Creation
echo "<h2>9. Test PHP-Based Backup</h2>";
echo "<p><strong>Attempting to create a test backup...</strong></p>";

try {
    // Create necessary directories
    $backup_path = __DIR__ . '/storage/app/' . (getenv('APP_NAME') ?: 'MCC-News-Aggregator');
    if (!is_dir($backup_path)) {
        mkdir($backup_path, 0755, true);
        echo "<p class='pass'>✓ Created backup directory: {$backup_path}</p>";
    }
    
    $temp_path = __DIR__ . '/storage/app/backup-temp';
    if (!is_dir($temp_path)) {
        mkdir($temp_path, 0755, true);
        echo "<p class='pass'>✓ Created temp directory: {$temp_path}</p>";
    }
    
    // Test file write
    $test_file = $temp_path . '/test_' . time() . '.txt';
    file_put_contents($test_file, 'Test backup file');
    
    if (file_exists($test_file)) {
        echo "<p class='pass'>✓ Can write to temp directory</p>";
        unlink($test_file);
    } else {
        echo "<p class='fail'>✗ Cannot write to temp directory</p>";
    }
    
    echo "<p class='pass'>✓ Backup directories are ready</p>";
    
} catch (Exception $e) {
    echo "<p class='fail'>✗ Error during test: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If you see any red '✗' marks above, those need to be fixed before backups will work.</p>";
echo "<p><strong>Most common issues:</strong></p>";
echo "<ul>";
echo "<li>Storage directories not writable (need 755 or 775 permissions)</li>";
echo "<li>ZipArchive extension not installed</li>";
echo "<li>Low memory or execution time limits</li>";
echo "<li>Database connection issues</li>";
echo "</ul>";

// Helper functions
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int)$value;
    
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}
