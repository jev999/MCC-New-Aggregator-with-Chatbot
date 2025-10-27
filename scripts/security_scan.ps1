# Security Scan Script for MCC News Aggregator (PowerShell)
# Run this script regularly to check application security
# Usage: .\security_scan.ps1 [domain]

param(
    [string]$Domain = "http://localhost:8000"
)

$OutputDir = "security_reports"
$Date = Get-Date -Format "yyyyMMdd_HHmmss"
$ReportFile = "$OutputDir\scan_$Date.txt"
$SummaryFile = "$OutputDir\summary_$Date.json"

if (!(Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Path $OutputDir | Out-Null
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "MCC News Aggregator - Security Scan" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Scanning: $Domain"
Write-Host "Report: $ReportFile"
Write-Host ""

# Initialize report
$ReportContent = @"
Security Scan Report - $Date
Domain: $Domain
Timestamp: $(Get-Date)
========================================

"@

# Check 1: Security Headers
Write-Host "Checking Security Headers..." -ForegroundColor Yellow
$ReportContent += "`n=== SECURITY HEADERS ===`n"

try {
    $Headers = Invoke-WebRequest -Uri $Domain -Method Head -UseBasicParsing -TimeoutSec 10
    
    # Check Content Security Policy
    $cspPresent = $false
    if ($Headers.Headers."Content-Security-Policy") {
        Write-Host "[OK] Content-Security-Policy: Present" -ForegroundColor Green
        $ReportContent += "[OK] Content-Security-Policy: Present`n"
        $cspPresent = $true
    } else {
        Write-Host "[MISSING] Content-Security-Policy: MISSING" -ForegroundColor Red
        $ReportContent += "[MISSING] Content-Security-Policy: MISSING`n"
    }
    
    # Check HSTS
    $hstsPresent = $false
    if ($Headers.Headers."Strict-Transport-Security") {
        Write-Host "[OK] Strict-Transport-Security: Present" -ForegroundColor Green
        $ReportContent += "[OK] Strict-Transport-Security: Present`n"
        $hstsPresent = $true
    } else {
        Write-Host "[WARNING] Strict-Transport-Security: MISSING" -ForegroundColor Yellow
        $ReportContent += "[WARNING] Strict-Transport-Security: MISSING`n"
    }
    
    # Check X-Frame-Options
    $framePresent = $false
    if ($Headers.Headers."X-Frame-Options") {
        Write-Host "[OK] X-Frame-Options: Present" -ForegroundColor Green
        $ReportContent += "[OK] X-Frame-Options: Present`n"
        $framePresent = $true
    } else {
        Write-Host "[MISSING] X-Frame-Options: MISSING" -ForegroundColor Red
        $ReportContent += "[MISSING] X-Frame-Options: MISSING`n"
    }
    
    # Check X-Content-Type-Options
    $contentTypePresent = $false
    if ($Headers.Headers."X-Content-Type-Options") {
        Write-Host "[OK] X-Content-Type-Options: Present" -ForegroundColor Green
        $ReportContent += "[OK] X-Content-Type-Options: Present`n"
        $contentTypePresent = $true
    } else {
        Write-Host "[MISSING] X-Content-Type-Options: MISSING" -ForegroundColor Red
        $ReportContent += "[MISSING] X-Content-Type-Options: MISSING`n"
    }
    
} catch {
    Write-Host "Error checking headers: $_" -ForegroundColor Red
    $ReportContent += "Error checking headers: $_`n"
}

# Check 2: Check for debug files
Write-Host "`nChecking for debug files..." -ForegroundColor Yellow
$ReportContent += "`n=== DEBUG FILES CHECK ===`n"
$debugFilesFound = $false

if (Test-Path "public\debug.php") {
    Write-Host "[FOUND] public\debug.php" -ForegroundColor Red
    $ReportContent += "[FOUND] public\debug.php (should be removed in production)`n"
    $debugFilesFound = $true
}

$testFiles = Get-ChildItem -Path "public" -Filter "test_*.php" -ErrorAction SilentlyContinue
if ($testFiles) {
    Write-Host "[WARNING] Found test files in public directory" -ForegroundColor Yellow
    $ReportContent += "[WARNING] Found test files:`n"
    $testFiles | ForEach-Object { $ReportContent += "$($_.FullName)`n" }
    $debugFilesFound = $true
}

if (!$debugFilesFound) {
    $ReportContent += "[OK] No debug files found in public directory`n"
}

# Check 3: Configuration check
Write-Host "`nChecking configuration..." -ForegroundColor Yellow
$ReportContent += "`n=== CONFIGURATION CHECK ===`n"

if (Test-Path ".env") {
    $envContent = Get-Content ".env"
    
    if ($envContent -match "APP_DEBUG=true") {
        Write-Host "[WARNING] APP_DEBUG=true (should be false in production)" -ForegroundColor Yellow
        $ReportContent += "[WARNING] APP_DEBUG=true (should be false in production)`n"
    }
    
    if ($envContent -match "APP_ENV=production") {
        Write-Host "[OK] APP_ENV=production" -ForegroundColor Green
        $ReportContent += "[OK] APP_ENV=production`n"
    } else {
        Write-Host "[WARNING] APP_ENV not set to production" -ForegroundColor Yellow
        $ReportContent += "[WARNING] APP_ENV not set to production`n"
    }
} else {
    Write-Host "[WARNING] .env file not found" -ForegroundColor Yellow
    $ReportContent += "[WARNING] .env file not found`n"
}

# Summary
$ReportContent += "`n=== SUMMARY ===`n"
$ReportContent += "CSP Present: $cspPresent`n"
$ReportContent += "HSTS Present: $hstsPresent`n"
$ReportContent += "X-Frame-Options: $framePresent`n"
$ReportContent += "X-Content-Type-Options: $contentTypePresent`n"
$ReportContent += "Debug Files Found: $debugFilesFound`n"

# Save report
$ReportContent | Out-File -FilePath $ReportFile -Encoding UTF8

# Create JSON summary
$Summary = @{
    timestamp = $Date
    domain = $Domain
    csp_present = $cspPresent
    hsts_present = $hstsPresent
    x_frame_options_present = $framePresent
    x_content_type_options_present = $contentTypePresent
    debug_files_found = $debugFilesFound
} | ConvertTo-Json

$Summary | Out-File -FilePath $SummaryFile -Encoding UTF8

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Scan Complete!" -ForegroundColor Green
Write-Host "Full Report: $ReportFile" -ForegroundColor Cyan
Write-Host "Summary: $SummaryFile" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Display summary
Write-Host "`nSECURITY SUMMARY:" -ForegroundColor Yellow
Write-Host $Summary

