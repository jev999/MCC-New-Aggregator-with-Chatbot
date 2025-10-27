<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogService;
use Symfony\Component\HttpFoundation\Response;

class MonitoringMiddleware
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log request start
        $this->logRequest($request);

        // Check for suspicious patterns
        $this->detectSuspiciousActivity($request);

        $response = $next($request);

        // Log response
        $this->logResponse($request, $response);

        return $response;
    }

    /**
     * Log incoming request
     */
    private function logRequest(Request $request): void
    {
        // Skip logging for static assets
        if ($this->isStaticAsset($request)) {
            return;
        }

        Log::channel('monitoring')->info('Incoming request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id() ?? 'guest',
        ]);
    }

    /**
     * Log response
     */
    private function logResponse(Request $request, Response $response): void
    {
        // Skip logging for static assets
        if ($this->isStaticAsset($request)) {
            return;
        }

        $statusCode = $response->getStatusCode();

        // Log errors
        if ($statusCode >= 400) {
            Log::channel('monitoring')->warning('Error response', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status' => $statusCode,
                'ip' => $request->ip(),
                'user_id' => auth()->id() ?? 'guest',
            ]);
        }

        // Alert on critical errors
        if ($statusCode >= 500) {
            Log::channel('security')->critical('Critical error detected', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status' => $statusCode,
                'ip' => $request->ip(),
            ]);
        }
    }

    /**
     * Detect suspicious activity patterns
     */
    private function detectSuspiciousActivity(Request $request): void
    {
        try {
            $ip = $request->ip();
            $content = $request->getContent() ?? '';
            $url = $request->fullUrl() ?? '';

            // Check for SQL injection attempts
            $sqlInjectionPatterns = [
                '/union\s+select/i',
                '/select.*from/i',
                '/insert\s+into/i',
                '/delete\s+from/i',
                '/drop\s+table/i',
                '/--\s*$/',
                '/\/\*/',
            ];

            foreach ($sqlInjectionPatterns as $pattern) {
                if (preg_match($pattern, $content) || preg_match($pattern, $url)) {
                    $this->logSuspiciousActivitySafe(
                        'SQL injection attempt detected',
                        'high',
                        ['pattern' => $pattern, 'ip' => $ip]
                    );
                    break;
                }
            }

            // Check for XSS attempts
            $xssPatterns = [
                '/<script/i',
                '/javascript:/i',
                '/onerror/i',
                '/onload/i',
                '/<iframe/i',
            ];

            foreach ($xssPatterns as $pattern) {
                if (preg_match($pattern, $content) || preg_match($pattern, $url)) {
                    $this->logSuspiciousActivitySafe(
                        'XSS attempt detected',
                        'high',
                        ['pattern' => $pattern, 'ip' => $ip]
                    );
                    break;
                }
            }

            // Check for path traversal attempts
            if (preg_match('/\.\./', $url) || preg_match('/\.\./', $content)) {
                $this->logSuspiciousActivitySafe(
                    'Path traversal attempt detected',
                    'high',
                    ['ip' => $ip]
                );
            }

            // Check for unusually large requests
            $contentLength = strlen($content);
            $headerLength = (int)$request->header('Content-Length', 0);
            $finalLength = $contentLength ?: $headerLength;
            
            if ($finalLength > 10485760) { // 10MB
                $this->logSuspiciousActivitySafe(
                    'Unusually large request detected',
                    'medium',
                    ['size' => $finalLength, 'ip' => $ip]
                );
            }

            // Check for rapid requests from same IP
            $this->checkRateLimit($request);
        } catch (\Exception $e) {
            // Prevent monitoring errors from breaking the application
            Log::channel('monitoring')->error('Monitoring middleware error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Safely log suspicious activity
     */
    private function logSuspiciousActivitySafe(string $description, string $severity, array $context): void
    {
        try {
            if ($this->activityLogService) {
                $this->activityLogService->logSuspiciousActivity($description, $severity, $context);
            }
        } catch (\Exception $e) {
            Log::channel('security')->warning($description, $context);
        }
    }

    /**
     * Check rate limiting for suspicious activity
     */
    private function checkRateLimit(Request $request): void
    {
        $ip = $request->ip();
        $cacheKey = "monitoring:ip:{$ip}";
        
        $requestCount = cache()->get($cacheKey, 0);
        $requestCount++;

        // Store count for 1 minute
        cache()->put($cacheKey, $requestCount, 60);

        // Alert if more than 100 requests per minute
        if ($requestCount > 100) {
            $this->activityLogService->logSuspiciousActivity(
                'High request rate detected',
                'medium',
                ['request_count' => $requestCount, 'ip' => $ip]
            );
        }
    }

    /**
     * Check if request is for static asset
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        
        $staticExtensions = [
            'css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg',
            'ico', 'woff', 'woff2', 'ttf', 'eot'
        ];

        foreach ($staticExtensions as $extension) {
            if (str_ends_with($path, ".{$extension}")) {
                return true;
            }
        }

        return false;
    }
}

