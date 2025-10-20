<?php

echo "Fixing storage link...\n";

$publicStoragePath = __DIR__ . '/public/storage';
$storageAppPublicPath = __DIR__ . '/storage/app/public';

// Remove existing link/directory
if (file_exists($publicStoragePath)) {
    if (is_link($publicStoragePath)) {
        echo "Removing existing symbolic link...\n";
        unlink($publicStoragePath);
    } elseif (is_dir($publicStoragePath)) {
        echo "Removing existing directory...\n";
        // Remove directory recursively
        function removeDirectory($dir) {
            if (!is_dir($dir)) return false;
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                is_dir($path) ? removeDirectory($path) : unlink($path);
            }
            return rmdir($dir);
        }
        removeDirectory($publicStoragePath);
    }
}

// Create new symbolic link
if (function_exists('symlink')) {
    echo "Creating symbolic link...\n";
    $result = symlink($storageAppPublicPath, $publicStoragePath);
    if ($result) {
        echo "✅ Symbolic link created successfully\n";
    } else {
        echo "❌ Failed to create symbolic link\n";
    }
} else {
    echo "❌ symlink() function not available\n";
    echo "Trying alternative method...\n";
    
    // Alternative: Create junction on Windows
    $command = "mklink /J \"$publicStoragePath\" \"$storageAppPublicPath\"";
    echo "Running: $command\n";
    $output = shell_exec($command);
    echo "Output: $output\n";
}

// Verify the link
if (file_exists($publicStoragePath)) {
    echo "✅ Storage path exists\n";
    
    // Test access to a known file
    $testImagePath = $publicStoragePath . '/announcement-images/H5JP6pLc5w3A0KugmHIatusSNTed1sQGBdDSfPkC.png';
    if (file_exists($testImagePath)) {
        echo "✅ Test image accessible via public link\n";
    } else {
        echo "❌ Test image not accessible via public link\n";
    }
} else {
    echo "❌ Storage path still not accessible\n";
}

echo "Done!\n";
