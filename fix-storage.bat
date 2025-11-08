@echo off
REM ============================================================================
REM MCC News Aggregator - Storage Fix Script (Windows)
REM Run this script on Windows (local development) to fix storage symlink
REM Usage: fix-storage.bat
REM Note: Must run as Administrator for symlink creation
REM ============================================================================

echo ============================================
echo   MCC NAC - Storage Fix Script (Windows)
echo ============================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script requires Administrator privileges!
    echo Right-click and select "Run as Administrator"
    echo.
    pause
    exit /b 1
)

REM Check if we're in a Laravel project
if not exist "artisan" (
    echo ERROR: artisan file not found!
    echo Make sure you're in the Laravel project root directory
    echo.
    pause
    exit /b 1
)

echo Step 1: Checking current setup...
echo.

REM Check if storage/app/public exists
if exist "storage\app\public" (
    echo [OK] Storage directory exists
) else (
    echo [WARN] Storage directory missing, creating...
    mkdir storage\app\public
    echo [OK] Created storage/app/public
)

echo.
echo Step 2: Creating symbolic link...
echo.

REM Remove old symlink if exists
if exist "public\storage" (
    rmdir "public\storage" 2>nul
    del "public\storage" 2>nul
    echo Removed old symlink
)

REM Create new symlink using artisan
php artisan storage:link

if %errorLevel% equ 0 (
    echo [OK] Symbolic link created successfully
) else (
    echo [ERROR] Failed to create symbolic link
    echo Make sure you're running as Administrator
)

echo.
echo Step 3: Clearing caches...
echo.

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

if %errorLevel% equ 0 (
    echo [OK] All caches cleared
)

echo.
echo Step 4: Checking upload directories...
echo.

set DIRS=announcement-images announcement-videos event-images event-videos news-images news-videos

for %%d in (%DIRS%) do (
    if exist "storage\app\public\%%d" (
        echo [OK] storage/app/public/%%d exists
    ) else (
        echo [WARN] storage/app/public/%%d not created yet
    )
)

echo.
echo ============================================
echo Storage fix completed!
echo ============================================
echo.
echo IMPORTANT FOR PRODUCTION DEPLOYMENT:
echo.
echo 1. Update .env file on production server:
echo    - APP_URL=https://mcc-nac.com (NOT localhost!)
echo    - FILESYSTEM_DISK=public
echo    - APP_ENV=production
echo    - APP_DEBUG=false
echo.
echo 2. Upload fix-storage.sh to server and run:
echo    bash fix-storage.sh
echo.
echo 3. Set proper permissions on server:
echo    chmod -R 775 storage
echo    chmod -R 775 bootstrap/cache
echo    chown -R www-data:www-data storage
echo.
echo 4. Test using storage-test.php diagnostic tool
echo.
pause
