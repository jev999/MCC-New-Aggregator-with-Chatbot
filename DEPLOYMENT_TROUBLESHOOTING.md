# HTTP 500 Error - Production Deployment Troubleshooting Guide

## 🔴 **Common Causes of HTTP 500 Errors**

### **1. Database Connection Issues (Most Common)**
**Error**: `SQLSTATE[HY000] [2002] Connection refused`

**Solution**:
```bash
# 1. Check your .env file on production server
nano .env

# 2. Ensure DB_HOST points to your remote database (NOT 127.0.0.1)
DB_HOST=your_remote_db_host_from_provider
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 3. Clear Laravel cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### **2. Missing Apache Modules (mod_rewrite)**

**Solution**:
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Enable mod_headers (for security headers)
sudo a2enmod headers

# Restart Apache
sudo systemctl restart apache2
# OR
sudo service apache2 restart
```

**Check if mod_rewrite is enabled**:
```bash
apache2ctl -M | grep rewrite
```

### **3. .htaccess Not Being Processed**

**Check AllowOverride** in Apache config:
```apache
<Directory /var/www/html>
    Options Indexes FollowSymLinks
    AllowOverride All  # ← Must be "All" not "None"
    Require all granted
</Directory>
```

**Location**: Usually in `/etc/apache2/sites-available/000-default.conf` or your site's config file.

**After editing**:
```bash
sudo apache2ctl configtest  # Check for syntax errors
sudo systemctl restart apache2
```

### **4. File Permissions**

**Fix permissions**:
```bash
# Set correct ownership
sudo chown -R www-data:www-data /path/to/your/project

# Set correct permissions
sudo find /path/to/your/project -type f -exec chmod 644 {} \;
sudo find /path/to/your/project -type d -exec chmod 755 {} \;

# Storage and cache directories need write access
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### **5. Missing APP_KEY**

**Generate application key**:
```bash
php artisan key:generate
```

### **6. Storage Link Missing**

**Create symbolic link**:
```bash
php artisan storage:link
```

### **7. PHP Version Issues**

**Check PHP version** (need 8.1+):
```bash
php -v
```

**Switch PHP version** (if needed):
```bash
# For Apache with php-fpm
sudo a2enmod php8.1
sudo a2dismod php8.0
sudo systemctl restart apache2

# Or update to PHP 8.2
sudo a2enmod php8.2
sudo systemctl restart apache2
```

### **8. Composer Autoload Issues**

**Regenerate autoload files**:
```bash
composer dump-autoload
```

### **9. Insufficient Memory Limit**

**Check PHP memory limit**:
```bash
php -i | grep memory_limit
```

**Increase in php.ini** (usually `/etc/php/8.x/apache2/php.ini`):
```ini
memory_limit = 256M
```

**Restart Apache** after changing php.ini:
```bash
sudo systemctl restart apache2
```

## 🔍 **Step-by-Step Debugging Process**

### **Step 1: Enable Detailed Error Logging**

**In `.env`**:
```env
APP_DEBUG=true  # Temporarily enable for debugging
```

**In `config/app.php`**:
```php
'log_level' => 'debug',
```

**Check Laravel logs**:
```bash
tail -f storage/logs/laravel.log
```

### **Step 2: Check Apache Error Log**

**View recent errors**:
```bash
sudo tail -f /var/log/apache2/error.log
```

### **Step 3: Test Database Connection**

**Create test file** `public/test-db.php`:
```php
<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    DB::connection()->getPdo();
    echo "✅ Database connected successfully!";
    echo "<br>Database: " . DB::connection()->getDatabaseName();
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}
```

**Access**: `https://your-domain.com/test-db.php`

### **Step 4: Test Basic Laravel**

**Create test file** `public/test-laravel.php`:
```php
<?php
echo "PHP Version: " . phpversion() . "<br>";
echo "Laravel loaded: " . (class_exists('Illuminate\Support\Facades\Facade') ? 'Yes' : 'No') . "<br>";
phpinfo();
```

**Access**: `https://your-domain.com/test-laravel.php`

## ✅ **Quick Fix Checklist**

```bash
# 1. Enable Apache modules
sudo a2enmod rewrite headers
sudo systemctl restart apache2

# 2. Fix permissions
sudo chown -R www-data:www-data /path/to/project
sudo chmod -R 775 storage bootstrap/cache

# 3. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Generate app key (if missing)
php artisan key:generate

# 5. Create storage link
php artisan storage:link

# 6. Cache configuration (for production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Check .env configuration
php artisan config:show | grep DB_HOST
```

## 🎯 **Production Environment Setup**

**Final `.env` configuration**:
```env
APP_NAME="MCC News Aggregator"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://mcc-nac.com

# Database
DB_CONNECTION=mysql
DB_HOST=your_remote_host
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.mcc-nac.com

# Cache
CACHE_STORE=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

## 📞 **Still Having Issues?**

1. **Check Apache error log**: `/var/log/apache2/error.log`
2. **Check Laravel log**: `storage/logs/laravel.log`
3. **Verify PHP error log**: `/var/log/php/error.log`
4. **Test with minimal files**: Temporarily rename `.htaccess` to see if it's the issue
5. **Contact hosting provider**: Ask about PHP version, mod_rewrite, and AllowOverride settings

## 🚨 **Security Reminder**

After debugging, **remember to**:
- Set `APP_DEBUG=false` in production
- Delete test files (`test-db.php`, `test-laravel.php`)
- Set appropriate file permissions
- Enable HTTPS/SSL certificate
