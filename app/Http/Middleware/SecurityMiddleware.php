<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Rate limiting for login attempts
        $this->applyRateLimit($request);
        
        // 2. Check for suspicious request patterns
        $this->checkSuspiciousPatterns($request);
        
        // 3. Validate request size
        $this->validateRequestSize($request);
        
        // 4. Check for malicious headers
        $this->checkMaliciousHeaders($request);
        
        // 5. Log security events
        $this->logSecurityEvent($request);
        
        return $next($request);
    }
    
    /**
     * Apply rate limiting based on IP and endpoint
     */
    private function applyRateLimit(Request $request)
    {
        $key = 'security_rate_limit:' . $request->ip() . ':' . $request->path();
        
        // Different limits for different endpoints
        $limits = [
            '/login' => ['max_attempts' => 5, 'decay_minutes' => 1],
            '/password/email' => ['max_attempts' => 3, 'decay_minutes' => 5],
            '/password/reset' => ['max_attempts' => 3, 'decay_minutes' => 5],
        ];
        
        $path = $request->path();
        $limit = $limits[$path] ?? ['max_attempts' => 10, 'decay_minutes' => 1];
        
        if (RateLimiter::tooManyAttempts($key, $limit['max_attempts'])) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'path' => $path,
                'user_agent' => $request->userAgent(),
                'seconds_remaining' => $seconds
            ]);
            
            abort(429, 'Too many requests. Please try again in ' . ceil($seconds / 60) . ' minutes.');
        }
        
        RateLimiter::hit($key, $limit['decay_minutes'] * 60);
    }
    
    /**
     * Check for suspicious request patterns
     */
    private function checkSuspiciousPatterns(Request $request)
    {
        $suspiciousPatterns = [
            // SQL injection patterns
            '/(\bunion\s+select|\bselect\s+.*\bfrom\s+|\binsert\s+into|\bupdate\s+.*\bset\s+|\bdelete\s+from|\bdrop\s+table|\balter\s+table)/i',
            // Script injection patterns
            '/(<script[^>]*>|<\/script>|<iframe[^>]*>|<object[^>]*>|<embed[^>]*>)/i',
            // Command injection patterns
            '/(\bsystem\s*\(|\bexec\s*\(|\bshell_exec\s*\(|\bpassthru\s*\()/i',
            // PHP code injection
            '/(<\?php|<\?=|\bphp:)/i',
            // JavaScript injection
            '/(javascript:|vbscript:|data:text\/html)/i',
        ];
        
        $allInput = array_merge(
            $request->all(),
            $request->headers->all(),
            ['url' => $request->fullUrl()]
        );
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($suspiciousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        Log::critical('Suspicious pattern detected', [
                            'ip' => $request->ip(),
                            'pattern' => $pattern,
                            'field' => $key,
                            'value' => substr($value, 0, 100), // Log first 100 chars only
                            'user_agent' => $request->userAgent(),
                            'url' => $request->fullUrl()
                        ]);
                        
                        abort(422, 'Invalid input detected.');
                    }
                }
            }
        }
    }
    
    /**
     * Validate request size to prevent DoS
     */
    private function validateRequestSize(Request $request)
    {
        $maxSize = 1024 * 1024; // 1MB max request size
        
        if ($request->header('Content-Length') > $maxSize) {
            Log::warning('Request size exceeded', [
                'ip' => $request->ip(),
                'size' => $request->header('Content-Length'),
                'max_size' => $maxSize
            ]);
            
            abort(413, 'Request entity too large.');
        }
    }
    
    /**
     * Check for malicious headers
     */
    private function checkMaliciousHeaders(Request $request)
    {
        $maliciousHeaders = [
            'X-Forwarded-Host',
            'X-Original-URL',
            'X-Rewrite-URL',
            'X-Forwarded-Server',
        ];
        
        foreach ($maliciousHeaders as $header) {
            if ($request->hasHeader($header)) {
                Log::warning('Malicious header detected', [
                    'ip' => $request->ip(),
                    'header' => $header,
                    'value' => $request->header($header)
                ]);
                
                abort(400, 'Invalid request headers.');
            }
        }
    }
    
    /**
     * Log security events
     */
    private function logSecurityEvent(Request $request)
    {
        // Log all authentication attempts
        if (in_array($request->path(), ['/login', '/password/email', '/password/reset'])) {
            Log::info('Authentication attempt', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);
        }
    }
}
