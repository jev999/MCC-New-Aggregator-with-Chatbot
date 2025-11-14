<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Database Connection Test\n";
echo "========================\n\n";

echo "Configuration:\n";
echo "  Host: " . config('database.connections.mysql.host') . "\n";
echo "  Database: " . config('database.connections.mysql.database') . "\n";
echo "  Username: " . config('database.connections.mysql.username') . "\n";
echo "  Password: " . (config('database.connections.mysql.password') ? '****' : '(empty)') . "\n\n";

echo "Testing connection...\n";

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
    exit(1);
}
