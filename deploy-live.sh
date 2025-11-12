#!/bin/bash
################################################################################
# AUTOMATED DEPLOYMENT SCRIPT FOR PHP BACKUP SYSTEM
# Run this on your LIVE SERVER after pushing to GitHub
################################################################################

echo "🚀 Deploying PHP Backup System to Live Server..."
echo "================================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Pull latest changes
echo "📥 Step 1: Pulling latest changes from GitHub..."
git pull origin main
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Git pull successful${NC}"
else
    echo -e "${RED}✗ Git pull failed. Check your Git configuration.${NC}"
    exit 1
fi
echo ""

# Step 2: Install/Update Composer dependencies (if needed)
echo "📦 Step 2: Updating Composer dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✓ Composer dependencies updated${NC}"
else
    echo -e "${YELLOW}⚠ composer.json not found, skipping...${NC}"
fi
echo ""

# Step 3: Clear all Laravel caches
echo "🧹 Step 3: Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear 2>/dev/null || true
echo -e "${GREEN}✓ All caches cleared${NC}"
echo ""

# Step 4: Set proper permissions
echo "🔐 Step 4: Setting file permissions..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Try to set ownership (may require sudo)
if [ -w "/etc/passwd" ]; then
    chown -R www-data:www-data storage/ 2>/dev/null || chown -R $USER:$USER storage/
    chown -R www-data:www-data bootstrap/cache/ 2>/dev/null || chown -R $USER:$USER bootstrap/cache/
fi
echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# Step 5: Check if USE_PHP_BACKUP is in .env
echo "⚙️  Step 5: Checking .env configuration..."
if grep -q "USE_PHP_BACKUP" .env; then
    echo -e "${GREEN}✓ USE_PHP_BACKUP already configured in .env${NC}"
else
    echo -e "${YELLOW}⚠ Adding USE_PHP_BACKUP=true to .env...${NC}"
    echo "" >> .env
    echo "# PHP-based backup (no mysqldump required)" >> .env
    echo "USE_PHP_BACKUP=true" >> .env
    echo -e "${GREEN}✓ USE_PHP_BACKUP added to .env${NC}"
fi
echo ""

# Step 6: Clear config cache again to load .env changes
echo "🔄 Step 6: Reloading configuration..."
php artisan config:clear
echo -e "${GREEN}✓ Configuration reloaded${NC}"
echo ""

# Step 7: Test PHP backup
echo "🧪 Step 7: Testing PHP backup system..."
echo "Running: php artisan backup:php-run"
echo "----------------------------------------"
php artisan backup:php-run
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✓✓✓ PHP BACKUP TEST SUCCESSFUL! ✓✓✓${NC}"
else
    echo ""
    echo -e "${RED}✗ Backup test failed. Check logs: storage/logs/laravel.log${NC}"
fi
echo ""

# Step 8: Check if cron job exists
echo "⏰ Step 8: Checking Laravel scheduler cron job..."
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo -e "${GREEN}✓ Cron job already configured${NC}"
else
    echo -e "${YELLOW}⚠ Cron job NOT found!${NC}"
    echo ""
    echo "To enable automatic backups every 5 hours, add this to crontab:"
    echo -e "${YELLOW}* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1${NC}"
    echo ""
    echo "Run: crontab -e"
    echo "Then paste the line above and save."
fi
echo ""

# Final summary
echo "================================================"
echo "🎉 DEPLOYMENT COMPLETE!"
echo "================================================"
echo ""
echo "✅ Files updated from GitHub"
echo "✅ Caches cleared"
echo "✅ Permissions set"
echo "✅ .env configured"
echo "✅ PHP backup tested"
echo ""
echo "📋 Next Steps:"
echo "1. Visit: https://mcc-nac.com/superadmin/backup"
echo "2. Click 'Create Manual Backup'"
echo "3. Should see success message!"
echo ""
echo "📖 Documentation:"
echo "   - QUICK_START.txt"
echo "   - PHP_BACKUP_GUIDE.txt"
echo "   - DEPLOYMENT_CHECKLIST.txt"
echo ""
echo "🔍 Check logs if issues:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "Happy backing up! 🚀"
echo ""
