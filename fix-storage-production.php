<?php
/**
 * Production Storage Fix Script
 * This script fixes broken images and videos in production by creating proper symlinks
 * and directory structure.
 * 
 * Run this file ONCE after deployment by visiting: https://mcc-nac.com/fix-storage-production.php
 * Then DELETE this file for security.
 */

// Security: Only allow execution from command line or localhost in development
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
    // In production, require a secret key
    $secret = $_GET['secret'] ?? '';
    if ($secret !== 'mcc2025fix') {
        die('Access denied. This script can only be run with the correct secret key.');
    }
}

echo "=== MCC News Aggregator - Storage Fix Script ===\n\n";

// Define paths
$publicPath = __DIR__ . '/public';
$storagePath = __DIR__ . '/storage/app/public';
$symlinkPath = $publicPath . '/storage';

echo "1. Checking directory structure...\n";
echo "   Public path: $publicPath\n";
echo "   Storage path: $storagePath\n";
echo "   Symlink path: $symlinkPath\n\n";

// Step 1: Ensure storage/app/public directory exists
if (!file_exists($storagePath)) {
    echo "2. Creating storage/app/public directory...\n";
    if (!mkdir($storagePath, 0755, true)) {
        die("ERROR: Failed to create storage directory. Check permissions.\n");
    }
    echo "   ✓ Directory created\n\n";
} else {
    echo "2. Storage directory exists ✓\n\n";
}

// Step 2: Create required subdirectories
$subdirs = [
    'announcement-images',
    'announcement-videos',
    'event-images',
    'event-videos',
    'news-images',
    'news-videos',
];

echo "3. Creating subdirectories...\n";
foreach ($subdirs as $subdir) {
    $fullPath = $storagePath . '/' . $subdir;
    if (!file_exists($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "   ✓ Created: $subdir\n";
        } else {
            echo "   ✗ Failed: $subdir\n";
        }
    } else {
        echo "   ✓ Exists: $subdir\n";
    }
}
echo "\n";

// Step 3: Handle symbolic link
echo "4. Checking symbolic link...\n";

if (file_exists($symlinkPath) || is_link($symlinkPath)) {
    // Check if it's a valid symlink
    if (is_link($symlinkPath)) {
        $linkTarget = readlink($symlinkPath);
        echo "   Current symlink points to: $linkTarget\n";
        
        // Check if it points to the correct location
        if (realpath($linkTarget) === realpath($storagePath)) {
            echo "   ✓ Symlink is correct\n\n";
        } else {
            echo "   ✗ Symlink points to wrong location\n";
            echo "   Removing old symlink...\n";
            
            if (is_link($symlinkPath)) {
                unlink($symlinkPath);
            } elseif (is_dir($symlinkPath)) {
                rmdir($symlinkPath);
            } else {
                unlink($symlinkPath);
            }
            
            echo "   Creating new symlink...\n";
            if (symlink($storagePath, $symlinkPath)) {
                echo "   ✓ Symlink created successfully\n\n";
            } else {
                echo "   ✗ Failed to create symlink\n\n";
            }
        }
    } else {
        // It's a regular file or directory, remove it
        echo "   ✗ Path exists but is not a symlink\n";
        echo "   Removing and creating symlink...\n";
        
        if (is_dir($symlinkPath)) {
            rmdir($symlinkPath);
        } else {
            unlink($symlinkPath);
        }
        
        if (symlink($storagePath, $symlinkPath)) {
            echo "   ✓ Symlink created successfully\n\n";
        } else {
            echo "   ✗ Failed to create symlink\n\n";
        }
    }
} else {
    echo "   No symlink exists, creating...\n";
    if (symlink($storagePath, $symlinkPath)) {
        echo "   ✓ Symlink created successfully\n\n";
    } else {
        // If symlink fails (common on Windows or restricted hosts), try alternative method
        echo "   ✗ Symlink failed. Trying alternative method...\n\n";
        
        echo "   === ALTERNATIVE: Create .htaccess redirect ===\n";
        echo "   Since symlink creation failed, we'll use .htaccess to redirect requests.\n\n";
        
        // Create a .htaccess in public/storage to redirect to storage/app/public
        $htaccessContent = <<<HTACCESS
# Redirect storage requests to actual storage location
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ ../../storage/app/public/$1 [L]
</IfModule>

# Allow access to image and video files
<FilesMatch "\.(jpg|jpeg|png|gif|mp4|webm|svg)$">
    Order allow,deny
    Allow from all
</FilesMatch>
HTACCESS;
        
        // Create public/storage directory if it doesn't exist
        if (!file_exists($symlinkPath)) {
            mkdir($symlinkPath, 0755, true);
        }
        
        $htaccessFile = $symlinkPath . '/.htaccess';
        if (file_put_contents($htaccessFile, $htaccessContent)) {
            echo "   ✓ .htaccess redirect created\n\n";
        } else {
            echo "   ✗ Failed to create .htaccess\n\n";
        }
    }
}

// Step 4: Check file permissions
echo "5. Checking file permissions...\n";
$storagePerms = fileperms($storagePath);
$readable = is_readable($storagePath);
$writable = is_writable($storagePath);

echo "   Storage permissions: " . substr(sprintf('%o', $storagePerms), -4) . "\n";
echo "   Readable: " . ($readable ? '✓ Yes' : '✗ No') . "\n";
echo "   Writable: " . ($writable ? '✓ Yes' : '✗ No') . "\n\n";

if (!$readable || !$writable) {
    echo "   ⚠ Warning: Storage directory may not have correct permissions\n";
    echo "   Try running: chmod -R 755 storage/\n\n";
}

// Step 5: Count existing files
echo "6. Counting existing media files...\n";
$imageCount = 0;
$videoCount = 0;

foreach ($subdirs as $subdir) {
    $fullPath = $storagePath . '/' . $subdir;
    if (file_exists($fullPath)) {
        $files = glob($fullPath . '/*');
        $count = count($files);
        echo "   $subdir: $count files\n";
        
        if (strpos($subdir, 'image') !== false) {
            $imageCount += $count;
        } else {
            $videoCount += $count;
        }
    }
}

echo "\n   Total images: $imageCount\n";
echo "   Total videos: $videoCount\n\n";

// Step 6: Test URL access
echo "7. Testing URL access...\n";
$testImagePath = $symlinkPath . '/test-access.txt';
$testContent = 'Storage access is working! ' . date('Y-m-d H:i:s');

if (file_put_contents($testImagePath, $testContent)) {
    echo "   ✓ Test file created\n";
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'mcc-nac.com';
    $testUrl = "$protocol://$host/storage/test-access.txt";
    
    echo "   Test URL: $testUrl\n";
    echo "   Visit this URL to verify storage access is working.\n\n";
} else {
    echo "   ✗ Failed to create test file\n\n";
}

echo "=== Storage Fix Complete ===\n\n";

echo "NEXT STEPS:\n";
echo "1. Visit your test URL above to verify storage access\n";
echo "2. Go to your dashboard and check if images/videos are now visible\n";
echo "3. If images still don't appear, check the browser console for errors\n";
echo "4. DELETE this file (fix-storage-production.php) for security!\n\n";

echo "If problems persist, contact your hosting provider to:\n";
echo "- Enable symlink support\n";
echo "- Set proper file permissions (755 for directories, 644 for files)\n";
echo "- Ensure mod_rewrite is enabled for .htaccess\n\n";

// If running in browser, show formatted output
if (php_sapi_name() !== 'cli') {
    echo "<script>
    document.body.style.fontFamily = 'monospace';
    document.body.style.whiteSpace = 'pre';
    document.body.style.padding = '20px';
    document.body.style.backgroundColor = '#f5f5f5';
    </script>";
}
