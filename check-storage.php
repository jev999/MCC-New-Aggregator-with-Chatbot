<?php
/**
 * Storage Configuration Checker
 * This diagnostic script checks your Laravel storage setup in production
 * 
 * Run by visiting: https://mcc-nac.com/check-storage.php
 * DELETE this file after use for security!
 */

// Security check
$secret = $_GET['secret'] ?? '';
if ($secret !== 'mcc2025check') {
    die('Access denied. Add ?secret=mcc2025check to the URL');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Storage Configuration Checker</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #0f0;
            padding: 20px;
            line-height: 1.6;
        }
        .header {
            color: #0ff;
            font-size: 20px;
            border-bottom: 2px solid #0ff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .section {
            background: #2a2a2a;
            border: 1px solid #0f0;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        .label { color: #0ff; font-weight: bold; }
        .value { color: #fff; }
        ul { list-style-type: none; padding-left: 20px; }
        li:before { content: "→ "; color: #0f0; }
        .status-ok:before { content: "✓ "; color: #0f0; }
        .status-fail:before { content: "✗ "; color: #f00; }
        .status-warn:before { content: "⚠ "; color: #ff0; }
    </style>
</head>
<body>
    <div class="header">═══ MCC STORAGE CONFIGURATION CHECKER ═══</div>

<?php

echo "<div class='section'>";
echo "<div class='label'>1. SERVER INFORMATION</div><br>";
echo "PHP Version: <span class='value'>" . phpversion() . "</span><br>";
echo "Server Software: <span class='value'>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</span><br>";
echo "Document Root: <span class='value'>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</span><br>";
echo "Current Directory: <span class='value'>" . __DIR__ . "</span><br>";
echo "</div>";

// Check directory structure
echo "<div class='section'>";
echo "<div class='label'>2. DIRECTORY STRUCTURE CHECK</div><br>";
echo "<ul>";

$dirs = [
    'storage' => __DIR__ . '/storage',
    'storage/app' => __DIR__ . '/storage/app',
    'storage/app/public' => __DIR__ . '/storage/app/public',
    'public' => __DIR__ . '/public',
    'public/storage' => __DIR__ . '/public/storage',
];

foreach ($dirs as $name => $path) {
    $exists = file_exists($path);
    $readable = $exists && is_readable($path);
    $writable = $exists && is_writable($path);
    $isLink = $exists && is_link($path);
    
    echo "<li class='" . ($exists ? 'status-ok' : 'status-fail') . "'>";
    echo "$name: ";
    
    if ($exists) {
        echo "<span class='success'>EXISTS</span> | ";
        echo "Readable: " . ($readable ? "<span class='success'>YES</span>" : "<span class='error'>NO</span>") . " | ";
        echo "Writable: " . ($writable ? "<span class='success'>YES</span>" : "<span class='error'>NO</span>") . " | ";
        
        if ($isLink) {
            $target = readlink($path);
            echo "Symlink → <span class='value'>$target</span>";
        } else {
            $perms = fileperms($path);
            echo "Perms: <span class='value'>" . substr(sprintf('%o', $perms), -4) . "</span>";
        }
    } else {
        echo "<span class='error'>MISSING</span>";
    }
    
    echo "</li>";
}

echo "</ul>";
echo "</div>";

// Check media directories
echo "<div class='section'>";
echo "<div class='label'>3. MEDIA DIRECTORIES CHECK</div><br>";
echo "<ul>";

$mediaPath = __DIR__ . '/storage/app/public';
$mediaDirs = [
    'announcement-images',
    'announcement-videos',
    'event-images',
    'event-videos',
    'news-images',
    'news-videos',
];

foreach ($mediaDirs as $dir) {
    $fullPath = $mediaPath . '/' . $dir;
    $exists = file_exists($fullPath);
    
    echo "<li class='" . ($exists ? 'status-ok' : 'status-warn') . "'>";
    echo "$dir: ";
    
    if ($exists) {
        $files = glob($fullPath . '/*');
        $count = count($files);
        echo "<span class='success'>EXISTS</span> | Files: <span class='value'>$count</span>";
    } else {
        echo "<span class='warning'>MISSING</span>";
    }
    
    echo "</li>";
}

echo "</ul>";
echo "</div>";

// Check symlink status
echo "<div class='section'>";
echo "<div class='label'>4. SYMLINK STATUS</div><br>";

$symlinkPath = __DIR__ . '/public/storage';
$targetPath = __DIR__ . '/storage/app/public';

if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        $linkTarget = readlink($symlinkPath);
        $linkTargetReal = realpath($linkTarget);
        $targetPathReal = realpath($targetPath);
        
        echo "<li class='status-ok'>Symlink exists</li>";
        echo "Link target: <span class='value'>$linkTarget</span><br>";
        echo "Real path: <span class='value'>$linkTargetReal</span><br>";
        echo "Expected: <span class='value'>$targetPathReal</span><br>";
        
        if ($linkTargetReal === $targetPathReal) {
            echo "<br><span class='success'>✓ SYMLINK IS CORRECT</span>";
        } else {
            echo "<br><span class='error'>✗ SYMLINK POINTS TO WRONG LOCATION</span>";
        }
    } else {
        echo "<li class='status-fail'>Path exists but is NOT a symlink</li>";
        echo "Type: <span class='warning'>" . (is_dir($symlinkPath) ? 'DIRECTORY' : 'FILE') . "</span><br>";
        echo "<span class='error'>This will cause media access issues!</span>";
    }
} else {
    echo "<li class='status-fail'>Symlink does NOT exist</li>";
    echo "<span class='error'>Media files will not be accessible!</span>";
}

echo "</div>";

// Check URL access
echo "<div class='section'>";
echo "<div class='label'>5. URL ACCESS TEST</div><br>";

// Create a test file
$testFile = __DIR__ . '/storage/app/public/test-checker.txt';
$testContent = 'Access test: ' . date('Y-m-d H:i:s');
$testWritten = @file_put_contents($testFile, $testContent);

if ($testWritten) {
    echo "<li class='status-ok'>Test file written successfully</li>";
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'mcc-nac.com';
    $testUrl = "$protocol://$host/storage/test-checker.txt";
    
    echo "Test URL: <a href='$testUrl' target='_blank' style='color: #0ff;'>$testUrl</a><br>";
    echo "Click the link above. If you can read the test content, storage access works!<br>";
    
    // Try to read via HTTP
    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response === $testContent) {
        echo "<br><span class='success'>✓ URL ACCESS WORKS! Storage is configured correctly.</span>";
    } else {
        echo "<br><span class='error'>✗ URL ACCESS FAILED (HTTP $httpCode)</span>";
        echo "<br>This means the symlink or .htaccess is not working.";
    }
} else {
    echo "<li class='status-fail'>Cannot write test file</li>";
    echo "<span class='error'>Check storage directory permissions!</span>";
}

echo "</div>";

// Recommendations
echo "<div class='section'>";
echo "<div class='label'>6. RECOMMENDATIONS</div><br>";

$issues = [];

if (!file_exists(__DIR__ . '/storage/app/public')) {
    $issues[] = "Create storage/app/public directory";
}

if (!file_exists(__DIR__ . '/public/storage') || !is_link(__DIR__ . '/public/storage')) {
    $issues[] = "Run fix-storage-production.php to create proper symlink";
}

if (!is_writable(__DIR__ . '/storage/app/public')) {
    $issues[] = "Set storage permissions: chmod -R 755 storage/";
}

if (count($issues) === 0) {
    echo "<span class='success'>✓ No major issues detected! Your storage is properly configured.</span>";
} else {
    echo "<span class='warning'>⚠ Issues found that need fixing:</span><br><ul>";
    foreach ($issues as $issue) {
        echo "<li class='status-warn'>$issue</li>";
    }
    echo "</ul>";
    echo "<br><span class='warning'>Run the fix-storage-production.php script to resolve these issues.</span>";
}

echo "</div>";

?>

    <div class="section">
        <div class="label">NEXT STEPS</div><br>
        <span class="warning">1. Review the checks above</span><br>
        <span class="warning">2. If issues found, run: fix-storage-production.php?secret=mcc2025fix</span><br>
        <span class="warning">3. After fixing, re-run this checker to verify</span><br>
        <span class="error">4. DELETE this file (check-storage.php) for security!</span>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #0f0; text-align: center; color: #666;">
        MCC News Aggregator © 2025 | Storage Diagnostic Tool
    </div>
</body>
</html>
