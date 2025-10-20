#!/bin/bash

# Secure File Permissions Setup Script
# This script sets proper file permissions for Laravel application security

echo "🔒 Setting up secure file permissions for Laravel application..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_warning "This script is running as root. Consider running as a regular user with sudo privileges."
fi

# Get the current directory (should be Laravel root)
LARAVEL_ROOT=$(pwd)

if [ ! -f "$LARAVEL_ROOT/artisan" ]; then
    print_error "This doesn't appear to be a Laravel application directory (artisan file not found)"
    exit 1
fi

print_status "Setting up permissions for Laravel application in: $LARAVEL_ROOT"

# Set directory permissions (755)
print_status "Setting directory permissions to 755..."
find "$LARAVEL_ROOT" -type d -exec chmod 755 {} \;

# Set file permissions (644)
print_status "Setting file permissions to 644..."
find "$LARAVEL_ROOT" -type f -exec chmod 644 {} \;

# Set special permissions for sensitive files
print_status "Setting restrictive permissions for sensitive files..."

# .env files
if [ -f "$LARAVEL_ROOT/.env" ]; then
    chmod 600 "$LARAVEL_ROOT/.env"
    print_status "Set .env permissions to 600"
fi

if [ -f "$LARAVEL_ROOT/.env.production" ]; then
    chmod 600 "$LARAVEL_ROOT/.env.production"
    print_status "Set .env.production permissions to 600"
fi

# Configuration files
if [ -f "$LARAVEL_ROOT/config/database.php" ]; then
    chmod 600 "$LARAVEL_ROOT/config/database.php"
    print_status "Set config/database.php permissions to 600"
fi

if [ -f "$LARAVEL_ROOT/config/app.php" ]; then
    chmod 600 "$LARAVEL_ROOT/config/app.php"
    print_status "Set config/app.php permissions to 600"
fi

# Storage and cache directories (775)
print_status "Setting storage and cache directory permissions to 775..."
if [ -d "$LARAVEL_ROOT/storage" ]; then
    chmod -R 775 "$LARAVEL_ROOT/storage"
    print_status "Set storage directory permissions to 775"
fi

if [ -d "$LARAVEL_ROOT/bootstrap/cache" ]; then
    chmod -R 775 "$LARAVEL_ROOT/bootstrap/cache"
    print_status "Set bootstrap/cache directory permissions to 775"
fi

# Vendor directory (755)
print_status "Setting vendor directory permissions to 755..."
if [ -d "$LARAVEL_ROOT/vendor" ]; then
    chmod -R 755 "$LARAVEL_ROOT/vendor"
    print_status "Set vendor directory permissions to 755"
fi

# Public directory (755)
print_status "Setting public directory permissions to 755..."
if [ -d "$LARAVEL_ROOT/public" ]; then
    chmod -R 755 "$LARAVEL_ROOT/public"
    print_status "Set public directory permissions to 755"
fi

# Set executable permissions for artisan
if [ -f "$LARAVEL_ROOT/artisan" ]; then
    chmod +x "$LARAVEL_ROOT/artisan"
    print_status "Set artisan executable permissions"
fi

# Set executable permissions for composer
if [ -f "$LARAVEL_ROOT/composer.phar" ]; then
    chmod +x "$LARAVEL_ROOT/composer.phar"
    print_status "Set composer.phar executable permissions"
fi

# Create .htaccess in root directory to deny access to sensitive files
print_status "Creating root .htaccess for additional security..."
cat > "$LARAVEL_ROOT/.htaccess" << 'EOF'
# Deny access to all files in root directory
<Files "*">
    Require all denied
</Files>

# Allow access to public directory
<Directory "public">
    Require all granted
</Directory>

# Deny access to sensitive files
<Files ".env*">
    Require all denied
</Files>

<Files "composer.*">
    Require all denied
</Files>

<Files "package*.json">
    Require all denied
</Files>

<Files "artisan">
    Require all denied
</Files>

<Files "*.log">
    Require all denied
</Files>

<Files "*.sql">
    Require all denied
</Files>

<Files "*.md">
    Require all denied
</Files>

# Deny access to hidden files
<FilesMatch "^\.">
    Require all denied
</FilesMatch>

# Deny access to backup files
<FilesMatch "\.(bak|backup|old|orig|save|swp|tmp)$">
    Require all denied
</FilesMatch>
EOF

print_status "Created root .htaccess file"

# Set permissions for the new .htaccess
chmod 644 "$LARAVEL_ROOT/.htaccess"

# Create storage link if it doesn't exist
if [ ! -L "$LARAVEL_ROOT/public/storage" ]; then
    print_status "Creating storage symbolic link..."
    php "$LARAVEL_ROOT/artisan" storage:link
    print_status "Storage link created"
fi

# Clear and cache configuration
print_status "Clearing and caching configuration..."
php "$LARAVEL_ROOT/artisan" config:clear
php "$LARAVEL_ROOT/artisan" config:cache
php "$LARAVEL_ROOT/artisan" route:cache
php "$LARAVEL_ROOT/artisan" view:cache

print_status "Configuration cached"

# Set proper ownership (if running as root or with sudo)
if [[ $EUID -eq 0 ]] || sudo -n true 2>/dev/null; then
    print_status "Setting proper file ownership..."
    
    # Get web server user (common names)
    WEB_USER=""
    if id "www-data" &>/dev/null; then
        WEB_USER="www-data"
    elif id "apache" &>/dev/null; then
        WEB_USER="apache"
    elif id "nginx" &>/dev/null; then
        WEB_USER="nginx"
    elif id "httpd" &>/dev/null; then
        WEB_USER="httpd"
    fi
    
    if [ ! -z "$WEB_USER" ]; then
        chown -R "$WEB_USER:$WEB_USER" "$LARAVEL_ROOT"
        print_status "Set ownership to $WEB_USER:$WEB_USER"
    else
        print_warning "Could not determine web server user. Please set ownership manually."
    fi
else
    print_warning "Not running as root. Please set proper file ownership manually."
fi

# Security recommendations
echo ""
echo "🔒 Security Setup Complete!"
echo ""
echo "📋 Additional Security Recommendations:"
echo "   1. Ensure your web server is configured to serve only the 'public' directory"
echo "   2. Set up SSL/TLS certificates for HTTPS"
echo "   3. Configure a firewall to restrict access"
echo "   4. Regularly update dependencies with 'composer update'"
echo "   5. Monitor logs for suspicious activity"
echo "   6. Set up automated backups"
echo "   7. Use environment variables for sensitive configuration"
echo ""
echo "🔍 To verify permissions, run:"
echo "   ls -la $LARAVEL_ROOT"
echo "   ls -la $LARAVEL_ROOT/storage"
echo "   ls -la $LARAVEL_ROOT/bootstrap/cache"
echo ""
echo "✅ File permissions have been set securely!"
