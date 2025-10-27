<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Handle an incoming request and apply security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Get security configuration
        $securityConfig = config('security.headers');

        // ============================================
        // 1. HTTP Strict Transport Security (HSTS)
        // ============================================
        // Recommended: max-age=31536000; includeSubDomains
        // Forces browsers to use HTTPS connections only
        if (isset($securityConfig['hsts']) && $securityConfig['hsts']['enabled']) {
            $hstsMaxAge = $securityConfig['hsts']['max-age'] ?? 31536000;
            $includeSubdomains = isset($securityConfig['hsts']['include_subdomains']) && $securityConfig['hsts']['include_subdomains'] ? '; includeSubDomains' : '';
            $preload = isset($securityConfig['hsts']['preload']) && $securityConfig['hsts']['preload'] ? '; preload' : '';
            
            // Only send HSTS over HTTPS (required by specification)
            // HSTS must never be sent over HTTP as it can cause lockout issues
            if ($request->secure()) {
                $hstsValue = "max-age={$hstsMaxAge}{$includeSubdomains}{$preload}";
                $response->headers->set('Strict-Transport-Security', $hstsValue);
            }
        }

        // ============================================
        // 2. X-Frame-Options
        // ============================================
        // Recommended: SAMEORIGIN
        // Prevents clickjacking by controlling if site can be framed
        if (isset($securityConfig['frame_options'])) {
            $response->headers->set('X-Frame-Options', $securityConfig['frame_options']);
        }

        // ============================================
        // 3. X-Content-Type-Options
        // ============================================
        // Recommended: nosniff
        // Prevents MIME-sniffing and forces declared content-type
        if (isset($securityConfig['content_type_options'])) {
            $response->headers->set('X-Content-Type-Options', $securityConfig['content_type_options']);
        }

        // ============================================
        // 4. Referrer-Policy
        // ============================================
        // Recommended: strict-origin-when-cross-origin
        // Controls how much referrer information is sent with requests
        if (isset($securityConfig['referrer_policy'])) {
            $response->headers->set('Referrer-Policy', $securityConfig['referrer_policy']);
        }

        // ============================================
        // 5. Permissions-Policy
        // ============================================
        // Controls which browser features and APIs can be used
        if (isset($securityConfig['permissions_policy'])) {
            $permissions = [];
            foreach ($securityConfig['permissions_policy'] as $feature => $allowed) {
                // Format: feature=(self) for allowed, feature=() for blocked
                $permissions[] = $feature . '=' . ($allowed ? '(self)' : '()');
            }
            if (!empty($permissions)) {
                $response->headers->set('Permissions-Policy', implode(', ', $permissions));
            }
        }

        // ============================================
        // 6. Content Security Policy (CSP)
        // ============================================
        // Controls which resources can be loaded by your application
        $csp = [];
        if (isset($securityConfig['csp'])) {
            foreach ($securityConfig['csp'] as $directive => $values) {
                if (is_bool($values) && $values) {
                    $csp[] = $directive;
                } elseif (is_array($values)) {
                    $csp[] = "$directive " . implode(' ', $values);
                }
            }
        }
        if (!empty($csp)) {
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        // ============================================
        // 7. X-XSS-Protection (Legacy)
        // ============================================
        // Legacy header for older browsers (IE, Chrome, Safari)
        // Modern browsers rely on CSP instead
        if (isset($securityConfig['xss_protection']) && $securityConfig['xss_protection']) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        // ============================================
        // Additional Security Headers
        // ============================================

        // X-Permitted-Cross-Domain-Policies
        // Restricts Adobe Flash and PDF cross-domain policies
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // X-Download-Options
        // Prevents IE from executing downloads in site's context
        $response->headers->set('X-Download-Options', 'noopen');

        // Clear-Site-Data (for logout/clear session scenarios)
        // Clears browser data when logging out
        if ($request->routeIs('logout') || 
            $request->routeIs('*.logout') || 
            str_contains($request->path(), 'logout')) {
            $response->headers->set('Clear-Site-Data', '"cache", "cookies", "storage"');
        }

        // ============================================
        // Remove Information Disclosure Headers
        // ============================================
        // Remove headers that reveal server information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}

