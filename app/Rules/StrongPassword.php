<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return false;
        }

        // Check minimum length (8 characters)
        if (strlen($value) < 8) {
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            return false;
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $value)) {
            return false;
        }

        // Check for at least one special character
        if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
            return false;
        }

        // Check against common weak passwords
        $weakPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey',
            '12345678', 'password1', 'qwerty123', 'admin123', 'letmein123',
            'welcome123', 'monkey123', 'dragon', 'master', 'hello',
            'login', 'princess', 'rockyou', '123123', '12345',
            '1234', '123', '111111', '000000', '666666',
            '888888', '123321', '654321', '987654', '1234567',
            'qwertyuiop', 'asdfghjkl', 'zxcvbnm', 'iloveyou', 'sunshine',
            'superman', 'batman', 'football', 'baseball', 'basketball',
            'hockey', 'tennis', 'soccer', 'golf', 'swimming',
            'running', 'cycling', 'skiing', 'snowboarding', 'surfing'
        ];

        if (in_array(strtolower($value), $weakPasswords)) {
            return false;
        }

        // Check for repeated characters (more than 3 in a row)
        if (preg_match('/(.)\1{3,}/', $value)) {
            return false;
        }

        // Check for sequential characters (like 123, abc, etc.) - but allow if mixed with other characters
        if (preg_match('/(?:012|123|234|345|456|567|678|789|890)/', $value) && 
            !preg_match('/[a-zA-Z]/', $value)) {
            return false;
        }
        
        if (preg_match('/(?:abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', $value) && 
            !preg_match('/[0-9]/', $value)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character. It cannot be a common weak password or contain repeated or sequential characters.';
    }
}
