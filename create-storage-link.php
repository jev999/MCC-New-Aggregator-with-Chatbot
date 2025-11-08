<?php
/**
 * Web-based Storage Symlink Creator
 * 
 * SECURITY WARNING: Delete this file after use!
 * 
 * Upload this file to your Laravel public/ directory and access it via:
 * https://mcc-nac.com/create-storage-link.php
 * 
 * After successful symlink creation, DELETE THIS FILE immediately!
 */

// Password protection - change this to a secure password
define('ACCESS_PASSWORD', 'mcc-nac-storage-2025');

// Check password
if (!isset($_GET['password']) || $_GET['password'] !== ACCESS_PASSWORD) {
    die('<h1>Access Denied</h1><p>Add ?password=YOUR_PASSWORD to the URL</p>');
}

echo '<h1>MCC-NAC Storage Symlink Creator</h1>';
echo '<pre>';

// Define paths
$targetPath = __DIR__ . '/../storage/app/public';
$linkPath = __DIR__ . '/storage';

echo "Target Path: $targetPath\n";
echo "Link Path: $linkPath\n\n";

// Check if symlink already exists
if (file_exists($linkPath)) {
    if (is_link($linkPath)) {
        echo "✓ Symlink already exists!\n";
        echo "  Current target: " . readlink($linkPath) . "\n\n";
        
        // Verify it points to the correct location
        if (readlink($linkPath) === $targetPath) {
            echo "✓ Symlink is correctly configured.\n";
        } else {
            echo "⚠ Symlink points to wrong location. Recreating...\n";
            unlink($linkPath);
            goto create_symlink;
        }
    } else {
        echo "⚠ A file/directory named 'storage' exists but it's not a symlink.\n";
        echo "  Please manually remove/rename: $linkPath\n";
        echo "  Then run this script again.\n";
    }
} else {
    create_symlink:
    
    // Check if target directory exists
    if (!file_exists($targetPath)) {
        echo "⚠ Target directory does not exist: $targetPath\n";
        echo "  Creating target directory...\n";
        mkdir($targetPath, 0775, true);
    }
    
    // Create symlink
    echo "Creating symlink...\n";
    
    if (symlink($targetPath, $linkPath)) {
        echo "✓ Storage symlink created successfully!\n";
        echo "  Link: $linkPath -> $targetPath\n";
    } else {
        echo "✗ Failed to create symlink.\n\n";
        echo "ERROR: Your hosting provider may not allow symlink creation.\n";
        echo "Please contact your hosting support or use the manual method below.\n\n";
        
        echo "MANUAL METHOD:\n";
        echo "1. Connect via FTP/File Manager\n";
        echo "2. Navigate to: public/\n";
        echo "3. Create a symbolic link named 'storage' pointing to:\n";
        echo "   ../storage/app/public\n\n";
        
        echo "ALTERNATIVE: Some hosts require you to use SSH:\n";
        echo "   cd /path/to/your/laravel/root\n";
        echo "   php artisan storage:link\n";
    }
}

echo "\n";
echo "========================================\n";
echo "Storage Directory Contents:\n";
echo "========================================\n";

// List storage directory contents
if (file_exists($targetPath)) {
    $files = scandir($targetPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $fullPath = $targetPath . '/' . $file;
            $size = is_dir($fullPath) ? '[DIR]' : number_format(filesize($fullPath)) . ' bytes';
            echo "$file - $size\n";
        }
    }
    
    if (count($files) <= 2) {
        echo "(Empty - no uploaded files yet)\n";
    }
} else {
    echo "Target directory does not exist!\n";
}

echo "\n";
echo "========================================\n";
echo "Testing Image URLs:\n";
echo "========================================\n";

// Test some common paths
$testPaths = [
    '/storage/announcements/',
    '/storage/news/',
    '/storage/events/',
];

foreach ($testPaths as $path) {
    $fullPath = __DIR__ . $path;
    if (file_exists($fullPath)) {
        echo "✓ $path exists\n";
    } else {
        echo "⚠ $path does not exist (may be created when first upload occurs)\n";
    }
}

echo "\n";
echo "========================================\n";
echo "⚠ IMPORTANT SECURITY WARNING ⚠\n";
echo "========================================\n";
echo "DELETE THIS FILE IMMEDIATELY AFTER USE!\n";
echo "Leaving this file accessible is a security risk.\n";
echo "\n";
echo "To delete via SSH:\n";
echo "  rm /path/to/public/create-storage-link.php\n";
echo "\n";
echo "Or delete via FTP/File Manager\n";
echo "========================================\n";

echo '</pre>';

// Show delete button
echo '<form method="post" style="margin-top: 20px;">';
echo '<button type="submit" name="delete_self" style="background: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Delete This File Now</button>';
echo '</form>';

// Self-delete functionality
if (isset($_POST['delete_self'])) {
    if (unlink(__FILE__)) {
        die('<h2 style="color: green;">✓ File deleted successfully!</h2>');
    } else {
        die('<h2 style="color: red;">✗ Failed to delete file. Please delete manually.</h2>');
    }
}
