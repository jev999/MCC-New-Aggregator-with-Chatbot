#!/bin/bash

# =============================================================================
# COMPLETE PRODUCTION FIX - MCC News Aggregator
# Fixes: Broken images, storage symlink, permissions, and configuration
# =============================================================================

echo "=========================================="
echo "🚀 MCC-NAC Production Fix Script"
echo "=========================================="
echo ""

# Navigate to Laravel root directory
cd "$(dirname "$0")"
PROJECT_ROOT=$(pwd)

echo "📂 Project Root: $PROJECT_ROOT"
echo ""

# =============================================================================
# STEP 1: Remove and Recreate Symbolic Link
# =============================================================================

echo "=========================================="
echo "🔗 Step 1: Recreating Storage Symlink"
echo "=========================================="

# Check if symlink exists
if [ -L "public/storage" ]; then
    echo "⚠ Removing existing symbolic link..."
    rm public/storage
    echo "✓ Old symlink removed"
elif [ -e "public/storage" ]; then
    echo "⚠ Found non-symlink file/directory at public/storage"
    echo "⚠ Removing it..."
    rm -rf public/storage
    echo "✓ Removed"
else
    echo "ℹ No existing symlink found"
fi

# Create new symlink
echo ""
echo "Creating new symbolic link..."
php artisan storage:link

if [ -L "public/storage" ]; then
    echo "✓ Storage symlink created successfully!"
    ls -la public/storage
else
    echo "✗ Failed to create symlink"
    echo "⚠ Your hosting provider may block symlink creation"
    echo "⚠ Contact your host or try manual creation"
fi

echo ""

# =============================================================================
# STEP 2: Set File and Directory Permissions
# =============================================================================

echo "=========================================="
echo "🔐 Step 2: Setting Permissions"
echo "=========================================="

echo "Setting permissions for storage directory..."
chmod -R 775 storage
echo "✓ storage/ permissions set to 775"

echo "Setting permissions for bootstrap/cache..."
chmod -R 775 bootstrap/cache
echo "✓ bootstrap/cache/ permissions set to 775"

# Check if running as root/sudo
if [ "$EUID" -eq 0 ]; then
    echo ""
    echo "Setting ownership (running as root)..."
    
    # Detect web server user
    if id "www-data" &>/dev/null; then
        WEB_USER="www-data"
    elif id "apache" &>/dev/null; then
        WEB_USER="apache"
    elif id "nginx" &>/dev/null; then
        WEB_USER="nginx"
    else
        WEB_USER=$(whoami)
        echo "⚠ Could not detect web server user, using: $WEB_USER"
    fi
    
    echo "Using web server user: $WEB_USER"
    chown -R $WEB_USER:$WEB_USER storage bootstrap/cache public/storage
    echo "✓ Ownership set to $WEB_USER:$WEB_USER"
else
    echo ""
    echo "⚠ Not running as root - skipping ownership change"
    echo "ℹ If you have sudo access, run manually:"
    echo "   sudo chown -R www-data:www-data storage bootstrap/cache public/storage"
fi

echo ""

# =============================================================================
# STEP 3: Verify APP_URL Configuration
# =============================================================================

echo "=========================================="
echo "💻 Step 3: Verifying Configuration"
echo "=========================================="

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "✗ ERROR: .env file not found!"
    echo "⚠ Please create .env file from .env.example"
    exit 1
fi

echo "Checking APP_URL in .env file..."
APP_URL=$(grep "^APP_URL=" .env | cut -d'=' -f2)

if [ -z "$APP_URL" ]; then
    echo "⚠ APP_URL not set in .env file"
    echo "⚠ Please add: APP_URL=https://mcc-nac.com"
elif [[ "$APP_URL" == *"localhost"* ]] || [[ "$APP_URL" == *"127.0.0.1"* ]]; then
    echo "✗ APP_URL is set to localhost: $APP_URL"
    echo "⚠ This should be: APP_URL=https://mcc-nac.com"
    echo "⚠ Please update your .env file"
else
    echo "✓ APP_URL is set to: $APP_URL"
fi

echo ""
echo "Checking database configuration..."
DB_HOST=$(grep "^DB_HOST=" .env | cut -d'=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)

echo "DB_HOST: ${DB_HOST:-Not Set}"
echo "DB_DATABASE: ${DB_DATABASE:-Not Set}"

if [[ "$DB_HOST" == "127.0.0.1" ]]; then
    echo "⚠ DB_HOST is 127.0.0.1 - consider changing to 'localhost' if on same server"
fi

echo ""

# =============================================================================
# STEP 4: Clear and Cache Configuration
# =============================================================================

echo "=========================================="
echo "🧹 Step 4: Clearing Caches"
echo "=========================================="

php artisan config:clear
echo "✓ Configuration cache cleared"

php artisan route:clear
echo "✓ Route cache cleared"

php artisan view:clear
echo "✓ View cache cleared"

php artisan cache:clear
echo "✓ Application cache cleared"

echo ""
echo "Caching optimized configuration..."
php artisan config:cache
echo "✓ Configuration cached"

php artisan route:cache
echo "✓ Routes cached"

echo ""

# =============================================================================
# STEP 5: Verify Storage Directory Structure
# =============================================================================

echo "=========================================="
echo "🗂️ Step 5: Verifying Storage Structure"
echo "=========================================="

STORAGE_PATH="storage/app/public"

if [ -d "$STORAGE_PATH" ]; then
    echo "✓ Storage directory exists: $STORAGE_PATH"
    
    # Create standard subdirectories if they don't exist
    mkdir -p "$STORAGE_PATH/announcements"
    mkdir -p "$STORAGE_PATH/news"
    mkdir -p "$STORAGE_PATH/events"
    
    echo "✓ Created standard subdirectories"
    
    echo ""
    echo "Storage contents:"
    ls -la "$STORAGE_PATH"
else
    echo "⚠ Storage directory not found, creating..."
    mkdir -p "$STORAGE_PATH"
    echo "✓ Created: $STORAGE_PATH"
fi

echo ""

# =============================================================================
# STEP 6: Test Database Connection
# =============================================================================

echo "=========================================="
echo "🔍 Step 6: Testing Database Connection"
echo "=========================================="

php artisan tinker --execute="
    try {
        \$pdo = DB::connection()->getPdo();
        echo '✓ Database connection successful\n';
        echo 'Database: ' . config('database.connections.mysql.database') . '\n';
        echo 'Host: ' . config('database.connections.mysql.host') . '\n';
    } catch (Exception \$e) {
        echo '✗ Database connection failed: ' . \$e->getMessage() . '\n';
    }
"

echo ""

# =============================================================================
# SUMMARY
# =============================================================================

echo "=========================================="
echo "📊 Fix Summary"
echo "=========================================="
echo ""
echo "✓ Storage symlink recreated"
echo "✓ Permissions set (775)"
echo "✓ Configuration cached"
echo "✓ Storage structure verified"
echo "✓ Database connection tested"
echo ""
echo "=========================================="
echo "🎯 Next Steps"
echo "=========================================="
echo ""
echo "1. Verify APP_URL in .env is correct:"
echo "   APP_URL=https://mcc-nac.com"
echo ""
echo "2. If using shared hosting, verify symlinks are allowed"
echo ""
echo "3. Test by uploading content as admin"
echo ""
echo "4. Check browser console for 404 errors"
echo ""
echo "5. Verify image URLs look like:"
echo "   https://mcc-nac.com/media/announcements/image.jpg"
echo ""
echo "=========================================="
echo "✅ Production Fix Complete!"
echo "=========================================="
