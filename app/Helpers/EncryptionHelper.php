<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class EncryptionHelper
{
    /**
     * Encrypt an ID for transmission
     */
    public static function encryptId($id)
    {
        try {
            return Crypt::encryptString((string) $id);
        } catch (\Exception $e) {
            \Log::error('Failed to encrypt ID: ' . $e->getMessage());
            return base64_encode($id);
        }
    }

    /**
     * Decrypt an encrypted ID
     */
    public static function decryptId($encryptedId)
    {
        try {
            return Crypt::decryptString($encryptedId);
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt ID: ' . $e->getMessage());
            return base64_decode($encryptedId);
        }
    }

    /**
     * Encrypt PII data
     */
    public static function encryptPII($data)
    {
        try {
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            \Log::error('Failed to encrypt PII: ' . $e->getMessage());
            return $data;
        }
    }

    /**
     * Decrypt PII data
     */
    public static function decryptPII($encryptedData)
    {
        try {
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt PII: ' . $e->getMessage());
            return $encryptedData;
        }
    }

    /**
     * Generate a random UUID string
     */
    public static function generateUuid()
    {
        return (string) Str::uuid();
    }
}

