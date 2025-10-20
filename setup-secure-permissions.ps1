# Secure File Permissions Setup Script for Windows
# This script sets proper file permissions for Laravel application security

param(
    [switch]$Force
)

# Colors for output
$Red = "Red"
$Green = "Green"
$Yellow = "Yellow"

# Function to print colored output
function Write-Status {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor $Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠️  $Message" -ForegroundColor $Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor $Red
}

Write-Host "🔒 Setting up secure file permissions for Laravel application..." -ForegroundColor $Green

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")

if (-not $isAdmin -and -not $Force) {
    Write-Warning "This script should be run as Administrator for proper file permissions. Use -Force to continue anyway."
    exit 1
}

# Get the current directory (should be Laravel root)
$LaravelRoot = Get-Location

if (-not (Test-Path "$LaravelRoot\artisan")) {
    Write-Error "This doesn't appear to be a Laravel application directory (artisan file not found)"
    exit 1
}

Write-Status "Setting up permissions for Laravel application in: $LaravelRoot"

# Function to set file permissions
function Set-SecureFilePermissions {
    param(
        [string]$Path,
        [string]$AccessRule,
        [string]$Description
    )
    
    try {
        $acl = Get-Acl $Path
        $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule($AccessRule)
        $acl.SetAccessRule($accessRule)
        Set-Acl -Path $Path -AclObject $acl
        Write-Status $Description
    }
    catch {
        Write-Warning "Could not set permissions for $Path : $($_.Exception.Message)"
    }
}

# Set permissions for sensitive files
Write-Status "Setting restrictive permissions for sensitive files..."

# .env files
if (Test-Path "$LaravelRoot\.env") {
    icacls "$LaravelRoot\.env" /inheritance:r /grant:r "Administrators:(F)" /grant:r "SYSTEM:(F)" 2>$null
    Write-Status "Set .env permissions to Administrators and SYSTEM only"
}

if (Test-Path "$LaravelRoot\.env.production") {
    icacls "$LaravelRoot\.env.production" /inheritance:r /grant:r "Administrators:(F)" /grant:r "SYSTEM:(F)" 2>$null
    Write-Status "Set .env.production permissions to Administrators and SYSTEM only"
}

# Configuration files
if (Test-Path "$LaravelRoot\config\database.php") {
    icacls "$LaravelRoot\config\database.php" /inheritance:r /grant:r "Administrators:(F)" /grant:r "SYSTEM:(F)" 2>$null
    Write-Status "Set config/database.php permissions to Administrators and SYSTEM only"
}

if (Test-Path "$LaravelRoot\config\app.php") {
    icacls "$LaravelRoot\config\app.php" /inheritance:r /grant:r "Administrators:(F)" /grant:r "SYSTEM:(F)" 2>$null
    Write-Status "Set config/app.php permissions to Administrators and SYSTEM only"
}

# Storage directory permissions
if (Test-Path "$LaravelRoot\storage") {
    Write-Status "Setting storage directory permissions..."
    
    # Set permissions for IIS_IUSRS (if IIS is installed)
    if (Get-LocalUser -Name "IIS_IUSRS" -ErrorAction SilentlyContinue) {
        icacls "$LaravelRoot\storage" /inheritance:r /grant:r "IIS_IUSRS:(OI)(CI)(F)" /grant:r "Administrators:(OI)(CI)(F)" /grant:r "SYSTEM:(OI)(CI)(F)" 2>$null
        Write-Status "Set storage directory permissions for IIS_IUSRS"
    }
    else {
        # For Apache or other web servers, set permissions for current user
        $currentUser = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
        icacls "$LaravelRoot\storage" /inheritance:r /grant:r "$currentUser`:(OI)(CI)(F)" /grant:r "Administrators:(OI)(CI)(F)" /grant:r "SYSTEM:(OI)(CI)(F)" 2>$null
        Write-Status "Set storage directory permissions for current user"
    }
}

# Bootstrap cache directory
if (Test-Path "$LaravelRoot\bootstrap\cache") {
    Write-Status "Setting bootstrap/cache directory permissions..."
    
    if (Get-LocalUser -Name "IIS_IUSRS" -ErrorAction SilentlyContinue) {
        icacls "$LaravelRoot\bootstrap\cache" /inheritance:r /grant:r "IIS_IUSRS:(OI)(CI)(F)" /grant:r "Administrators:(OI)(CI)(F)" /grant:r "SYSTEM:(OI)(CI)(F)" 2>$null
        Write-Status "Set bootstrap/cache directory permissions for IIS_IUSRS"
    }
    else {
        $currentUser = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
        icacls "$LaravelRoot\bootstrap\cache" /inheritance:r /grant:r "$currentUser`:(OI)(CI)(F)" /grant:r "Administrators:(OI)(CI)(F)" /grant:r "SYSTEM:(OI)(CI)(F)" 2>$null
        Write-Status "Set bootstrap/cache directory permissions for current user"
    }
}

# Vendor directory permissions (read-only for web server)
if (Test-Path "$LaravelRoot\vendor") {
    Write-Status "Setting vendor directory permissions..."
    icacls "$LaravelRoot\vendor" /inheritance:r /grant:r "Administrators:(OI)(CI)(RX)" /grant:r "SYSTEM:(OI)(CI)(RX)" 2>$null
    Write-Status "Set vendor directory permissions to read-only for Administrators and SYSTEM"
}

# Public directory permissions
if (Test-Path "$LaravelRoot\public") {
    Write-Status "Setting public directory permissions..."
    icacls "$LaravelRoot\public" /inheritance:r /grant:r "Everyone:(OI)(CI)(RX)" /grant:r "Administrators:(OI)(CI)(F)" /grant:r "SYSTEM:(OI)(CI)(F)" 2>$null
    Write-Status "Set public directory permissions for web access"
}

# Create .htaccess in root directory to deny access to sensitive files
Write-Status "Creating root .htaccess for additional security..."
$htaccessContent = @'
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
'@

$htaccessContent | Out-File -FilePath "$LaravelRoot\.htaccess" -Encoding UTF8
Write-Status "Created root .htaccess file"

# Create storage link if it doesn't exist
if (-not (Test-Path "$LaravelRoot\public\storage")) {
    Write-Status "Creating storage symbolic link..."
    try {
        & php "$LaravelRoot\artisan" storage:link
        Write-Status "Storage link created"
    }
    catch {
        Write-Warning "Could not create storage link: $($_.Exception.Message)"
    }
}

# Clear and cache configuration
Write-Status "Clearing and caching configuration..."
try {
    & php "$LaravelRoot\artisan" config:clear
    & php "$LaravelRoot\artisan" config:cache
    & php "$LaravelRoot\artisan" route:cache
    & php "$LaravelRoot\artisan" view:cache
    Write-Status "Configuration cached"
}
catch {
    Write-Warning "Could not cache configuration: $($_.Exception.Message)"
}

# Security recommendations
Write-Host ""
Write-Host "🔒 Security Setup Complete!" -ForegroundColor $Green
Write-Host ""
Write-Host "📋 Additional Security Recommendations:" -ForegroundColor $Yellow
Write-Host "   1. Ensure your web server is configured to serve only the 'public' directory"
Write-Host "   2. Set up SSL/TLS certificates for HTTPS"
Write-Host "   3. Configure Windows Firewall to restrict access"
Write-Host "   4. Regularly update dependencies with 'composer update'"
Write-Host "   5. Monitor logs for suspicious activity"
Write-Host "   6. Set up automated backups"
Write-Host "   7. Use environment variables for sensitive configuration"
Write-Host "   8. Consider using Windows Defender or other antivirus software"
Write-Host ""
Write-Host "🔍 To verify permissions, run:" -ForegroundColor $Yellow
Write-Host "   Get-Acl `"$LaravelRoot`" | Format-List"
Write-Host "   Get-Acl `"$LaravelRoot\storage`" | Format-List"
Write-Host "   Get-Acl `"$LaravelRoot\bootstrap\cache`" | Format-List"
Write-Host ""
Write-Host "✅ File permissions have been set securely!" -ForegroundColor $Green
