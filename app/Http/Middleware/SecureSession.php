<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SecureSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            
            // Session fingerprinting - check if IP or User Agent has changed
            if (config('session.security.enable_fingerprinting')) {
                $this->validateSessionFingerprint($request, $admin);
            }
            
            // Regenerate session ID periodically
            if (config('session.security.regenerate_frequency')) {
                $this->regenerateSessionPeriodically($request);
            }
            
            // Check maximum session lifetime
            if (config('session.security.max_lifetime')) {
                $this->checkMaxSessionLifetime($request, $admin);
            }
        }

        return $next($request);
    }

    /**
     * Validate session fingerprint (IP and User Agent)
     */
    protected function validateSessionFingerprint(Request $request, $admin): void
    {
        $currentIp = $request->ip();
        $currentUserAgent = $request->userAgent();
        
        $sessionIp = Session::get('session_ip');
        $sessionUserAgent = Session::get('session_user_agent');
        
        // First time setting fingerprint
        if (!$sessionIp || !$sessionUserAgent) {
            Session::put('session_ip', $currentIp);
            Session::put('session_user_agent', $currentUserAgent);
            return;
        }
        
        // Check for IP change
        if (config('session.security.force_logout_on_ip_change') && $sessionIp !== $currentIp) {
            \Log::warning('Session IP mismatch - forcing logout', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'expected_ip' => $sessionIp,
                'actual_ip' => $currentIp,
                'timestamp' => now()->toISOString()
            ]);
            
            Auth::guard('admin')->logout();
            Session::invalidate();
            Session::regenerateToken();
            
            abort(401, 'Your session has been terminated due to IP address change. Please login again.');
        }
        
        // Check for User Agent change
        if (config('session.security.force_logout_on_user_agent_change') && $sessionUserAgent !== $currentUserAgent) {
            \Log::warning('Session User Agent mismatch - forcing logout', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'expected_user_agent' => $sessionUserAgent,
                'actual_user_agent' => $currentUserAgent,
                'timestamp' => now()->toISOString()
            ]);
            
            Auth::guard('admin')->logout();
            Session::invalidate();
            Session::regenerateToken();
            
            abort(401, 'Your session has been terminated due to browser change. Please login again.');
        }
    }

    /**
     * Regenerate session ID periodically for security
     */
    protected function regenerateSessionPeriodically(Request $request): void
    {
        $frequency = config('session.security.regenerate_frequency', 10);
        $requestCount = Session::get('session_request_count', 0);
        
        if ($requestCount >= $frequency) {
            $request->session()->regenerate();
            Session::put('session_request_count', 0);
            
            \Log::info('Session ID regenerated for security', [
                'admin_id' => Auth::guard('admin')->id(),
                'request_count' => $requestCount,
                'timestamp' => now()->toISOString()
            ]);
        } else {
            Session::put('session_request_count', $requestCount + 1);
        }
    }

    /**
     * Check if session has exceeded maximum lifetime
     */
    protected function checkMaxSessionLifetime(Request $request, $admin): void
    {
        $maxLifetime = config('session.security.max_lifetime', 120); // Default 2 hours in minutes
        $sessionStart = Session::get('session_start_time');
        
        if (!$sessionStart) {
            Session::put('session_start_time', now()->timestamp);
            return;
        }
        
        $minutesSinceStart = (now()->timestamp - $sessionStart) / 60;
        
        if ($minutesSinceStart > $maxLifetime) {
            \Log::info('Maximum session lifetime exceeded - forcing logout', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'session_duration' => $minutesSinceStart,
                'max_lifetime' => $maxLifetime,
                'timestamp' => now()->toISOString()
            ]);
            
            Auth::guard('admin')->logout();
            Session::invalidate();
            Session::regenerateToken();
            
            abort(401, 'Your session has expired. Please login again.');
        }
    }
}
