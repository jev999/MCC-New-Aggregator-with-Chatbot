<?php
/**
 * Deployment Verification Script
 * Upload this to your server root and run it to check Laravel structure
 */

echo "<h2>Laravel Deployment Verification</h2>";

$basePath = __DIR__;
$requiredFiles = [
    'routes/api.php',
    'routes/web.php',
    'routes/console.php',
    'bootstrap/app.php',
    'config/app.php',
    'public/index.php',
    'vendor/autoload.php'
];

$requiredDirs = [
    'routes',
    'bootstrap',
    'config',
    'public',
    'vendor',
    'storage',
    'storage/framework',
    'storage/logs'
];

echo "<h3>Checking Required Files:</h3>";
foreach ($requiredFiles as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - MISSING<br>";
    }
}

echo "<h3>Checking Required Directories:</h3>";
foreach ($requiredDirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (is_dir($fullPath)) {
        echo "✅ $dir/ - EXISTS<br>";
    } else {
        echo "❌ $dir/ - MISSING<br>";
    }
}

echo "<h3>Checking Laravel Bootstrap:</h3>";
try {
    if (file_exists($basePath . '/vendor/autoload.php')) {
        require_once $basePath . '/vendor/autoload.php';
        echo "✅ Composer autoloader - LOADED<br>";
        
        if (file_exists($basePath . '/bootstrap/app.php')) {
            $app = require_once $basePath . '/bootstrap/app.php';
            echo "✅ Laravel bootstrap - LOADED<br>";
        }
    } else {
        echo "❌ Composer autoloader - NOT FOUND<br>";
    }
} catch (Exception $e) {
    echo "❌ Bootstrap Error: " . $e->getMessage() . "<br>";
}

echo "<h3>Environment Check:</h3>";
echo "Current Directory: " . getcwd() . "<br>";
echo "Script Path: " . __DIR__ . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h3>File Permissions:</h3>";
$checkPaths = ['routes', 'bootstrap', 'config', 'storage'];
foreach ($checkPaths as $path) {
    $fullPath = $basePath . '/' . $path;
    if (file_exists($fullPath)) {
        $perms = fileperms($fullPath);
        echo "$path: " . substr(sprintf('%o', $perms), -4) . "<br>";
    }
}
?>
