<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphService
{
    protected $clientId;
    protected $clientSecret;
    protected $tenantId;
    protected $accessToken;
    protected $senderUpn;

    public function __construct()
    {
        $this->clientId = config('services.microsoft.client_id');
        $this->clientSecret = config('services.microsoft.client_secret');
        $this->tenantId = config('services.microsoft.tenant_id');
        // Prefer config, fallback to env
        $this->senderUpn = config('services.microsoft.sender_upn', env('MS_GRAPH_SENDER_UPN'));
    }

    /**
     * Get access token for Microsoft Graph API
     */
    protected function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        // Check if credentials are configured
        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->tenantId)) {
            Log::warning('Microsoft Graph API credentials not configured');
            return null;
        }

        try {
            $response = Http::asForm()->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['access_token'])) {
                    $this->accessToken = $responseData['access_token'];
                    Log::info('Microsoft Graph access token obtained successfully');
                    return $this->accessToken;
                } else {
                    Log::error('Access token not found in response', ['response' => $responseData]);
                    return null;
                }
            }

            Log::error('Failed to get Microsoft Graph access token', [
                'response' => $response->json(),
                'status' => $response->status(),
                'tenant_id' => $this->tenantId
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Microsoft Graph API error: ' . $e->getMessage(), [
                'tenant_id' => $this->tenantId,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validate if MS365 account exists
     */
    public function validateUser($email)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return false;
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("https://graph.microsoft.com/v1.0/users/{$email}");

            if ($response->successful()) {
                $userData = $response->json();
                return [
                    'exists' => true,
                    'user' => $userData
                ];
            }

            // If user not found (404), account doesn't exist
            if ($response->status() === 404) {
                return [
                    'exists' => false,
                    'error' => 'User not found in Microsoft 365'
                ];
            }

            Log::error('Microsoft Graph API validation error', [
                'email' => $email,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return [
                'exists' => false,
                'error' => 'Unable to validate account'
            ];

        } catch (\Exception $e) {
            Log::error('Microsoft Graph API validation exception: ' . $e->getMessage(), [
                'email' => $email
            ]);

            return [
                'exists' => false,
                'error' => 'Validation service unavailable'
            ];
        }
    }

    /**
     * Send email using Microsoft Graph API (application permissions)
     */
    public function sendEmail($to, $subject, $body, $isHtml = true)
    {
        // Check if required environment variables are set
        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->tenantId)) {
            Log::warning('Microsoft Graph API credentials not configured. Skipping Graph API email send.');
            return false;
        }

        if (empty($this->senderUpn)) {
            Log::warning('MS_GRAPH_SENDER_UPN not configured. Cannot call /users/{sender}/sendMail');
            return false;
        }

        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::warning('Failed to get Microsoft Graph access token. Skipping Graph API email send.');
            return false;
        }

        try {
            $emailData = [
                'message' => [
                    'subject' => $subject,
                    'body' => [
                        'contentType' => $isHtml ? 'HTML' : 'Text',
                        'content' => $body
                    ],
                    'toRecipients' => [
                        [
                            'emailAddress' => [
                                'address' => $to
                            ]
                        ]
                    ]
                ],
                'saveToSentItems' => true
            ];

            // Application flow requires targeting a specific mailbox (not /me)
            $endpoint = "https://graph.microsoft.com/v1.0/users/{$this->senderUpn}/sendMail";

            $response = Http::withToken($accessToken)
                ->post($endpoint, $emailData);

            if ($response->successful()) {
                Log::info('Email sent successfully via Microsoft Graph API', ['to' => $to, 'from' => $this->senderUpn]);
                return true;
            }

            Log::error('Failed to send email via Microsoft Graph', [
                'to' => $to,
                'from' => $this->senderUpn,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Microsoft Graph email sending error: ' . $e->getMessage(), [
                'to' => $to,
                'from' => $this->senderUpn,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }
}