# Security Headers Implementation

This document explains the comprehensive security headers implemented in the MCC News Aggregator application.

## Overview

Security headers are HTTP response headers that instruct browsers on how to behave when handling your site's content. They help protect against various attacks including XSS, clickjacking, code injection, and more.

## Implemented Security Headers

### 1. Strict-Transport-Security (HSTS)

**Purpose:** Forces browsers to use HTTPS connections only

**Configuration:**
```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

**Details:**
- `max-age=31536000`: Browsers remember to only use HTTPS for 1 year (31,536,000 seconds)
- `includeSubDomains`: Apply HSTS policy to all subdomains
- `preload`: Allow inclusion in browser HSTS preload lists

**Benefits:**
- Prevents protocol downgrade attacks
- Prevents cookie hijacking
- Automatic HTTPS redirection by browser

**Environment Variables:**
```env
SECURITY_HSTS_ENABLED=true
SECURITY_HSTS_MAX_AGE=31536000
SECURITY_HSTS_INCLUDE_SUBDOMAINS=true
SECURITY_HSTS_PRELOAD=true
```

**Note:** HSTS is only enabled in production with HTTPS to prevent lockout during development.

---

### 2. X-Frame-Options

**Purpose:** Prevents clickjacking attacks by controlling if your site can be framed

**Configuration:**
```
X-Frame-Options: SAMEORIGIN
```

**Values:**
- `DENY`: Site cannot be framed at all
- `SAMEORIGIN`: Site can only be framed by pages on the same origin (recommended)
- `ALLOW-FROM uri`: Site can be framed by specified URI (deprecated)

**Benefits:**
- Prevents clickjacking attacks
- Protects against UI redress attacks
- Prevents malicious sites from embedding your content

**Environment Variable:**
```env
SECURITY_X_FRAME_OPTIONS=SAMEORIGIN
```

---

### 3. X-Content-Type-Options

**Purpose:** Prevents MIME-sniffing and forces declared content-type

**Configuration:**
```
X-Content-Type-Options: nosniff
```

**Details:**
- Only valid value is `nosniff`
- Prevents browsers from interpreting files as a different MIME type
- Forces browsers to respect declared Content-Type

**Benefits:**
- Prevents MIME confusion attacks
- Blocks script execution from non-script MIME types
- Reduces attack surface for XSS

**Environment Variable:**
```env
SECURITY_X_CONTENT_TYPE_OPTIONS=nosniff
```

---

### 4. Referrer-Policy

**Purpose:** Controls how much referrer information is sent with requests

**Configuration:**
```
Referrer-Policy: strict-origin-when-cross-origin
```

**Available Values:**
- `no-referrer`: Never send referrer
- `no-referrer-when-downgrade`: Send only for same security level (HTTPS to HTTPS)
- `origin`: Send only origin (domain)
- `origin-when-cross-origin`: Full URL for same origin, origin only for cross-origin
- `same-origin`: Send only for same origin requests
- `strict-origin`: Send origin only for same security level
- `strict-origin-when-cross-origin`: Full URL for same origin, origin for cross-origin same security (recommended)
- `unsafe-url`: Always send full URL (not recommended)

**Benefits:**
- Protects user privacy
- Prevents sensitive URL information leakage
- Reduces tracking capabilities
- Maintains analytics for same-origin requests

**Environment Variable:**
```env
SECURITY_REFERRER_POLICY=strict-origin-when-cross-origin
```

---

### 5. Permissions-Policy

**Purpose:** Controls which browser features and APIs can be used

**Configuration:**
```
Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), fullscreen=(self), picture-in-picture=(self)
```

**Format:**
- `feature=()`: Feature is completely disabled
- `feature=(self)`: Feature is allowed for same origin only
- `feature=(self "https://example.com")`: Feature allowed for self and specific origin

**Controlled Features:**
- `geolocation`: GPS location access
- `microphone`: Microphone access
- `camera`: Camera access
- `payment`: Payment Request API
- `usb`: USB device access
- `accelerometer`: Accelerometer sensor
- `gyroscope`: Gyroscope sensor
- `magnetometer`: Magnetometer sensor
- `fullscreen`: Fullscreen API
- `picture-in-picture`: Picture-in-Picture API

**Benefits:**
- Reduces attack surface
- Prevents unauthorized access to sensitive APIs
- Protects user privacy
- Controls third-party feature usage

**Environment Variables:**
```env
SECURITY_PERMISSIONS_GEOLOCATION=false
SECURITY_PERMISSIONS_MICROPHONE=false
SECURITY_PERMISSIONS_CAMERA=false
SECURITY_PERMISSIONS_PAYMENT=false
SECURITY_PERMISSIONS_USB=false
SECURITY_PERMISSIONS_ACCELEROMETER=false
SECURITY_PERMISSIONS_GYROSCOPE=false
SECURITY_PERMISSIONS_MAGNETOMETER=false
SECURITY_PERMISSIONS_FULLSCREEN=true
SECURITY_PERMISSIONS_PICTURE_IN_PICTURE=true
```

---

### 6. Content-Security-Policy (CSP)

**Purpose:** Controls which resources can be loaded by your application

**Configuration:** (Example)
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' data: https://fonts.gstatic.com
```

**Common Directives:**
- `default-src`: Default policy for loading content
- `script-src`: Valid sources for JavaScript
- `style-src`: Valid sources for stylesheets
- `img-src`: Valid sources for images
- `font-src`: Valid sources for fonts
- `connect-src`: Valid sources for fetch, XHR, WebSocket
- `frame-src`: Valid sources for frames
- `object-src`: Valid sources for plugins (Flash, etc.)
- `base-uri`: Restricts URLs in `<base>` element
- `form-action`: Valid endpoints for form submissions
- `frame-ancestors`: Valid parents for embedding

**Special Keywords:**
- `'self'`: Same origin as the document
- `'none'`: Block everything
- `'unsafe-inline'`: Allow inline scripts/styles (reduces security)
- `'unsafe-eval'`: Allow eval() and similar (reduces security)
- `data:`: Allow data: URIs
- `https:`: Allow any HTTPS resource

**Benefits:**
- Prevents XSS attacks
- Blocks unauthorized resource loading
- Mitigates data injection attacks
- Controls inline script execution

**Configuration:** See `config/security.php` for full CSP configuration

---

### 7. X-XSS-Protection (Legacy)

**Purpose:** Legacy XSS protection for older browsers

**Configuration:**
```
X-XSS-Protection: 1; mode=block
```

**Values:**
- `0`: Disable XSS filtering
- `1`: Enable XSS filtering (removes unsafe parts)
- `1; mode=block`: Enable XSS filtering (blocks entire page)

**Note:** This is a legacy header. Modern browsers rely on CSP instead. Included for older browser support.

**Environment Variable:**
```env
SECURITY_XSS_PROTECTION=true
```

---

## Additional Security Headers

### X-Permitted-Cross-Domain-Policies

Restricts Adobe Flash and PDF cross-domain policies.
```
X-Permitted-Cross-Domain-Policies: none
```

### X-Download-Options

Prevents Internet Explorer from executing downloads in site's context.
```
X-Download-Options: noopen
```

### Clear-Site-Data

Clears browser data when logging out.
```
Clear-Site-Data: "cache", "cookies", "storage"
```

---

## Testing Security Headers

### Online Tools

1. **Security Headers**: https://securityheaders.com/
   - Scan your site and get a grade (A+ is best)
   - Provides detailed explanations

2. **Mozilla Observatory**: https://observatory.mozilla.org/
   - Comprehensive security analysis
   - Provides actionable recommendations

3. **SSL Labs**: https://www.ssllabs.com/ssltest/
   - Tests SSL/TLS configuration
   - Validates HSTS implementation

### Browser DevTools

1. Open browser DevTools (F12)
2. Go to Network tab
3. Reload the page
4. Click on any request
5. View Response Headers
6. Verify security headers are present

### Command Line Testing

```bash
# Test with curl
curl -I https://yourdomain.com

# Test specific header
curl -I https://yourdomain.com | grep -i "strict-transport-security"
```

---

## Configuration Files

### Main Configuration
**File:** `config/security.php`

Contains all security header configurations with detailed comments.

### Environment Variables
**File:** `.env`

Set environment-specific values for security headers.

### Middleware
**File:** `app/Http/Middleware/SecurityHeaders.php`

Implements the security headers middleware.

### Bootstrap
**File:** `bootstrap/app.php`

Registers the SecurityHeaders middleware globally for web routes.

---

## Best Practices

1. **Start with Report-Only Mode**: When implementing CSP, use `Content-Security-Policy-Report-Only` first
2. **Use HTTPS**: Many security headers only work with HTTPS (especially HSTS)
3. **Test Thoroughly**: Ensure headers don't break legitimate functionality
4. **Monitor Violations**: Set up CSP violation reporting
5. **Keep Updated**: Security recommendations evolve, review headers periodically
6. **Balance Security and Functionality**: Some headers may need relaxation for third-party integrations

---

## Security Header Grades

| Header | Implementation | Grade |
|--------|---------------|-------|
| HSTS | ✅ Implemented | A+ |
| X-Frame-Options | ✅ Implemented | A |
| X-Content-Type-Options | ✅ Implemented | A |
| Referrer-Policy | ✅ Implemented | A |
| Permissions-Policy | ✅ Implemented | A |
| Content-Security-Policy | ✅ Implemented | A |
| X-XSS-Protection | ✅ Implemented | B (Legacy) |

---

## Troubleshooting

### Issue: HSTS not appearing in headers

**Solution:** 
- HSTS only works with HTTPS in production
- Check `APP_ENV=production` in `.env`
- Ensure site is accessed via HTTPS
- Verify `SECURITY_HSTS_ENABLED=true`

### Issue: CSP blocking resources

**Solution:**
- Check browser console for CSP violations
- Add trusted sources to CSP directives in `config/security.php`
- Use `'unsafe-inline'` temporarily for debugging (not recommended for production)

### Issue: X-Frame-Options preventing embedding

**Solution:**
- If you need to embed content, change to `SAMEORIGIN`
- For specific domains, consider using CSP `frame-ancestors` instead

---

## Resources

- [OWASP Secure Headers Project](https://owasp.org/www-project-secure-headers/)
- [MDN Web Docs - HTTP Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers)
- [Content Security Policy Reference](https://content-security-policy.com/)
- [Security Headers Quick Reference](https://securityheaders.com/)

---

## Support

For issues or questions about security headers implementation, contact the development team or refer to the Laravel security documentation.

**Last Updated:** 2025-10-27
**Version:** 1.0.0
