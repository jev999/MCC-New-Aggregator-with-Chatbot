<?php
// Simple test file to check if PHP is working
echo "<h1>PHP is working!</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Check if Laravel files exist
$laravelFiles = [
    '../bootstrap/app.php',
    '../app/Http/Kernel.php',
    '../config/app.php',
    '../.env'
];

echo "<h2>Laravel Files Check:</h2>";
foreach ($laravelFiles as $file) {
    $exists = file_exists($file);
    echo "<p>" . $file . ": " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

// Check directory permissions
echo "<h2>Directory Permissions:</h2>";
$dirs = ['.', '..', '../storage', '../bootstrap/cache'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "<p>" . $dir . ": " . $perms . "</p>";
    }
}
?>
