# Data Security and Privacy Implementation

## Overview
This document outlines the comprehensive data security and privacy measures implemented in the MCC-NAC Laravel application to ensure compliance with the Data Privacy Act of 2012 (DPA) and international security best practices.

## 🔒 Security Features Implemented

### 1. HTTPS/TLS Enforcement
- **Force HTTPS Middleware**: Automatically redirects all HTTP traffic to HTTPS in production
- **Security Headers**: Comprehensive security headers including HSTS, CSP, and XSS protection
- **TLS Configuration**: Strong encryption for all data in transit

### 2. Data Encryption at Rest
- **Sensitive Field Encryption**: Email addresses and usernames are encrypted using AES-256-CBC
- **Automatic Encryption/Decryption**: Model events handle encryption transparently
- **Key Management**: Laravel's built-in encryption key management

### 3. Data Minimization
- **Minimal Data Collection**: Only necessary data is collected and stored
- **Automated Purging**: Regular cleanup of old, unnecessary data
- **Retention Policies**: Defined retention periods for different data types

### 4. Privacy Compliance
- **DPA Compliance**: Full compliance with Data Privacy Act of 2012
- **Terms and Conditions**: Comprehensive legal pages
- **Privacy Policy**: Detailed data collection and usage policies
- **User Rights**: Implementation of data subject rights

## 📋 Implementation Details

### Security Middleware
```php
// Force HTTPS in production
ForceHttps::class

// Security headers
SecurityHeaders::class
```

### Data Encryption Service
```php
// Encrypt sensitive data
DataEncryptionService::encrypt($data)

// Decrypt sensitive data
DataEncryptionService::decrypt($encryptedData)
```

### Data Purging Service
```php
// Automated data purging
DataPurgingService::purgeOldData()

// Get retention statistics
DataPurgingService::getRetentionStats()
```

### Console Commands
```bash
# Purge old data manually
php artisan data:purge

# Dry run to see what would be purged
php artisan data:purge --dry-run
```

## 🛡️ Security Headers Implemented

| Header | Value | Purpose |
|--------|-------|---------|
| X-Content-Type-Options | nosniff | Prevents MIME type sniffing |
| X-Frame-Options | DENY | Prevents clickjacking |
| X-XSS-Protection | 1; mode=block | XSS protection |
| Strict-Transport-Security | max-age=31536000 | Forces HTTPS |
| Content-Security-Policy | [CSP Policy] | Prevents code injection |
| Referrer-Policy | strict-origin-when-cross-origin | Controls referrer information |
| Permissions-Policy | geolocation=(), microphone=(), camera=() | Restricts browser features |

## 🔐 Data Encryption

### Encrypted Fields
- **User Model**: `ms365_account`, `gmail_account`
- **Admin Model**: `username`

### Encryption Process
1. Data is automatically encrypted when saving to database
2. Data is automatically decrypted when retrieving from database
3. Failed decryption attempts are logged for monitoring

## 📊 Data Retention Policies

| Data Type | Retention Period | Purge Condition |
|-----------|------------------|-----------------|
| Notifications | 90 days | Read notifications only |
| Sessions | 30 days | Inactive sessions |
| Password Reset Tokens | 24 hours | Expired tokens |
| Inactive Users | 2 years | No email verification + no activity |
| Security Logs | 1 year | All security events |

## 🔄 Automated Data Purging

### Scheduled Tasks
- **Daily at 2:00 AM**: Automated data purging
- **Manual Execution**: Available via console command
- **Dry Run Mode**: Test purging without actual deletion

### Purging Process
1. Old notifications (>90 days, read only)
2. Orphaned comments (parent content deleted)
3. Inactive users (>2 years, no activity)
4. Expired password reset tokens
5. Old session data (>30 days)

## 📜 Legal Compliance

### Terms and Conditions
- Comprehensive terms covering platform usage
- User responsibilities and prohibited uses
- Intellectual property rights
- Limitation of liability

### Privacy Policy
- Detailed data collection practices
- Data usage and sharing policies
- User rights under DPA
- Contact information for privacy inquiries

### Data Privacy Act Compliance
- **Right to Information**: Users informed about data collection
- **Right to Access**: Users can request their data
- **Right to Correction**: Users can correct inaccurate data
- **Right to Erasure**: Users can request data deletion
- **Right to Object**: Users can object to data processing
- **Right to Data Portability**: Users can export their data
- **Right to Withdraw Consent**: Users can withdraw consent

## 🚀 Deployment Checklist

### Production Environment
- [ ] Enable HTTPS enforcement
- [ ] Configure security headers
- [ ] Set up automated data purging
- [ ] Enable data encryption
- [ ] Configure session security
- [ ] Set up monitoring and logging

### Environment Variables
```env
# Security Configuration
FORCE_HTTPS=true
SESSION_SECURE=true
COOKIE_SECURE=true
APP_ENV=production
APP_DEBUG=false
```

## 📈 Monitoring and Logging

### Security Events Logged
- Failed login attempts
- Data encryption/decryption failures
- Suspicious activity patterns
- Data purging operations
- Privacy policy access

### Monitoring Features
- Rate limiting for login attempts
- Alert thresholds for suspicious activity
- Admin notifications for security events
- Comprehensive audit logs

## 🔧 Maintenance

### Regular Tasks
1. **Weekly**: Review security logs
2. **Monthly**: Test data purging process
3. **Quarterly**: Review and update privacy policies
4. **Annually**: Security audit and penetration testing

### Command Line Tools
```bash
# Check security configuration
php artisan config:show security

# Purge old data
php artisan data:purge

# Clear security caches
php artisan config:clear
php artisan cache:clear
```

## 📞 Support and Contact

### Data Protection Officer
- **Email**: privacy@mcc-nac.edu.ph
- **Phone**: [Contact Number]
- **Address**: Mabini Colleges of the Philippines - Nueva Ecija Academic Center

### Privacy Inquiries
For privacy-related questions or to exercise your rights under the Data Privacy Act, contact our Data Protection Officer.

## 📚 Additional Resources

- [Data Privacy Act of 2012](https://privacy.gov.ph/)
- [National Privacy Commission](https://privacy.gov.ph/)
- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Security Guidelines](https://owasp.org/)

---

**Last Updated**: {{ date('F d, Y') }}  
**Version**: 1.0  
**Compliance**: Data Privacy Act of 2012 (Republic Act No. 10173)
