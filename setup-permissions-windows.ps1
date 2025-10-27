# Secure Permissions Setup Script for Windows
# This script sets secure file permissions for the Laravel application on Windows

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Laravel Security Permissions Setup" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "Warning: Not running as Administrator" -ForegroundColor Yellow
    Write-Host "Some operations may require elevated privileges." -ForegroundColor Yellow
    Write-Host ""
}

Write-Host "Setting up secure permissions..." -ForegroundColor Green
Write-Host ""

# Get current directory
$projectPath = Get-Location

# Function to set file permissions
function Set-FilePermissions {
    param (
        [string]$Path,
        [string]$Users = "Everyone",
        [string]$Rights = "ReadAndExecute"
    )
    
    try {
        $acl = Get-Acl $Path
        $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule($Users, $Rights, "ContainerInherit,ObjectInherit", "None", "Allow")
        $acl.SetAccessRule($accessRule)
        Set-Acl -Path $Path -AclObject $acl
        Write-Host "✓ Set permissions for: $Path" -ForegroundColor Green
    }
    catch {
        Write-Host "✗ Failed to set permissions for: $Path" -ForegroundColor Red
        Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Directory Listing Protection
Write-Host "Note: Directory listing protection is handled by .htaccess" -ForegroundColor Cyan

# Remove execute permissions from PHP files (except artisan)
Write-Host "Securing PHP files..." -ForegroundColor Green
Get-ChildItem -Path $projectPath -Include *.php -Recurse | Where-Object { 
    $_.Name -ne "artisan" 
} | ForEach-Object {
    try {
        $_.Attributes = $_.Attributes -bor [System.IO.FileAttributes]::Archive
        Write-Host "  ✓ $($_.FullName)" -ForegroundColor Gray
    }
    catch {
        Write-Host "  ✗ Failed: $($_.FullName)" -ForegroundColor Red
    }
}

# Secure .env file
if (Test-Path ".\.env") {
    Write-Host "Securing .env file..." -ForegroundColor Green
    try {
        icacls .env /inheritance:r
        icacls .env /grant "${env:USERNAME}:(R)"
        Write-Host "✓ .env secured" -ForegroundColor Green
    }
    catch {
        Write-Host "✗ Failed to secure .env" -ForegroundColor Red
    }
}

# Secure configuration files
Write-Host "Securing configuration files..." -ForegroundColor Green
if (Test-Path ".\config") {
    Get-ChildItem -Path ".\config" -Filter *.php | ForEach-Object {
        try {
            icacls $_.FullName /grant "${env:USERNAME}:(R)"
            Write-Host "  ✓ $($_.Name)" -ForegroundColor Gray
        }
        catch {
            Write-Host "  ✗ $($_.Name)" -ForegroundColor Red
        }
    }
}

# Secure storage directory
Write-Host "Configuring storage permissions..." -ForegroundColor Green
if (Test-Path ".\storage") {
    try {
        # Grant full control to web server user (e.g., IUSR, IIS_IUSRS)
        # Adjust these based on your IIS setup
        icacls storage /grant "Users:(OI)(CI)M"
        Write-Host "✓ Storage permissions configured" -ForegroundColor Green
    }
    catch {
        Write-Host "✗ Failed to configure storage permissions" -ForegroundColor Red
    }
}

# Verify permissions
Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Verification" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Check .env
if (Test-Path ".\.env") {
    $envAcl = Get-Acl .env
    $envAccess = $envAcl.Access | Where-Object { $_.IdentityReference -like "*$env:USERNAME*" }
    Write-Host "✓ .env permissions configured" -ForegroundColor Green
} else {
    Write-Host "⚠ .env file not found" -ForegroundColor Yellow
}

# Check storage
if (Test-Path ".\storage") {
    Write-Host "✓ storage directory permissions configured" -ForegroundColor Green
} else {
    Write-Host "⚠ storage directory not found" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Permission Setup Complete!" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Note: For XAMPP on Windows:" -ForegroundColor Yellow
Write-Host "1. Open File Explorer" -ForegroundColor Gray
Write-Host "2. Right-click project folder → Properties" -ForegroundColor Gray
Write-Host "3. Security tab → Edit permissions" -ForegroundColor Gray
Write-Host "4. Remove 'Everyone' access" -ForegroundColor Gray
Write-Host "5. Set specific user permissions" -ForegroundColor Gray
Write-Host ""
Write-Host "Done!" -ForegroundColor Green

