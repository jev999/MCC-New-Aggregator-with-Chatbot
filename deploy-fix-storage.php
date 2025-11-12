<?php
/**
 * Production Storage Fix Script
 * Run this script on your production server to fix storage access issues
 */

echo "🚀 MCC News Aggregator - Storage Fix Script\n";
echo "==========================================\n\n";

// Check if running via CLI or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>🚀 MCC News Aggregator - Storage Fix Script</h1>";
    echo "<pre>";
}

// Function to log messages
function logMessage($message, $type = 'info') {
    global $isCLI;
    
    $icons = [
        'info' => '🔍',
        'success' => '✅',
        'warning' => '⚠️',
        'error' => '❌'
    ];
    
    $icon = $icons[$type] ?? '🔍';
    $timestamp = date('Y-m-d H:i:s');
    
    if ($isCLI) {
        echo "[{$timestamp}] {$icon} {$message}\n";
    } else {
        $colors = [
            'info' => '#007bff',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'error' => '#dc3545'
        ];
        $color = $colors[$type] ?? '#007bff';
        echo "<span style='color: {$color}'>[{$timestamp}] {$icon} {$message}</span>\n";
    }
}

try {
    // Step 1: Check current directory structure
    logMessage("Step 1: Checking directory structure...");
    
    $baseDir = __DIR__;
    $storageDir = $baseDir . '/storage/app/public';
    $publicDir = $baseDir . '/public';
    $publicStorageDir = $publicDir . '/storage';
    
    logMessage("Base directory: " . $baseDir);
    logMessage("Storage directory: " . $storageDir);
    logMessage("Public directory: " . $publicDir);
    logMessage("Public storage link: " . $publicStorageDir);
    
    // Step 2: Create storage directories if they don't exist
    logMessage("Step 2: Creating storage directories...");
    
    $storageSubdirs = [
        'announcement-images',
        'announcement-videos',
        'event-images', 
        'event-videos',
        'news-images',
        'news-videos',
        'profile_pictures'
    ];
    
    foreach ($storageSubdirs as $subdir) {
        $fullPath = $storageDir . '/' . $subdir;
        if (!is_dir($fullPath)) {
            if (mkdir($fullPath, 0755, true)) {
                logMessage("Created directory: {$subdir}", 'success');
            } else {
                logMessage("Failed to create directory: {$subdir}", 'error');
            }
        } else {
            logMessage("Directory exists: {$subdir}", 'info');
        }
    }
    
    // Step 3: Fix permissions
    logMessage("Step 3: Fixing permissions...");
    
    // Set storage directory permissions
    if (is_dir($storageDir)) {
        chmod($storageDir, 0755);
        logMessage("Set permissions for storage directory", 'success');
        
        // Set permissions for subdirectories and files
        foreach ($storageSubdirs as $subdir) {
            $fullPath = $storageDir . '/' . $subdir;
            if (is_dir($fullPath)) {
                chmod($fullPath, 0755);
                
                // Set file permissions
                $files = glob($fullPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        chmod($file, 0644);
                    }
                }
                logMessage("Fixed permissions for {$subdir} and its files", 'success');
            }
        }
    }
    
    // Step 4: Create or fix storage symlink
    logMessage("Step 4: Creating storage symlink...");
    
    if (file_exists($publicStorageDir)) {
        if (is_link($publicStorageDir)) {
            logMessage("Storage symlink already exists", 'info');
            $target = readlink($publicStorageDir);
            logMessage("Current symlink target: " . $target);
            
            // Check if symlink is correct
            $expectedTarget = realpath($storageDir);
            $currentTarget = realpath($target);
            
            if ($currentTarget !== $expectedTarget) {
                logMessage("Symlink target is incorrect, fixing...", 'warning');
                unlink($publicStorageDir);
            } else {
                logMessage("Symlink target is correct", 'success');
                $symlinkFixed = true;
            }
        } elseif (is_dir($publicStorageDir)) {
            logMessage("Storage directory exists instead of symlink, removing...", 'warning');
            
            // Remove directory recursively
            function removeDirectory($dir) {
                if (!is_dir($dir)) return false;
                
                $files = array_diff(scandir($dir), array('.', '..'));
                foreach ($files as $file) {
                    $path = $dir . '/' . $file;
                    if (is_dir($path)) {
                        removeDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
                return rmdir($dir);
            }
            
            if (removeDirectory($publicStorageDir)) {
                logMessage("Removed existing directory", 'success');
            } else {
                logMessage("Failed to remove existing directory", 'error');
            }
        }
    }
    
    // Create symlink if it doesn't exist or was removed
    if (!isset($symlinkFixed) && !file_exists($publicStorageDir)) {
        if (function_exists('symlink')) {
            if (symlink($storageDir, $publicStorageDir)) {
                logMessage("Created storage symlink successfully", 'success');
            } else {
                logMessage("Failed to create symlink, will try alternative method", 'warning');
                
                // Fallback: Create hard copy
                function copyDirectory($source, $dest) {
                    if (!is_dir($dest)) {
                        mkdir($dest, 0755, true);
                    }
                    
                    $files = scandir($source);
                    foreach ($files as $file) {
                        if ($file != '.' && $file != '..') {
                            $sourcePath = $source . '/' . $file;
                            $destPath = $dest . '/' . $file;
                            
                            if (is_dir($sourcePath)) {
                                copyDirectory($sourcePath, $destPath);
                            } else {
                                copy($sourcePath, $destPath);
                            }
                        }
                    }
                }
                
                copyDirectory($storageDir, $publicStorageDir);
                logMessage("Created storage directory copy (fallback method)", 'warning');
            }
        } else {
            logMessage("Symlink function not available, creating directory copy", 'warning');
            
            // Create directory copy as fallback
            if (!is_dir($publicStorageDir)) {
                mkdir($publicStorageDir, 0755, true);
            }
            
            // Copy existing files
            foreach ($storageSubdirs as $subdir) {
                $sourceDir = $storageDir . '/' . $subdir;
                $destDir = $publicStorageDir . '/' . $subdir;
                
                if (is_dir($sourceDir)) {
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    
                    $files = glob($sourceDir . '/*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            $filename = basename($file);
                            copy($file, $destDir . '/' . $filename);
                        }
                    }
                    logMessage("Copied files from {$subdir}", 'success');
                }
            }
        }
    }
    
    // Step 5: Test storage access
    logMessage("Step 5: Testing storage access...");
    
    // Create a test file
    $testFile = $storageDir . '/test-access.txt';
    $testContent = 'Storage access test - ' . date('Y-m-d H:i:s');
    
    if (file_put_contents($testFile, $testContent)) {
        logMessage("Created test file in storage", 'success');
        
        // Check if accessible via public
        $publicTestFile = $publicStorageDir . '/test-access.txt';
        if (file_exists($publicTestFile) && file_get_contents($publicTestFile) === $testContent) {
            logMessage("Test file accessible via public storage - SUCCESS!", 'success');
        } else {
            logMessage("Test file NOT accessible via public storage - symlink issue", 'error');
        }
        
        // Clean up test file
        unlink($testFile);
        if (file_exists($publicTestFile)) {
            unlink($publicTestFile);
        }
        logMessage("Cleaned up test files", 'info');
    } else {
        logMessage("Failed to create test file - permission issue", 'error');
    }
    
    // Step 6: Check existing announcement images
    logMessage("Step 6: Checking existing announcement images...");
    
    $announcementImagesDir = $storageDir . '/announcement-images';
    if (is_dir($announcementImagesDir)) {
        $images = glob($announcementImagesDir . '/*');
        $imageCount = count($images);
        logMessage("Found {$imageCount} announcement images");
        
        if ($imageCount > 0) {
            // Test first few images
            $testImages = array_slice($images, 0, 3);
            foreach ($testImages as $image) {
                $filename = basename($image);
                $publicImagePath = $publicStorageDir . '/announcement-images/' . $filename;
                
                if (file_exists($publicImagePath)) {
                    logMessage("✓ Image accessible: {$filename}", 'success');
                } else {
                    logMessage("✗ Image NOT accessible: {$filename}", 'error');
                }
            }
        }
    } else {
        logMessage("No announcement images directory found");
    }
    
    // Step 7: Final summary
    logMessage("Step 7: Final summary");
    logMessage("===================");
    
    $checks = [
        'Storage directory exists' => is_dir($storageDir),
        'Storage is writable' => is_writable($storageDir),
        'Public storage exists' => file_exists($publicStorageDir),
        'Public storage is accessible' => is_readable($publicStorageDir),
    ];
    
    $allPassed = true;
    foreach ($checks as $check => $result) {
        $status = $result ? 'PASS' : 'FAIL';
        $type = $result ? 'success' : 'error';
        logMessage("{$check}: {$status}", $type);
        
        if (!$result) {
            $allPassed = false;
        }
    }
    
    if ($allPassed) {
        logMessage("\n🎉 STORAGE FIX COMPLETED SUCCESSFULLY!", 'success');
        logMessage("Your announcement images should now be accessible.", 'success');
    } else {
        logMessage("\n⚠️ SOME ISSUES REMAIN", 'warning');
        logMessage("Please check the errors above and fix them manually.", 'warning');
    }
    
    // Step 8: Next steps
    logMessage("\nNext steps:", 'info');
    logMessage("1. Test your announcement images in the browser", 'info');
    logMessage("2. If images still don't work, check web server configuration", 'info');
    logMessage("3. Visit /storage-diagnostics (in debug mode) for detailed info", 'info');
    logMessage("4. Run 'php artisan storage:fix-link' for advanced fixing", 'info');
    
} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage(), 'error');
    logMessage("Stack trace: " . $e->getTraceAsString(), 'error');
}

if (!$isCLI) {
    echo "</pre>";
}

echo "\n🏁 Script completed at " . date('Y-m-d H:i:s') . "\n";
?>
