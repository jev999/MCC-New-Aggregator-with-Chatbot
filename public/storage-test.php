<?php
/**
 * Storage Diagnostic Tool for MCC News Aggregator
 * 
 * Upload this file to your production server at: public/storage-test.php
 * Then access it at: https://mcc-nac.com/storage-test.php
 * 
 * This will help diagnose why images and videos are broken.
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Storage Diagnostic - MCC NAC</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        .info { color: #3b82f6; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .command {
            background: #1f2937;
            color: #10b981;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>🔍 MCC NAC Storage Diagnostic</h1>
    
    <?php
    // Get base paths
    $publicPath = __DIR__;
    $storageLinkPath = $publicPath . '/storage';
    $storageAppPublicPath = dirname($publicPath) . '/storage/app/public';
    
    // Check 1: Symbolic Link
    echo '<div class="section">';
    echo '<h2>1. Symbolic Link Check</h2>';
    if (is_link($storageLinkPath)) {
        echo '<p class="success">✅ Symbolic link EXISTS</p>';
        echo '<p>Link target: <code>' . readlink($storageLinkPath) . '</code></p>';
        
        if (is_dir($storageLinkPath) && is_readable($storageLinkPath)) {
            echo '<p class="success">✅ Link is READABLE</p>';
        } else {
            echo '<p class="error">❌ Link exists but is NOT READABLE</p>';
            echo '<p>Run: <code class="command">chmod -R 775 ' . $storageAppPublicPath . '</code></p>';
        }
    } else {
        echo '<p class="error">❌ Symbolic link DOES NOT EXIST</p>';
        echo '<p class="warning">This is likely the cause of broken images!</p>';
        echo '<p>Run this command on your server:</p>';
        echo '<div class="command">php artisan storage:link</div>';
    }
    echo '</div>';
    
    // Check 2: Storage Directory
    echo '<div class="section">';
    echo '<h2>2. Storage Directory Check</h2>';
    if (is_dir($storageAppPublicPath)) {
        echo '<p class="success">✅ Storage directory EXISTS</p>';
        echo '<p>Path: <code>' . $storageAppPublicPath . '</code></p>';
        
        if (is_readable($storageAppPublicPath)) {
            echo '<p class="success">✅ Directory is READABLE</p>';
        } else {
            echo '<p class="error">❌ Directory exists but is NOT READABLE</p>';
        }
        
        if (is_writable($storageAppPublicPath)) {
            echo '<p class="success">✅ Directory is WRITABLE</p>';
        } else {
            echo '<p class="error">❌ Directory is NOT WRITABLE</p>';
            echo '<p>Run: <code class="command">chmod -R 775 ' . $storageAppPublicPath . '</code></p>';
        }
    } else {
        echo '<p class="error">❌ Storage directory DOES NOT EXIST</p>';
        echo '<p>Create it: <code class="command">mkdir -p ' . $storageAppPublicPath . '</code></p>';
    }
    echo '</div>';
    
    // Check 3: Uploaded Files
    echo '<div class="section">';
    echo '<h2>3. Uploaded Files Check</h2>';
    $uploadDirs = [
        'announcement-images',
        'announcement-videos',
        'event-images',
        'event-videos',
        'news-images',
        'news-videos'
    ];
    
    echo '<table>';
    echo '<tr><th>Directory</th><th>Status</th><th>File Count</th></tr>';
    foreach ($uploadDirs as $dir) {
        $fullPath = $storageAppPublicPath . '/' . $dir;
        echo '<tr>';
        echo '<td><code>' . $dir . '</code></td>';
        
        if (is_dir($fullPath)) {
            $files = glob($fullPath . '/*');
            $fileCount = count($files);
            echo '<td class="success">✅ Exists</td>';
            echo '<td>' . $fileCount . ' files</td>';
        } else {
            echo '<td class="warning">⚠️ Not created yet</td>';
            echo '<td>-</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    
    // Check 4: Test Image Access
    echo '<div class="section">';
    echo '<h2>4. Test Image Access</h2>';
    
    // Find a test image
    $testImage = null;
    foreach ($uploadDirs as $dir) {
        $fullPath = $storageAppPublicPath . '/' . $dir;
        if (is_dir($fullPath)) {
            $images = glob($fullPath . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            if (!empty($images)) {
                $testImage = basename($images[0]);
                $testImagePath = $dir . '/' . $testImage;
                break;
            }
        }
    }
    
    if ($testImage) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST'];
        $testUrl = $protocol . '://' . $domain . '/storage/' . $testImagePath;
        
        echo '<p>Found test image: <code>' . $testImagePath . '</code></p>';
        echo '<p>Test URL: <a href="' . $testUrl . '" target="_blank">' . $testUrl . '</a></p>';
        echo '<p>Try clicking the link above. If it loads, your storage is working!</p>';
        echo '<img src="' . $testUrl . '" alt="Test Image" style="max-width: 300px; margin: 10px 0; border: 2px solid #ddd; border-radius: 5px;">';
    } else {
        echo '<p class="info">ℹ️ No uploaded images found yet. Upload content from admin panel first.</p>';
    }
    echo '</div>';
    
    // Check 5: Environment
    echo '<div class="section">';
    echo '<h2>5. Server Environment</h2>';
    echo '<table>';
    echo '<tr><th>Setting</th><th>Value</th></tr>';
    echo '<tr><td>PHP Version</td><td>' . phpversion() . '</td></tr>';
    echo '<tr><td>Server Software</td><td>' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</td></tr>';
    echo '<tr><td>Document Root</td><td><code>' . $_SERVER['DOCUMENT_ROOT'] . '</code></td></tr>';
    echo '<tr><td>Current User</td><td>' . get_current_user() . '</td></tr>';
    echo '<tr><td>Symlink Support</td><td>' . (function_exists('symlink') ? '<span class="success">✅ Yes</span>' : '<span class="error">❌ No</span>') . '</td></tr>';
    echo '</table>';
    echo '</div>';
    
    // Quick Fix Commands
    echo '<div class="section">';
    echo '<h2>6. Quick Fix Commands</h2>';
    echo '<p>Run these commands on your production server (via SSH):</p>';
    echo '<div class="command">';
    echo '# Create symbolic link<br>';
    echo 'php artisan storage:link<br><br>';
    echo '# Set proper permissions<br>';
    echo 'chmod -R 775 storage<br>';
    echo 'chmod -R 775 bootstrap/cache<br><br>';
    echo '# Set ownership (replace www-data with your web server user)<br>';
    echo 'chown -R www-data:www-data storage<br>';
    echo 'chown -R www-data:www-data bootstrap/cache<br><br>';
    echo '# Clear caches<br>';
    echo 'php artisan config:clear<br>';
    echo 'php artisan cache:clear';
    echo '</div>';
    echo '</div>';
    
    // Alternative Solution
    echo '<div class="section">';
    echo '<h2>7. Alternative: Direct Upload (No Symlink)</h2>';
    echo '<p>If your hosting doesn\'t support symbolic links, you can store files directly in <code>public/uploads/</code>:</p>';
    echo '<ol>';
    echo '<li>Update your <code>.env</code> file: <code>FILESYSTEM_DISK=public_uploads</code></li>';
    echo '<li>Clear config: <code class="command">php artisan config:clear</code></li>';
    echo '<li>Create uploads directory: <code class="command">mkdir public/uploads && chmod 775 public/uploads</code></li>';
    echo '</ol>';
    echo '<p class="warning">⚠️ You\'ll need to re-upload existing content or move files from storage/app/public/ to public/uploads/</p>';
    echo '</div>';
    ?>
    
    <div class="section">
        <h2>📝 Summary</h2>
        <p>After fixing the issues above:</p>
        <ol>
            <li>Make sure <code>APP_URL</code> in your <code>.env</code> is set to <code>https://mcc-nac.com</code></li>
            <li>Run <code>php artisan config:clear</code></li>
            <li>Test by uploading new content from admin panel</li>
            <li><strong>Delete this diagnostic file for security!</strong></li>
        </ol>
    </div>
    
    <div style="text-align: center; margin: 30px 0; color: #6b7280;">
        <p>MCC News Aggregator - Storage Diagnostic Tool</p>
        <p style="color: #ef4444; font-weight: bold;">⚠️ Delete this file after fixing the issues!</p>
    </div>
</body>
</html>
