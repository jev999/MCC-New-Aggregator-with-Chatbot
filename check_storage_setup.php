<?php
/**
 * Script to check and fix storage setup for production
 * Run this script to ensure storage is properly configured
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Storage Configuration Check ===\n\n";

// Check if storage link exists
$publicPath = public_path('storage');
$storagePath = storage_path('app/public');

echo "Checking storage link...\n";
echo "Public storage path: $publicPath\n";
echo "Storage app/public path: $storagePath\n\n";

if (file_exists($publicPath)) {
    if (is_link($publicPath)) {
        echo "✅ Storage link exists and is a symbolic link\n";
        echo "Link target: " . readlink($publicPath) . "\n";
    } else {
        echo "⚠️  Storage path exists but is not a symbolic link\n";
    }
} else {
    echo "❌ Storage link does not exist\n";
    echo "Run: php artisan storage:link\n";
}

// Check storage disk configuration
echo "\nChecking storage disk configuration...\n";
$config = config('filesystems.disks.public');
echo "Public disk root: " . $config['root'] . "\n";
echo "Public disk URL: " . $config['url'] . "\n";

// Test URL generation
echo "\nTesting URL generation...\n";
$testPath = 'test-image.jpg';
echo "Storage::disk('public')->url('$testPath'): " . \Storage::disk('public')->url($testPath) . "\n";
echo "asset('storage/$testPath'): " . asset("storage/$testPath") . "\n";

// Check if we can write to storage
echo "\nTesting storage write permissions...\n";
$testFile = 'test-write-' . time() . '.txt';
try {
    \Storage::disk('public')->put($testFile, 'test content');
    echo "✅ Can write to storage\n";
    
    // Clean up test file
    \Storage::disk('public')->delete($testFile);
    echo "✅ Can delete from storage\n";
} catch (Exception $e) {
    echo "❌ Cannot write to storage: " . $e->getMessage() . "\n";
}

// Check existing media files
echo "\nChecking existing media files...\n";
$directories = ['announcement-images', 'announcement-videos', 'event-images', 'event-videos', 'news-images', 'news-videos'];

foreach ($directories as $dir) {
    $files = \Storage::disk('public')->files($dir);
    echo "$dir: " . count($files) . " files\n";
    
    if (count($files) > 0) {
        $sampleFile = $files[0];
        $url = \Storage::disk('public')->url($sampleFile);
        echo "  Sample URL: $url\n";
        
        // Check if file is accessible via HTTP
        $fullPath = storage_path('app/public/' . $sampleFile);
        if (file_exists($fullPath)) {
            echo "  ✅ File exists on disk\n";
        } else {
            echo "  ❌ File missing on disk\n";
        }
    }
}

echo "\n=== Check Complete ===\n";
echo "\nIf you see issues above, run these commands:\n";
echo "1. php artisan storage:link\n";
echo "2. chmod -R 755 storage/\n";
echo "3. chmod -R 755 public/storage/\n";
