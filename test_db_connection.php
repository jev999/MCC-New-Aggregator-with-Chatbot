<?php

echo "=== Database Connection Test ===\n\n";

// Test basic PDO connection
try {
    echo "1. Testing direct PDO connection...\n";
    
    $host = '127.0.0.1';
    $port = '3306';
    $dbname = 'mccbot';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "   ✅ Direct PDO connection: SUCCESS\n";
    
    // Test if database exists
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Connected to database: " . $result['current_db'] . "\n";
    
    // Test if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✅ Tables found: " . count($tables) . "\n";
    
    if (count($tables) > 0) {
        echo "   📋 Available tables:\n";
        foreach (array_slice($tables, 0, 5) as $table) {
            echo "      - {$table}\n";
        }
        if (count($tables) > 5) {
            echo "      ... and " . (count($tables) - 5) . " more\n";
        }
    }
    
} catch (PDOException $e) {
    echo "   ❌ Direct PDO connection: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
    
    // Check if it's a connection refused error
    if (strpos($e->getMessage(), 'Connection refused') !== false || 
        strpos($e->getMessage(), 'actively refused') !== false) {
        echo "\n🔧 SOLUTION: MySQL server is not running!\n";
        echo "   1. Open XAMPP Control Panel\n";
        echo "   2. Click 'Start' next to MySQL\n";
        echo "   3. Wait for it to show 'Running' status\n";
        echo "   4. Try again\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "\n🔧 SOLUTION: Database 'mccbot' doesn't exist!\n";
        echo "   1. Open phpMyAdmin (http://localhost/phpmyadmin)\n";
        echo "   2. Create database named 'mccbot'\n";
        echo "   3. Run: php artisan migrate\n";
    }
    
    exit(1);
}

echo "\n2. Testing Laravel database configuration...\n";

// Check .env file
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    
    // Extract database config
    preg_match('/DB_HOST=(.*)/', $envContent, $hostMatch);
    preg_match('/DB_PORT=(.*)/', $envContent, $portMatch);
    preg_match('/DB_DATABASE=(.*)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.*)/', $envContent, $userMatch);
    
    $envHost = isset($hostMatch[1]) ? trim($hostMatch[1]) : 'not set';
    $envPort = isset($portMatch[1]) ? trim($portMatch[1]) : 'not set';
    $envDb = isset($dbMatch[1]) ? trim($dbMatch[1]) : 'not set';
    $envUser = isset($userMatch[1]) ? trim($userMatch[1]) : 'not set';
    
    echo "   📋 Laravel .env configuration:\n";
    echo "      DB_HOST: {$envHost}\n";
    echo "      DB_PORT: {$envPort}\n";
    echo "      DB_DATABASE: {$envDb}\n";
    echo "      DB_USERNAME: {$envUser}\n";
    
    if ($envHost === '127.0.0.1' && $envPort === '3306' && $envDb === 'mccbot' && $envUser === 'root') {
        echo "   ✅ Laravel database configuration: CORRECT\n";
    } else {
        echo "   ⚠️  Laravel database configuration: CHECK NEEDED\n";
    }
} else {
    echo "   ❌ .env file not found\n";
}

echo "\n3. Testing session configuration...\n";

// Check session configuration
if (file_exists('.env')) {
    if (strpos($envContent, 'SESSION_DRIVER=database') !== false) {
        echo "   📋 Session driver: database\n";
        echo "   ℹ️  This requires 'sessions' table in database\n";
        
        // Check if sessions table exists
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
            if ($stmt->rowCount() > 0) {
                echo "   ✅ Sessions table: EXISTS\n";
            } else {
                echo "   ❌ Sessions table: MISSING\n";
                echo "   🔧 Run: php artisan session:table && php artisan migrate\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Cannot check sessions table\n";
        }
    } else {
        echo "   📋 Session driver: file (default)\n";
        echo "   ✅ No database required for sessions\n";
    }
}

echo "\n=== SUMMARY ===\n";

if (isset($pdo)) {
    echo "✅ Database connection is working!\n";
    echo "✅ Your welcome page should load now\n";
    echo "\n🚀 Try visiting: http://127.0.0.1:8000/\n";
} else {
    echo "❌ Database connection failed\n";
    echo "🔧 Fix the database connection first\n";
}

echo "\n=== Quick Fix Commands ===\n";
echo "1. Start XAMPP MySQL service\n";
echo "2. If needed, run migrations: php artisan migrate\n";
echo "3. Clear cache: php artisan cache:clear\n";
echo "4. Clear config: php artisan config:clear\n";
echo "5. Start server: php artisan serve\n";
