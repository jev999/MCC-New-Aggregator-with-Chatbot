<?php
/**
 * Database Connection Test Tool
 * 
 * SECURITY WARNING: Delete this file immediately after use!
 * 
 * Upload to: public/test-db-connection.php
 * Access: https://mcc-nac.com/test-db-connection.php?password=test123
 */

// Password protection
define('ACCESS_PASSWORD', 'test123');

if (!isset($_GET['password']) || $_GET['password'] !== ACCESS_PASSWORD) {
    die('<h1>Access Denied</h1><p>Add ?password=test123 to the URL</p>');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>MCC-NAC Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .btn { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 20px; }
        .btn:hover { background: #c82333; }
    </style>
</head>
<body>
    <h1>🔍 MCC-NAC Database Connection Test</h1>
    
    <?php
    // Load Laravel
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo '<h2>1. Environment Configuration</h2>';
    echo '<pre>';
    echo 'APP_ENV: ' . env('APP_ENV', 'Not Set') . "\n";
    echo 'APP_URL: ' . env('APP_URL', 'Not Set') . "\n";
    echo 'APP_DEBUG: ' . (env('APP_DEBUG') ? 'true' : 'false') . "\n";
    echo '</pre>';
    
    echo '<h2>2. Database Configuration</h2>';
    echo '<pre>';
    echo 'DB_CONNECTION: ' . config('database.default') . "\n";
    echo 'DB_HOST: ' . config('database.connections.mysql.host') . "\n";
    echo 'DB_PORT: ' . config('database.connections.mysql.port') . "\n";
    echo 'DB_DATABASE: ' . config('database.connections.mysql.database') . "\n";
    echo 'DB_USERNAME: ' . config('database.connections.mysql.username') . "\n";
    echo 'DB_PASSWORD: ' . (config('database.connections.mysql.password') ? '********' : 'Not Set') . "\n";
    echo '</pre>';
    
    echo '<h2>3. Connection Test</h2>';
    
    try {
        $pdo = DB::connection()->getPdo();
        
        echo '<div class="success">';
        echo '✓ <strong>Database Connected Successfully!</strong><br>';
        echo 'Driver: ' . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . '<br>';
        echo 'Server Version: ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . '<br>';
        echo '</div>';
        
        // Test query
        echo '<h2>4. Database Test Query</h2>';
        try {
            $result = DB::select('SELECT DATABASE() as db, USER() as user, VERSION() as version');
            echo '<div class="success">';
            echo '<pre>';
            echo 'Current Database: ' . $result[0]->db . "\n";
            echo 'Current User: ' . $result[0]->user . "\n";
            echo 'MySQL Version: ' . $result[0]->version . "\n";
            echo '</pre>';
            echo '</div>';
            
            // Check tables
            echo '<h2>5. Database Tables</h2>';
            $tables = DB::select('SHOW TABLES');
            echo '<div class="info">';
            echo '<strong>Total Tables: ' . count($tables) . '</strong>';
            echo '<pre>';
            
            $tableList = [];
            foreach ($tables as $table) {
                $tableArray = (array)$table;
                $tableName = reset($tableArray);
                $tableList[] = $tableName;
            }
            
            // Check important tables
            $importantTables = ['users', 'admins', 'announcements', 'news', 'events', 'migrations'];
            echo "\nImportant Tables Check:\n";
            echo str_repeat('-', 50) . "\n";
            foreach ($importantTables as $table) {
                $exists = in_array($table, $tableList);
                echo $table . ': ' . ($exists ? '✓ Exists' : '✗ Missing') . "\n";
            }
            
            echo "\nAll Tables:\n";
            echo str_repeat('-', 50) . "\n";
            foreach ($tableList as $table) {
                echo $table . "\n";
            }
            echo '</pre>';
            echo '</div>';
            
            // Check content counts
            echo '<h2>6. Content Statistics</h2>';
            try {
                $userCount = DB::table('users')->count();
                $adminCount = DB::table('admins')->count();
                $announcementCount = DB::table('announcements')->count();
                $newsCount = DB::table('news')->count();
                $eventCount = DB::table('events')->count();
                
                echo '<div class="info">';
                echo '<pre>';
                echo 'Users: ' . $userCount . "\n";
                echo 'Admins: ' . $adminCount . "\n";
                echo 'Announcements: ' . $announcementCount . "\n";
                echo 'News: ' . $newsCount . "\n";
                echo 'Events: ' . $eventCount . "\n";
                echo '</pre>';
                echo '</div>';
                
                // Check for content with media
                echo '<h2>7. Media Files Check</h2>';
                $announcementsWithMedia = DB::table('announcements')
                    ->where(function($q) {
                        $q->whereNotNull('image_path')
                          ->orWhereNotNull('video_path')
                          ->orWhereNotNull('image_paths')
                          ->orWhereNotNull('video_paths');
                    })
                    ->count();
                
                echo '<div class="info">';
                echo '<pre>';
                echo 'Announcements with Media: ' . $announcementsWithMedia . "\n";
                echo '</pre>';
                echo '</div>';
                
                if ($announcementsWithMedia > 0) {
                    echo '<div class="warning">';
                    echo '<strong>⚠ Content with media found in database</strong><br>';
                    echo 'If images appear broken, check:<br>';
                    echo '1. Storage symlink exists: public/storage -> storage/app/public<br>';
                    echo '2. Files exist in: storage/app/public/<br>';
                    echo '3. File permissions are correct (775)<br>';
                    echo '</div>';
                }
                
            } catch (\Exception $e) {
                echo '<div class="error">';
                echo '✗ Error counting records: ' . $e->getMessage();
                echo '</div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">';
            echo '✗ Error running test query: ' . $e->getMessage();
            echo '</div>';
        }
        
    } catch (\Exception $e) {
        echo '<div class="error">';
        echo '<strong>✗ Database Connection Failed!</strong><br><br>';
        echo '<strong>Error Message:</strong><br>';
        echo $e->getMessage() . '<br><br>';
        
        echo '<strong>Common Solutions:</strong><br>';
        echo '1. Check DB_HOST in .env file<br>';
        echo '2. Verify DB_USERNAME and DB_PASSWORD<br>';
        echo '3. Make sure MySQL service is running<br>';
        echo '4. Check firewall settings<br>';
        echo '5. Run: php artisan config:clear && php artisan config:cache<br>';
        echo '</div>';
    }
    
    echo '<h2>8. Storage Configuration</h2>';
    echo '<pre>';
    echo 'Default Disk: ' . config('filesystems.default') . "\n";
    echo 'Public Disk Root: ' . config('filesystems.disks.public.root') . "\n";
    echo 'Public Disk URL: ' . config('filesystems.disks.public.url') . "\n";
    echo '</pre>';
    
    // Check storage symlink
    echo '<h2>9. Storage Symlink Check</h2>';
    $storageLinkPath = public_path('storage');
    $storageTargetPath = storage_path('app/public');
    
    if (file_exists($storageLinkPath)) {
        if (is_link($storageLinkPath)) {
            $linkTarget = readlink($storageLinkPath);
            echo '<div class="success">';
            echo '✓ Storage symlink exists<br>';
            echo 'Link: ' . $storageLinkPath . '<br>';
            echo 'Target: ' . $linkTarget . '<br>';
            echo '</div>';
            
            // Check if target exists
            if (!file_exists($storageTargetPath)) {
                echo '<div class="error">';
                echo '✗ Target directory does not exist: ' . $storageTargetPath;
                echo '</div>';
            }
        } else {
            echo '<div class="warning">';
            echo '⚠ File/directory exists at storage path but is not a symlink<br>';
            echo 'Path: ' . $storageLinkPath;
            echo '</div>';
        }
    } else {
        echo '<div class="error">';
        echo '✗ Storage symlink does NOT exist<br>';
        echo 'Run: php artisan storage:link';
        echo '</div>';
    }
    
    // List storage contents
    echo '<h2>10. Storage Directory Contents</h2>';
    if (file_exists($storageTargetPath)) {
        $directories = scandir($storageTargetPath);
        echo '<div class="info">';
        echo '<pre>';
        echo 'Path: ' . $storageTargetPath . "\n\n";
        echo 'Contents:\n';
        foreach ($directories as $item) {
            if ($item !== '.' && $item !== '..') {
                $fullPath = $storageTargetPath . '/' . $item;
                $type = is_dir($fullPath) ? '[DIR]' : '[FILE]';
                echo $type . ' ' . $item . "\n";
            }
        }
        if (count($directories) <= 2) {
            echo "\n(Empty - no uploads yet)\n";
        }
        echo '</pre>';
        echo '</div>';
    } else {
        echo '<div class="error">';
        echo '✗ Storage directory does not exist: ' . $storageTargetPath;
        echo '</div>';
    }
    
    ?>
    
    <h2>⚠️ SECURITY WARNING</h2>
    <div class="error">
        <strong>DELETE THIS FILE IMMEDIATELY AFTER USE!</strong><br>
        This file exposes sensitive database information and should not be left accessible on your production server.
    </div>
    
    <form method="post" onsubmit="return confirm('Are you sure you want to delete this file?');">
        <button type="submit" name="delete_self" class="btn">🗑️ Delete This File Now</button>
    </form>
    
    <?php
    // Self-delete functionality
    if (isset($_POST['delete_self'])) {
        if (unlink(__FILE__)) {
            die('<div class="success"><h2>✓ File deleted successfully!</h2></div>');
        } else {
            echo '<div class="error">✗ Failed to delete file. Please delete manually via FTP/SSH.</div>';
        }
    }
    ?>
    
    <p style="margin-top: 30px; color: #666; font-size: 12px;">
        MCC News Aggregator - Database Connection Test Tool
    </p>
</body>
</html>
