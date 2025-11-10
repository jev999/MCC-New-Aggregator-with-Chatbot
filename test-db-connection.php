<?php
/**
 * Database Connection Test Script
 * Run this via: php test-db-connection.php
 */

echo "=== Database Connection Test ===\n\n";

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $envFile = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $envFile);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes if present
            $value = trim($value, '"\'');
            putenv("$key=$value");
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

echo "Configuration:\n";
echo "  Host: $host\n";
echo "  Port: $port\n";
echo "  Database: $database\n";
echo "  Username: $username\n";
echo "  Password: " . (empty($password) ? '[EMPTY]' : '[SET - ' . strlen($password) . ' chars]') . "\n\n";

// Test 1: Check if credentials are set
if (empty($database) || empty($username)) {
    echo "❌ ERROR: Database credentials are not properly configured in .env file\n";
    exit(1);
}

// Test 2: Try to connect
echo "Testing connection...\n";
try {
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Successfully connected to MySQL server\n\n";
    
    // Test 3: Try to select the database
    echo "Testing database access...\n";
    $pdo->exec("USE `$database`");
    echo "✓ Successfully selected database: $database\n\n";
    
    // Test 4: Try to show tables
    echo "Testing SHOW TABLES permission...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Successfully retrieved " . count($tables) . " tables\n";
    
    if (count($tables) > 0) {
        echo "\nFirst 5 tables:\n";
        foreach (array_slice($tables, 0, 5) as $table) {
            echo "  - $table\n";
        }
    }
    
    // Test 5: Check user privileges
    echo "\n\nChecking user privileges...\n";
    $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Grants for $username:\n";
    foreach ($grants as $grant) {
        echo "  $grant\n";
    }
    
    echo "\n✅ ALL TESTS PASSED! Database connection is working properly.\n";
    echo "\nIf backup is still failing, the issue might be with:\n";
    echo "  1. File permissions on storage/app/backups/ directory\n";
    echo "  2. PHP memory/execution time limits\n";
    echo "  3. Session/authentication issues\n";
    
} catch (PDOException $e) {
    echo "\n❌ DATABASE CONNECTION FAILED!\n\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n\n";
    
    if ($e->getCode() == 1045) {
        echo "SOLUTION: Access denied error means:\n";
        echo "  1. The password in your .env file is INCORRECT\n";
        echo "  2. Or the user doesn't exist\n";
        echo "  3. Or the user exists but doesn't have proper permissions\n\n";
        echo "STEPS TO FIX:\n";
        echo "  1. Log into cPanel\n";
        echo "  2. Go to MySQL Databases\n";
        echo "  3. Verify the username '$username' exists\n";
        echo "  4. If password is wrong, reset it and update .env file\n";
        echo "  5. Make sure user is added to database '$database' with ALL PRIVILEGES\n";
    } elseif ($e->getCode() == 2002) {
        echo "SOLUTION: Can't connect to MySQL server means:\n";
        echo "  1. MySQL/MariaDB service is not running\n";
        echo "  2. Or the host '$host' is incorrect\n";
        echo "  3. Or the port '$port' is blocked\n";
    } else {
        echo "SOLUTION: Check the error message above for specific details\n";
    }
    
    exit(1);
}
