# Security Scanning Scripts

This directory contains scripts for automated security scanning of the MCC News Aggregator application.

## Available Scripts

### 1. security_scan.sh (Linux/macOS)

Comprehensive security scanning script for Unix-based systems.

**Usage:**
```bash
chmod +x scripts/security_scan.sh
./scripts/security_scan.sh https://your-domain.com
```

**Features:**
- Checks security headers (CSP, HSTS, X-Frame-Options, etc.)
- Detects WAF presence using wafwoof
- Verifies SSL/TLS configuration
- Scans composer dependencies for vulnerabilities
- Checks for debug files in production
- Validates configuration settings
- Generates JSON summary report

**Output:**
- Full report: `security_reports/scan_YYYYMMDD_HHMMSS.txt`
- Summary: `security_reports/summary_YYYYMMDD_HHMMSS.json`

### 2. security_scan.ps1 (Windows)

PowerShell version of the security scanning script.

**Usage:**
```powershell
.\scripts\security_scan.ps1 -Domain "https://your-domain.com"
# or
.\scripts\security_scan.ps1  # Uses http://localhost:8000 by default
```

**Features:**
- Same features as bash script
- Optimized for Windows environment
- Generates both text and JSON reports

### 3. wafwoof_scan.py (Cross-platform)

Python-based wafwoof integration script.

**Usage:**
```bash
# Install wafwoof first
pip install wafw00f

# Run scan
python scripts/wafwoof_scan.py https://your-domain.com
```

**Features:**
- Integrates with wafwoof for WAF detection
- Generates JSON reports
- Cross-platform compatibility

## Prerequisites

### For Linux/macOS:
- Bash shell
- curl
- Composer (for dependency scanning)
- wafwoof (optional, for WAF detection)

Install dependencies:
```bash
sudo apt-get install curl  # Ubuntu/Debian
brew install curl  # macOS
pip install wafw00f  # For WAF detection
```

### For Windows:
- PowerShell 5.1 or higher
- curl (usually pre-installed in Windows 10+)
- Composer (for dependency scanning)

### For Python Script:
- Python 3.6+
- wafwoof library: `pip install wafw00f`

## Automated Scanning

### Linux/macOS (Cron)

Add to crontab for weekly scans:
```bash
crontab -e

# Add this line (runs every Monday at 2 AM)
0 2 * * 1 /path/to/project/scripts/security_scan.sh https://your-domain.com
```

### Windows (Task Scheduler)

1. Open Task Scheduler
2. Create Basic Task
3. Set trigger (Weekly on Monday at 2 AM)
4. Set action: Start program
5. Program: `powershell.exe`
6. Arguments: `-File "C:\path\to\project\scripts\security_scan.ps1"`

## GitHub Actions Integration

Create `.github/workflows/security-scan.yml`:

```yaml
name: Security Scan

on:
  schedule:
    - cron: '0 2 * * 1'  # Weekly on Monday
  workflow_dispatch:

jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Run security scan
        run: |
          chmod +x scripts/security_scan.sh
          ./scripts/security_scan.sh https://your-domain.com
      
      - name: Upload reports
        uses: actions/upload-artifact@v3
        with:
          name: security-reports
          path: security_reports/*
```

## Interpreting Results

### Security Headers Status

- ✓ Present: Header is properly configured
- ✗ Missing: Header not found (security risk)
- ⚠ Warning: Should be configured in production

### WAF Detection

- **WAF Detected**: Good - indicates DDoS protection is active
- **No WAF Detected**: May indicate CDN not configured

### Dependency Scan

- **No vulnerabilities**: Safe to deploy
- **Vulnerabilities found**: Update dependencies before deployment

## Report Files

Reports are stored in `security_reports/` directory:

- `scan_YYYYMMDD_HHMMSS.txt` - Full detailed report
- `summary_YYYYMMDD_HHMMSS.json` - Summary in JSON format

## Best Practices

1. **Run scans regularly**: Weekly or before major deployments
2. **Review reports**: Address any security issues immediately
3. **Archive reports**: Keep historical data for compliance
4. **Automate**: Set up cron jobs or scheduled tasks
5. **Include in CI/CD**: Add to deployment pipeline

## Troubleshooting

### Script Permission Denied
```bash
chmod +x scripts/security_scan.sh
```

### wafwoof Not Found
```bash
pip install wafw00f
# or
pip3 install wafw00f
```

### Domain Not Reachable
- Check if application is running
- Verify URL is correct
- Check firewall rules

## Support

For security concerns or questions:
- Review `APPLICATION_SECURITY_IMPLEMENTATION.md`
- Check Laravel security documentation
- Contact security team

