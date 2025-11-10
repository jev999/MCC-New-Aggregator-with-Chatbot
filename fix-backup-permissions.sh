#!/bin/bash

# =============================================================================
# Backup System Permission Fix Script for Production
# =============================================================================
# This script fixes common permission issues with the backup functionality
# Run this on your production server to ensure backup works properly
# =============================================================================

echo "========================================="
echo "MCC Backup System Permission Fix"
echo "========================================="
echo ""

# Get the script directory (project root)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if running as root or with sudo
if [ "$EUID" -eq 0 ]; then 
    echo -e "${YELLOW}Warning: Running as root. It's better to run as the web server user.${NC}"
    echo ""
fi

echo "Step 1: Checking current setup..."
echo "-----------------------------------"

# Check if storage directory exists
if [ ! -d "storage" ]; then
    echo -e "${RED}Error: storage/ directory not found!${NC}"
    echo "Are you in the project root directory?"
    exit 1
fi

echo -e "${GREEN}✓ storage/ directory found${NC}"

# Check if storage/app exists
if [ ! -d "storage/app" ]; then
    echo -e "${YELLOW}Creating storage/app directory...${NC}"
    mkdir -p storage/app
fi

echo -e "${GREEN}✓ storage/app directory exists${NC}"

echo ""
echo "Step 2: Creating backup directories..."
echo "-----------------------------------"

# Create backup directories if they don't exist
mkdir -p storage/app/backups
mkdir -p storage/app/backup-temp

echo -e "${GREEN}✓ Backup directories created${NC}"

echo ""
echo "Step 3: Setting permissions..."
echo "-----------------------------------"

# Set permissions for storage directory
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo -e "${GREEN}✓ Base permissions set (775)${NC}"

# Get web server user
WEB_USER=$(ps aux | grep -E 'apache|httpd|nginx|www-data' | grep -v root | head -1 | awk '{print $1}')

if [ -n "$WEB_USER" ]; then
    echo "Detected web server user: $WEB_USER"
    
    # Try to change ownership
    if [ "$EUID" -eq 0 ]; then
        chown -R $WEB_USER:$WEB_USER storage/app/backups
        chown -R $WEB_USER:$WEB_USER storage/app/backup-temp
        chown -R $WEB_USER:$WEB_USER storage/logs
        echo -e "${GREEN}✓ Ownership changed to $WEB_USER${NC}"
    else
        echo -e "${YELLOW}⚠ Not running as root, skipping ownership change${NC}"
        echo "  You may need to run: sudo chown -R $WEB_USER:$WEB_USER storage/"
    fi
else
    echo -e "${YELLOW}⚠ Could not detect web server user${NC}"
    echo "  Common users: www-data, apache, nginx"
fi

echo ""
echo "Step 4: Setting SELinux context (if applicable)..."
echo "-----------------------------------"

# Check if SELinux is enabled
if command -v getenforce &> /dev/null; then
    SELINUX_STATUS=$(getenforce)
    if [ "$SELINUX_STATUS" = "Enforcing" ] || [ "$SELINUX_STATUS" = "Permissive" ]; then
        echo "SELinux is enabled ($SELINUX_STATUS)"
        
        if command -v chcon &> /dev/null; then
            chcon -R -t httpd_sys_rw_content_t storage/app/backups
            chcon -R -t httpd_sys_rw_content_t storage/app/backup-temp
            chcon -R -t httpd_sys_rw_content_t storage/logs
            echo -e "${GREEN}✓ SELinux context set${NC}"
        else
            echo -e "${YELLOW}⚠ chcon command not found, skipping SELinux context${NC}"
        fi
    else
        echo "SELinux is not enforcing, skipping..."
    fi
else
    echo "SELinux not detected, skipping..."
fi

echo ""
echo "Step 5: Verifying permissions..."
echo "-----------------------------------"

# Check if directories are writable
if [ -w "storage/app/backups" ]; then
    echo -e "${GREEN}✓ storage/app/backups is writable${NC}"
else
    echo -e "${RED}✗ storage/app/backups is NOT writable!${NC}"
    echo "  Current permissions: $(ls -ld storage/app/backups)"
fi

if [ -w "storage/app/backup-temp" ]; then
    echo -e "${GREEN}✓ storage/app/backup-temp is writable${NC}"
else
    echo -e "${RED}✗ storage/app/backup-temp is NOT writable!${NC}"
    echo "  Current permissions: $(ls -ld storage/app/backup-temp)"
fi

if [ -w "storage/logs" ]; then
    echo -e "${GREEN}✓ storage/logs is writable${NC}"
else
    echo -e "${RED}✗ storage/logs is NOT writable!${NC}"
    echo "  Current permissions: $(ls -ld storage/logs)"
fi

echo ""
echo "Step 6: Testing database connection..."
echo "-----------------------------------"

# Run Laravel test command if artisan exists
if [ -f "artisan" ]; then
    php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';" 2>&1 | grep -q "successfully"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Database connection successful${NC}"
    else
        echo -e "${YELLOW}⚠ Could not verify database connection${NC}"
        echo "  Please check your .env database credentials"
    fi
else
    echo -e "${YELLOW}⚠ artisan not found, skipping database test${NC}"
fi

echo ""
echo "========================================="
echo "Setup Complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Visit your backup page: https://mcc-nac.com/superadmin/backup"
echo "2. Run the diagnostic test: https://mcc-nac.com/superadmin/backup/test"
echo "3. Try creating a backup"
echo ""
echo "If issues persist:"
echo "- Check Laravel logs: storage/logs/laravel.log"
echo "- Check PHP error logs"
echo "- Verify .env database credentials"
echo "- Ensure mysqldump is installed (optional but recommended)"
echo ""
echo "For additional help, check BACKUP_TROUBLESHOOTING.md"
echo ""
