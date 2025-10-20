<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING FILE UPLOAD SYSTEM ===\n\n";

// Test 1: Check storage directories
echo "1. CHECKING STORAGE DIRECTORIES:\n";

$directories = [
    'storage/app/public' => storage_path('app/public'),
    'storage/app/public/news-images' => storage_path('app/public/news-images'),
    'storage/app/public/news-videos' => storage_path('app/public/news-videos'),
    'storage/app/public/news-csv' => storage_path('app/public/news-csv'),
    'public/storage' => public_path('storage')
];

foreach ($directories as $name => $path) {
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    echo "  $name: " . ($exists ? '✅ Exists' : '❌ Missing') . 
         ($exists ? ($writable ? ' (Writable)' : ' (Not Writable)') : '') . "\n";
}

// Test 2: Check storage link
echo "\n2. CHECKING STORAGE LINK:\n";
$storageLink = public_path('storage');
if (is_link($storageLink)) {
    $target = readlink($storageLink);
    echo "  Storage link exists: ✅ Yes\n";
    echo "  Points to: $target\n";
    echo "  Target exists: " . (is_dir($target) ? '✅ Yes' : '❌ No') . "\n";
} else {
    echo "  Storage link exists: ❌ No\n";
    echo "  Attempting to create storage link...\n";
    
    // Try to create the link
    $result = shell_exec('cd ' . base_path() . ' && php artisan storage:link 2>&1');
    echo "  Result: $result\n";
}

// Test 3: Check file permissions
echo "\n3. CHECKING FILE PERMISSIONS:\n";
$testFile = storage_path('app/public/test_write.txt');
$canWrite = file_put_contents($testFile, 'test') !== false;
echo "  Can write to storage: " . ($canWrite ? '✅ Yes' : '❌ No') . "\n";

if ($canWrite) {
    unlink($testFile);
    echo "  Test file cleaned up\n";
}

// Test 4: Check PHP upload settings
echo "\n4. CHECKING PHP UPLOAD SETTINGS:\n";
$uploadSettings = [
    'file_uploads' => ini_get('file_uploads'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'memory_limit' => ini_get('memory_limit')
];

foreach ($uploadSettings as $setting => $value) {
    echo "  $setting: $value\n";
}

// Test 5: Simulate file upload
echo "\n5. SIMULATING FILE UPLOAD:\n";

// Copy a test file to simulate upload
$sourceFiles = glob(storage_path('app/public/announcement-images/*'));
if (!empty($sourceFiles)) {
    $sourceFile = $sourceFiles[0];
    $testUploadDir = storage_path('app/public/news-images');
    
    if (!is_dir($testUploadDir)) {
        mkdir($testUploadDir, 0755, true);
        echo "  Created news-images directory\n";
    }
    
    $testFileName = 'upload_test_' . time() . '_' . basename($sourceFile);
    $testUploadPath = $testUploadDir . '/' . $testFileName;
    
    if (copy($sourceFile, $testUploadPath)) {
        echo "  ✅ File upload simulation successful\n";
        echo "  Test file: $testFileName\n";
        echo "  File size: " . number_format(filesize($testUploadPath) / 1024, 2) . " KB\n";
        
        // Test web access
        $webUrl = asset('storage/news-images/' . $testFileName);
        echo "  Web URL: $webUrl\n";
        
        // Clean up
        unlink($testUploadPath);
        echo "  Test file cleaned up\n";
    } else {
        echo "  ❌ File upload simulation failed\n";
    }
} else {
    echo "  ❌ No source files available for testing\n";
}

// Test 6: Check Laravel configuration
echo "\n6. CHECKING LARAVEL CONFIGURATION:\n";

try {
    $filesystemConfig = config('filesystems.disks.public');
    echo "  Public disk driver: " . $filesystemConfig['driver'] . "\n";
    echo "  Public disk root: " . $filesystemConfig['root'] . "\n";
    echo "  Public disk URL: " . $filesystemConfig['url'] . "\n";
    
    $appUrl = config('app.url');
    echo "  App URL: $appUrl\n";
} catch (Exception $e) {
    echo "  ❌ Error reading configuration: " . $e->getMessage() . "\n";
}

// Test 7: Check recent logs for upload errors
echo "\n7. CHECKING RECENT UPLOAD LOGS:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    
    // Look for upload-related errors
    $uploadErrors = array_filter($lines, function($line) {
        return str_contains($line, 'upload') || 
               str_contains($line, 'file') || 
               str_contains($line, 'storage') ||
               str_contains($line, 'News creation');
    });
    
    if (!empty($uploadErrors)) {
        echo "  Recent upload-related log entries:\n";
        foreach (array_slice($uploadErrors, -5) as $log) {
            echo "    " . trim($log) . "\n";
        }
    } else {
        echo "  No recent upload-related log entries found\n";
    }
} else {
    echo "  Laravel log file not found\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
echo "\nRECOMMENDATIONS:\n";
echo "1. Try creating a news article through the web interface\n";
echo "2. Check browser console for JavaScript errors\n";
echo "3. Monitor Laravel logs during upload attempts\n";
echo "4. Verify files are actually selected before form submission\n";
