#!/bin/bash

# ============================================================================
# MCC News Aggregator - Production Deployment Script
# Copy and run this script on your PRODUCTION SERVER
# ============================================================================

echo "============================================"
echo "  MCC NAC - Production Deployment"
echo "============================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}Step 1: Pulling latest code from GitHub...${NC}"
git pull origin main

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Code updated successfully${NC}"
else
    echo -e "${RED}❌ Failed to pull code${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}Step 2: Creating storage symlink...${NC}"

# Remove old symlink if exists
if [ -L "public/storage" ]; then
    rm public/storage
    echo "Removed old symlink"
fi

php artisan storage:link

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Symlink created: public/storage -> storage/app/public${NC}"
else
    echo -e "${YELLOW}⚠️  Warning: Could not create symlink${NC}"
    echo "Your hosting may not support symlinks"
    echo "Alternative: Set FILESYSTEM_DISK=public_uploads in .env"
fi

echo ""
echo -e "${BLUE}Step 3: Setting permissions...${NC}"

chmod -R 775 storage
chmod -R 775 bootstrap/cache

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Permissions set (775)${NC}"
else
    echo -e "${YELLOW}⚠️  Could not set permissions (may need sudo)${NC}"
fi

# Detect web server user
WEB_USER="www-data"
if [ -n "$(which apache2)" ]; then
    WEB_USER="www-data"
elif [ -n "$(which nginx)" ]; then
    WEB_USER="nginx"
fi

echo "Setting ownership to $WEB_USER..."
chown -R $WEB_USER:$WEB_USER storage bootstrap/cache 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Ownership set to $WEB_USER${NC}"
else
    echo -e "${YELLOW}⚠️  Could not set ownership (try: sudo chown -R $WEB_USER:$WEB_USER storage)${NC}"
fi

echo ""
echo -e "${BLUE}Step 4: Clearing caches...${NC}"

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize

echo -e "${GREEN}✅ All caches cleared${NC}"

echo ""
echo -e "${BLUE}Step 5: Checking .env configuration...${NC}"

# Check APP_URL
APP_URL=$(grep "^APP_URL=" .env | cut -d '=' -f2)
if [ "$APP_URL" = "https://mcc-nac.com" ]; then
    echo -e "${GREEN}✅ APP_URL is correct: $APP_URL${NC}"
else
    echo -e "${YELLOW}⚠️  APP_URL may be incorrect: $APP_URL${NC}"
    echo "Should be: https://mcc-nac.com"
fi

# Check FILESYSTEM_DISK
FILESYSTEM_DISK=$(grep "^FILESYSTEM_DISK=" .env | cut -d '=' -f2)
if [ "$FILESYSTEM_DISK" = "public" ]; then
    echo -e "${GREEN}✅ FILESYSTEM_DISK is correct: $FILESYSTEM_DISK${NC}"
else
    echo -e "${YELLOW}⚠️  FILESYSTEM_DISK: $FILESYSTEM_DISK${NC}"
    echo "Should be: public"
fi

echo ""
echo -e "${BLUE}Step 6: Verifying storage directories...${NC}"

UPLOAD_DIRS=(
    "storage/app/public/announcement-images"
    "storage/app/public/announcement-videos"
    "storage/app/public/event-images"
    "storage/app/public/event-videos"
    "storage/app/public/news-images"
    "storage/app/public/news-videos"
)

for dir in "${UPLOAD_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        FILE_COUNT=$(find "$dir" -type f 2>/dev/null | wc -l)
        echo -e "${GREEN}✅ $dir${NC} - $FILE_COUNT files"
    else
        echo -e "${YELLOW}⚠️  $dir${NC} - not created yet"
    fi
done

echo ""
echo "============================================"
echo -e "${GREEN}✅ Deployment completed!${NC}"
echo "============================================"
echo ""
echo "Next steps:"
echo "1. Clear browser cache (Ctrl+Shift+R)"
echo "2. Test uploading content from admin panel"
echo "3. Check if images/videos display correctly"
echo ""
echo "If images still don't work:"
echo "1. Upload storage-test.php to public/"
echo "2. Access: https://mcc-nac.com/storage-test.php"
echo "3. Follow diagnostic results"
echo ""
