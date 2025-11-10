<?php
/**
 * Database Backup Connection Test Script
 * This script helps diagnose issues with remote database backup
 */

// Load Laravel environment
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Backup Connection Diagnostic ===\n\n";

// Get database configuration
$host = config('database.connections.mysql.host');
$database = config('database.connections.mysql.database');
$username = config('database.connections.mysql.username');
$port = config('database.connections.mysql.port', 3306);

echo "Database Configuration:\n";
echo "- Host: $host\n";
echo "- Port: $port\n";
echo "- Database: $database\n";
echo "- Username: $username\n";
echo "- Password: " . (config('database.connections.mysql.password') ? '[SET]' : '[NOT SET]') . "\n\n";

// Test 1: Basic PDO Connection
echo "Test 1: Testing PDO Connection...\n";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $username, config('database.connections.mysql.password'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ PDO connection successful\n\n";
} catch (PDOException $e) {
    echo "✗ PDO connection failed: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n\n";
    
    // Provide specific troubleshooting
    if ($e->getCode() == 1045) {
        echo "TROUBLESHOOTING:\n";
        echo "- This is an authentication error\n";
        echo "- Check your database username and password\n";
        echo "- Make sure the user has permission to connect from your IP/host\n";
        echo "- For remote databases, the user needs to be created with proper host permissions:\n";
        echo "  Example: CREATE USER 'user'@'%' IDENTIFIED BY 'password';\n";
        echo "  Or: CREATE USER 'user'@'your-ip' IDENTIFIED BY 'password';\n\n";
    } elseif ($e->getCode() == 2002) {
        echo "TROUBLESHOOTING:\n";
        echo "- Cannot connect to the database server\n";
        echo "- Check if the host and port are correct\n";
        echo "- Check if the remote database server is running\n";
        echo "- Check firewall settings\n\n";
    }
    exit(1);
}

// Test 2: Laravel DB Connection
echo "Test 2: Testing Laravel DB Connection...\n";
try {
    DB::connection()->getPdo();
    echo "✓ Laravel DB connection successful\n\n";
} catch (Exception $e) {
    echo "✗ Laravel DB connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Get Tables
echo "Test 3: Getting database tables...\n";
try {
    $results = DB::select('SHOW TABLES');
    echo "✓ Found " . count($results) . " tables\n";
    
    if (!empty($results)) {
        $firstResult = $results[0];
        $columns = get_object_vars($firstResult);
        $columnName = array_key_first($columns);
        
        echo "Table column name: $columnName\n";
        echo "\nFirst 5 tables:\n";
        
        foreach (array_slice($results, 0, 5) as $index => $result) {
            echo ($index + 1) . ". " . $result->$columnName . "\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Failed to get tables: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: Check Write Permissions
echo "Test 4: Checking backup directory permissions...\n";
$backupPath = storage_path('app/backups');

if (!file_exists($backupPath)) {
    echo "Backup directory doesn't exist. Creating...\n";
    mkdir($backupPath, 0755, true);
}

if (is_writable($backupPath)) {
    echo "✓ Backup directory is writable: $backupPath\n";
} else {
    echo "✗ Backup directory is NOT writable: $backupPath\n";
    echo "Please run: chmod -R 755 $backupPath\n";
}
echo "\n";

// Test 5: Test a small backup
echo "Test 5: Creating a test backup...\n";
try {
    $tables = [];
    $results = DB::select('SHOW TABLES');
    $firstResult = $results[0];
    $columns = get_object_vars($firstResult);
    $columnName = array_key_first($columns);
    
    foreach ($results as $result) {
        $tables[] = $result->$columnName;
    }
    
    $testFile = $backupPath . '/test_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $sql = "-- Test Backup\n";
    $sql .= "-- Database: $database\n";
    $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Just get structure of first table for testing
    if (!empty($tables)) {
        $firstTable = $tables[0];
        $structure = DB::select("SHOW CREATE TABLE `$firstTable`");
        $createTable = $structure[0]->{'Create Table'};
        $sql .= "-- Table: $firstTable\n";
        $sql .= $createTable . ";\n";
    }
    
    file_put_contents($testFile, $sql);
    
    if (file_exists($testFile) && filesize($testFile) > 0) {
        echo "✓ Test backup created successfully!\n";
        echo "File: $testFile\n";
        echo "Size: " . filesize($testFile) . " bytes\n";
        
        // Clean up test file
        unlink($testFile);
        echo "Test file cleaned up.\n";
    } else {
        echo "✗ Test backup file was not created or is empty\n";
    }
} catch (Exception $e) {
    echo "✗ Test backup failed: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "\nIf all tests pass, the backup should work correctly.\n";
echo "If you still have issues, check the Laravel logs at: storage/logs/laravel.log\n";
