# 🚀 MCC News Aggregator - Production Deployment Guide

## 🚨 **Current Issue: HTTP 500 Error on mcc-nac.com**

Your domain `mcc-nac.com` is showing an HTTP 500 error. Follow this guide to fix it.

## 📋 **Pre-Deployment Checklist**

### ✅ **Server Requirements**
- **PHP Version**: 8.2+ (Laravel 12 requirement)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 8.0+ or MariaDB 10.3+
- **Extensions**: mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath, fileinfo

### ✅ **Files to Upload**
```
├── app/                    # Laravel application files
├── bootstrap/              # Bootstrap files
├── config/                 # Configuration files
├── database/              # Database files
├── public/                # Web root (point domain here)
├── resources/             # Views, assets
├── routes/                # Route definitions
├── storage/               # Storage directory (needs write permissions)
├── vendor/                # Composer dependencies
├── .env                   # Environment configuration
├── artisan                # Artisan CLI
├── composer.json          # Dependencies
└── composer.lock          # Locked dependencies
```

## 🔧 **Step-by-Step Deployment**

### **Step 1: Upload Files**
1. Upload all project files to your hosting server
2. Point your domain's document root to the `public/` directory
3. **Important**: The web root should be `/path/to/your/project/public/`, not the project root

### **Step 2: Set File Permissions**
```bash
# Make storage and bootstrap/cache writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set ownership (replace 'www-data' with your server's web user)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### **Step 3: Environment Configuration**
1. Copy `env.production.example` to `.env` on your server
2. Update these critical settings:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mcc-nac.com

# Database settings (update with your production database)
DB_HOST=your_db_host
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Security settings
FORCE_HTTPS=true
SESSION_SECURE=true
COOKIE_SECURE=true
SESSION_DOMAIN=.mcc-nac.com
```

### **Step 4: Install Dependencies**
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate
```

### **Step 5: Database Setup**
```bash
# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force
```

### **Step 6: Clear and Cache**
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 7: Security Configuration**
1. Copy `public/.htaccess.production` to `public/.htaccess`
2. This enables:
   - HTTPS redirection
   - Security headers
   - File protection
   - Performance optimizations

## 🔍 **Troubleshooting Tools**

### **Diagnostic Scripts**
1. **Upload diagnostic script**: Upload `deploy-production.php` to your `public/` directory
2. **Access**: Visit `https://mcc-nac.com/deploy-production.php`
3. **Check results**: This will show you exactly what's wrong

### **Debug Script**
1. **Upload**: Upload `debug.php` to your `public/` directory
2. **Access**: Visit `https://mcc-nac.com/debug.php`
3. **Review**: Check for missing files, permissions, or configuration issues

## 🚨 **Common HTTP 500 Fixes**

### **1. Wrong Document Root**
- ❌ Domain pointing to `/path/to/project/`
- ✅ Domain should point to `/path/to/project/public/`

### **2. Missing .env File**
```bash
# Copy and configure
cp env.production.example .env
php artisan key:generate
```

### **3. File Permissions**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### **4. Missing Dependencies**
```bash
composer install --optimize-autoloader --no-dev
```

### **5. PHP Version**
- Ensure server runs PHP 8.2+
- Check with: `php -v`

### **6. Missing PHP Extensions**
Required extensions:
- mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath, fileinfo

## 📞 **Quick Fix Commands**

Run these commands on your production server:

```bash
# 1. Fix permissions
chmod -R 775 storage bootstrap/cache

# 2. Install dependencies
composer install --optimize-autoloader --no-dev

# 3. Generate key
php artisan key:generate

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Run migrations
php artisan migrate --force

# 6. Cache for production
php artisan config:cache
php artisan route:cache
```

## 🔗 **Testing Your Fix**

1. Visit `https://mcc-nac.com/deploy-production.php` - Should show all green checkmarks
2. Visit `https://mcc-nac.com/debug.php` - Should show successful Laravel bootstrap
3. Visit `https://mcc-nac.com/` - Should load your application

## 📧 **Support**

If you're still getting HTTP 500 errors after following this guide:

1. Check your hosting provider's error logs
2. Run the diagnostic scripts above
3. Verify your server meets all requirements
4. Contact your hosting provider for server-specific issues

---

**Generated**: $(date)
**Version**: 1.0
**For**: MCC News Aggregator Production Deployment
