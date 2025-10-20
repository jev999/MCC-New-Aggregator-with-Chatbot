@echo off
REM Security Implementation Setup Script for Windows
REM This script helps set up the security features for the MCC-NAC application

echo 🔒 Setting up Data Security and Privacy Implementation...

REM Check if we're in the right directory
if not exist "artisan" (
    echo ❌ Error: Please run this script from the Laravel project root directory
    pause
    exit /b 1
)

echo ✅ Laravel project detected

REM Generate application key if not exists
php artisan key:generate

REM Clear caches
echo 🧹 Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

REM Run migrations
echo 📊 Running database migrations...
php artisan migrate --force

REM Test data purging command
echo 🧪 Testing data purging command...
php artisan data:purge --dry-run

REM Check security configuration
echo 🔍 Checking security configuration...
php artisan config:show security

echo.
echo 🎉 Security implementation setup complete!
echo.
echo 📋 Next steps:
echo 1. Configure your web server to use HTTPS
echo 2. Set FORCE_HTTPS=true in your .env file for production
echo 3. Set up automated data purging with: php artisan schedule:work
echo 4. Review and customize the privacy policies as needed
echo 5. Test the security features in your environment
echo.
echo 📚 Documentation: See DATA_SECURITY_PRIVACY_IMPLEMENTATION.md
echo 🔗 Legal pages: /terms-and-conditions, /privacy-policy
echo.
echo ⚠️  Important: Test thoroughly in a staging environment before production deployment!
echo.
pause
