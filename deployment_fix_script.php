<?php
/**
 * MCC-NAC Deployment Fix Script
 * This script addresses common deployment issues that cause errors on domain vs localhost
 */

echo "=== MCC-NAC Deployment Fix Script ===\n\n";

// 1. Check and fix session configuration
echo "1. Checking session configuration...\n";

$sessionConfigPath = __DIR__ . '/config/session.php';
if (file_exists($sessionConfigPath)) {
    $sessionConfig = file_get_contents($sessionConfigPath);
    
    // Check for problematic session settings
    if (strpos($sessionConfig, "'same_site' => env('SESSION_SAME_SITE', 'strict')") !== false) {
        echo "   ⚠️  Found strict SameSite policy - this can cause issues in production\n";
        echo "   🔧 Recommendation: Set SESSION_SAME_SITE=lax in .env\n";
    }
    
    if (strpos($sessionConfig, "env('APP_ENV') === 'production'") !== false) {
        echo "   ⚠️  Found production-specific secure cookie setting\n";
        echo "   🔧 Recommendation: Ensure HTTPS is properly configured\n";
    }
    
    echo "   ✅ Session configuration file found\n";
} else {
    echo "   ❌ Session configuration file not found\n";
}

// 2. Check for .env file
echo "\n2. Checking environment configuration...\n";

$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "   ✅ .env file found\n";
    
    $envContent = file_get_contents($envPath);
    
    // Check critical settings
    $checks = [
        'APP_URL' => 'Application URL',
        'APP_ENV' => 'Application Environment',
        'DB_CONNECTION' => 'Database Connection',
        'SESSION_DRIVER' => 'Session Driver',
        'SESSION_SECURE_COOKIE' => 'Session Secure Cookie',
        'SESSION_SAME_SITE' => 'Session SameSite Policy'
    ];
    
    foreach ($checks as $key => $description) {
        if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
            $value = trim($matches[1]);
            echo "   📋 {$description}: {$value}\n";
            
            // Specific checks
            if ($key === 'APP_URL' && !str_starts_with($value, 'https://')) {
                echo "      ⚠️  Warning: APP_URL should use HTTPS in production\n";
            }
            
            if ($key === 'SESSION_SAME_SITE' && $value === 'strict') {
                echo "      ⚠️  Warning: 'strict' SameSite can cause login issues\n";
            }
        } else {
            echo "   ❌ {$description}: NOT SET\n";
        }
    }
} else {
    echo "   ❌ .env file not found - this is likely the main issue!\n";
    echo "   🔧 Create .env file using the production_env_template.txt\n";
}

// 3. Check database configuration
echo "\n3. Checking database configuration...\n";

if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    if (preg_match('/DB_CONNECTION=(.*)/', $envContent, $matches)) {
        $dbConnection = trim($matches[1]);
        echo "   📋 Database Connection: {$dbConnection}\n";
        
        if ($dbConnection === 'sqlite') {
            echo "   ⚠️  Warning: SQLite may not work well in production\n";
            echo "   🔧 Consider switching to MySQL for production\n";
        }
    }
}

// 4. Check storage configuration
echo "\n4. Checking storage configuration...\n";

$storageLink = __DIR__ . '/public/storage';
if (is_link($storageLink)) {
    echo "   ✅ Storage symbolic link exists\n";
} else {
    echo "   ❌ Storage symbolic link missing\n";
    echo "   🔧 Run: php artisan storage:link\n";
}

// 5. Check for common deployment issues
echo "\n5. Checking for common deployment issues...\n";

$issues = [];
$solutions = [];

// Check if APP_KEY is set
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (!preg_match('/APP_KEY=base64:/', $envContent)) {
        $issues[] = "APP_KEY not properly set";
        $solutions[] = "Run: php artisan key:generate";
    }
}

// Check if sessions table exists (if using database sessions)
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (strpos($envContent, 'SESSION_DRIVER=database') !== false) {
        $issues[] = "Database sessions enabled but sessions table may not exist";
        $solutions[] = "Run: php artisan session:table && php artisan migrate";
    }
}

if (empty($issues)) {
    echo "   ✅ No obvious deployment issues detected\n";
} else {
    echo "   ⚠️  Potential issues found:\n";
    foreach ($issues as $i => $issue) {
        echo "      - {$issue}\n";
        echo "        Solution: {$solutions[$i]}\n";
    }
}

// 6. Generate deployment checklist
echo "\n=== DEPLOYMENT CHECKLIST ===\n";
echo "Run these commands on your production server:\n\n";

echo "1. Create .env file:\n";
echo "   cp production_env_template.txt .env\n";
echo "   # Edit .env with your actual database credentials\n\n";

echo "2. Generate application key:\n";
echo "   php artisan key:generate\n\n";

echo "3. Create storage link:\n";
echo "   php artisan storage:link\n\n";

echo "4. Run database migrations:\n";
echo "   php artisan migrate\n\n";

echo "5. Create sessions table (if using database sessions):\n";
echo "   php artisan session:table\n";
echo "   php artisan migrate\n\n";

echo "6. Clear all caches:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan route:clear\n";
echo "   php artisan view:clear\n\n";

echo "7. Set proper permissions:\n";
echo "   chmod -R 755 storage/\n";
echo "   chmod -R 755 bootstrap/cache/\n";
echo "   chown -R www-data:www-data storage/ (if using Apache)\n\n";

echo "8. Test the application:\n";
echo "   Visit: https://mcc-nac.com/login\n";
echo "   Check browser console for errors\n";
echo "   Test login functionality\n\n";

echo "=== COMMON DEPLOYMENT ERRORS & SOLUTIONS ===\n\n";

echo "❌ Error: 'Session store not set on request'\n";
echo "   Solution: Ensure SESSION_DRIVER is set in .env\n\n";

echo "❌ Error: 'No application encryption key has been specified'\n";
echo "   Solution: Run 'php artisan key:generate'\n\n";

echo "❌ Error: 'Connection refused' or database errors\n";
echo "   Solution: Check DB_* settings in .env file\n\n";

echo "❌ Error: 'Storage link not found' or media not loading\n";
echo "   Solution: Run 'php artisan storage:link'\n\n";

echo "❌ Error: 'CSRF token mismatch' or login issues\n";
echo "   Solution: Set SESSION_SAME_SITE=lax in .env\n\n";

echo "❌ Error: 'Secure cookie' errors\n";
echo "   Solution: Ensure HTTPS is properly configured or set SESSION_SECURE_COOKIE=false\n\n";

echo "✅ Deployment fix analysis complete!\n";
echo "📋 Review the checklist above and apply the necessary fixes.\n";
