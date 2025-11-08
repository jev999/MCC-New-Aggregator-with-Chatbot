<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    |
    | HSTS tells browsers to always use HTTPS when connecting to your site.
    | This prevents protocol downgrade attacks and cookie hijacking.
    |
    */

    'hsts' => [
        'enabled' => env('SECURITY_HSTS_ENABLED', true),
        'max_age' => env('SECURITY_HSTS_MAX_AGE', 31536000), // 1 year
        'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('SECURITY_HSTS_PRELOAD', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | X-Frame-Options
    |--------------------------------------------------------------------------
    |
    | Prevents your site from being embedded in iframes (clickjacking protection).
    | Options: DENY, SAMEORIGIN, ALLOW-FROM uri
    |
    */

    'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),

    /*
    |--------------------------------------------------------------------------
    | X-Content-Type-Options
    |--------------------------------------------------------------------------
    |
    | Prevents browsers from MIME-sniffing a response away from the declared
    | content-type. Should always be 'nosniff'.
    |
    */

    'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),

    /*
    |--------------------------------------------------------------------------
    | X-XSS-Protection
    |--------------------------------------------------------------------------
    |
    | Legacy header for older browsers that enables XSS filtering.
    | Modern browsers use CSP instead.
    |
    */

    'xss_protection' => env('SECURITY_XSS_PROTECTION', true),

    /*
    |--------------------------------------------------------------------------
    | Referrer-Policy
    |--------------------------------------------------------------------------
    |
    | Controls how much referrer information is sent with requests.
    |
    */

    'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),

    /*
    |--------------------------------------------------------------------------
    | Permissions-Policy (formerly Feature-Policy)
    |--------------------------------------------------------------------------
    |
    | Controls which browser features and APIs can be used in the page.
    |
    */

    'permissions_policy' => [
        'geolocation' => env('SECURITY_PERMISSIONS_GEOLOCATION', false),
        'microphone' => env('SECURITY_PERMISSIONS_MICROPHONE', false),
        'camera' => env('SECURITY_PERMISSIONS_CAMERA', false),
        'payment' => env('SECURITY_PERMISSIONS_PAYMENT', false),
        'usb' => env('SECURITY_PERMISSIONS_USB', false),
        'accelerometer' => env('SECURITY_PERMISSIONS_ACCELEROMETER', false),
        'gyroscope' => env('SECURITY_PERMISSIONS_GYROSCOPE', false),
        'magnetometer' => env('SECURITY_PERMISSIONS_MAGNETOMETER', false),
        'fullscreen' => env('SECURITY_PERMISSIONS_FULLSCREEN', true),
        'picture-in-picture' => env('SECURITY_PERMISSIONS_PICTURE_IN_PICTURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | CSP helps prevent XSS, clickjacking, and other code injection attacks.
    | 'nonce' enables automatic nonce generation for inline scripts/styles.
    |
    */

    'csp' => [
        'enabled' => env('SECURITY_CSP_ENABLED', true),
        'use_nonce' => env('SECURITY_CSP_USE_NONCE', true),
        'report_only' => env('SECURITY_CSP_REPORT_ONLY', false),
        
        'directives' => [
            // Default source for all resources
            'default-src' => ["'self'"],
            
            // Script sources - allows CDNs used in your application
            'script-src' => [
                "'self'",
                "'nonce'", // Will be replaced with actual nonce
                'https://cdn.tailwindcss.com',
                'https://cdn.jsdelivr.net',
                'https://cdnjs.cloudflare.com',
                'https://www.google.com',
                'https://www.gstatic.com',
                'https://www.googletagmanager.com',
            ],
            
            // Style sources - allows CDNs and inline styles with nonce
            'style-src' => [
                "'self'",
                "'nonce'", // Will be replaced with actual nonce
                "'unsafe-inline'", // Required for some libraries
                'https://cdn.tailwindcss.com',
                'https://cdnjs.cloudflare.com',
                'https://fonts.googleapis.com',
            ],
            
            // Image sources
            'img-src' => [
                "'self'",
                'data:',
                'https:',
                'blob:',
            ],
            
            // Font sources
            'font-src' => [
                "'self'",
                'data:',
                'https://fonts.gstatic.com',
                'https://cdnjs.cloudflare.com',
            ],
            
            // Connect sources (AJAX, WebSocket, etc.)
            'connect-src' => [
                "'self'",
                'https://www.google.com',
                'https://www.gstatic.com',
            ],
            
            // Frame sources
            'frame-src' => [
                "'self'",
                'https://www.google.com',
            ],
            
            // Object sources (Flash, etc.)
            'object-src' => ["'none'"],
            
            // Base URI
            'base-uri' => ["'self'"],
            
            // Form action
            'form-action' => ["'self'"],
            
            // Frame ancestors
            'frame-ancestors' => ["'self'"],
        ],
    ],

];
