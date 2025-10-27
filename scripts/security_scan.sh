#!/bin/bash

# Security Scan Script for MCC News Aggregator
# Run this script regularly to check application security
# Usage: ./security_scan.sh [domain]

DOMAIN=${1:-"https://mcc-nac.com"}
OUTPUT_DIR="security_reports"
DATE=$(date +%Y%m%d_%H%M%S)
REPORT_FILE="$OUTPUT_DIR/scan_$DATE.txt"
SUMMARY_FILE="$OUTPUT_DIR/summary_$DATE.json"

mkdir -p "$OUTPUT_DIR"

echo "========================================="
echo "MCC News Aggregator - Security Scan"
echo "========================================="
echo "Scanning: $DOMAIN"
echo "Report: $REPORT_FILE"
echo ""

# Initialize report
echo "Security Scan Report - $DATE" > "$REPORT_FILE"
echo "Domain: $DOMAIN" >> "$REPORT_FILE"
echo "Timestamp: $(date)" >> "$REPORT_FILE"
echo "========================================" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

# Check 1: Security Headers
echo "Checking Security Headers..."
echo "=== SECURITY HEADERS ===" >> "$REPORT_FILE"
HEADERS_CHECK=$(curl -sI "$DOMAIN")

# Check for Content Security Policy
if echo "$HEADERS_CHECK" | grep -qi "content-security-policy"; then
    echo "✓ Content-Security-Policy: Present" | tee -a "$REPORT_FILE"
    CSP_PRESENT=true
else
    echo "✗ Content-Security-Policy: MISSING" | tee -a "$REPORT_FILE"
    CSP_PRESENT=false
fi

# Check for HSTS
if echo "$HEADERS_CHECK" | grep -qi "strict-transport-security"; then
    echo "✓ Strict-Transport-Security: Present" | tee -a "$REPORT_FILE"
    HSTS_PRESENT=true
else
    echo "⚠ Strict-Transport-Security: MISSING (expected in production)" | tee -a "$REPORT_FILE"
    HSTS_PRESENT=false
fi

# Check for X-Frame-Options
if echo "$HEADERS_CHECK" | grep -qi "x-frame-options"; then
    echo "✓ X-Frame-Options: Present" | tee -a "$REPORT_FILE"
    FRAME_PRESENT=true
else
    echo "✗ X-Frame-Options: MISSING" | tee -a "$REPORT_FILE"
    FRAME_PRESENT=false
fi

# Check for X-Content-Type-Options
if echo "$HEADERS_CHECK" | grep -qi "x-content-type-options"; then
    echo "✓ X-Content-Type-Options: Present" | tee -a "$REPORT_FILE"
    CONTENT_TYPE_PRESENT=true
else
    echo "✗ X-Content-Type-Options: MISSING" | tee -a "$REPORT_FILE"
    CONTENT_TYPE_PRESENT=false
fi

echo "" >> "$REPORT_FILE"
echo "Full Headers:" >> "$REPORT_FILE"
echo "$HEADERS_CHECK" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

# Check 2: WAF Detection (if wafw00f is installed)
echo "Checking WAF..."
echo "=== WAF DETECTION ===" >> "$REPORT_FILE"
if command -v wafw00f &> /dev/null; then
    wafw00f_output=$(wafw00f -v "$DOMAIN" 2>&1)
    echo "$wafw00f_output" | tee -a "$REPORT_FILE"
    
    if echo "$wafw00f_output" | grep -qi "WAF detected"; then
        echo "✓ WAF: Detected and active" >> "$REPORT_FILE"
        WAF_ACTIVE=true
    else
        echo "⚠ WAF: Not detected (may need CDN configuration)" >> "$REPORT_FILE"
        WAF_ACTIVE=false
    fi
else
    echo "wafwoof not installed. Install with: pip install wafw00f" | tee -a "$REPORT_FILE"
    WAF_ACTIVE=unknown
fi
echo "" >> "$REPORT_FILE"

# Check 3: SSL/TLS Configuration
echo "Checking SSL/TLS..."
echo "=== SSL/TLS CONFIGURATION ===" >> "$REPORT_FILE"
if [[ $DOMAIN == https://* ]]; then
    SSL_INFO=$(curl -sIv "$DOMAIN" 2>&1 | grep -i "ssl\|tls\|cipher")
    echo "$SSL_INFO" >> "$REPORT_FILE"
    
    if echo "$SSL_INFO" | grep -qi "TLS"; then
        echo "✓ SSL/TLS: Active" | tee -a "$REPORT_FILE"
        SSL_ACTIVE=true
    else
        echo "⚠ SSL/TLS: Cannot verify" | tee -a "$REPORT_FILE"
        SSL_ACTIVE=false
    fi
else
    echo "⚠ Not using HTTPS (production requires HTTPS)" >> "$REPORT_FILE"
    SSL_ACTIVE=false
fi
echo "" >> "$REPORT_FILE"

# Check 4: Dependency Vulnerability Scan
echo "Checking Dependencies..."
echo "=== DEPENDENCY SECURITY CHECK ===" >> "$REPORT_FILE"
if command -v composer &> /dev/null; then
    if [ -f "composer.json" ]; then
        COMPOSER_AUDIT=$(composer audit 2>&1)
        echo "$COMPOSER_AUDIT" | tee -a "$REPORT_FILE"
        
        if echo "$COMPOSER_AUDIT" | grep -qi "No known vulnerable packages found"; then
            echo "✓ Dependencies: No known vulnerabilities" >> "$REPORT_FILE"
            VULNERABILITIES=false
        else
            echo "✗ Dependencies: Vulnerabilities found" >> "$REPORT_FILE"
            VULNERABILITIES=true
        fi
    else
        echo "composer.json not found" | tee -a "$REPORT_FILE"
        VULNERABILITIES=unknown
    fi
else
    echo "Composer not found" | tee -a "$REPORT_FILE"
    VULNERABILITIES=unknown
fi
echo "" >> "$REPORT_FILE"

# Check 5: Check for debug files
echo "Checking for debug files..."
echo "=== DEBUG FILES CHECK ===" >> "$REPORT_FILE"
DEBUG_FILES_FOUND=false
if [ -f "public/debug.php" ]; then
    echo "✗ Found: public/debug.php (should be removed in production)" | tee -a "$REPORT_FILE"
    DEBUG_FILES_FOUND=true
fi

if [ -d "public" ]; then
    TEST_FILES=$(find public -name "test_*.php" 2>/dev/null)
    if [ ! -z "$TEST_FILES" ]; then
        echo "⚠ Found test files in public directory" | tee -a "$REPORT_FILE"
        echo "$TEST_FILES" >> "$REPORT_FILE"
        DEBUG_FILES_FOUND=true
    fi
fi

if [ "$DEBUG_FILES_FOUND" = false ]; then
    echo "✓ No debug files found in public directory" >> "$REPORT_FILE"
fi
echo "" >> "$REPORT_FILE"

# Check 6: Check configuration
echo "Checking configuration..."
echo "=== CONFIGURATION CHECK ===" >> "$REPORT_FILE"
if [ -f ".env" ]; then
    if grep -q "APP_DEBUG=true" .env; then
        echo "⚠ APP_DEBUG=true (should be false in production)" | tee -a "$REPORT_FILE"
    else
        echo "✓ APP_DEBUG properly configured" >> "$REPORT_FILE"
    fi
    
    if grep -q "APP_ENV=production" .env; then
        echo "✓ APP_ENV=production" >> "$REPORT_FILE"
    else
        echo "⚠ APP_ENV not set to production" | tee -a "$REPORT_FILE"
    fi
else
    echo ".env file not found" | tee -a "$REPORT_FILE"
fi
echo "" >> "$REPORT_FILE"

# Summary
echo "=== SUMMARY ===" >> "$REPORT_FILE"
echo "CSP Present: $CSP_PRESENT" >> "$REPORT_FILE"
echo "HSTS Present: $HSTS_PRESENT" >> "$REPORT_FILE"
echo "X-Frame-Options: $FRAME_PRESENT" >> "$REPORT_FILE"
echo "X-Content-Type-Options: $CONTENT_TYPE_PRESENT" >> "$REPORT_FILE"
echo "WAF Active: $WAF_ACTIVE" >> "$REPORT_FILE"
echo "SSL Active: $SSL_ACTIVE" >> "$REPORT_FILE"
echo "Vulnerabilities Found: $VULNERABILITIES" >> "$REPORT_FILE"
echo "Debug Files Found: $DEBUG_FILES_FOUND" >> "$REPORT_FILE"

# Create JSON summary
cat > "$SUMMARY_FILE" << EOF
{
    "timestamp": "$DATE",
    "domain": "$DOMAIN",
    "csp_present": $CSP_PRESENT,
    "hsts_present": $HSTS_PRESENT,
    "x_frame_options_present": $FRAME_PRESENT,
    "x_content_type_options_present": $CONTENT_TYPE_PRESENT,
    "waf_active": "$WAF_ACTIVE",
    "ssl_active": $SSL_ACTIVE,
    "vulnerabilities_found": "$VULNERABILITIES",
    "debug_files_found": $DEBUG_FILES_FOUND
}
EOF

echo "" >> "$REPORT_FILE"
echo "Full report saved to: $REPORT_FILE" >> "$REPORT_FILE"
echo "Summary saved to: $SUMMARY_FILE" >> "$REPORT_FILE"

echo ""
echo "========================================="
echo "Scan Complete!"
echo "Full Report: $REPORT_FILE"
echo "Summary: $SUMMARY_FILE"
echo "========================================="

# Display summary
echo ""
echo "SECURITY SUMMARY:"
cat "$SUMMARY_FILE"

