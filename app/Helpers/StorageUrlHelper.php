<?php

if (!function_exists('storage_asset')) {
    /**
     * Generate a publicly accessible URL for a file stored on the public disk.
     * Falls back to the legacy /storage URL if route generation fails.
     */
    function storage_asset(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        try {
            return route('storage.serve', ['path' => $cleanPath]);
        } catch (\Throwable $e) {
            return asset('storage/' . $cleanPath);
        }
    }
}

