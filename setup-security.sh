#!/bin/bash

# Security Implementation Setup Script
# This script helps set up the security features for the MCC-NAC application

echo "🔒 Setting up Data Security and Privacy Implementation..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

echo "✅ Laravel project detected"

# Generate application key if not exists
if [ -z "$(grep APP_KEY .env 2>/dev/null | cut -d '=' -f2)" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate
else
    echo "✅ Application key already exists"
fi

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Test data purging command
echo "🧪 Testing data purging command..."
php artisan data:purge --dry-run

# Check security configuration
echo "🔍 Checking security configuration..."
php artisan config:show security

echo ""
echo "🎉 Security implementation setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Configure your web server to use HTTPS"
echo "2. Set FORCE_HTTPS=true in your .env file for production"
echo "3. Set up automated data purging with: php artisan schedule:work"
echo "4. Review and customize the privacy policies as needed"
echo "5. Test the security features in your environment"
echo ""
echo "📚 Documentation: See DATA_SECURITY_PRIVACY_IMPLEMENTATION.md"
echo "🔗 Legal pages: /terms-and-conditions, /privacy-policy"
echo ""
echo "⚠️  Important: Test thoroughly in a staging environment before production deployment!"
