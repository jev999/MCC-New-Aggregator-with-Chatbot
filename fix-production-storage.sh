#!/bin/bash

# =============================================================================
# FIX PRODUCTION STORAGE - Create Storage Symlink and Set Permissions
# =============================================================================

echo "=========================================="
echo "Fixing Production Storage Issues"
echo "=========================================="

# Navigate to Laravel root directory
cd "$(dirname "$0")"

# 1. Create storage symlink
echo ""
echo "Step 1: Creating storage symlink..."
php artisan storage:link

# 2. Set correct permissions for storage directories
echo ""
echo "Step 2: Setting storage permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 3. Set ownership (change www-data to your web server user if different)
echo ""
echo "Step 3: Setting correct ownership..."
# Uncomment the appropriate line based on your server setup:
# For Apache/Nginx on Ubuntu/Debian:
# sudo chown -R www-data:www-data storage bootstrap/cache public/storage
# For Apache on CentOS/RHEL:
# sudo chown -R apache:apache storage bootstrap/cache public/storage
# For Nginx on CentOS/RHEL:
# sudo chown -R nginx:nginx storage bootstrap/cache public/storage

echo ""
echo "Step 4: Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 5. Optimize for production
echo ""
echo "Step 5: Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Verify storage symlink
echo ""
echo "Step 6: Verifying storage symlink..."
if [ -L "public/storage" ]; then
    echo "✓ Storage symlink exists"
    ls -la public/storage
else
    echo "✗ Storage symlink does NOT exist"
    echo "Creating manually..."
    ln -s "$(pwd)/storage/app/public" "$(pwd)/public/storage"
fi

# 7. Check storage directory structure
echo ""
echo "Step 7: Checking storage directory structure..."
echo "Contents of storage/app/public:"
ls -la storage/app/public/

echo ""
echo "=========================================="
echo "Storage Fix Complete!"
echo "=========================================="
echo ""
echo "IMPORTANT: If using cPanel or shared hosting, you may need to:"
echo "1. Use the manual symlink method (see instructions below)"
echo "2. Contact your hosting provider if symlink creation fails"
echo ""
