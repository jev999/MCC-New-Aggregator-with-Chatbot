<?php

if (!function_exists('csp_nonce')) {
    /**
     * Get or generate a CSP nonce for the current request
     *
     * @return string
     */
    function csp_nonce(): string
    {
        // Check if session is available
        if (!session()) {
            return base64_encode(random_bytes(16));
        }

        // Try to get existing nonce from session
        if (!session()->has('csp_nonce')) {
            // Generate a new cryptographically secure nonce
            $nonce = base64_encode(random_bytes(16));
            session()->put('csp_nonce', $nonce);
        }

        return session()->get('csp_nonce');
    }
}

if (!function_exists('csp_meta')) {
    /**
     * Generate CSP meta tag for the current page
     *
     * @return string
     */
    function csp_meta(): string
    {
        $nonce = csp_nonce();
        return "<meta property=\"csp-nonce\" content=\"{$nonce}\">";
    }
}
