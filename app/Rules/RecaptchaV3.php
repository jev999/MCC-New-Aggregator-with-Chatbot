<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaV3 implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip validation if reCAPTCHA is not configured
        if (!config('services.recaptcha.secret_key')) {
            Log::warning('reCAPTCHA v3 validation skipped: Secret key not configured');
            return;
        }

        try {
            // Make request to Google's reCAPTCHA verification endpoint
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value, // The token from the client
                'remoteip' => request()->ip(), // Optional, but recommended for accuracy
            ])->json();

            // Log the response for debugging
            Log::info('reCAPTCHA v3 verification response', [
                'success' => $response['success'] ?? false,
                'score' => $response['score'] ?? null,
                'action' => $response['action'] ?? null,
                'hostname' => $response['hostname'] ?? null,
                'error-codes' => $response['error-codes'] ?? [],
            ]);

            // 1. Check if the verification request was successful
            if (!isset($response['success']) || $response['success'] !== true) {
                $errorCodes = $response['error-codes'] ?? ['unknown-error'];
                Log::warning('reCAPTCHA v3 verification failed', [
                    'error_codes' => $errorCodes,
                    'ip' => request()->ip(),
                ]);
                $fail('The reCAPTCHA verification failed. Please try again.');
                return;
            }

            // 2. Check the score against the defined threshold
            $threshold = config('services.recaptcha.threshold', 0.5);
            $score = $response['score'] ?? 0;
            
            if ($score < $threshold) {
                // Low score suggests bot behavior
                Log::warning('reCAPTCHA v3 low score detected', [
                    'score' => $score,
                    'threshold' => $threshold,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                $fail('Your request was identified as suspicious. Please try again later.');
                return;
            }

            // 3. Verify the action name (Optional, but recommended)
            $expectedAction = 'login';
            if (isset($response['action']) && $response['action'] !== $expectedAction) {
                Log::warning('reCAPTCHA v3 action mismatch', [
                    'expected' => $expectedAction,
                    'received' => $response['action'],
                    'ip' => request()->ip(),
                ]);
                $fail('reCAPTCHA action mismatch.');
                return;
            }

            // Log successful verification
            Log::info('reCAPTCHA v3 verification successful', [
                'score' => $score,
                'threshold' => $threshold,
                'ip' => request()->ip(),
            ]);

        } catch (\Exception $e) {
            // Log the exception but don't fail validation to prevent blocking legitimate users
            Log::error('reCAPTCHA v3 verification exception', [
                'message' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            
            // In production, you might want to fail here for security
            // For now, we'll log and continue
            if (config('app.env') === 'production') {
                $fail('Unable to verify reCAPTCHA. Please try again.');
            }
        }
    }
}
