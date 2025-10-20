<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait SecurityValidationTrait
{
    /**
     * Validate input for dangerous patterns to prevent SQL injection and code injection
     */
    protected function validateSecureInput(Request $request)
    {
        $dangerousPatterns = $this->getDangerousPatterns();

        // Check all input fields for dangerous patterns
        foreach ($request->all() as $key => $value) {
            if ($key !== '_token' && is_string($value) && !empty($value)) {
                foreach ($dangerousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        \Log::warning('Dangerous pattern detected in authentication form', [
                            'field' => $key,
                            'pattern' => $pattern,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'controller' => get_class($this),
                        ]);
                        
                        abort(422, 'Invalid input detected. Please use only standard alphanumeric characters.');
                    }
                }

                // Check for excessive special characters (potential obfuscation)
                $specialCharCount = preg_match_all('/[^a-zA-Z0-9@._\-\s]/', $value);
                if ($specialCharCount > strlen($value) * 0.3) {
                    \Log::warning('Excessive special characters detected in authentication form', [
                        'field' => $key,
                        'special_char_ratio' => $specialCharCount / strlen($value),
                        'ip' => $request->ip(),
                        'controller' => get_class($this),
                    ]);
                    
                    abort(422, 'Input contains too many special characters.');
                }
            }
        }
    }

    /**
     * Check if input contains dangerous patterns
     */
    protected function containsDangerousPatterns($value)
    {
        if (empty($value)) {
            return false;
        }

        $dangerousPatterns = $this->getDangerousPatterns();

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get array of dangerous patterns for validation
     */
    private function getDangerousPatterns()
    {
        return [
            // TypeScript/JavaScript patterns
            '/\bfunction\s*\(/i',
            '/\bvar\s+/i',
            '/\blet\s+/i',
            '/\bconst\s+/i',
            '/\bclass\s+/i',
            '/\binterface\s+/i',
            '/\btype\s+/i',
            '/\bnamespace\s+/i',
            '/\bimport\s+/i',
            '/\bexport\s+/i',
            '/\brequire\s*\(/i',
            '/\bconsole\./i',
            '/\balert\s*\(/i',
            '/\beval\s*\(/i',
            '/\bsetTimeout\s*\(/i',
            '/\bsetInterval\s*\(/i',
            // SQL injection patterns
            '/\bunion\s+select/i',
            '/\bselect\s+.*\bfrom\s+/i',
            '/\binsert\s+into/i',
            '/\bupdate\s+.*\bset\s+/i',
            '/\bdelete\s+from/i',
            '/\bdrop\s+table/i',
            '/\balter\s+table/i',
            '/\bcreate\s+table/i',
            '/\btruncate\s+table/i',
            '/\bexec\s*\(/i',
            '/\bexecute\s*\(/i',
            // Script tags and HTML
            '/<script[^>]*>/i',
            '/<\/script>/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/<link[^>]*>/i',
            '/<meta[^>]*>/i',
            // PHP patterns
            '/<\?php/i',
            '/<\?=/i',
            '/\bphp:/i',
            // Command injection
            '/\bsystem\s*\(/i',
            '/\bexec\s*\(/i',
            '/\bshell_exec\s*\(/i',
            '/\bpassthru\s*\(/i',
            // Other dangerous patterns
            '/javascript:/i',
            '/vbscript:/i',
            '/data:text\/html/i',
            '/\bon\w+\s*=/i', // event handlers like onclick=
            '/\\\x[0-9a-f]{2}/i', // hex encoding
            '/\\\u[0-9a-f]{4}/i', // unicode encoding
        ];
    }

    /**
     * Get secure validation rules for common authentication fields
     */
    protected function getSecureValidationRules()
    {
        return [
            'username' => [
                'nullable',
                'string',
                'max:50',
                'min:3',
                'regex:/^[a-zA-Z0-9_-]+$/',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in username.');
                    }
                    // Additional security checks
                    if ($value && $this->isSuspiciousInput($value)) {
                        $fail('Suspicious input pattern detected.');
                    }
                },
            ],
            'ms365_account' => [
                'nullable',
                'email',
                'max:100',
                'min:10',
                'regex:/^[a-zA-Z0-9._%+-]+@.*\.edu\.ph$/',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in email address.');
                    }
                    // Validate email format more strictly
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Invalid email format.');
                    }
                },
            ],
            'gmail_account' => [
                'nullable',
                'email',
                'max:100',
                'min:10',
                'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in email address.');
                    }
                    // Validate email format more strictly
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Invalid email format.');
                    }
                },
            ],
            'password' => [
                'nullable',
                'string',
                'max:255',
                'min:8',
                function ($attribute, $value, $fail) {
                    if ($value && $this->containsDangerousPatterns($value)) {
                        $fail('Invalid characters detected in password.');
                    }
                    // Check for common weak passwords
                    if ($value && $this->isWeakPassword($value)) {
                        $fail('Password is too weak. Please use a stronger password.');
                    }
                    // Check password strength requirements
                    if ($value && !$this->isStrongPassword($value)) {
                        $fail('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.');
                    }
                },
            ],
        ];
    }

    /**
     * Get secure validation messages
     */
    protected function getSecureValidationMessages()
    {
        return [
            'username.regex' => 'Username can only contain letters, numbers, underscores, and hyphens',
            'username.min' => 'Username must be at least 3 characters long',
            'ms365_account.regex' => 'Please enter a valid .edu.ph email address',
            'ms365_account.min' => 'Email address is too short',
            'gmail_account.regex' => 'Please enter a valid Gmail address',
            'gmail_account.min' => 'Email address is too short',
            'password.min' => 'Password must be at least 8 characters long',
        ];
    }

    /**
     * Check if input contains suspicious patterns
     */
    protected function isSuspiciousInput($value)
    {
        // Check for repeated characters (potential DoS)
        if (preg_match('/(.)\1{10,}/', $value)) {
            return true;
        }
        
        // Check for excessive length variations
        if (strlen($value) > 1000) {
            return true;
        }
        
        // Check for null bytes
        if (strpos($value, '\0') !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if password is weak
     */
    protected function isWeakPassword($password)
    {
        $weakPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey',
            '12345678', 'password1', 'qwerty123', 'admin123', 'letmein123',
            'welcome123', 'monkey123', 'dragon', 'master', 'hello',
            'login', 'princess', 'rockyou', '123123', '12345',
            '1234', '123', '111111', '000000', '666666',
            '888888', '123321', '654321', '987654', '1234567',
            'qwertyuiop', 'asdfghjkl', 'zxcvbnm', 'iloveyou', 'sunshine',
            'superman', 'batman', 'football', 'baseball', 'basketball'
        ];
        
        return in_array(strtolower($password), $weakPasswords);
    }

    /**
     * Check if password meets strength requirements
     */
    protected function isStrongPassword($password)
    {
        if (empty($password) || strlen($password) < 8) {
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Check for at least one special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }

        // Check for repeated characters (more than 3 in a row)
        if (preg_match('/(.)\1{3,}/', $password)) {
            return false;
        }

        // Check for sequential characters - but allow if mixed with other characters
        if (preg_match('/(?:012|123|234|345|456|567|678|789|890)/', $password) && 
            !preg_match('/[a-zA-Z]/', $password)) {
            return false;
        }
        
        if (preg_match('/(?:abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', $password) && 
            !preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove null bytes
        $value = str_replace('\0', '', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Limit length to prevent DoS
        $value = substr($value, 0, 1000);
        
        return $value;
    }

    /**
     * Escape output for HTML context
     */
    protected function escapeHtml($value)
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape output for JavaScript context
     */
    protected function escapeJavaScript($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Validate and sanitize file upload
     */
    protected function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'])
    {
        if (!$file || !$file->isValid()) {
            return ['valid' => false, 'error' => 'Invalid file upload.'];
        }
        
        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return ['valid' => false, 'error' => 'File size too large. Maximum 5MB allowed.'];
        }
        
        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedTypes)) {
            return ['valid' => false, 'error' => 'File type not allowed.'];
        }
        
        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'application/pdf'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Invalid file type.'];
        }
        
        // Generate safe filename
        $filename = uniqid() . '.' . $extension;
        
        return [
            'valid' => true, 
            'filename' => $filename,
            'path' => 'uploads/' . $filename
        ];
    }
}
