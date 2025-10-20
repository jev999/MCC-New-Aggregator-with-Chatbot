<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip timeout check for login/logout routes and API routes
        if ($this->shouldSkipTimeoutCheck($request)) {
            return $next($request);
        }

        // Check if user is authenticated
        if (Auth::check()) {
            $this->checkSessionTimeout($request);
        }

        return $next($request);
    }

    /**
     * Check if the session has timed out
     */
    private function checkSessionTimeout(Request $request): void
    {
        $session = $request->session();
        $sessionLifetime = config('session.lifetime', 60) * 60; // Convert minutes to seconds
        $lastActivity = $session->get('last_activity', now()->timestamp);
        $currentTime = now()->timestamp;

        // Check if session has expired
        if (($currentTime - $lastActivity) > $sessionLifetime) {
            $this->handleSessionTimeout($request);
            return;
        }

        // Check for inactivity timeout (separate from session lifetime)
        $inactivityTimeout = 30 * 60; // 30 minutes of inactivity
        $lastUserActivity = $session->get('last_user_activity', now()->timestamp);

        if (($currentTime - $lastUserActivity) > $inactivityTimeout) {
            $this->handleInactivityTimeout($request);
            return;
        }

        // Update last activity timestamp
        $session->put('last_activity', $currentTime);
        
        // Update user activity timestamp for non-background requests
        if (!$this->isBackgroundRequest($request)) {
            $session->put('last_user_activity', $currentTime);
        }
    }

    /**
     * Handle session timeout
     */
    private function handleSessionTimeout(Request $request): void
    {
        $userId = Auth::id();
        $userEmail = Auth::user() ? Auth::user()->ms365_account : 'unknown';

        Log::info('Session timeout detected', [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'timestamp' => now()->toISOString()
        ]);

        // Logout the user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            abort(419, 'Session expired. Please login again.');
        }

        // Redirect to login with timeout message
        redirect()->route('login')
            ->with('warning', 'Your session has expired due to inactivity. Please login again.')
            ->send();
        exit;
    }

    /**
     * Handle inactivity timeout
     */
    private function handleInactivityTimeout(Request $request): void
    {
        $userId = Auth::id();
        $userEmail = Auth::user() ? Auth::user()->ms365_account : 'unknown';

        Log::info('Inactivity timeout detected', [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'timestamp' => now()->toISOString()
        ]);

        // Logout the user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            abort(419, 'Session expired due to inactivity. Please login again.');
        }

        // Redirect to login with inactivity message
        redirect()->route('login')
            ->with('info', 'You have been logged out due to inactivity. Please login again.')
            ->send();
        exit;
    }

    /**
     * Determine if timeout check should be skipped for this request
     */
    private function shouldSkipTimeoutCheck(Request $request): bool
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

        $skipPaths = [
            'api/*',
            '_debugbar/*',
            'telescope/*',
            'horizon/*'
        ];

        // Check route names
        if ($request->route() && in_array($request->route()->getName(), $skipRoutes)) {
            return true;
        }

        // Check paths
        foreach ($skipPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if this is a background request that shouldn't update user activity
     */
    private function isBackgroundRequest(Request $request): bool
    {
        $backgroundPaths = [
            'api/heartbeat',
            'api/ping',
            'api/status',
            '_debugbar/*'
        ];

        foreach ($backgroundPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        // Check for AJAX requests that are just polling/background updates
        if ($request->ajax() && in_array($request->method(), ['GET', 'HEAD'])) {
            $backgroundHeaders = [
                'X-Background-Request',
                'X-Polling-Request',
                'X-Heartbeat'
            ];

            foreach ($backgroundHeaders as $header) {
                if ($request->hasHeader($header)) {
                    return true;
                }
            }
        }

        return false;
    }
}
