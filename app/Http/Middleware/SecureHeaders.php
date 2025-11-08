<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Handle an incoming request and add security headers to the response.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Force HTTPS (Strict Transport Security)
        // Tells browsers to only access this site via HTTPS for 1 year
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Prevent Clickjacking - only allow same origin framing
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy - control what referrer info is sent
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (formerly Feature Policy)
        // Disable geolocation, microphone, camera for privacy
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=(), camera=(), payment=(), usb=()');

        // X-XSS-Protection for older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Security Policy (CSP) - Customized for MCC News Aggregator
        // Allows Google reCAPTCHA, maps, SweetAlert2, Font Awesome, Google Fonts, Leaflet
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://nominatim.openstreetmap.org",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: http:",
            "connect-src 'self' https://www.google.com https://nominatim.openstreetmap.org https://tile.openstreetmap.org",
            "frame-src 'self' https://www.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Remove Server signature for security
        $response->headers->remove('X-Powered-By');

        // Prevent browser caching of sensitive pages
        if ($request->is('login') || $request->is('*/login') || $request->is('admin/*') || $request->is('superadmin/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
