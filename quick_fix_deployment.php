<?php
/**
 * Quick Deployment Fix for MCC-NAC
 * Run this script to quickly fix common deployment issues
 */

echo "=== MCC-NAC Quick Deployment Fix ===\n\n";

// 1. Check if .env exists
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    echo "❌ .env file not found!\n";
    echo "🔧 Creating minimal .env file...\n";
    
    $envContent = file_get_contents(__DIR__ . '/env_minimal_template.txt');
    file_put_contents($envPath, $envContent);
    echo "✅ Created .env file from template\n";
    echo "⚠️  IMPORTANT: Update database credentials in .env file!\n\n";
} else {
    echo "✅ .env file exists\n\n";
}

// 2. Check APP_KEY
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (!preg_match('/APP_KEY=base64:/', $envContent)) {
        echo "❌ APP_KEY not set properly\n";
        echo "🔧 Run: php artisan key:generate\n\n";
    } else {
        echo "✅ APP_KEY is set\n\n";
    }
}

// 3. Check storage link
$storageLink = __DIR__ . '/public/storage';
if (!is_link($storageLink)) {
    echo "❌ Storage link missing\n";
    echo "🔧 Run: php artisan storage:link\n\n";
} else {
    echo "✅ Storage link exists\n\n";
}

// 4. Backup current .htaccess and use safe version
$htaccessPath = __DIR__ . '/public/.htaccess';
$htaccessBackup = __DIR__ . '/public/.htaccess.backup';
$htaccessSafe = __DIR__ . '/public/.htaccess.safe';

if (file_exists($htaccessSafe)) {
    if (file_exists($htaccessPath)) {
        copy($htaccessPath, $htaccessBackup);
        echo "✅ Backed up current .htaccess\n";
    }
    
    copy($htaccessSafe, $htaccessPath);
    echo "✅ Applied safe .htaccess configuration\n\n";
} else {
    echo "❌ Safe .htaccess file not found\n\n";
}

echo "=== DEPLOYMENT STEPS ===\n";
echo "1. Update .env with your database credentials\n";
echo "2. Run: php artisan key:generate\n";
echo "3. Run: php artisan storage:link\n";
echo "4. Run: php artisan migrate\n";
echo "5. Run: php artisan config:clear\n";
echo "6. Run: php artisan cache:clear\n";
echo "7. Test: https://mcc-nac.com/login\n\n";

echo "=== CRITICAL SETTINGS FOR .env ===\n";
echo "SESSION_DRIVER=file\n";
echo "SESSION_SECURE_COOKIE=false\n";
echo "SESSION_SAME_SITE=lax\n";
echo "SESSION_ENCRYPT=false\n";
echo "APP_DEBUG=false\n";
echo "APP_ENV=production\n\n";

echo "✅ Quick fix script completed!\n";
echo "📋 Follow the deployment steps above to complete the fix.\n";
?>
