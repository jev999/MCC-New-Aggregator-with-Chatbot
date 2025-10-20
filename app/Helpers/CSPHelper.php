<?php

namespace App\Helpers;

class CSPHelper
{
    /**
     * Generate a cryptographically secure nonce
     */
    public static function generateNonce(): string
    {
        return base64_encode(random_bytes(16));
    }

    /**
     * Get the current nonce for the request
     */
    public static function getNonce(): string
    {
        // Check if session is available
        if (!app()->bound('session') || !session()->isStarted()) {
            return self::generateNonce();
        }
        
        if (!session()->has('csp_nonce')) {
            session(['csp_nonce' => self::generateNonce()]);
        }
        
        return session('csp_nonce');
    }

    /**
     * Generate secure CSP header
     */
    public static function generateCSP(): string
    {
        $nonce = self::getNonce();
        
        return implode('; ', [
            "default-src 'self'",
            // Allow required CDNs
            "script-src 'self' 'nonce-{$nonce}' https://www.google.com https://www.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://cdn.tailwindcss.com https://fonts.googleapis.com",
            // Allow inline styles so Tailwind CDN can inject runtime styles
            "style-src 'self' 'unsafe-inline' 'nonce-{$nonce}' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ]);
    }
}
