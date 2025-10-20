<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains comprehensive security settings for the application
    | including HTTPS enforcement, data encryption, and privacy compliance.
    |
    */

    'https_enforcement' => [
        'enabled' => env('FORCE_HTTPS', true),
        'redirect_status' => 301,
        'exclude_paths' => [
            '/health',
            '/up',
        ],
    ],

    'data_encryption' => [
        'enabled' => true,
        'algorithm' => 'AES-256-CBC',
        'encrypted_fields' => [
            'users' => ['ms365_account', 'gmail_account'],
            'admins' => ['username'],
        ],
        'key_rotation' => [
            'enabled' => false,
            'interval_days' => 90,
        ],
    ],

    'data_retention' => [
        'notifications' => [
            'retention_days' => 90,
            'purge_read_only' => true,
        ],
        'sessions' => [
            'retention_days' => 30,
        ],
        'password_reset_tokens' => [
            'retention_hours' => 24,
        ],
        'inactive_users' => [
            'retention_years' => 2,
            'require_email_verification' => true,
        ],
        'logs' => [
            'retention_days' => 365,
        ],
    ],

    'privacy_compliance' => [
        'dpa_compliance' => true,
        'data_minimization' => true,
        'consent_required' => true,
        'right_to_erasure' => true,
        'data_portability' => true,
        'privacy_by_design' => true,
    ],

    'security_headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self';",
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'X-Permitted-Cross-Domain-Policies' => 'none',
        'X-Download-Options' => 'noopen',
        'X-DNS-Prefetch-Control' => 'off',
    ],

    'session_security' => [
        'secure' => env('SESSION_SECURE', true),
        'http_only' => true,
        'same_site' => 'strict',
        'lifetime' => 120, // minutes
        'regenerate_on_login' => true,
        'regenerate_on_logout' => true,
        'encrypt' => true,
    ],

    'cookie_security' => [
        'secure' => env('COOKIE_SECURE', true),
        'http_only' => true,
        'same_site' => 'strict',
        'encrypt' => true,
    ],

    'rate_limiting' => [
        'enabled' => true,
        'login_attempts' => 5,
        'login_decay_minutes' => 1,
        'password_reset_attempts' => 3,
        'password_reset_decay_minutes' => 5,
        'registration_attempts' => 3,
        'registration_decay_minutes' => 5,
    ],

    'monitoring' => [
        'enabled' => true,
        'log_security_events' => true,
        'log_failed_attempts' => true,
        'log_suspicious_activity' => true,
        'alert_threshold' => 5,
        'alert_timeframe_minutes' => 10,
        'notify_admins' => true,
    ],

    'backup_security' => [
        'enabled' => true,
        'encrypt_backups' => true,
        'frequency' => 'daily',
        'retention_days' => 30,
        'test_restore' => true,
    ],
];