<?php
// Simple diagnostic script to test Apache/PHP configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MCC News Aggregator - Diagnostic Report</h1>";
echo "<h2>PHP Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] ?? 'Unknown' . "</p>";

echo "<h2>Laravel Environment Check</h2>";

// Check if Laravel files exist
$laravelFiles = [
    'Bootstrap App' => '../bootstrap/app.php',
    'Vendor Autoload' => '../vendor/autoload.php',
    'Environment File' => '../.env',
    'Storage Directory' => '../storage',
    'Config Directory' => '../config'
];

foreach ($laravelFiles as $name => $path) {
    $exists = file_exists($path);
    $status = $exists ? '✅ EXISTS' : '❌ MISSING';
    echo "<p><strong>$name:</strong> $status</p>";
}

echo "<h2>Environment Variables</h2>";
if (file_exists('../.env')) {
    $envContent = file_get_contents('../.env');
    $envLines = explode("\n", $envContent);
    foreach ($envLines as $line) {
        if (strpos($line, '=') !== false && !empty(trim($line)) && !startsWith(trim($line), '#')) {
            [$key, $value] = explode('=', $line, 2);
            if (in_array($key, ['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION'])) {
                echo "<p><strong>$key:</strong> $value</p>";
            }
        }
    }
}

echo "<h2>Laravel Bootstrap Test</h2>";
try {
    // Test Laravel bootstrap
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    echo "<p>✅ Laravel bootstrap successful</p>";
    
    // Test basic Laravel functionality
    $config = $app->make('config');
    echo "<p>✅ Configuration service available</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Laravel bootstrap failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

echo "<h2>PHP Extensions</h2>";
$requiredExtensions = ['mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅ LOADED' : '❌ MISSING';
    echo "<p><strong>$ext:</strong> $status</p>";
}

echo "<p><em>Generated at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
