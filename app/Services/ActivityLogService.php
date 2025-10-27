<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogService
{
    /**
     * Log user activity with optional metadata
     */
    public function logActivity(
        string $action,
        string $description = null,
        array $context = [],
        int $userId = null
    ): void {
        $userId = $userId ?? Auth::id();
        $userType = $this->getUserType();
        
        // Log to database
        ActivityLog::create([
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'description' => $description ?? $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'context' => json_encode($context),
            'created_at' => now(),
        ]);

        // Also log to file for redundancy
        Log::channel('activity')->info("Activity: {$action}", [
            'user_id' => $userId,
            'user_type' => $userType,
            'description' => $description ?? $action,
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'context' => $context,
        ]);
    }

    /**
     * Log sensitive operations
     */
    public function logSensitiveActivity(
        string $action,
        string $description,
        array $context = [],
        int $userId = null
    ): void {
        $userId = $userId ?? Auth::id();
        $userType = $this->getUserType();

        // Log to database
        ActivityLog::create([
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'context' => json_encode($context),
            'is_sensitive' => true,
            'created_at' => now(),
        ]);

        // Log to security channel
        Log::channel('security')->warning("Sensitive Activity: {$action}", [
            'user_id' => $userId,
            'user_type' => $userType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'context' => $context,
        ]);
    }

    /**
     * Log unauthorized access attempts
     */
    public function logUnauthorizedAccess(
        string $resource,
        string $reason = null,
        array $context = []
    ): void {
        $userId = Auth::id() ?? 'guest';
        
        Log::channel('security')->alert('Unauthorized access attempt', [
            'user_id' => $userId,
            'resource' => $resource,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'context' => $context,
        ]);

        // Alert administrators if configured
        $this->sendSecurityAlert('Unauthorized access attempt', [
            'resource' => $resource,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(
        string $description,
        string $severity = 'medium',
        array $context = []
    ): void {
        $userId = Auth::id() ?? 'guest';
        
        Log::channel('security')->warning("Suspicious activity detected ({$severity})", [
            'user_id' => $userId,
            'description' => $description,
            'severity' => $severity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'context' => $context,
        ]);

        // Alert for high-severity events
        if ($severity === 'high' || $severity === 'critical') {
            $this->sendSecurityAlert("Suspicious activity: {$description}", [
                'severity' => $severity,
                'user_id' => $userId,
                'ip_address' => request()->ip(),
            ]);
        }
    }

    /**
     * Detect unusual activity patterns
     */
    public function detectUnusualActivity(int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        if (!$userId) return false;

        $recentActivity = ActivityLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subHours(1))
            ->count();

        // Flag unusual activity (e.g., more than 50 actions per hour)
        if ($recentActivity > 50) {
            $this->logSuspiciousActivity(
                "High frequency of actions detected",
                'medium',
                ['action_count' => $recentActivity]
            );
            return true;
        }

        return false;
    }

    /**
     * Send security alert (placeholder for email/notification system)
     */
    private function sendSecurityAlert(string $subject, array $details = []): void
    {
        // TODO: Implement email notification or webhook integration
        // For now, just log it
        Log::channel('security')->critical('Security alert triggered', [
            'subject' => $subject,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get user type from authenticated user
     */
    private function getUserType(): string
    {
        if (!Auth::check()) {
            return 'guest';
        }

        $user = Auth::user();
        
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return 'super_admin';
        }
        
        if (method_exists($user, 'isOfficeAdmin') && $user->isOfficeAdmin()) {
            return 'office_admin';
        }
        
        if (method_exists($user, 'isDepartmentAdmin') && $user->isDepartmentAdmin()) {
            return 'department_admin';
        }

        return 'regular_user';
    }

    /**
     * Get activity logs for a user
     */
    public function getUserLogs(int $userId, int $limit = 50)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sensitive activity logs
     */
    public function getSensitiveLogs(int $limit = 100)
    {
        return ActivityLog::where('is_sensitive', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

