<?php
/**
 * Production Storage Fix Script
 * 
 * This script helps fix storage symlink and permissions issues on production servers
 * Run this script in your Laravel root directory when you don't have server SSH access
 */

// Ensure this is run from Laravel root
if (!file_exists('artisan') || !file_exists('composer.json')) {
    die("ERROR: This script must be run from your Laravel project root directory.\n");
}

echo "=== Laravel Production Storage Fix Script ===\n\n";

// Load Laravel application
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "✅ Laravel application loaded successfully\n\n";

// Function to check and fix storage symlink
function fixStorageSymlink() {
    echo "🔗 Checking storage symlink...\n";
    
    $publicStoragePath = public_path('storage');
    $targetPath = storage_path('app/public');
    
    echo "   Public path: $publicStoragePath\n";
    echo "   Target path: $targetPath\n";
    
    // Check current state
    if (file_exists($publicStoragePath)) {
        if (is_link($publicStoragePath)) {
            $currentTarget = readlink($publicStoragePath);
            echo "   ✅ Symlink exists, points to: $currentTarget\n";
            
            // Check if it points to the right place
            if (realpath($currentTarget) === realpath($targetPath)) {
                echo "   ✅ Symlink is correct!\n";
                return true;
            } else {
                echo "   ⚠️  Symlink points to wrong location\n";
                echo "   🔧 Removing incorrect symlink...\n";
                unlink($publicStoragePath);
            }
        } else {
            echo "   ⚠️  public/storage exists but is not a symlink\n";
            if (is_dir($publicStoragePath)) {
                $files = glob($publicStoragePath . '/*');
                if (empty($files)) {
                    echo "   🔧 Removing empty storage directory...\n";
                    rmdir($publicStoragePath);
                } else {
                    echo "   ❌ Cannot fix: public/storage directory contains files\n";
                    return false;
                }
            }
        }
    }
    
    // Create the symlink
    echo "   🔧 Creating storage symlink...\n";
    
    try {
        if (function_exists('symlink')) {
            if (symlink($targetPath, $publicStoragePath)) {
                echo "   ✅ Symlink created successfully!\n";
                return true;
            } else {
                echo "   ❌ Failed to create symlink using symlink()\n";
            }
        } else {
            echo "   ❌ symlink() function not available on this server\n";
        }
        
        // Try Laravel's artisan command as fallback
        echo "   🔧 Trying Laravel artisan command...\n";
        Artisan::call('storage:link');
        
        if (is_link($publicStoragePath)) {
            echo "   ✅ Symlink created using artisan command!\n";
            return true;
        } else {
            echo "   ❌ Artisan command failed to create symlink\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error creating symlink: " . $e->getMessage() . "\n";
        return false;
    }
}

// Function to check storage permissions
function checkStoragePermissions() {
    echo "\n📁 Checking storage permissions...\n";
    
    $storagePath = storage_path();
    $appPublicPath = storage_path('app/public');
    
    $directories = [
        'storage' => $storagePath,
        'storage/app/public' => $appPublicPath,
        'storage/app/public/announcement-images' => $appPublicPath . '/announcement-images',
        'storage/app/public/event-images' => $appPublicPath . '/event-images',
        'storage/app/public/news-images' => $appPublicPath . '/news-images',
    ];
    
    foreach ($directories as $name => $path) {
        if (is_dir($path)) {
            $perms = substr(sprintf('%o', fileperms($path)), -3);
            $readable = is_readable($path) ? '✅' : '❌';
            $writable = is_writable($path) ? '✅' : '❌';
            
            echo "   $name: $perms | Readable: $readable | Writable: $writable\n";
            
            // Check for common files
            if (strpos($name, 'images') !== false) {
                $files = glob($path . '/*');
                $fileCount = count($files);
                echo "     └─ Contains $fileCount files\n";
                
                // Check a sample file permission
                if ($fileCount > 0) {
                    $sampleFile = $files[0];
                    $filePerms = substr(sprintf('%o', fileperms($sampleFile)), -3);
                    $fileReadable = is_readable($sampleFile) ? '✅' : '❌';
                    echo "     └─ Sample file: " . basename($sampleFile) . " ($filePerms) Readable: $fileReadable\n";
                }
            }
        } else {
            echo "   $name: ❌ Directory does not exist\n";
        }
    }
}

// Function to test file access
function testFileAccess() {
    echo "\n🧪 Testing file access...\n";
    
    // Get a sample announcement with image
    try {
        $announcement = \App\Models\Announcement::whereNotNull('image_path')
            ->orWhereNotNull('image_paths')
            ->first();
            
        if ($announcement) {
            echo "   Testing with Announcement ID: {$announcement->id}\n";
            
            $imagePaths = [];
            if ($announcement->image_path) {
                $imagePaths[] = $announcement->image_path;
            }
            if ($announcement->image_paths) {
                $decoded = json_decode($announcement->image_paths, true);
                if (is_array($decoded)) {
                    $imagePaths = array_merge($imagePaths, $decoded);
                }
            }
            
            foreach ($imagePaths as $imagePath) {
                $fullPath = storage_path('app/public/' . $imagePath);
                $publicUrl = url('storage/' . $imagePath);
                
                echo "   Image: $imagePath\n";
                echo "     └─ Storage path: $fullPath\n";
                echo "     └─ File exists: " . (file_exists($fullPath) ? '✅' : '❌') . "\n";
                echo "     └─ File readable: " . (is_readable($fullPath) ? '✅' : '❌') . "\n";
                echo "     └─ Public URL: $publicUrl\n";
                
                if (file_exists($fullPath)) {
                    $size = filesize($fullPath);
                    $perms = substr(sprintf('%o', fileperms($fullPath)), -3);
                    echo "     └─ File size: " . number_format($size) . " bytes\n";
                    echo "     └─ File permissions: $perms\n";
                }
                echo "\n";
            }
        } else {
            echo "   ⚠️  No announcements with images found to test\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error testing file access: " . $e->getMessage() . "\n";
    }
}

// Function to provide hosting provider instructions
function generateHostingInstructions() {
    echo "\n📧 Instructions for your hosting provider:\n";
    echo "========================================\n\n";
    
    $storagePath = storage_path('app/public');
    $publicPath = public_path('storage');
    
    echo "Dear Hosting Support,\n\n";
    echo "I need help fixing file permissions and symlinks for my Laravel application.\n";
    echo "Please execute the following commands on my server:\n\n";
    
    echo "1. Create storage symlink:\n";
    echo "   cd " . base_path() . "\n";
    echo "   ln -sf $storagePath $publicPath\n\n";
    
    echo "2. Fix storage permissions:\n";
    echo "   chown -R www-data:www-data " . storage_path() . "\n";
    echo "   chmod -R 755 " . storage_path() . "\n";
    echo "   find " . storage_path() . " -type f -exec chmod 644 {} \\;\n\n";
    
    echo "3. Verify the symlink:\n";
    echo "   ls -la " . public_path() . "/storage\n\n";
    
    echo "The symlink should point from:\n";
    echo "   FROM: $publicPath\n";
    echo "   TO: $storagePath\n\n";
    
    echo "Thank you!\n\n";
    echo "========================================\n";
}

// Main execution
echo "🚀 Starting storage diagnostics and fixes...\n\n";

// Step 1: Check and fix symlink
$symlinkFixed = fixStorageSymlink();

// Step 2: Check permissions
checkStoragePermissions();

// Step 3: Test file access
testFileAccess();

// Step 4: Generate instructions
generateHostingInstructions();

// Summary
echo "\n📋 SUMMARY:\n";
echo "===========\n";
echo "Symlink Status: " . ($symlinkFixed ? "✅ Fixed" : "❌ Needs hosting provider help") . "\n";
echo "Custom Route: ✅ Available at /media/{path} (proxy)\n";
echo "Diagnostics: ✅ Available at /storage-diagnostics\n";

if (!$symlinkFixed) {
    echo "\n⚠️  NEXT STEPS:\n";
    echo "1. Contact your hosting provider with the instructions above\n";
    echo "2. Or try accessing: https://mcc-nac.com/fix-production-storage?force=1\n";
    echo "3. Your site should work with the custom /storage route as a temporary fix\n";
}

echo "\n✅ Script completed!\n";
