# Database Backup Troubleshooting Guide

## Overview
This guide helps you diagnose and fix issues with the database backup functionality on the MCC News Aggregator portal.

## Error: "Server error. Please check the Laravel logs or contact the administrator."

This error appears when the backup creation fails. Here are the common causes and solutions:

---

## Quick Fix Steps (Most Common Issues)

### Step 1: Run the Permission Fix Script

On your production server (via SSH):

```bash
cd /path/to/your/project
chmod +x fix-backup-permissions.sh
./fix-backup-permissions.sh
```

Or manually:

```bash
cd /path/to/your/project
mkdir -p storage/app/backups
mkdir -p storage/app/backup-temp
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Step 2: Run the Diagnostic Test

Visit this URL in your browser while logged in as a superadmin:
```
https://mcc-nac.com/superadmin/backup/test
```

This will show you detailed information about:
- Authentication status
- PHP configuration
- Directory permissions
- Database connection
- Disk space
- Any detected issues

---

## Common Issues and Solutions

### 1. Directory Permission Issues

**Problem:** The backup directory doesn't exist or is not writable.

**Symptoms:**
- Error message about directory permissions
- Backup creation fails silently

**Solution:**

```bash
# Create directories
mkdir -p storage/app/backups
mkdir -p storage/app/backup-temp

# Set permissions
chmod -R 775 storage/app/backups
chmod -R 775 storage/app/backup-temp

# Set ownership (replace 'www-data' with your web server user)
sudo chown -R www-data:www-data storage/app/backups
sudo chown -R www-data:www-data storage/app/backup-temp
```

**How to find your web server user:**
```bash
ps aux | grep -E 'apache|httpd|nginx|www-data' | grep -v root | head -1 | awk '{print $1}'
```

Common web server users:
- Ubuntu/Debian: `www-data`
- CentOS/RHEL: `apache` or `nginx`
- cPanel: Your cPanel username

### 2. SELinux Issues (CentOS/RHEL/AlmaLinux)

**Problem:** SELinux is blocking write access.

**Check if SELinux is enabled:**
```bash
getenforce
```

**Solution:**
```bash
# Set proper SELinux context
sudo chcon -R -t httpd_sys_rw_content_t storage/app/backups
sudo chcon -R -t httpd_sys_rw_content_t storage/app/backup-temp
sudo chcon -R -t httpd_sys_rw_content_t storage/logs

# Make it persistent
sudo semanage fcontext -a -t httpd_sys_rw_content_t "$(pwd)/storage/app/backups(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "$(pwd)/storage/app/backup-temp(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "$(pwd)/storage/logs(/.*)?"
sudo restorecon -Rv storage/
```

### 3. PHP Memory/Timeout Limits

**Problem:** Large databases cause PHP to run out of memory or time out.

**Current Implementation:**
The controller now automatically increases limits:
- Memory: 512M
- Execution time: 300 seconds (5 minutes)

**If still failing, increase server limits:**

Edit `php.ini`:
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
```

Or in `.htaccess`:
```apache
php_value memory_limit 512M
php_value max_execution_time 300
```

**Restart PHP after changes:**
```bash
# For PHP-FPM
sudo systemctl restart php-fpm

# For Apache
sudo systemctl restart apache2  # Debian/Ubuntu
sudo systemctl restart httpd    # CentOS/RHEL
```

### 4. Database Connection Issues

**Problem:** Cannot connect to database.

**Check your `.env` file:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**Test connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

If successful, you'll see: `PDO {#...}`

**Common issues:**
- Wrong credentials
- Database server not running
- Firewall blocking connection
- Wrong host (use 127.0.0.1 instead of localhost or vice versa)

### 5. Disk Space Issues

**Problem:** Not enough disk space to create backup.

**Check disk space:**
```bash
df -h
```

**Solution:**
- Free up disk space
- Remove old backups
- Clean up logs: `php artisan log:clear`

### 6. Missing mysqldump

**Problem:** mysqldump not available (this is optional, Laravel has a fallback).

**Check if mysqldump is installed:**
```bash
which mysqldump
```

**Install if needed:**
```bash
# Ubuntu/Debian
sudo apt-get install mysql-client

# CentOS/RHEL
sudo yum install mysql
```

### 7. Hostinger/Shared Hosting Specific Issues

**Common issues on shared hosting:**

1. **Open BaseDir Restriction:**
   - Contact support to add storage paths to open_basedir
   - Or disable: `php_admin_value open_basedir none`

2. **Disabled Functions:**
   - Check if `exec`, `shell_exec` are disabled
   - Laravel fallback method doesn't use these

3. **File Manager Access:**
   - Use File Manager to set permissions to 755 or 775
   - Right-click folder → Change Permissions

4. **.htaccess issues:**
   - Ensure .htaccess allows PHP directives
   - Check for conflicting rules

---

## Checking Laravel Logs

**View recent errors:**
```bash
tail -n 100 storage/logs/laravel.log
```

**Search for backup-related errors:**
```bash
grep -i "backup" storage/logs/laravel.log | tail -n 50
```

**Common error patterns to look for:**
- `Permission denied`
- `Failed to create backup directory`
- `is not writable`
- `SQLSTATE` (database errors)
- `Maximum execution time`
- `Allowed memory size`

---

## Testing the Fix

### Method 1: Use the Diagnostic Endpoint
Visit: `https://mcc-nac.com/superadmin/backup/test`

This will show you:
```json
{
  "status": "OK",
  "authenticated": true,
  "is_superadmin": true,
  "php": {
    "version": "8.x.x",
    "memory_limit": "512M",
    ...
  },
  "directories": {
    "backup_path": "/path/to/storage/app/backups",
    "exists": true,
    "is_writable": true,
    ...
  },
  "database": {
    "connected": true,
    "table_count": 54
  },
  "issues": []
}
```

**If `status` is "ISSUES_FOUND"**, check the `issues` array for specific problems.

### Method 2: Try Creating a Backup
1. Go to: `https://mcc-nac.com/superadmin/backup`
2. Click "Create Backup Now"
3. Check the result

### Method 3: Check Logs
```bash
tail -f storage/logs/laravel.log
```
Then try creating a backup and watch for errors.

---

## Production Deployment Checklist

When deploying to production, always:

- [ ] Run `./fix-backup-permissions.sh`
- [ ] Test backup creation in production
- [ ] Verify `.env` database credentials
- [ ] Check disk space (at least 1GB free recommended)
- [ ] Ensure proper file ownership
- [ ] Configure SELinux if applicable
- [ ] Test the diagnostic endpoint
- [ ] Set up automated backups (optional)

---

## Automated Backups (Optional)

### Set up a Cron Job

Add to crontab:
```bash
# Daily backup at 2 AM
0 2 * * * cd /path/to/project && php artisan backup:run
```

Or for the custom backup controller:
```bash
0 2 * * * curl -X POST https://mcc-nac.com/superadmin/backup/create \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

---

## Getting Help

If you're still experiencing issues after following this guide:

1. **Check the diagnostic endpoint output**
2. **Review Laravel logs** (`storage/logs/laravel.log`)
3. **Check PHP error logs** (location varies by server)
4. **Contact your hosting support** with:
   - The error message
   - Laravel log excerpts
   - Diagnostic endpoint output
   - PHP version and hosting environment

---

## Technical Details

### What the Backup Does

1. **Connects to the database** using credentials from `.env`
2. **Checks directory permissions** for `storage/app/backups`
3. **Attempts mysqldump** (if available)
4. **Falls back to Laravel method** if mysqldump fails
5. **Creates SQL file** with all tables and data
6. **Stores in** `storage/app/backups/`

### Backup File Format

Files are named: `backup_YYYY-MM-DD_HH-ii-ss.sql`

Example: `backup_2024-11-10_09-30-45.sql`

### Fallback Method

The system has two backup methods:
1. **Primary:** mysqldump (faster, more reliable)
2. **Fallback:** Laravel DB queries (works everywhere)

If mysqldump fails or isn't available, it automatically uses the fallback method.

---

## Security Notes

- Backup files contain **sensitive data**
- Keep `storage/app/backups/` protected (not web-accessible)
- Download and store backups securely
- Delete old backups regularly
- Never commit backups to Git
- Restrict backup page to superadmins only

---

## Quick Reference Commands

```bash
# Check permissions
ls -la storage/app/backups

# Fix permissions
chmod -R 775 storage/app/backups
sudo chown -R www-data:www-data storage/app/backups

# Check disk space
df -h

# View logs
tail -f storage/logs/laravel.log

# Test database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"

# Check SELinux
getenforce

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

**Last Updated:** November 10, 2024
**Version:** 1.0
