<?php
/**
 * Session Configuration Fix for Production Deployment
 * This script modifies session configuration to be more production-friendly
 */

echo "=== Session Configuration Fix ===\n\n";

$sessionConfigPath = __DIR__ . '/config/session.php';

if (!file_exists($sessionConfigPath)) {
    echo "❌ Session configuration file not found!\n";
    exit(1);
}

echo "📋 Current session configuration:\n";

$currentConfig = file_get_contents($sessionConfigPath);

// Check current settings
$checks = [
    'SESSION_SECURE_COOKIE' => 'Secure cookies in production',
    'SESSION_SAME_SITE' => 'SameSite policy',
    'SESSION_DOMAIN' => 'Session domain',
    'SESSION_DRIVER' => 'Session driver'
];

foreach ($checks as $key => $description) {
    if (preg_match("/'{$key}' => env\('{$key}', ([^)]+)\)/", $currentConfig, $matches)) {
        $defaultValue = $matches[1];
        echo "   {$description}: {$defaultValue}\n";
    }
}

echo "\n🔧 Recommended .env settings for production:\n";
echo "   SESSION_DRIVER=database\n";
echo "   SESSION_LIFETIME=120\n";
echo "   SESSION_EXPIRE_ON_CLOSE=false\n";
echo "   SESSION_ENCRYPT=true\n";
echo "   SESSION_SECURE_COOKIE=true\n";
echo "   SESSION_HTTP_ONLY=true\n";
echo "   SESSION_SAME_SITE=lax\n";
echo "   SESSION_DOMAIN=.mcc-nac.com\n\n";

echo "⚠️  Important Notes:\n";
echo "   1. SESSION_SAME_SITE=lax (instead of strict) prevents login issues\n";
echo "   2. SESSION_DOMAIN=.mcc-nac.com allows cookies on subdomains\n";
echo "   3. SESSION_SECURE_COOKIE=true requires HTTPS\n";
echo "   4. SESSION_DRIVER=database requires sessions table\n\n";

echo "🚀 Next Steps:\n";
echo "   1. Update your .env file with the recommended settings\n";
echo "   2. Run: php artisan session:table\n";
echo "   3. Run: php artisan migrate\n";
echo "   4. Run: php artisan config:clear\n";
echo "   5. Test login functionality\n\n";

echo "✅ Session configuration analysis complete!\n";
