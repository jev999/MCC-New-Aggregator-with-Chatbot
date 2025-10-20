<?php
/**
 * Production Deployment Script for MCC News Aggregator
 * This script helps deploy the application to production servers
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MCC News Aggregator - Production Deployment</h1>";
echo "<p><strong>Deployment Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check if running on production server
$isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'mcc-nac.com') !== false;

if ($isProduction) {
    echo "<p><strong>🌐 Running on Production Server:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
} else {
    echo "<p><strong>🏠 Running on Local Server:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>";
}

echo "<h2>📋 Deployment Checklist</h2>";

$checks = [
    'PHP Version' => [
        'check' => version_compare(PHP_VERSION, '8.2.0', '>='),
        'current' => PHP_VERSION,
        'required' => '8.2.0+',
        'critical' => true
    ],
    'Laravel Files' => [
        'check' => file_exists('../bootstrap/app.php') && file_exists('../vendor/autoload.php'),
        'current' => file_exists('../bootstrap/app.php') ? 'Present' : 'Missing',
        'required' => 'Present',
        'critical' => true
    ],
    'Environment File' => [
        'check' => file_exists('../.env'),
        'current' => file_exists('../.env') ? 'Present' : 'Missing',
        'required' => 'Present',
        'critical' => true
    ],
    'Storage Writable' => [
        'check' => is_writable('../storage'),
        'current' => is_writable('../storage') ? 'Writable' : 'Not Writable',
        'required' => 'Writable',
        'critical' => true
    ],
    'Bootstrap Cache Writable' => [
        'check' => is_writable('../bootstrap/cache'),
        'current' => is_writable('../bootstrap/cache') ? 'Writable' : 'Not Writable',
        'required' => 'Writable',
        'critical' => true
    ]
];

// Required PHP Extensions
$extensions = ['mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo'];
foreach ($extensions as $ext) {
    $checks["PHP Extension: $ext"] = [
        'check' => extension_loaded($ext),
        'current' => extension_loaded($ext) ? 'Loaded' : 'Missing',
        'required' => 'Loaded',
        'critical' => true
    ];
}

$allPassed = true;
foreach ($checks as $name => $check) {
    $status = $check['check'] ? '✅ PASS' : '❌ FAIL';
    $color = $check['check'] ? 'green' : 'red';
    
    echo "<p><strong>$name:</strong> <span style='color: $color'>$status</span> - {$check['current']} (Required: {$check['required']})</p>";
    
    if (!$check['check'] && $check['critical']) {
        $allPassed = false;
    }
}

echo "<h2>🔧 Laravel Bootstrap Test</h2>";
try {
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    echo "<p>✅ Laravel bootstrap successful</p>";
    
    // Test configuration
    $config = $app->make('config');
    echo "<p>✅ Configuration service available</p>";
    
    // Check APP_KEY
    $appKey = $config->get('app.key');
    if (empty($appKey)) {
        echo "<p>❌ APP_KEY is missing - run 'php artisan key:generate'</p>";
        $allPassed = false;
    } else {
        echo "<p>✅ APP_KEY is configured</p>";
    }
    
    // Check database connection
    try {
        $db = $app->make('db');
        $db->connection()->getPdo();
        echo "<p>✅ Database connection successful</p>";
    } catch (Exception $e) {
        echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
        $allPassed = false;
    }
    
} catch (Exception $e) {
    echo "<p>❌ Laravel bootstrap failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    $allPassed = false;
}

echo "<h2>🚀 Deployment Commands</h2>";
if ($isProduction) {
    echo "<p><strong>Run these commands on your production server:</strong></p>";
    echo "<pre>";
    echo "# 1. Install/Update Dependencies\n";
    echo "composer install --optimize-autoloader --no-dev\n\n";
    
    echo "# 2. Generate Application Key (if needed)\n";
    echo "php artisan key:generate\n\n";
    
    echo "# 3. Clear All Caches\n";
    echo "php artisan config:clear\n";
    echo "php artisan cache:clear\n";
    echo "php artisan route:clear\n";
    echo "php artisan view:clear\n\n";
    
    echo "# 4. Set File Permissions\n";
    echo "chmod -R 775 storage bootstrap/cache\n";
    echo "chown -R www-data:www-data storage bootstrap/cache\n\n";
    
    echo "# 5. Run Database Migrations (if needed)\n";
    echo "php artisan migrate --force\n\n";
    
    echo "# 6. Cache Configuration for Production\n";
    echo "php artisan config:cache\n";
    echo "php artisan route:cache\n";
    echo "php artisan view:cache\n";
    echo "</pre>";
} else {
    echo "<p><em>This appears to be a local environment. Upload to production server first.</em></p>";
}

echo "<h2>📊 Overall Status</h2>";
if ($allPassed) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ All checks passed! Ready for production deployment.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ Some checks failed. Please fix the issues above before deploying.</p>";
}

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='debug.php'>🔍 Diagnostic Script</a></p>";
echo "<p><a href='../'>🏠 Application Home</a></p>";

echo "<hr>";
echo "<p><em>Generated by MCC News Aggregator Deployment Script v1.0</em></p>";
?>
