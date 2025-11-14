<?php

echo "Checking .env files...\n";
echo "=====================\n\n";

// Check for .env files
$files = ['.env', '.env local', '.env.local', '.env.production'];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "Found: $file\n";
        
        // Read database config
        $content = file_get_contents($file);
        
        // Extract DB settings
        preg_match('/DB_HOST=(.+)/m', $content, $host);
        preg_match('/DB_DATABASE=(.+)/m', $content, $db);
        preg_match('/DB_USERNAME=(.+)/m', $content, $user);
        
        echo "  Host: " . ($host[1] ?? 'not set') . "\n";
        echo "  Database: " . ($db[1] ?? 'not set') . "\n";
        echo "  Username: " . ($user[1] ?? 'not set') . "\n\n";
    }
}

echo "\nNote: If using remote database, DB_HOST should be the remote server hostname/IP\n";
echo "Example: DB_HOST=server123.hostinger.com\n";
