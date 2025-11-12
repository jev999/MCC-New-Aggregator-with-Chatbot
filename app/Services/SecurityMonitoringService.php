<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\SecurityAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityMonitoringService
{
    /**
     * Detect and log suspicious activity
     */
    public function detectSuspiciousActivity(string $activity, array $data = [], string $severity = 'medium'): void
    {
        $suspiciousPatterns = $this->checkSuspiciousPatterns($activity, $data);
        
        if (!empty($suspiciousPatterns)) {
            $this->createSecurityAlert($activity, $data, $suspiciousPatterns, $severity);
            $this->logSuspiciousActivity($activity, $data, $suspiciousPatterns);
            
            // Send real-time alerts for high-severity issues
            if ($severity === 'critical' || $severity === 'high') {
                $this->sendRealTimeAlert($activity, $data, $suspiciousPatterns, $severity);
            }
        }
    }

    /**
     * Check for suspicious patterns
     */
    protected function checkSuspiciousPatterns(string $activity, array $data): array
    {
        $patterns = [];

        // Pattern 1: Multiple failed login attempts
        if ($activity === 'login_failed') {
            $ip = isset($data['ip']) ? $data['ip'] : null;
            $username = isset($data['username']) ? $data['username'] : null;
            $attempts = $this->getFailedLoginAttempts($ip, $username);
            if ($attempts > 5) {
                $patterns[] = "Multiple failed login attempts detected: {$attempts} attempts";
            }
        }

        // Pattern 2: Unauthorized access attempts
        if ($activity === 'unauthorized_access') {
            $url = isset($data['url']) ? $data['url'] : 'unknown resource';
            $patterns[] = "Unauthorized access attempt to {$url}";
        }

        // Pattern 3: Unusual time access (outside business hours)
        if ($this->isOutsideBusinessHours()) {
            $patterns[] = "Access outside business hours";
        }

        // Pattern 4: Unusual location (if IP tracking is enabled)
        if (isset($data['ip'])) {
            $adminId = isset($data['admin_id']) ? $data['admin_id'] : null;
            if ($this->isUnusualLocation($data['ip'], $adminId)) {
                $patterns[] = "Access from unusual location";
            }
        }

        // Pattern 5: Rapid-fire requests (potential attack)
        $adminId = isset($data['admin_id']) ? $data['admin_id'] : null;
        $ip = isset($data['ip']) ? $data['ip'] : null;
        if ($this->isRapidFireActivity($adminId, $ip)) {
            $patterns[] = "Rapid-fire requests detected (possible bot/attack)";
        }

        // Pattern 6: Privilege escalation attempts
        if ($activity === 'permission_denied' && isset($data['required_permission'])) {
            $adminId = isset($data['admin_id']) ? $data['admin_id'] : null;
            if ($this->isPrivilegeEscalationAttempt($adminId, $data['required_permission'])) {
                $patterns[] = "Possible privilege escalation attempt";
            }
        }

        // Pattern 7: Bulk data operations
        if (isset($data['bulk_operation']) && $data['bulk_operation'] === true) {
            $count = isset($data['count']) ? $data['count'] : 0;
            if ($count > 100) {
                $patterns[] = "Large bulk operation detected: {$count} items";
            }
        }

        // Pattern 8: Critical system changes
        if (in_array($activity, ['role_changed', 'permission_granted', 'admin_created', 'settings_modified'])) {
            $patterns[] = "Critical system change: {$activity}";
        }

        // Pattern 9: Multiple account access
        if (isset($data['ip']) && $this->isMultipleAccountAccess($data['ip'])) {
            $patterns[] = "Multiple accounts accessed from same IP";
        }

        // Pattern 10: Session hijacking indicators
        if (isset($data['session_mismatch']) && $data['session_mismatch'] === true) {
            $patterns[] = "Possible session hijacking detected";
        }

        return $patterns;
    }

    /**
     * Get failed login attempts count
     */
    protected function getFailedLoginAttempts(?string $ip, ?string $username): int
    {
        $cacheKey = "failed_login_{$ip}_{$username}";
        return (int) Cache::get($cacheKey, 0);
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedLoginAttempts(string $ip, string $username): void
    {
        $cacheKey = "failed_login_{$ip}_{$username}";
        $attempts = (int) Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $attempts + 1, now()->addHour());
    }

    /**
     * Clear failed login attempts
     */
    public function clearFailedLoginAttempts(string $ip, string $username): void
    {
        $cacheKey = "failed_login_{$ip}_{$username}";
        Cache::forget($cacheKey);
    }

    /**
     * Check if access is outside business hours (8 AM - 6 PM)
     */
    protected function isOutsideBusinessHours(): bool
    {
        $hour = now()->hour;
        return $hour < 8 || $hour >= 18;
    }

    /**
     * Check if IP is from unusual location
     */
    protected function isUnusualLocation(string $ip, ?int $adminId): bool
    {
        if (!$adminId) {
            return false;
        }

        // Get admin's usual IPs from cache
        $cacheKey = "usual_ips_admin_{$adminId}";
        $usualIps = Cache::get($cacheKey, []);

        // If this is a new IP, flag it
        if (!in_array($ip, $usualIps)) {
            // Add to usual IPs if less than 5 stored
            if (count($usualIps) < 5) {
                $usualIps[] = $ip;
                Cache::put($cacheKey, $usualIps, now()->addDays(30));
            }
            return count($usualIps) > 0; // Suspicious if we already have other IPs
        }

        return false;
    }

    /**
     * Check for rapid-fire activity
     */
    protected function isRapidFireActivity(?int $adminId, ?string $ip): bool
    {
        $identifier = $adminId ?? $ip ?? 'unknown';
        $cacheKey = "request_count_{$identifier}";
        
        $requests = Cache::get($cacheKey, []);
        $requests[] = now()->timestamp;
        
        // Keep only last 60 seconds
        $requests = array_filter($requests, function($timestamp) {
            return $timestamp > (now()->timestamp - 60);
        });
        
        Cache::put($cacheKey, $requests, now()->addMinutes(2));
        
        // More than 30 requests per minute is suspicious
        return count($requests) > 30;
    }

    /**
     * Check for privilege escalation attempts
     */
    protected function isPrivilegeEscalationAttempt(?int $adminId, string $permission): bool
    {
        if (!$adminId) {
            return false;
        }

        $cacheKey = "privilege_escalation_{$adminId}";
        $attempts = Cache::get($cacheKey, []);
        $attempts[] = [
            'permission' => $permission,
            'timestamp' => now()->timestamp
        ];
        
        // Keep only last hour
        $attempts = array_filter($attempts, function($attempt) {
            return $attempt['timestamp'] > (now()->timestamp - 3600);
        });
        
        Cache::put($cacheKey, $attempts, now()->addHours(2));
        
        // More than 5 permission denied in an hour is suspicious
        return count($attempts) > 5;
    }

    /**
     * Check for multiple account access from same IP
     */
    protected function isMultipleAccountAccess(string $ip): bool
    {
        $cacheKey = "accounts_from_ip_{$ip}";
        $accounts = Cache::get($cacheKey, []);
        
        // More than 3 different accounts from same IP in short time is suspicious
        return count($accounts) > 3;
    }

    /**
     * Track account access from IP
     */
    public function trackAccountAccess(int $adminId, string $ip): void
    {
        $cacheKey = "accounts_from_ip_{$ip}";
        $accounts = Cache::get($cacheKey, []);
        
        if (!in_array($adminId, $accounts)) {
            $accounts[] = $adminId;
            Cache::put($cacheKey, $accounts, now()->addHour());
        }
    }

    /**
     * Create security alert in database
     */
    protected function createSecurityAlert(string $activity, array $data, array $patterns, string $severity): void
    {
        try {
            SecurityAlert::create([
                'activity_type' => $activity,
                'severity' => $severity,
                'admin_id' => isset($data['admin_id']) ? $data['admin_id'] : null,
                'ip_address' => isset($data['ip']) ? $data['ip'] : null,
                'user_agent' => isset($data['user_agent']) ? $data['user_agent'] : null,
                'url' => isset($data['url']) ? $data['url'] : null,
                'description' => implode('; ', $patterns),
                'data' => json_encode($data),
                'resolved' => false,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create security alert', [
                'error' => $e->getMessage(),
                'activity' => $activity,
            ]);
        }
    }

    /**
     * Log suspicious activity
     */
    protected function logSuspiciousActivity(string $activity, array $data, array $patterns): void
    {
        Log::warning('Suspicious activity detected', [
            'activity' => $activity,
            'patterns' => $patterns,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Send real-time alert for critical issues
     */
    protected function sendRealTimeAlert(string $activity, array $data, array $patterns, string $severity): void
    {
        // Get superadmins to notify
        $superadmins = Admin::where('role', 'superadmin')->get();

        $adminUsername = 'Unknown';
        if (isset($data['admin_id'])) {
            $admin = Admin::find($data['admin_id']);
            if ($admin) {
                $adminUsername = $admin->username;
            }
        }

        $alertData = [
            'activity' => $activity,
            'severity' => strtoupper($severity),
            'patterns' => $patterns,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'ip' => isset($data['ip']) ? $data['ip'] : 'Unknown',
            'admin' => $adminUsername,
            'url' => isset($data['url']) ? $data['url'] : 'N/A',
        ];

        // Log to a dedicated security channel
        Log::channel('security')->critical('SECURITY ALERT: ' . $activity, $alertData);

        // In production, send email alerts to superadmins
        if (config('app.env') === 'production') {
            foreach ($superadmins as $admin) {
                // Queue email notification
                // Mail::to($admin->email)->queue(new SecurityAlertMail($alertData));
            }
        }

        // Store in cache for real-time dashboard
        $this->storeRealtimeAlert($alertData);
    }

    /**
     * Store alert for real-time dashboard
     */
    protected function storeRealtimeAlert(array $alertData): void
    {
        $cacheKey = 'realtime_security_alerts';
        $alerts = Cache::get($cacheKey, []);
        
        array_unshift($alerts, $alertData);
        
        // Keep only last 50 alerts
        $alerts = array_slice($alerts, 0, 50);
        
        Cache::put($cacheKey, $alerts, now()->addDay());
    }

    /**
     * Get real-time alerts for dashboard
     */
    public function getRealtimeAlerts(int $limit = 20): array
    {
        $cacheKey = 'realtime_security_alerts';
        $alerts = Cache::get($cacheKey, []);
        
        return array_slice($alerts, 0, $limit);
    }

    /**
     * Get security statistics
     */
    public function getSecurityStatistics(): array
    {
        $today = now()->startOfDay();
        
        return [
            'total_alerts_today' => SecurityAlert::where('created_at', '>=', $today)->count(),
            'critical_alerts_today' => SecurityAlert::where('created_at', '>=', $today)
                ->where('severity', 'critical')->count(),
            'high_alerts_today' => SecurityAlert::where('created_at', '>=', $today)
                ->where('severity', 'high')->count(),
            'unresolved_alerts' => SecurityAlert::where('resolved', false)->count(),
            'failed_logins_today' => SecurityAlert::where('created_at', '>=', $today)
                ->where('activity_type', 'login_failed')->count(),
            'unauthorized_access_today' => SecurityAlert::where('created_at', '>=', $today)
                ->where('activity_type', 'unauthorized_access')->count(),
        ];
    }

    /**
     * Mark alert as resolved
     */
    public function resolveAlert(int $alertId, int $resolvedBy): bool
    {
        try {
            $alert = SecurityAlert::findOrFail($alertId);
            $alert->update([
                'resolved' => true,
                'resolved_by' => $resolvedBy,
                'resolved_at' => now(),
            ]);

            activity()
                ->causedBy(Admin::find($resolvedBy))
                ->performedOn($alert)
                ->log('Security alert resolved');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to resolve alert', [
                'alert_id' => $alertId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
