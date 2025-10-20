<?php
/**
 * Production URL Fix Script for mcc-nac.com
 * This script helps configure your Laravel app for the live domain
 */

echo "=== MCC-NAC Production URL Configuration ===\n\n";

// Check current .env file
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "✅ .env file found\n";
    
    $envContent = file_get_contents($envPath);
    
    // Check current APP_URL
    if (preg_match('/APP_URL=(.*)/', $envContent, $matches)) {
        $currentUrl = trim($matches[1]);
        echo "Current APP_URL: $currentUrl\n";
        
        if ($currentUrl !== 'https://mcc-nac.com') {
            echo "⚠️  APP_URL needs to be updated to: https://mcc-nac.com\n";
            
            // Update APP_URL
            $newEnvContent = preg_replace('/APP_URL=.*/', 'APP_URL=https://mcc-nac.com', $envContent);
            
            // Backup original .env
            copy($envPath, $envPath . '.backup.' . date('Y-m-d-H-i-s'));
            echo "📁 Created backup: .env.backup." . date('Y-m-d-H-i-s') . "\n";
            
            // Write updated .env
            file_put_contents($envPath, $newEnvContent);
            echo "✅ Updated APP_URL to: https://mcc-nac.com\n";
        } else {
            echo "✅ APP_URL is correctly set\n";
        }
    } else {
        echo "❌ APP_URL not found in .env file\n";
        echo "Please add: APP_URL=https://mcc-nac.com\n";
    }
} else {
    echo "❌ .env file not found\n";
    echo "Please create .env file with: APP_URL=https://mcc-nac.com\n";
}

echo "\n=== Required Commands for Your Live Server ===\n";
echo "Run these commands on your mcc-nac.com server:\n\n";

echo "1. Create storage symbolic link:\n";
echo "   php artisan storage:link\n\n";

echo "2. Clear application cache:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan route:clear\n";
echo "   php artisan view:clear\n\n";

echo "3. Set proper permissions:\n";
echo "   chmod -R 755 storage/\n";
echo "   chmod -R 755 public/storage/\n";
echo "   chown -R www-data:www-data storage/ (if using Apache/Nginx)\n\n";

echo "4. Check if storage directory exists:\n";
echo "   ls -la public/storage\n\n";

echo "=== Test URLs ===\n";
echo "After running the commands, your media URLs should look like:\n";
echo "https://mcc-nac.com/storage/announcement-images/filename.jpg\n";
echo "https://mcc-nac.com/storage/announcement-videos/filename.mp4\n\n";

echo "=== Verification ===\n";
echo "1. Upload a test announcement with image/video\n";
echo "2. Check user dashboard at: https://mcc-nac.com/dashboard\n";
echo "3. Verify media displays correctly\n\n";

echo "✅ Configuration complete for mcc-nac.com!\n";
