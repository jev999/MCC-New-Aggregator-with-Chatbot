<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    protected $secret;
    protected $client;
    protected $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct()
    {
        // Use existing config keys from config/services.php
        $this->secret = config('services.recaptcha.secret_key');
        $this->client = new Client(['timeout' => 5]);
    }

    /**
     * Verifies a token, returns array: success, score, action, hostname, challenge_ts, raw
     */
    public function verify(string $token, ?string $remoteIp = null): array
    {
        try {
            $res = $this->client->post($this->verifyUrl, [
                'form_params' => [
                    'secret' => $this->secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ],
            ]);

            $body = json_decode((string) $res->getBody(), true);

            return [
                'success' => $body['success'] ?? false,
                'score' => isset($body['score']) ? (float) $body['score'] : null,
                'action' => $body['action'] ?? null,
                'hostname' => $body['hostname'] ?? null,
                'challenge_ts' => $body['challenge_ts'] ?? null,
                'raw' => $body,
            ];
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verify error: ' . $e->getMessage());
            return [
                'success' => false,
                'score' => null,
                'action' => null,
                'hostname' => null,
                'challenge_ts' => null,
                'raw' => ['exception' => $e->getMessage()],
            ];
        }
    }
}


