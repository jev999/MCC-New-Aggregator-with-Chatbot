# =============================================================================
# Backup System Permission Fix Script for Windows (Local Development)
# =============================================================================
# This script fixes common permission issues with the backup functionality
# Run this in PowerShell with administrator privileges
# =============================================================================

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "MCC Backup System Permission Fix (Windows)" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# Get the script directory (project root)
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptPath

Write-Host "Step 1: Checking current setup..." -ForegroundColor Yellow
Write-Host "-----------------------------------"

# Check if storage directory exists
if (-Not (Test-Path "storage")) {
    Write-Host "Error: storage/ directory not found!" -ForegroundColor Red
    Write-Host "Are you in the project root directory?"
    exit 1
}

Write-Host "✓ storage/ directory found" -ForegroundColor Green

# Check if storage/app exists
if (-Not (Test-Path "storage/app")) {
    Write-Host "Creating storage/app directory..." -ForegroundColor Yellow
    New-Item -Path "storage/app" -ItemType Directory -Force | Out-Null
}

Write-Host "✓ storage/app directory exists" -ForegroundColor Green

Write-Host ""
Write-Host "Step 2: Creating backup directories..." -ForegroundColor Yellow
Write-Host "-----------------------------------"

# Create backup directories if they don't exist
if (-Not (Test-Path "storage/app/backups")) {
    New-Item -Path "storage/app/backups" -ItemType Directory -Force | Out-Null
    Write-Host "✓ Created storage/app/backups" -ForegroundColor Green
} else {
    Write-Host "✓ storage/app/backups already exists" -ForegroundColor Green
}

if (-Not (Test-Path "storage/app/backup-temp")) {
    New-Item -Path "storage/app/backup-temp" -ItemType Directory -Force | Out-Null
    Write-Host "✓ Created storage/app/backup-temp" -ForegroundColor Green
} else {
    Write-Host "✓ storage/app/backup-temp already exists" -ForegroundColor Green
}

Write-Host ""
Write-Host "Step 3: Setting permissions (Windows)..." -ForegroundColor Yellow
Write-Host "-----------------------------------"

# Remove read-only attributes
Get-ChildItem -Path "storage" -Recurse | ForEach-Object {
    if ($_.Attributes -band [System.IO.FileAttributes]::ReadOnly) {
        $_.Attributes = $_.Attributes -bxor [System.IO.FileAttributes]::ReadOnly
    }
}

Write-Host "✓ Removed read-only attributes from storage directory" -ForegroundColor Green

# Grant full control to current user
$acl = Get-Acl "storage"
$currentUser = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule($currentUser, "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow")
$acl.SetAccessRule($accessRule)
Set-Acl "storage" $acl

Write-Host "✓ Set full control permissions for: $currentUser" -ForegroundColor Green

Write-Host ""
Write-Host "Step 4: Verifying permissions..." -ForegroundColor Yellow
Write-Host "-----------------------------------"

# Check if directories are writable (Windows check)
$testFile = "storage/app/backups/test_write.tmp"
try {
    [System.IO.File]::WriteAllText($testFile, "test")
    Remove-Item $testFile -Force
    Write-Host "✓ storage/app/backups is writable" -ForegroundColor Green
} catch {
    Write-Host "✗ storage/app/backups is NOT writable!" -ForegroundColor Red
    Write-Host "  Error: $($_.Exception.Message)"
}

$testFile = "storage/app/backup-temp/test_write.tmp"
try {
    [System.IO.File]::WriteAllText($testFile, "test")
    Remove-Item $testFile -Force
    Write-Host "✓ storage/app/backup-temp is writable" -ForegroundColor Green
} catch {
    Write-Host "✗ storage/app/backup-temp is NOT writable!" -ForegroundColor Red
    Write-Host "  Error: $($_.Exception.Message)"
}

Write-Host ""
Write-Host "Step 5: Checking PHP and Database..." -ForegroundColor Yellow
Write-Host "-----------------------------------"

# Check if PHP is in PATH
$phpPath = Get-Command php -ErrorAction SilentlyContinue
if ($phpPath) {
    Write-Host "✓ PHP found: $($phpPath.Source)" -ForegroundColor Green
    
    # Get PHP version
    $phpVersion = & php -v 2>&1 | Select-String -Pattern "PHP (\d+\.\d+\.\d+)" | ForEach-Object { $_.Matches.Groups[1].Value }
    Write-Host "  Version: $phpVersion" -ForegroundColor Cyan
    
    # Test database connection
    Write-Host "  Testing database connection..." -ForegroundColor Cyan
    $testDb = & php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'OK'; } catch (\Exception `$e) { echo 'FAIL: ' . `$e->getMessage(); }" 2>&1
    if ($testDb -like "*OK*") {
        Write-Host "✓ Database connection successful" -ForegroundColor Green
    } else {
        Write-Host "⚠ Database connection issue" -ForegroundColor Yellow
        Write-Host "  $testDb"
    }
} else {
    Write-Host "⚠ PHP not found in PATH" -ForegroundColor Yellow
    Write-Host "  Make sure PHP is installed and added to your PATH variable"
}

# Check if MySQL is running (XAMPP)
$mysqlService = Get-Process mysqld -ErrorAction SilentlyContinue
if ($mysqlService) {
    Write-Host "✓ MySQL is running" -ForegroundColor Green
} else {
    Write-Host "⚠ MySQL may not be running" -ForegroundColor Yellow
    Write-Host "  Start it from XAMPP Control Panel"
}

Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Make sure XAMPP MySQL is running"
Write-Host "2. Visit: http://localhost/MCC-News-Aggregator-with-Chatbot-main/public/superadmin/backup"
Write-Host "3. Or test: http://localhost/MCC-News-Aggregator-with-Chatbot-main/public/superadmin/backup/test"
Write-Host "4. Try creating a backup"
Write-Host ""
Write-Host "If issues persist:" -ForegroundColor Yellow
Write-Host "- Check Laravel logs: storage/logs/laravel.log"
Write-Host "- Check .env database credentials"
Write-Host "- Make sure XAMPP services are running"
Write-Host "- Check README.md and BACKUP_TROUBLESHOOTING.md"
Write-Host ""

# Pause to see results
Write-Host "Press any key to continue..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
