#!/bin/bash
# Secure Permissions Setup Script
# This script sets secure file permissions for the Laravel application

echo "=================================="
echo "Laravel Security Permissions Setup"
echo "=================================="
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    echo -e "${YELLOW}Warning: Running as root. Consider running as web server user.${NC}"
fi

# Get web server user
if [ -f /etc/passwd ]; then
    WEB_USER=$(ps aux | grep -E '[a]pache|[h]ttpd|www-data' | grep -v root | head -1 | awk '{print $1}')
    
    if [ -z "$WEB_USER" ]; then
        WEB_USER="www-data"  # Default for Ubuntu/Debian
    fi
fi

echo "Web server user detected: ${WEB_USER}"
read -p "Is this correct? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    read -p "Enter web server user: " WEB_USER
fi

echo ""
echo "Setting up secure permissions..."
echo ""

# Set directory permissions (755 = drwxr-xr-x)
echo -e "${GREEN}Setting directory permissions to 755...${NC}"
find . -type d -exec chmod 755 {} \;

# Set file permissions (644 = -rw-r--r--)
echo -e "${GREEN}Setting file permissions to 644...${NC}"
find . -type f -exec chmod 644 {} \;

# Special permissions for storage directories
echo -e "${GREEN}Setting storage permissions to 775...${NC}"
if [ -d "storage" ]; then
    chmod -R 775 storage
fi

# Special permissions for bootstrap/cache
echo -e "${GREEN}Setting bootstrap/cache permissions to 775...${NC}"
if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
fi

# Make artisan executable
echo -e "${GREEN}Making artisan executable...${NC}"
if [ -f "artisan" ]; then
    chmod +x artisan
fi

# Secure .env file (600 = -rw-------)
echo -e "${GREEN}Securing .env file...${NC}"
if [ -f ".env" ]; then
    chmod 600 .env
    echo -e "${GREEN}✓ .env secured${NC}"
else
    echo -e "${YELLOW}⚠ .env file not found${NC}"
fi

# Secure configuration files
echo -e "${GREEN}Setting config file permissions...${NC}"
if [ -d "config" ]; then
    find config -type f -exec chmod 644 {} \;
fi

# Secure logs directory
echo -e "${GREEN}Securing logs directory...${NC}"
if [ -d "storage/logs" ]; then
    chmod -R 775 storage/logs
fi

if [ -d "storage/framework" ]; then
    chmod -R 775 storage/framework
fi

# Secure private keys
echo -e "${GREEN}Securing private keys...${NC}"
find storage -name "*.key" -exec chmod 600 {} \; 2>/dev/null

# Set proper ownership (requires root)
if [ "$EUID" -eq 0 ]; then 
    echo ""
    echo -e "${YELLOW}Setting ownership...${NC}"
    read -p "Set ownership to $WEB_USER? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        chown -R $WEB_USER:$WEB_USER .
        echo -e "${GREEN}✓ Ownership set to $WEB_USER${NC}"
    fi
else
    echo -e "${YELLOW}Note: To change ownership, run as root with:${NC}"
    echo -e "${YELLOW}sudo chown -R $WEB_USER:$WEB_USER .${NC}"
fi

echo ""
echo "=================================="
echo "Permission Setup Complete!"
echo "=================================="
echo ""

# Verification
echo "Verifying permissions..."
echo ""

# Check .env
if [ -f ".env" ]; then
    ENV_PERM=$(stat -c %a .env 2>/dev/null || stat -f %A .env)
    if [ "$ENV_PERM" = "600" ]; then
        echo -e "${GREEN}✓ .env has correct permissions (600)${NC}"
    else
        echo -e "${RED}✗ .env has incorrect permissions ($ENV_PERM, should be 600)${NC}"
    fi
fi

# Check storage
if [ -d "storage" ]; then
    STORAGE_PERM=$(stat -c %a storage 2>/dev/null || stat -f %A storage)
    if [ "$STORAGE_PERM" = "775" ]; then
        echo -e "${GREEN}✓ storage has correct permissions (775)${NC}"
    else
        echo -e "${YELLOW}⚠ storage permissions: $STORAGE_PERM (preferred: 775)${NC}"
    fi
fi

# Check bootstrap/cache
if [ -d "bootstrap/cache" ]; then
    CACHE_PERM=$(stat -c %a bootstrap/cache 2>/dev/null || stat -f %A bootstrap/cache)
    if [ "$CACHE_PERM" = "775" ]; then
        echo -e "${GREEN}✓ bootstrap/cache has correct permissions (775)${NC}"
    else
        echo -e "${YELLOW}⚠ bootstrap/cache permissions: $CACHE_PERM (preferred: 775)${NC}"
    fi
fi

echo ""
echo "Done!"

