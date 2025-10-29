<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security headers for your application. These headers help
    | protect against various attacks including XSS, clickjacking, and more.
    |
    */

    'headers' => [

        /*
        |--------------------------------------------------------------------------
        | Content Security Policy (CSP)
        |--------------------------------------------------------------------------
        |
        | Configure Content Security Policy directives to control which resources
        | can be loaded by your application.
        |
        */

        'csp' => [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https://www.google.com', 'https://www.gstatic.com', 'https://www.googleapis.com'],
            'style-src' => ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com', 'https://cdn.jsdelivr.net'],
            'img-src' => ["'self'", 'data:', 'https:', 'blob:'],
            'font-src' => ["'self'", 'data:', 'https://fonts.gstatic.com', 'https://cdn.jsdelivr.net'],
            'connect-src' => ["'self'", 'https://www.google.com', 'https://generativelanguage.googleapis.com'],
            'frame-src' => ["'self'", 'https://www.google.com'],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'self'"],
            'upgrade-insecure-requests' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | HTTP Strict Transport Security (HSTS)
        |--------------------------------------------------------------------------
        |
        | Configure HSTS to force browsers to use HTTPS connections.
        | Recommended: max-age=31536000; includeSubDomains; preload
        |
        | max-age: Time in seconds browsers should remember to only use HTTPS (1 year = 31536000)
        | include_subdomains: Apply to all subdomains
        | preload: Include in browser HSTS preload lists
        |
        */

        'hsts' => [
            'enabled' => false,
            'max-age' => 0,
            'include_subdomains' => false,
            'preload' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Additional Security Headers
        |--------------------------------------------------------------------------
        |
        | Configure other security headers like X-Frame-Options, X-Content-Type-Options, etc.
        |
        */

        /*
        | X-Frame-Options
        | Prevents clickjacking attacks by controlling if site can be framed
        | Values: DENY (no framing), SAMEORIGIN (same origin only), ALLOW-FROM uri
        | Recommended: SAMEORIGIN
        */
        'frame_options' => null,

        /*
        | X-Content-Type-Options
        | Prevents MIME-sniffing and forces declared content-type
        | Value: nosniff (only valid value)
        | Recommended: nosniff
        */
        'content_type_options' => null,

        /*
        | X-XSS-Protection
        | Legacy header for older browsers (IE, Chrome, Safari)
        | Modern browsers rely on CSP instead
        | Value: 1; mode=block
        */
        'xss_protection' => false,

        /*
        | Referrer-Policy
        | Controls how much referrer information is included with requests
        | Values:
        |   - no-referrer: Never send referrer
        |   - no-referrer-when-downgrade: Send only for same security level
        |   - origin: Send only origin
        |   - origin-when-cross-origin: Full URL for same origin, origin only for cross-origin
        |   - same-origin: Send only for same origin
        |   - strict-origin: Send origin only for same security level
        |   - strict-origin-when-cross-origin: Full URL for same origin, origin for cross-origin (same security)
        |   - unsafe-url: Always send full URL (not recommended)
        | Recommended: strict-origin-when-cross-origin or no-referrer-when-downgrade
        */
        'referrer_policy' => null,

        /*
        | Permissions-Policy (formerly Feature-Policy)
        | Controls which browser features and APIs can be used
        | Format: feature=(self origin1 origin2) or feature=() to disable
        | Common features: geolocation, microphone, camera, payment, usb, accelerometer,
        |                  gyroscope, magnetometer, fullscreen, picture-in-picture
        */
        'permissions_policy' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Content Delivery Network settings for asset delivery and DDoS protection.
    |
    */

    'cdn' => [
        'enabled' => env('CDN_ENABLED', false),
        'url' => env('CDN_URL'),
        'provider' => env('CDN_PROVIDER', 'cloudflare'), // cloudflare, aws, bunnycdn, etc.
        
        'headers' => [
            // Custom headers for CDN requests
        ],
        
        'cache_control' => [
            'max_age' => 31536000, // 1 year for static assets
            'public' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting to prevent abuse and DDoS attacks.
    |
    */

    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => env('RATE_LIMIT_ATTEMPTS', 60),
        'decay_minutes' => env('RATE_LIMIT_DECAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Disabled Features
    |--------------------------------------------------------------------------
    |
    | List of Laravel features that should be disabled for security.
    |
    */

    'disabled_features' => [
        'broadcasting' => false, // Set to true to disable broadcasting
        'queue' => false, // Set to true to disable queues
        'telescope' => false, // Set to true to disable Telescope in production
        'debugbar' => false, // Set to true to disable Debugbar in production
        'api_documentation' => env('APP_ENV') === 'production', // Disable API docs in production
    ],

];

