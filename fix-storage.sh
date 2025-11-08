#!/bin/bash

# ============================================================================
# MCC News Aggregator - Storage Fix Script
# Run this script on your production server to fix broken images/videos
# Usage: bash fix-storage.sh
# ============================================================================

echo "============================================"
echo "  MCC NAC - Storage Fix Script"
echo "============================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found!${NC}"
    echo "Make sure you're in the Laravel project root directory"
    exit 1
fi

echo -e "${BLUE}📋 Step 1: Checking current setup...${NC}"
echo ""

# Check if public/storage exists
if [ -L "public/storage" ]; then
    echo -e "${GREEN}✅ Symbolic link exists${NC}"
    echo "   Target: $(readlink public/storage)"
else
    echo -e "${YELLOW}⚠️  Symbolic link does not exist${NC}"
fi

# Check if storage/app/public exists
if [ -d "storage/app/public" ]; then
    echo -e "${GREEN}✅ Storage directory exists${NC}"
else
    echo -e "${RED}❌ Storage directory missing${NC}"
    mkdir -p storage/app/public
    echo -e "${GREEN}✅ Created storage/app/public${NC}"
fi

echo ""
echo -e "${BLUE}📋 Step 2: Creating symbolic link...${NC}"

# Remove old symlink if exists
if [ -L "public/storage" ]; then
    rm public/storage
    echo "   Removed old symlink"
fi

# Create new symlink
php artisan storage:link

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Symbolic link created successfully${NC}"
else
    echo -e "${RED}❌ Failed to create symbolic link${NC}"
    echo -e "${YELLOW}Your hosting may not support symlinks.${NC}"
    echo -e "${YELLOW}Consider using FILESYSTEM_DISK=public_uploads in .env${NC}"
fi

echo ""
echo -e "${BLUE}📋 Step 3: Setting permissions...${NC}"

# Set directory permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Permissions set (775)${NC}"
else
    echo -e "${RED}❌ Failed to set permissions (may need sudo)${NC}"
fi

# Try to set ownership (may fail without sudo)
echo "   Attempting to set ownership..."
WEB_USER="www-data"

# Detect web server user
if [ -n "$(which apache2)" ]; then
    if [ "$(uname)" == "Darwin" ]; then
        WEB_USER="_www"
    else
        WEB_USER="www-data"
    fi
elif [ -n "$(which nginx)" ]; then
    WEB_USER="www-data"
fi

chown -R $WEB_USER:$WEB_USER storage 2>/dev/null
chown -R $WEB_USER:$WEB_USER bootstrap/cache 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Ownership set to $WEB_USER${NC}"
else
    echo -e "${YELLOW}⚠️  Could not set ownership (try: sudo chown -R $WEB_USER:$WEB_USER storage)${NC}"
fi

echo ""
echo -e "${BLUE}📋 Step 4: Clearing caches...${NC}"

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ All caches cleared${NC}"
fi

echo ""
echo -e "${BLUE}📋 Step 5: Checking upload directories...${NC}"

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
        FILE_COUNT=$(find "$dir" -type f | wc -l)
        echo -e "${GREEN}✅ $dir${NC} - $FILE_COUNT files"
    else
        echo -e "${YELLOW}⚠️  $dir${NC} - not created yet"
    fi
done

echo ""
echo "============================================"
echo -e "${GREEN}✅ Storage fix completed!${NC}"
echo "============================================"
echo ""
echo "Next steps:"
echo "1. Check your .env file:"
echo "   - APP_URL should be https://mcc-nac.com (not localhost!)"
echo "   - FILESYSTEM_DISK should be 'public'"
echo ""
echo "2. Run: php artisan config:clear"
echo ""
echo "3. Upload public/storage-test.php to your server"
echo "   Access: https://mcc-nac.com/storage-test.php"
echo ""
echo "4. Test by uploading content from admin panel"
echo ""
echo "5. Delete storage-test.php after testing"
echo ""
echo -e "${YELLOW}Note: If symlinks don't work on your hosting:${NC}"
echo "   Set FILESYSTEM_DISK=public_uploads in .env"
echo "   Create: mkdir public/uploads && chmod 775 public/uploads"
echo ""
