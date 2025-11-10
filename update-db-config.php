<?php
/**
 * Database Configuration Update Helper
 * This script helps you update your database connection settings
 */

echo "=== Database Configuration Update Helper ===\n\n";

// Read current .env file
$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "Error: .env file not found!\n";
    exit(1);
}

$envContent = file_get_contents($envPath);

// Display current configuration
echo "Current Database Configuration:\n";
preg_match('/DB_HOST=(.*)/', $envContent, $hostMatch);
preg_match('/DB_PORT=(.*)/', $envContent, $portMatch);
preg_match('/DB_DATABASE=(.*)/', $envContent, $dbMatch);
preg_match('/DB_USERNAME=(.*)/', $envContent, $userMatch);

echo "- Host: " . ($hostMatch[1] ?? 'Not set') . "\n";
echo "- Port: " . ($portMatch[1] ?? 'Not set') . "\n";
echo "- Database: " . ($dbMatch[1] ?? 'Not set') . "\n";
echo "- Username: " . ($userMatch[1] ?? 'Not set') . "\n\n";

// Check if it's using localhost
if (isset($hostMatch[1]) && (trim($hostMatch[1]) === '127.0.0.1' || trim($hostMatch[1]) === 'localhost')) {
    echo "⚠️  WARNING: You are using localhost (127.0.0.1) for a remote database!\n\n";
    echo "This is likely causing your backup issues.\n\n";
    
    echo "To fix this, you need to:\n";
    echo "1. Find your actual remote database hostname from your hosting provider\n";
    echo "2. Update DB_HOST in your .env file to use that hostname\n\n";
    
    echo "Common remote database hostnames:\n";
    echo "- mysql.yourdomain.com\n";
    echo "- db.yourdomain.com\n";
    echo "- An IP address (e.g., 123.45.67.89)\n\n";
    
    echo "Would you like to update the DB_HOST now? (You'll need the hostname ready)\n";
    echo "Press Enter to continue or Ctrl+C to cancel...\n";
    
    if (PHP_OS_FAMILY !== 'Windows') {
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
    }
    
    echo "\nEnter your remote database hostname (or press Enter to skip): ";
    $newHost = trim(fgets(STDIN));
    
    if (!empty($newHost) && $newHost !== '127.0.0.1' && $newHost !== 'localhost') {
        // Backup current .env
        $backupPath = $envPath . '.backup.' . date('Y-m-d_H-i-s');
        copy($envPath, $backupPath);
        echo "✓ Created backup at: $backupPath\n";
        
        // Update DB_HOST
        $newEnvContent = preg_replace(
            '/DB_HOST=.*/',
            'DB_HOST=' . $newHost,
            $envContent
        );
        
        file_put_contents($envPath, $newEnvContent);
        echo "✓ Updated DB_HOST to: $newHost\n";
        echo "\nPlease test your application to ensure it still works.\n";
        echo "If there are issues, you can restore from: $backupPath\n";
    } else {
        echo "Skipped. Please manually update DB_HOST in your .env file.\n";
    }
} else {
    echo "✓ Database host appears to be properly configured for remote access.\n";
}

echo "\n=== Configuration Check Complete ===\n";
