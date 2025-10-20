# Secure Server Configuration Guide

## Overview
This document provides comprehensive server security configuration for the Laravel application, including Apache/Nginx settings, file permissions, and security hardening measures.

## 1. Apache Configuration (.htaccess)

### Current Security Features Implemented:
- ✅ Directory listing disabled (`Options -Indexes`)
- ✅ HTTPS enforcement with redirects
- ✅ Security headers (CSP, HSTS, X-Frame-Options, etc.)
- ✅ Sensitive file access denied (.env, composer files, etc.)
- ✅ Directory access restrictions
- ✅ PHP execution disabled in uploads
- ✅ Server signature hidden

### Additional Apache Security Settings

#### For Apache Virtual Host Configuration:
```apache
# Disable server signature
ServerSignature Off
ServerTokens Prod

# Disable directory browsing
Options -Indexes -ExecCGI -Includes -MultiViews

# Disable server status and info
<Location "/server-status">
    Require all denied
</Location>

<Location "/server-info">
    Require all denied
</Location>

# Disable PHP execution in uploads
<Directory "/path/to/your/app/storage/app/public/uploads">
    <FilesMatch "\.php$">
        Require all denied
    </FilesMatch>
</Directory>

# Limit request size
LimitRequestBody 52428800  # 50MB

# Disable TRACE method
TraceEnable Off

# Hide PHP version
Header unset X-Powered-By
```

## 2. Nginx Configuration (Alternative)

If using Nginx, add these security directives:

```nginx
# Hide server version
server_tokens off;

# Disable directory listing
autoindex off;

# Security headers
add_header X-Frame-Options "DENY" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

# Deny access to sensitive files
location ~ /\. {
    deny all;
}

location ~ \.(env|log|sql|md|ini|conf|config)$ {
    deny all;
}

location ~ \.(bak|backup|old|orig|save|swp|tmp)$ {
    deny all;
}

# Deny access to directories
location ~ ^/(vendor|storage|database|tests|app|config|resources|routes|bootstrap)/ {
    deny all;
}

# Allow only storage/app/public
location ~ ^/storage/app/public/ {
    try_files $uri $uri/ /index.php?$query_string;
}

# Disable PHP execution in uploads
location ~ ^/storage/app/public/uploads/.*\.php$ {
    deny all;
}
```

## 3. File Permissions

### Recommended File Permissions:

```bash
# Set directory permissions (755)
find . -type d -exec chmod 755 {} \;

# Set file permissions (644)
find . -type f -exec chmod 644 {} \;

# Special permissions for sensitive files
chmod 600 .env
chmod 600 .env.production
chmod 600 config/database.php
chmod 600 config/app.php

# Storage and cache directories (775)
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Vendor directory (755)
chmod -R 755 vendor/

# Public directory (755)
chmod -R 755 public/
```

### Windows File Permissions (PowerShell):
```powershell
# Set restrictive permissions for .env file
icacls .env /inheritance:r /grant:r "Administrators:(F)" /grant:r "SYSTEM:(F)"

# Set permissions for storage directory
icacls storage /inheritance:r /grant:r "IIS_IUSRS:(OI)(CI)(F)" /grant:r "Administrators:(OI)(CI)(F)"

# Set permissions for vendor directory
icacls vendor /inheritance:r /grant:r "Administrators:(OI)(CI)(RX)" /grant:r "SYSTEM:(OI)(CI)(RX)"
```

## 4. PHP Security Configuration

### php.ini Security Settings:
```ini
# Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

# Hide PHP version
expose_php = Off

# Disable error display in production
display_errors = Off
display_startup_errors = Off
log_errors = On

# Set secure session settings
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

# File upload security
file_uploads = On
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20

# Memory and execution limits
memory_limit = 256M
max_execution_time = 30
max_input_time = 30
```

## 5. Laravel Security Configuration

### Environment Variables (.env):
```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Cache and Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_ENCRYPTION=tls
```

## 6. Database Security

### MySQL Security Settings:
```sql
-- Create dedicated database user
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON your_database.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;

-- Remove test databases
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
FLUSH PRIVILEGES;
```

## 7. SSL/TLS Configuration

### Let's Encrypt with Certbot:
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Obtain SSL certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

## 8. Firewall Configuration

### UFW (Ubuntu):
```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow ssh

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Deny all other traffic
sudo ufw default deny incoming
sudo ufw default allow outgoing
```

### Windows Firewall:
```powershell
# Allow HTTP
New-NetFirewallRule -DisplayName "HTTP" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow

# Allow HTTPS
New-NetFirewallRule -DisplayName "HTTPS" -Direction Inbound -Protocol TCP -LocalPort 443 -Action Allow

# Allow SSH (if needed)
New-NetFirewallRule -DisplayName "SSH" -Direction Inbound -Protocol TCP -LocalPort 22 -Action Allow
```

## 9. Monitoring and Logging

### Security Monitoring:
- Enable Apache/Nginx access and error logs
- Monitor Laravel logs in `storage/logs/`
- Set up log rotation
- Monitor failed login attempts
- Track file upload activities

### Log Rotation Configuration:
```bash
# /etc/logrotate.d/laravel
/var/www/html/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        /bin/kill -USR1 `cat /var/run/nginx.pid 2>/dev/null` 2>/dev/null || true
    endscript
}
```

## 10. Regular Security Maintenance

### Automated Security Tasks:
1. **Daily**: Check for failed login attempts
2. **Weekly**: Review access logs for suspicious activity
3. **Monthly**: Update dependencies with `composer update`
4. **Quarterly**: Security audit and penetration testing

### Security Checklist:
- [ ] All dependencies updated
- [ ] File permissions set correctly
- [ ] SSL certificate valid and auto-renewing
- [ ] Firewall configured and active
- [ ] Database user has minimal privileges
- [ ] Error reporting disabled in production
- [ ] Directory listing disabled
- [ ] Sensitive files protected
- [ ] Security headers implemented
- [ ] Log monitoring active

## 11. Emergency Response

### Incident Response Plan:
1. **Immediate**: Isolate affected systems
2. **Assessment**: Determine scope of compromise
3. **Containment**: Prevent further damage
4. **Recovery**: Restore from clean backups
5. **Lessons Learned**: Update security measures

### Backup Strategy:
- Daily automated database backups
- Weekly full application backups
- Offsite backup storage
- Regular backup restoration testing

## 12. Compliance and Standards

### Security Standards Compliance:
- OWASP Top 10
- PCI DSS (if handling payments)
- GDPR (for EU users)
- Local data protection regulations

### Documentation Requirements:
- Security policy documentation
- Incident response procedures
- User access management
- Data retention policies
- Regular security assessments

---

**Note**: This configuration should be tested in a staging environment before applying to production. Regular security audits and updates are essential for maintaining a secure application.
