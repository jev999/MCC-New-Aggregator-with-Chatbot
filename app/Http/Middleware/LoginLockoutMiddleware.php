<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LoginLockoutMiddleware
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
        // Only check lockout for POST requests to login
        if ($request->isMethod('POST') && $request->is('login')) {
            $lockoutKey = $this->getLockoutKey($request);
            $lockoutTime = session($lockoutKey);
            
            if ($lockoutTime) {
                try {
                    $lockoutTime = is_string($lockoutTime) ? \Carbon\Carbon::parse($lockoutTime) : $lockoutTime;
                    
                    if ($lockoutTime instanceof \Carbon\Carbon && now()->lessThan($lockoutTime)) {
                        $timeRemaining = $this->getLockoutTimeRemaining($request);
                        $accountIdentifier = $this->getAccountIdentifier($request);
                        
                        \Log::warning('MIDDLEWARE LOCKOUT BLOCK - Login attempt during lockout', [
                            'account_identifier' => $accountIdentifier,
                            'lockout_time_remaining' => $timeRemaining,
                            'ip' => $request->ip(),
                            'current_time' => now()->toDateTimeString(),
                            'lockout_expires' => $lockoutTime->toDateTimeString()
                        ]);
                        
                        return back()->withErrors([
                            'account_lockout' => "This account is temporarily locked due to too many failed login attempts. Please try again in {$timeRemaining} minute" . ($timeRemaining != 1 ? 's' : '') . "."
                        ])->with('lockout_time', $timeRemaining)
                          ->with('locked_account', $accountIdentifier);
                    }
                } catch (\Exception $e) {
                    \Log::error('Middleware lockout check error', [
                        'error' => $e->getMessage(),
                        'lockout_time' => $lockoutTime
                    ]);
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Get the lockout session key for specific account
     */
    private function getLockoutKey(Request $request)
    {
        $identifier = $this->getAccountIdentifier($request);
        return 'lockout_time_' . md5($identifier);
    }
    
    /**
     * Get unique account identifier based on login type
     */
    private function getAccountIdentifier(Request $request)
    {
        $loginType = $request->login_type;
        
        switch ($loginType) {
            case 'ms365':
                return $loginType . '_' . ($request->ms365_account ?? 'unknown');
            case 'user':
                return $loginType . '_' . ($request->gmail_account ?? 'unknown');
            case 'superadmin':
            case 'department-admin':
            case 'office-admin':
                return $loginType . '_' . ($request->username ?? ($request->ms365_account ?? 'unknown'));
            default:
                return 'unknown_' . $request->ip();
        }
    }
    
    /**
     * Get remaining lockout time in minutes
     */
    private function getLockoutTimeRemaining(Request $request)
    {
        $lockoutKey = $this->getLockoutKey($request);
        $lockoutTime = session($lockoutKey);
        
        if (!$lockoutTime) {
            return 0;
        }
        
        try {
            $lockoutTime = is_string($lockoutTime) ? \Carbon\Carbon::parse($lockoutTime) : $lockoutTime;
            
            if (!$lockoutTime instanceof \Carbon\Carbon) {
                return 0;
            }
            
            if (now()->greaterThan($lockoutTime)) {
                return 0;
            }
            
            $remaining = $lockoutTime->diffInMinutes(now());
            return max(0, ceil($remaining));
        } catch (\Exception $e) {
            return 0;
        }
    }
}
