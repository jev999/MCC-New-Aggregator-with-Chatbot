<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SessionSecurityMiddleware
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
        // Skip session security checks for certain routes
        if ($this->shouldSkipSecurityCheck($request)) {
            return $next($request);
        }

        // Check if user is authenticated
        if (Auth::check()) {
            $this->performSessionSecurityChecks($request);
        }

        return $next($request);
    }

    /**
     * Determine if security checks should be skipped for this request
     */
    protected function shouldSkipSecurityCheck(Request $request): bool
    {
        $skipRoutes = [
            'login',
            'logout',
            'password.change',
            'password.request',
            'password.reset',
            'register',
            'api.*',
        ];

        foreach ($skipRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Perform comprehensive session security checks
     */
    protected function performSessionSecurityChecks(Request $request): void
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();
        $currentIp = $request->ip();
        $currentUserAgent = $request->userAgent();

        // Check IP address change
        if (config('session.security.force_logout_on_ip_change', true)) {
            $this->checkIpChange($request, $user, $currentIp);
        }

        // Check user agent change
        if (config('session.security.force_logout_on_user_agent_change', false)) {
            $this->checkUserAgentChange($request, $user, $currentUserAgent);
        }

        // Check session fingerprint
        if (config('session.security.enable_fingerprinting', true)) {
            $this->checkSessionFingerprint($request, $user);
        }

        // Check maximum session lifetime
        $this->checkMaxSessionLifetime($request, $user);

        // Regenerate session ID periodically
        $this->regenerateSessionIfNeeded($request);

        // Update session activity
        $this->updateSessionActivity($request, $user);
    }

    /**
     * Check for IP address changes
     */
    protected function checkIpChange(Request $request, $user, string $currentIp): void
    {
        $storedIp = Session::get('security.ip_address');
        
        if ($storedIp && $storedIp !== $currentIp) {
            Log::warning('Session terminated due to IP address change', [
                'user_id' => $user->id,
                'old_ip' => $storedIp,
                'new_ip' => $currentIp,
                'session_id' => $request->session()->getId(),
                'timestamp' => now()->toISOString()
            ]);

            $this->forceLogout($request, 'Your session has been terminated due to a security policy (IP address change).');
        } else {
            Session::put('security.ip_address', $currentIp);
        }
    }

    /**
     * Check for user agent changes
     */
    protected function checkUserAgentChange(Request $request, $user, string $currentUserAgent): void
    {
        $storedUserAgent = Session::get('security.user_agent');
        
        if ($storedUserAgent && $storedUserAgent !== $currentUserAgent) {
            Log::warning('Session terminated due to user agent change', [
                'user_id' => $user->id,
                'old_user_agent' => $storedUserAgent,
                'new_user_agent' => $currentUserAgent,
                'session_id' => $request->session()->getId(),
                'timestamp' => now()->toISOString()
            ]);

            $this->forceLogout($request, 'Your session has been terminated due to a security policy (browser change).');
        } else {
            Session::put('security.user_agent', $currentUserAgent);
        }
    }

    /**
     * Check session fingerprint for additional security
     */
    protected function checkSessionFingerprint(Request $request, $user): void
    {
        $fingerprint = $this->generateSessionFingerprint($request);
        $storedFingerprint = Session::get('security.fingerprint');
        
        if ($storedFingerprint && $storedFingerprint !== $fingerprint) {
            Log::warning('Session terminated due to fingerprint mismatch', [
                'user_id' => $user->id,
                'stored_fingerprint' => $storedFingerprint,
                'current_fingerprint' => $fingerprint,
                'session_id' => $request->session()->getId(),
                'timestamp' => now()->toISOString()
            ]);

            $this->forceLogout($request, 'Your session has been terminated due to a security policy.');
        } else {
            Session::put('security.fingerprint', $fingerprint);
        }
    }

    /**
     * Generate session fingerprint
     */
    protected function generateSessionFingerprint(Request $request): string
    {
        $components = [
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Check maximum session lifetime
     */
    protected function checkMaxSessionLifetime(Request $request, $user): void
    {
        $sessionStartTime = Session::get('security.session_start_time');
        $maxLifetime = config('session.security.max_lifetime', 120); // 2 hours

        if ($sessionStartTime) {
            $sessionAge = now()->diffInMinutes($sessionStartTime);
            
            if ($sessionAge > $maxLifetime) {
                Log::info('Session terminated due to maximum lifetime exceeded', [
                    'user_id' => $user->id,
                    'session_age_minutes' => $sessionAge,
                    'max_lifetime_minutes' => $maxLifetime,
                    'session_id' => $request->session()->getId(),
                    'timestamp' => now()->toISOString()
                ]);

                $this->forceLogout($request, 'Your session has expired due to maximum session time.');
            }
        } else {
            Session::put('security.session_start_time', now());
        }
    }

    /**
     * Regenerate session ID periodically for security
     */
    protected function regenerateSessionIfNeeded(Request $request): void
    {
        $regenerateFrequency = config('session.security.regenerate_frequency', 10);
        $requestCount = Session::get('security.request_count', 0);
        
        if ($requestCount >= $regenerateFrequency) {
            $request->session()->regenerate();
            Session::put('security.request_count', 0);
            
            Log::debug('Session ID regenerated for security', [
                'user_id' => Auth::id(),
                'session_id' => $request->session()->getId(),
                'timestamp' => now()->toISOString()
            ]);
        } else {
            Session::put('security.request_count', $requestCount + 1);
        }
    }

    /**
     * Update session activity tracking
     */
    protected function updateSessionActivity(Request $request, $user): void
    {
        Session::put('security.last_activity', now());
        
        // Check for session timeout warning
        $timeoutWarning = config('session.security.timeout_warning', 5);
        $lastActivity = Session::get('security.last_activity');
        $sessionLifetime = config('session.lifetime', 30);
        
        $timeUntilExpiry = $sessionLifetime - now()->diffInMinutes($lastActivity);
        
        if ($timeUntilExpiry <= $timeoutWarning && $timeUntilExpiry > 0) {
            Session::put('security.timeout_warning', true);
            Session::put('security.time_remaining', $timeUntilExpiry);
        }
    }

    /**
     * Force logout with security logging
     */
    protected function forceLogout(Request $request, string $reason): void
    {
        $user = Auth::user();
        
        Log::info('Forced logout due to security policy', [
            'user_id' => $user ? $user->id : null,
            'reason' => $reason,
            'session_id' => $request->session()->getId(),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);

        // Clear all session data
        $request->session()->flush();
        
        // Logout user
        Auth::logout();
        
        // Invalidate session
        $request->session()->invalidate();
        
        // Regenerate CSRF token
        $request->session()->regenerateToken();
        
        // Redirect to login with error message
        redirect()->route('login')->with('error', $reason)->send();
        exit;
    }
}