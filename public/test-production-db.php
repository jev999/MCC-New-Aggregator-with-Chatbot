<?php
/**
 * Production Database Connection Test
 * This file tests the database connection on your production server
 * Access it at: https://mcc-nac.com/test-production-db.php
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test - mcc-nac.com</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        .config {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Connection Test</h1>
        
        <?php
        try {
            // Test database connection
            DB::connection()->getPdo();
            
            // Get database info
            $databaseName = DB::connection()->getDatabaseName();
            $pdo = DB::connection()->getPdo();
            
            echo '<div class="success">';
            echo '<strong>✅ Database Connection Successful!</strong><br>';
            echo 'Database: <code>' . htmlspecialchars($databaseName) . '</code><br>';
            echo 'Status: Connected<br>';
            echo 'Server Version: ' . htmlspecialchars($pdo->getAttribute(PDO::ATTR_SERVER_VERSION));
            echo '</div>';
            
            // Show current database configuration (non-sensitive)
            echo '<div class="info">';
            echo '<strong>📋 Current Configuration:</strong><br>';
            echo 'Host: <code>' . htmlspecialchars(env('DB_HOST', 'Not set')) . '</code><br>';
            echo 'Database: <code>' . htmlspecialchars(env('DB_DATABASE', 'Not set')) . '</code><br>';
            echo 'Port: <code>' . htmlspecialchars(env('DB_PORT', '3306')) . '</code><br>';
            echo 'Driver: <code>' . htmlspecialchars(env('DB_CONNECTION', 'mysql')) . '</code><br>';
            echo '</div>';
            
            // Test a simple query
            try {
                $tables = DB::select("SHOW TABLES");
                echo '<div class="success">';
                echo '<strong>📊 Database Tables:</strong><br>';
                echo 'Found ' . count($tables) . ' tables in database.<br>';
                if (count($tables) > 0) {
                    echo '<div class="config">';
                    foreach (array_slice($tables, 0, 10) as $table) {
                        $tableName = current((array)$table);
                        echo '• ' . htmlspecialchars($tableName) . '<br>';
                    }
                    if (count($tables) > 10) {
                        echo '... and ' . (count($tables) - 10) . ' more tables';
                    }
                    echo '</div>';
                }
                echo '</div>';
            } catch (\Exception $e) {
                echo '<div class="info">Could not list tables: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Database Connection Failed!</strong><br>';
            echo 'Error: <code>' . htmlspecialchars($e->getMessage()) . '</code><br>';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<strong>🔧 Troubleshooting Steps:</strong><br>';
            echo '1. Check your <code>.env</code> file for correct <code>DB_HOST</code><br>';
            echo '2. Verify your database credentials<br>';
            echo '3. Ensure remote database connections are allowed<br>';
            echo '4. Check firewall rules on port 3306<br>';
            echo '5. Contact your hosting provider if issue persists';
            echo '</div>';
        }
        ?>
        
        <div class="info">
            <strong>📝 Note:</strong> Delete this file after testing for security reasons.
        </div>
    </div>
</body>
</html>
