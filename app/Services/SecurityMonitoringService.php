<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SecurityMonitoringService
{
    /**
     * Monitor failed login attempts
     */
    public function monitorFailedLoginAttempts($ip, $email = null, $username = null)
    {
        $key = "failed_login_attempts:{$ip}";
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, now()->addMinutes(15));
        
        $data = [
            'ip' => $ip,
            'email' => $email,
            'username' => $username,
            'attempts' => $attempts,
            'timestamp' => now()->toISOString()
        ];
        
        Log::channel('security')->warning('Failed login attempt', $data);
        
        // Alert if threshold exceeded
        if ($attempts >= 5) {
            $this->sendSecurityAlert('Multiple failed login attempts', $data);
        }
        
        return $attempts;
    }

    /**
     * Monitor suspicious activity
     */
    public function monitorSuspiciousActivity($type, $data = [])
    {
        $key = "suspicious_activity:{$type}:" . request()->ip();
        $count = Cache::get($key, 0) + 1;
        
        Cache::put($key, $count, now()->addMinutes(30));
        
        $logData = array_merge([
            'type' => $type,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'count' => $count,
            'timestamp' => now()->toISOString()
        ], $data);
        
        Log::channel('security')->critical('Suspicious activity detected', $logData);
        
        // Alert if threshold exceeded
        if ($count >= 3) {
            $this->sendSecurityAlert('Suspicious activity detected', $logData);
        }
    }

    /**
     * Monitor rate limiting violations
     */
    public function monitorRateLimitViolations($endpoint, $ip)
    {
        $key = "rate_limit_violations:{$endpoint}:{$ip}";
        $violations = Cache::get($key, 0) + 1;
        
        Cache::put($key, $violations, now()->addMinutes(60));
        
        $data = [
            'endpoint' => $endpoint,
            'ip' => $ip,
            'violations' => $violations,
            'timestamp' => now()->toISOString()
        ];
        
        Log::channel('security')->warning('Rate limit violation', $data);
        
        // Alert if threshold exceeded
        if ($violations >= 10) {
            $this->sendSecurityAlert('Rate limit violations', $data);
        }
    }

    /**
     * Monitor SQL injection attempts
     */
    public function monitorSqlInjectionAttempts($query, $ip)
    {
        $key = "sql_injection_attempts:{$ip}";
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, now()->addMinutes(60));
        
        $data = [
            'ip' => $ip,
            'query' => substr($query, 0, 200), // Log first 200 chars only
            'attempts' => $attempts,
            'timestamp' => now()->toISOString()
        ];
        
        Log::channel('security')->critical('SQL injection attempt detected', $data);
        
        // Immediate alert for SQL injection attempts
        $this->sendSecurityAlert('SQL injection attempt detected', $data);
    }

    /**
     * Monitor XSS attempts
     */
    public function monitorXssAttempts($input, $field, $ip)
    {
        $key = "xss_attempts:{$ip}";
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, now()->addMinutes(60));
        
        $data = [
            'ip' => $ip,
            'field' => $field,
            'input' => substr($input, 0, 200), // Log first 200 chars only
            'attempts' => $attempts,
            'timestamp' => now()->toISOString()
        ];
        
        Log::channel('security')->critical('XSS attempt detected', $data);
        
        // Immediate alert for XSS attempts
        $this->sendSecurityAlert('XSS attempt detected', $data);
    }

    /**
     * Monitor file upload attempts
     */
    public function monitorFileUploadAttempts($file, $ip, $result)
    {
        $key = "file_upload_attempts:{$ip}";
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, now()->addMinutes(30));
        
        $data = [
            'ip' => $ip,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'valid' => $result['valid'] ?? false,
            'attempts' => $attempts,
            'timestamp' => now()->toISOString()
        ];
        
        if (!$result['valid']) {
            Log::channel('security')->warning('Invalid file upload attempt', $data);
            
            // Alert if threshold exceeded
            if ($attempts >= 5) {
                $this->sendSecurityAlert('Multiple invalid file upload attempts', $data);
            }
        }
    }

    /**
     * Send security alert
     */
    private function sendSecurityAlert($subject, $data)
    {
        if (!config('security.monitoring.enabled', true)) {
            return;
        }
        
        $adminEmails = config('security.monitoring.admin_emails', []);
        
        if (empty($adminEmails)) {
            return;
        }
        
        try {
            Mail::send('emails.security-alert', [
                'subject' => $subject,
                'data' => $data,
                'timestamp' => now()->toISOString()
            ], function ($message) use ($subject, $adminEmails) {
                $message->to($adminEmails)
                       ->subject("Security Alert: {$subject}");
            });
        } catch (\Exception $e) {
            Log::error('Failed to send security alert: ' . $e->getMessage());
        }
    }

    /**
     * Get security statistics
     */
    public function getSecurityStatistics($timeframe = '24h')
    {
        $hours = $timeframe === '24h' ? 24 : ($timeframe === '7d' ? 168 : 1);
        $since = now()->subHours($hours);
        
        $stats = [
            'failed_logins' => $this->getFailedLoginCount($since),
            'suspicious_activities' => $this->getSuspiciousActivityCount($since),
            'rate_limit_violations' => $this->getRateLimitViolationCount($since),
            'sql_injection_attempts' => $this->getSqlInjectionAttemptCount($since),
            'xss_attempts' => $this->getXssAttemptCount($since),
            'file_upload_attempts' => $this->getFileUploadAttemptCount($since),
        ];
        
        return $stats;
    }

    /**
     * Get failed login count
     */
    private function getFailedLoginCount($since)
    {
        return DB::table('security_logs')
            ->where('event_type', 'failed_login')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get suspicious activity count
     */
    private function getSuspiciousActivityCount($since)
    {
        return DB::table('security_logs')
            ->where('event_type', 'suspicious_activity')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get rate limit violation count
     */
    private function getRateLimitViolationCount($since)
    {
        return DB::table('security_logs')
            ->where('event_type', 'rate_limit_violation')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get SQL injection attempt count
     */
    private function getSqlInjectionAttemptCount($since)
    {
        return DB::table('security_logs')
            ->where('event_type', 'sql_injection_attempt')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get XSS attempt count
     */
    private function getXssAttemptCount($since)
    {
        return DB::table('security_logs')
            ->where('event_type', 'xss_attempt')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get file upload attempt count
     */
    private function getFileUploadAttemptCount($since)
    {
        return DB::table('security_logs')
            ->where('event_type', 'file_upload_attempt')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Check if IP is blacklisted
     */
    public function isIpBlacklisted($ip)
    {
        return Cache::has("blacklisted_ip:{$ip}");
    }

    /**
     * Blacklist IP address
     */
    public function blacklistIp($ip, $reason = 'Security violation', $duration = 24)
    {
        Cache::put("blacklisted_ip:{$ip}", [
            'reason' => $reason,
            'blacklisted_at' => now()->toISOString(),
            'duration_hours' => $duration
        ], now()->addHours($duration));
        
        Log::channel('security')->critical('IP address blacklisted', [
            'ip' => $ip,
            'reason' => $reason,
            'duration_hours' => $duration,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Whitelist IP address
     */
    public function whitelistIp($ip, $reason = 'Admin whitelist')
    {
        Cache::put("whitelisted_ip:{$ip}", [
            'reason' => $reason,
            'whitelisted_at' => now()->toISOString()
        ], now()->addDays(30));
        
        Log::channel('security')->info('IP address whitelisted', [
            'ip' => $ip,
            'reason' => $reason,
            'timestamp' => now()->toISOString()
        ]);
    }
}
