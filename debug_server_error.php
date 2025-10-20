<?php
// Debug script to identify 500 server error causes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Server Error Diagnostic</h1>";

// Check PHP version
echo "<h2>PHP Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Check if Laravel can be loaded
echo "<h2>Laravel Bootstrap Test</h2>";
try {
    // Try to load Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Composer autoload successful<br>";
    
    // Try to load Laravel app
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "✅ Laravel app bootstrap successful<br>";
    
    // Check environment
    if (file_exists(__DIR__ . '/.env')) {
        echo "✅ .env file exists<br>";
    } else {
        echo "❌ .env file missing<br>";
    }
    
    // Test database connection
    echo "<h2>Database Connection Test</h2>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Load environment
    $app->loadEnvironmentFrom('.env');
    
    try {
        $pdo = new PDO(
            "mysql:host=" . env('DB_HOST', '127.0.0.1') . ";port=" . env('DB_PORT', '3306') . ";dbname=" . env('DB_DATABASE'),
            env('DB_USERNAME'),
            env('DB_PASSWORD')
        );
        echo "✅ Database connection successful<br>";
        echo "Database: " . env('DB_DATABASE') . "<br>";
        echo "Host: " . env('DB_HOST') . "<br>";
    } catch (PDOException $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

// Check file permissions
echo "<h2>File Permissions Check</h2>";
$directories = [
    'storage/',
    'storage/logs/',
    'storage/framework/',
    'storage/framework/cache/',
    'storage/framework/sessions/',
    'storage/framework/views/',
    'bootstrap/cache/'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "Directory $dir: $perms " . (is_writable($dir) ? "✅ Writable" : "❌ Not writable") . "<br>";
    } else {
        echo "Directory $dir: ❌ Does not exist<br>";
    }
}

// Check .htaccess
echo "<h2>.htaccess Check</h2>";
if (file_exists('public/.htaccess')) {
    echo "✅ .htaccess exists in public/<br>";
    
    // Check if mod_rewrite is enabled
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        if (in_array('mod_rewrite', $modules)) {
            echo "✅ mod_rewrite is enabled<br>";
        } else {
            echo "❌ mod_rewrite is not enabled<br>";
        }
    } else {
        echo "⚠️ Cannot check mod_rewrite status<br>";
    }
} else {
    echo "❌ .htaccess missing in public/<br>";
}

echo "<h2>Environment Variables</h2>";
echo "APP_ENV: " . (env('APP_ENV') ?: 'Not set') . "<br>";
echo "APP_DEBUG: " . (env('APP_DEBUG') ? 'true' : 'false') . "<br>";
echo "APP_URL: " . (env('APP_URL') ?: 'Not set') . "<br>";

phpinfo();
?>
