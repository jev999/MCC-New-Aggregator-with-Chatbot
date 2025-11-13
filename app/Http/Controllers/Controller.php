<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Resolve the most accurate client IP address, respecting proxy headers.
     */
    protected function resolveClientIp(Request $request): string
    {
        $headersToInspect = [
            'CF-Connecting-IP',
            'True-Client-IP',
            'X-Forwarded-For',
            'X-Real-IP',
            'X-Client-IP',
            'X-Cluster-Client-IP',
            'Forwarded',
        ];

        foreach ($headersToInspect as $header) {
            $values = $request->headers->get($header);
            if (!$values) {
                continue;
            }

            $candidates = $this->extractIpCandidates($header, $values);

            foreach ($candidates as $candidate) {
                if ($this->isValidPublicIp($candidate)) {
                    return $candidate;
                }
            }
        }

        // Fall back to Laravel's detected client IP (may still be proxy IP when misconfigured)
        $fallback = $request->getClientIp();
        return is_string($fallback) ? $fallback : '0.0.0.0';
    }

    /**
     * Extract potential IPs from a header value.
     *
     * @return array<int, string>
     */
    private function extractIpCandidates(string $header, string $value): array
    {
        if (strcasecmp($header, 'Forwarded') === 0) {
            // RFC 7239 Forwarded header: extract the "for" parameter(s)
            preg_match_all('/for="?([^;"]+)"?/i', $value, $matches);
            return array_map([$this, 'sanitizeIpValue'], $matches[1] ?? []);
        }

        // Most proxy headers are comma-separated lists
        $parts = array_map('trim', explode(',', $value));
        return array_map([$this, 'sanitizeIpValue'], $parts);
    }

    /**
     * Ensure the value is in plain IP format without ports.
     */
    private function sanitizeIpValue(string $value): string
    {
        $value = trim($value, " \t\n\r\0\x0B\"");

        // Remove port if present (e.g., 192.0.2.1:1234)
        if (str_contains($value, ':') && str_contains($value, '.')) {
            // Could be IPv6 or IPv4 with port. Attempt to split on last colon for IPv4 with port.
            $lastColon = strrpos($value, ':');
            if ($lastColon !== false) {
                $maybeIp = substr($value, 0, $lastColon);
                $maybePort = substr($value, $lastColon + 1);
                if (ctype_digit($maybePort)) {
                    return $maybeIp;
                }
            }
        }

        // Handle IPv6 with port "[2001:db8::1]:1234"
        if (preg_match('/^\[(.*)\]:(\d+)$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    /**
     * Determine whether the IP is a valid public address.
     */
    private function isValidPublicIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
