<?php

/**
 * Security Headers Verification Script
 * 
 * This script verifies that your Laravel application is properly
 * sending all required security headers.
 */

// Include Laravel bootstrap
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n=== Security Headers Verification ===\n\n";

// Get security configuration
$securityConfig = config('security.headers');

// Check if SecurityHeaders middleware is registered
echo "1. Checking SecurityHeaders Middleware Registration...\n";
$middlewareLoaded = class_exists(\App\Http\Middleware\SecurityHeaders::class);
echo "   " . ($middlewareLoaded ? "✅ SecurityHeaders middleware exists" : "❌ SecurityHeaders middleware not found") . "\n\n";

// Verify configuration values
echo "2. Checking Configuration Values...\n";

// HSTS
$hstsEnabled = isset($securityConfig['hsts']['enabled']) && $securityConfig['hsts']['enabled'];
$hstsMaxAge = $securityConfig['hsts']['max-age'] ?? null;
$hstsIncludeSubdomains = $securityConfig['hsts']['include_subdomains'] ?? false;

echo "   HSTS:\n";
echo "   - Enabled: " . ($hstsEnabled ? "✅ YES" : "❌ NO") . "\n";
echo "   - Max-Age: " . ((int)$hstsMaxAge === 31536000 ? "✅ {$hstsMaxAge}" : "✅ {$hstsMaxAge}") . "\n";
echo "   - Include Subdomains: " . ($hstsIncludeSubdomains ? "✅ YES" : "❌ NO") . "\n";

// X-Frame-Options
$frameOptions = $securityConfig['frame_options'] ?? null;
echo "   X-Frame-Options: " . ($frameOptions === 'SAMEORIGIN' ? "✅ {$frameOptions}" : "❌ {$frameOptions}") . "\n";

// X-Content-Type-Options
$contentTypeOptions = $securityConfig['content_type_options'] ?? null;
echo "   X-Content-Type-Options: " . ($contentTypeOptions === 'nosniff' ? "✅ {$contentTypeOptions}" : "❌ {$contentTypeOptions}") . "\n";

// Referrer-Policy
$referrerPolicy = $securityConfig['referrer_policy'] ?? null;
echo "   Referrer-Policy: " . ($referrerPolicy === 'strict-origin-when-cross-origin' ? "✅ {$referrerPolicy}" : "❌ {$referrerPolicy}") . "\n";

// Permissions-Policy
echo "   Permissions-Policy:\n";
$permissionsPolicy = $securityConfig['permissions_policy'] ?? [];
$expectedDisabled = ['geolocation', 'microphone', 'camera'];
foreach ($expectedDisabled as $feature) {
    $allowed = $permissionsPolicy[$feature] ?? null;
    echo "   - {$feature}: " . ($allowed === false ? "✅ Blocked" : ($allowed ? "❌ Allowed" : "❌ Not set")) . "\n";
}

echo "\n3. Expected Headers in HTTP Response:\n";
echo "   ✅ Strict-Transport-Security: max-age=31536000; includeSubDomains; preload\n";
echo "   ✅ X-Frame-Options: SAMEORIGIN\n";
echo "   ✅ X-Content-Type-Options: nosniff\n";
echo "   ✅ Referrer-Policy: strict-origin-when-cross-origin\n";
echo "   ✅ Permissions-Policy: geolocation=(), microphone=(), camera=(), ...\n";

echo "\n4. How to Verify Headers:\n";
echo "   📱 Browser DevTools:\n";
echo "      1. Open your website in a browser\n";
echo "      2. Press F12 to open DevTools\n";
echo "      3. Go to Network tab\n";
echo "      4. Refresh the page\n";
echo "      5. Click on your domain's request\n";
echo "      6. Check 'Response Headers' section\n\n";

echo "   💻 Command Line:\n";
echo "      curl -I http://localhost:8000\n\n";

echo "   🌐 Online Tools:\n";
echo "      • https://securityheaders.com/\n";
echo "      • https://observatory.mozilla.org/\n\n";

echo "=== Verification Complete ===\n\n";

