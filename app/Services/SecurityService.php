<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SecurityService
{
    /**
     * Validate input for login forms (less strict than general validation)
     */
    public function validateLoginInput($input, $fieldName = 'input')
    {
        if (!is_string($input)) {
            return ['valid' => true];
        }

        // Only check for the most dangerous patterns for login forms
        $dangerousPatterns = [
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i', // event handlers
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                Log::warning('Dangerous pattern detected in login form', [
                    'field' => $fieldName,
                    'pattern' => $pattern,
                    'input_length' => strlen($input),
                    'timestamp' => now()->toISOString()
                ]);
                
                return [
                    'valid' => false,
                    'error' => 'Invalid input detected. Please use only standard characters.'
                ];
            }
        }

        // Check for excessive length (but allow longer passwords)
        if (strlen($input) > 2000) {
            return [
                'valid' => false,
                'error' => 'Input too long.'
            ];
        }
        
        // Check for null bytes
        if (strpos($input, '\\0') !== false) {
            return [
                'valid' => false,
                'error' => 'Invalid characters detected.'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Check if input is suspicious
     */
    private function isSuspiciousInput($input)
    {
        // Check for repeated characters (potential DoS)
        if (preg_match('/(.)\\1{10,}/', $input)) {
            return true;
        }
        
        // Check for excessive length
        if (strlen($input) > config('security.input_validation.max_length', 1000)) {
            return true;
        }
        
        // Check for null bytes
        if (strpos($input, '\\0') !== false) {
            return true;
        }
        
        // Check for excessive special characters (but be more lenient for passwords)
        $specialCharCount = preg_match_all('/[^a-zA-Z0-9@._\\-\\s]/', $input);
        // Increased threshold from 30% to 50% to allow stronger passwords
        if ($specialCharCount > strlen($input) * 0.5) {
            return true;
        }
        
        return false;
    }

    /**
     * Sanitize input data
     */
    public function sanitizeInput($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        
        // Remove null bytes
        $input = str_replace('\\0', '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Limit length
        $input = substr($input, 0, config('security.input_validation.max_length', 1000));
        
        return $input;
    }

    /**
     * Escape output for HTML context
     */
    public function escapeHtml($value)
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape output for JavaScript context
     */
    public function escapeJavaScript($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload($file, $allowedTypes = null)
    {
        if (!$file || !$file->isValid()) {
            return ['valid' => false, 'error' => 'Invalid file upload.'];
        }
        
        $config = config('security.injection_prevention.file_upload', []);
        $allowedTypes = $allowedTypes ?? $config['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif'];
        $maxSize = $config['max_size'] ?? 5 * 1024 * 1024; // 5MB
        
        // Check file size
        if ($file->getSize() > $maxSize) {
            return ['valid' => false, 'error' => 'File size too large. Maximum ' . ($maxSize / 1024 / 1024) . 'MB allowed.'];
        }
        
        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedTypes)) {
            return ['valid' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes)];
        }
        
        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = $config['allowed_mimes'] ?? [
            'image/jpeg', 'image/png', 'image/gif', 'application/pdf'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Invalid file type.'];
        }
        
        // Generate safe filename
        $filename = uniqid() . '_' . Str::random(10) . '.' . $extension;
        
        return [
            'valid' => true,
            'filename' => $filename,
            'path' => 'uploads/' . $filename,
            'size' => $file->getSize(),
            'mime_type' => $mimeType
        ];
    }

    /**
     * Check rate limit for IP and endpoint
     */
    public function checkRateLimit(Request $request, $endpoint = null)
    {
        $endpoint = $endpoint ?? $request->path();
        $ip = $request->ip();
        $key = "rate_limit:{$ip}:{$endpoint}";
        
        $limits = config('security.rate_limiting.endpoints', []);
        $limit = $limits[$endpoint] ?? [
            'attempts' => config('security.rate_limiting.default_attempts', 10),
            'decay_minutes' => config('security.rate_limiting.default_decay_minutes', 1)
        ];
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $limit['attempts']) {
            Log::warning('Rate limit exceeded', [
                'ip' => $ip,
                'endpoint' => $endpoint,
                'attempts' => $attempts,
                'limit' => $limit['attempts']
            ]);
            
            return [
                'allowed' => false,
                'retry_after' => $limit['decay_minutes'] * 60
            ];
        }
        
        Cache::put($key, $attempts + 1, now()->addMinutes($limit['decay_minutes']));
        
        return ['allowed' => true];
    }

    /**
     * Log security event
     */
    public function logSecurityEvent($event, $data = [])
    {
        if (!config('security.logging.enabled', true)) {
            return;
        }
        
        $logData = array_merge([
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data);
        
        Log::channel('security')->info('Security Event', $logData);
    }

    /**
     * Generate secure random token
     */
    public function generateSecureToken($length = 32)
    {
        return Str::random($length);
    }

    /**
     * Hash sensitive data
     */
    public function hashSensitiveData($data)
    {
        return Hash::make($data);
    }

    /**
     * Verify sensitive data
     */
    public function verifySensitiveData($data, $hash)
    {
        return Hash::check($data, $hash);
    }

    /**
     * Check for SQL injection in query
     */
    public function checkSqlInjection($query)
    {
        $sqlPatterns = [
            '/\\bunion\\s+select/i',
            '/\\bselect\\s+.*\\bfrom\\s+/i',
            '/\\binsert\\s+into/i',
            '/\\bupdate\\s+.*\\bset\\s+/i',
            '/\\bdelete\\s+from/i',
            '/\\bdrop\\s+table/i',
            '/\\balter\\s+table/i',
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate email format
     */
    public function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Additional checks for suspicious patterns
        if (preg_match('/[<>"\']/', $email)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check password strength
     */
    public function checkPasswordStrength($password)
    {
        $strength = 0;
        
        // Length check
        if (strlen($password) >= 8) $strength++;
        if (strlen($password) >= 12) $strength++;
        
        // Character variety checks
        if (preg_match('/[a-z]/', $password)) $strength++;
        if (preg_match('/[A-Z]/', $password)) $strength++;
        if (preg_match('/[0-9]/', $password)) $strength++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
        
        // Check against common weak passwords
        $weakPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey'
        ];
        
        if (in_array(strtolower($password), $weakPasswords)) {
            return ['strength' => 0, 'message' => 'Password is too common'];
        }
        
        $messages = [
            0 => 'Very weak',
            1 => 'Weak',
            2 => 'Fair',
            3 => 'Good',
            4 => 'Strong',
            5 => 'Very strong',
            6 => 'Excellent'
        ];
        
        return [
            'strength' => $strength,
            'message' => $messages[$strength] ?? 'Unknown'
        ];
    }

    /**
     * Get security headers
     */
    public function getSecurityHeaders()
    {
        return config('security.headers.security_headers', []);
    }

    /**
     * Apply security headers to response
     */
    public function applySecurityHeaders($response)
    {
        $headers = $this->getSecurityHeaders();
        
        foreach ($headers as $name => $value) {
            $response->header($name, $value);
        }
        
        return $response;
    }
}
