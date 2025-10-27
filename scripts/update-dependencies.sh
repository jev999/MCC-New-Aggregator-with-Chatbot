#!/bin/bash
# Dependency Update and Security Audit Script
# This script updates dependencies and checks for security vulnerabilities

echo "=================================="
echo "Dependency Update & Security Audit"
echo "=================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if running in correct directory
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: composer.json not found. Please run from project root.${NC}"
    exit 1
fi

echo "Current versions:"
echo ""
php -v | head -1
echo ""

if [ -f "composer.json" ]; then
    echo "Laravel version:"
    grep '"laravel/framework"' composer.json
    echo ""
fi

echo "=================================="
echo "Updating Composer Dependencies"
echo "=================================="
echo ""

# Run composer update
composer update --with-all-dependencies --interactive

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Composer update successful${NC}"
else
    echo -e "${RED}✗ Composer update failed${NC}"
fi

echo ""
echo "=================================="
echo "Running Composer Security Audit"
echo "=================================="
echo ""

# Check for security vulnerabilities
composer audit

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ No security vulnerabilities found in Composer packages${NC}"
else
    echo -e "${YELLOW}⚠ Security vulnerabilities detected!${NC}"
    echo "Run 'composer update PACKAGE_NAME' to fix specific packages"
fi

if [ -f "package.json" ]; then
    echo ""
    echo "=================================="
    echo "Updating NPM Packages"
    echo "=================================="
    echo ""
    
    # Update npm packages
    npm update
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ NPM update successful${NC}"
    else
        echo -e "${RED}✗ NPM update failed${NC}"
    fi
    
    echo ""
    echo "=================================="
    echo "Running NPM Security Audit"
    echo "=================================="
    echo ""
    
    # Check for security vulnerabilities
    npm audit
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ No security vulnerabilities found in NPM packages${NC}"
    else
        echo -e "${YELLOW}⚠ Security vulnerabilities detected!${NC}"
        echo "Run 'npm audit fix' to automatically fix vulnerabilities"
        echo "Run 'npm audit fix --force' for breaking changes (use with caution)"
    fi
fi

echo ""
echo "=================================="
echo "Summary"
echo "=================================="
echo ""
echo "Recommendations:"
echo "1. Review any security warnings above"
echo "2. Test your application after updates"
echo "3. Commit changes after verification"
echo ""
echo "Done!"

