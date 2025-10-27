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
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Get security configuration
        $securityConfig = config('security.headers');

        // Content Security Policy (CSP) - Build from configuration
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

        // HTTP Strict Transport Security (HSTS)
        if (isset($securityConfig['hsts']) && $securityConfig['hsts']['enabled']) {
            $hstsMaxAge = $securityConfig['hsts']['max-age'] ?? 31536000;
            $includeSubdomains = isset($securityConfig['hsts']['include_subdomains']) && $securityConfig['hsts']['include_subdomains'] ? '; includeSubDomains' : '';
            $preload = isset($securityConfig['hsts']['preload']) && $securityConfig['hsts']['preload'] ? '; preload' : '';
            
            if (config('app.env') === 'production' && $request->secure()) {
                $response->headers->set('Strict-Transport-Security', "max-age={$hstsMaxAge}{$includeSubdomains}{$preload}");
            }
        }

        // Content Security Policy
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // X-Content-Type-Options
        if (isset($securityConfig['content_type_options'])) {
            $response->headers->set('X-Content-Type-Options', $securityConfig['content_type_options']);
        }

        // Additional security headers from configuration
        if (isset($securityConfig['frame_options'])) {
            $response->headers->set('X-Frame-Options', $securityConfig['frame_options']);
        }

        if (isset($securityConfig['xss_protection']) && $securityConfig['xss_protection']) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        if (isset($securityConfig['referrer_policy'])) {
            $response->headers->set('Referrer-Policy', $securityConfig['referrer_policy']);
        }

        if (isset($securityConfig['permissions_policy'])) {
            $permissions = [];
            foreach ($securityConfig['permissions_policy'] as $feature => $allowed) {
                $permissions[] = $feature . '=' . ($allowed ? 'self' : '()');
            }
            $response->headers->set('Permissions-Policy', implode(', ', $permissions));
        }

        // X-Permitted-Cross-Domain-Policies
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Clear-Site-Data (for logout/clear session scenarios)
        if ($request->routeIs('logout')) {
            $response->headers->set('Clear-Site-Data', '"cache", "cookies", "storage"');
        }

        // Remove server information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}

