<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class DataEncryptionService
{
    /**
     * Encrypt sensitive data
     *
     * @param string $data
     * @return string
     */
    public static function encrypt(string $data): string
    {
        try {
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            Log::error('Data encryption failed', [
                'error' => $e->getMessage(),
                'data_length' => strlen($data)
            ]);
            throw $e;
        }
    }

    /**
     * Decrypt sensitive data
     *
     * @param string $encryptedData
     * @return string
     */
    public static function decrypt(string $encryptedData): string
    {
        try {
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            Log::error('Data decryption failed', [
                'error' => $e->getMessage(),
                'encrypted_data_length' => strlen($encryptedData)
            ]);
            throw $e;
        }
    }

    /**
     * Check if data is encrypted
     *
     * @param string $data
     * @return bool
     */
    public static function isEncrypted(string $data): bool
    {
        try {
            // Try to decrypt - if it fails, it's not encrypted
            Crypt::decryptString($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Encrypt array of sensitive fields
     *
     * @param array $data
     * @param array $sensitiveFields
     * @return array
     */
    public static function encryptFields(array $data, array $sensitiveFields): array
    {
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = self::encrypt($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Decrypt array of sensitive fields
     *
     * @param array $data
     * @param array $sensitiveFields
     * @return array
     */
    public static function decryptFields(array $data, array $sensitiveFields): array
    {
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $data[$field] = self::decrypt($data[$field]);
                } catch (\Exception $e) {
                    // If decryption fails, keep original value (might not be encrypted)
                    Log::warning('Failed to decrypt field', [
                        'field' => $field,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        return $data;
    }
}
