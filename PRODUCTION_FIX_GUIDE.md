# Production Error Fix Guide for mcc-nac.com

## 🔴 **ERROR IDENTIFIED**
**HTTP 500 Error**: Database connection refused
**Cause**: Application trying to connect to `127.0.0.1` (localhost) instead of your remote database

## ✅ **SOLUTION - Follow These Steps on Your Production Server**

### **Step 1: Edit Your .env File on Production Server**

SSH into your production server and edit the `.env` file:

```bash
nano /path/to/your/project/.env
```

### **Step 2: Update Database Configuration**

Find and update these lines:

```env
# CHANGE THIS LINE (likely says 127.0.0.1 or localhost)
DB_HOST=your_remote_database_host_here

# Keep your existing credentials:
DB_DATABASE=u802714156_mccNac
DB_USERNAME=u802714156_MCCnac
DB_PASSWORD=1Mccnac2025
```

**Important**: Replace `your_remote_database_host_here` with your actual remote database host (usually provided by your hosting provider).

### **Step 3: Update Other Critical Settings**

Add or update these settings in your `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mcc-nac.com

# Session Configuration
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Fix for session domain
SESSION_DOMAIN=.mcc-nac.com
```

### **Step 4: Clear All Cache**

After making changes, run these commands on your production server:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```

### **Step 5: Test Database Connection**

Create a test file `test-db-connection.php` in your public directory:

```php
<?php
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful!";
    echo "\nDatabase: " . DB::connection()->getDatabaseName();
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
```

Access it at: `https://mcc-nac.com/test-db-connection.php`

### **Step 6: Common Issues & Fixes**

#### Issue 1: Still getting connection refused
**Solution**: Check if your database host allows remote connections. Contact your hosting provider.

#### Issue 2: Permission denied
**Solution**: 
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Issue 3: App key not set
**Solution**:
```bash
php artisan key:generate
```

#### Issue 4: Storage link missing
**Solution**:
```bash
php artisan storage:link
```

## 🚨 **Quick Check Commands**

Run these on your production server to verify everything:

```bash
# Check environment
php artisan env

# Check configuration
php artisan config:show

# Check database connection
php artisan db:show

# Clear all caches
php artisan optimize:clear
php artisan config:cache
```

## 📞 **What to Check with Your Hosting Provider**

1. **Database Host Address** - Get the correct host (not 127.0.0.1)
2. **Remote Connections** - Ensure remote database connections are allowed
3. **Firewall Rules** - Check if port 3306 is open for MySQL
4. **PHP Version** - Ensure PHP 8.1 or higher is installed

## 🎯 **Expected Result**

After fixing the `.env` file and clearing cache, visiting `https://mcc-nac.com` should show your login page instead of HTTP 500 error.

## ⚠️ **Important Notes**

- NEVER commit your `.env` file to Git (it contains sensitive credentials)
- The `.env` file is different on production than on localhost
- Always clear cache after changing `.env` settings
- Test changes on a staging environment first if possible
