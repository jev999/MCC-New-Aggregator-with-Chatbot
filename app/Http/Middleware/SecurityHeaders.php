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

        // Only apply security headers to HTML responses
        if (!$this->shouldApplyHeaders($response)) {
            return $response;
        }

        // Apply HSTS (Strict Transport Security)
        $this->applyHSTS($response);

        // Apply X-Frame-Options
        $this->applyXFrameOptions($response);

        // Apply X-Content-Type-Options
        $this->applyXContentTypeOptions($response);

        // Apply X-XSS-Protection
        $this->applyXSSProtection($response);

        // Apply Referrer-Policy
        $this->applyReferrerPolicy($response);

        // Apply Permissions-Policy
        $this->applyPermissionsPolicy($response);

        // Apply Content Security Policy
        $this->applyCSP($response);

        return $response;
    }

    /**
     * Determine if headers should be applied to this response
     */
    protected function shouldApplyHeaders(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        
        // Apply headers to HTML responses
        return str_contains($contentType, 'text/html') || 
               empty($contentType);
    }

    /**
     * Apply HTTP Strict Transport Security header
     */
    protected function applyHSTS(Response $response): void
    {
        if (!config('security.hsts.enabled', true)) {
            return;
        }

        // Only apply HSTS in production over HTTPS
        if (app()->environment('production') && request()->secure()) {
            $maxAge = config('security.hsts.max_age', 31536000);
            $includeSubdomains = config('security.hsts.include_subdomains', true);
            $preload = config('security.hsts.preload', true);

            $header = "max-age={$maxAge}";
            if ($includeSubdomains) {
                $header .= '; includeSubDomains';
            }
            if ($preload) {
                $header .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $header);
        }
    }

    /**
     * Apply X-Frame-Options header
     */
    protected function applyXFrameOptions(Response $response): void
    {
        $option = config('security.x_frame_options', 'SAMEORIGIN');
        $response->headers->set('X-Frame-Options', $option);
    }

    /**
     * Apply X-Content-Type-Options header
     */
    protected function applyXContentTypeOptions(Response $response): void
    {
        $option = config('security.x_content_type_options', 'nosniff');
        $response->headers->set('X-Content-Type-Options', $option);
    }

    /**
     * Apply X-XSS-Protection header
     */
    protected function applyXSSProtection(Response $response): void
    {
        if (config('security.xss_protection', true)) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }
    }

    /**
     * Apply Referrer-Policy header
     */
    protected function applyReferrerPolicy(Response $response): void
    {
        $policy = config('security.referrer_policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Referrer-Policy', $policy);
    }

    /**
     * Apply Permissions-Policy header
     */
    protected function applyPermissionsPolicy(Response $response): void
    {
        $permissions = config('security.permissions_policy', []);
        $policies = [];

        foreach ($permissions as $feature => $allowed) {
            if ($allowed === true) {
                $policies[] = "{$feature}=(self)";
            } elseif ($allowed === false) {
                $policies[] = "{$feature}=()";
            }
        }

        if (!empty($policies)) {
            $response->headers->set('Permissions-Policy', implode(', ', $policies));
        }
    }

    /**
     * Apply Content Security Policy header
     */
    protected function applyCSP(Response $response): void
    {
        if (!config('security.csp.enabled', true)) {
            return;
        }

        $directives = config('security.csp.directives', []);
        $useNonce = config('security.csp.use_nonce', true);
        $reportOnly = config('security.csp.report_only', false);

        // Generate nonce for this request if enabled
        $nonce = null;
        if ($useNonce && function_exists('csp_nonce')) {
            $nonce = csp_nonce();
        }

        $cspParts = [];
        foreach ($directives as $directive => $sources) {
            if (empty($sources)) {
                continue;
            }

            $processedSources = [];
            foreach ($sources as $source) {
                // Replace 'nonce' placeholder with actual nonce
                if ($source === "'nonce'" && $nonce) {
                    $processedSources[] = "'nonce-{$nonce}'";
                } elseif ($source !== "'nonce'") {
                    $processedSources[] = $source;
                }
            }

            if (!empty($processedSources)) {
                $cspParts[] = $directive . ' ' . implode(' ', $processedSources);
            }
        }

        if (!empty($cspParts)) {
            $cspHeader = implode('; ', $cspParts);
            $headerName = $reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $response->headers->set($headerName, $cspHeader);
        }
    }
}
