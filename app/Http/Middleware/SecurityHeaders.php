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

        // Security headers have been removed

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

