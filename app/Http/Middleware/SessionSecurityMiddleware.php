<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip session security for login/logout routes
        if ($this->shouldSkipSecurityCheck($request)) {
            return $next($request);
        }

        // Check if user is authenticated
        if (Auth::check()) {
            $this->performSecurityChecks($request);
        }

        return $next($request);
    }

    /**
     * Perform various security checks on the session
     */
    private function performSecurityChecks(Request $request): void
    {
        $user = Auth::user();
        $session = $request->session();

        // Check for session hijacking by IP address
        $this->checkIPConsistency($request, $session);

        // Check for session hijacking by User Agent
        $this->checkUserAgentConsistency($request, $session);

        // Update last activity timestamp
        $this->updateLastActivity($session);

        // Check for concurrent sessions (optional - can be enabled)
        // $this->checkConcurrentSessions($user, $session);

        // Regenerate session ID periodically for security
        $this->regenerateSessionPeriodically($session);
    }

    /**
     * Check if IP address is consistent with session
     */
    private function checkIPConsistency(Request $request, $session): void
    {
        $currentIP = $request->ip();
        $sessionIP = $session->get('user_ip');

        if (!$sessionIP) {
            // First time - store IP
            $session->put('user_ip', $currentIP);
        } elseif ($sessionIP !== $currentIP) {
            // IP changed - potential session hijacking
            Log::warning('Session IP mismatch detected', [
                'user_id' => Auth::id(),
                'session_ip' => $sessionIP,
                'current_ip' => $currentIP,
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            // For high security, you might want to logout the user
            // Auth::logout();
            // $session->invalidate();
            // abort(401, 'Session security violation detected');
        }
    }

    /**
     * Check if User Agent is consistent with session
     */
    private function checkUserAgentConsistency(Request $request, $session): void
    {
        $currentUA = $request->userAgent();
        $sessionUA = $session->get('user_agent');

        if (!$sessionUA) {
            // First time - store User Agent
            $session->put('user_agent', $currentUA);
        } elseif ($sessionUA !== $currentUA) {
            // User Agent changed - potential session hijacking
            Log::warning('Session User Agent mismatch detected', [
                'user_id' => Auth::id(),
                'session_ua' => $sessionUA,
                'current_ua' => $currentUA,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            // For high security, you might want to logout the user
            // Auth::logout();
            // $session->invalidate();
            // abort(401, 'Session security violation detected');
        }
    }

    /**
     * Update last activity timestamp
     */
    private function updateLastActivity($session): void
    {
        $session->put('last_activity', now()->timestamp);
    }

    /**
     * Regenerate session ID periodically for security
     */
    private function regenerateSessionPeriodically($session): void
    {
        $lastRegeneration = $session->get('last_regeneration', 0);
        $regenerationInterval = 30 * 60; // 30 minutes

        if ((now()->timestamp - $lastRegeneration) > $regenerationInterval) {
            $session->regenerate();
            $session->put('last_regeneration', now()->timestamp);
            
            Log::info('Session ID regenerated for security', [
                'user_id' => Auth::id(),
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Check for concurrent sessions (optional feature)
     */
    private function checkConcurrentSessions($user, $session): void
    {
        // This would require storing active session IDs in database
        // and checking if user has multiple active sessions
        // Implementation depends on your security requirements
    }

    /**
     * Determine if security checks should be skipped for this request
     */
    private function shouldSkipSecurityCheck(Request $request): bool
    {
        $skipRoutes = [
            'login',
            'logout',
            'register',
            'password.request',
            'password.reset',
            'verification.notice',
            'verification.verify',
            'verification.send'
        ];

        return in_array($request->route()->getName(), $skipRoutes) ||
               $request->is('api/*') ||
               $request->is('_debugbar/*');
    }
}
