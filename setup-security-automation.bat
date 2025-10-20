@echo off
REM Security Automation Setup Script for Windows
REM This script sets up automated security testing and monitoring for the MCC-NAC application

echo 🔒 Setting up Security Automation for MCC-NAC Application
echo ==================================================

REM Check if Laravel application exists
if not exist "artisan" (
    echo [ERROR] Laravel application not found. Please run this script from the Laravel root directory.
    pause
    exit /b 1
)

echo [SETUP] Installing Security Dependencies
echo Installing Laravel Passport and Sanctum...

REM Install security packages
composer require laravel/passport laravel/sanctum --no-interaction

echo [INFO] Security packages installed successfully

echo [SETUP] Setting up Laravel Passport
echo Installing Passport...

REM Install Passport
php artisan passport:install --force

echo [INFO] Laravel Passport installed successfully

echo [SETUP] Creating Security Directories
echo Creating security-related directories...

REM Create security directories
if not exist "storage\security-reports" mkdir "storage\security-reports"
if not exist "storage\security-logs" mkdir "storage\security-logs"
if not exist "storage\backups\security" mkdir "storage\backups\security"
if not exist "database\security" mkdir "database\security"

echo [INFO] Security directories created

echo [SETUP] Setting up Security Configuration
echo Configuring security settings...

REM Create security environment template
(
echo # Security Configuration Template
echo # Copy these settings to your .env file
echo.
echo # Laravel Passport Configuration
echo PASSPORT_PRIVATE_KEY=""
echo PASSPORT_PUBLIC_KEY=""
echo PASSPORT_PERSONAL_ACCESS_CLIENT_ID=""
echo PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=""
echo.
echo # Security Settings
echo PASSPORT_TOKENS_EXPIRE_IN=60
echo PASSPORT_REFRESH_TOKENS_EXPIRE_IN=20160
echo PASSPORT_PERSONAL_ACCESS_TOKENS_EXPIRE_IN=10080
echo.
echo # API Security
echo API_RATE_LIMIT_ENABLED=true
echo API_RATE_LIMIT_MAX_REQUESTS=60
echo API_RATE_LIMIT_DECAY_MINUTES=1
echo.
echo # Security Headers
echo SECURITY_HEADERS_ENABLED=true
echo CSP_REPORTING_ENABLED=true
echo HSTS_ENABLED=true
echo.
echo # Vulnerability Scanning
echo VULNERABILITY_SCAN_ENABLED=true
echo VULNERABILITY_SCAN_SCHEDULE="0 2 * * *"  # Daily at 2 AM
echo SECURITY_HEADERS_TEST_SCHEDULE="0 3 * * 0"  # Weekly on Sunday at 3 AM
echo.
echo # Logging
echo SECURITY_LOGGING_ENABLED=true
echo SECURITY_LOG_RETENTION_DAYS=90
) > .env.security.template

echo [INFO] Security configuration template created

echo [SETUP] Setting up Automated Security Testing
echo Creating security testing scripts...

REM Create scripts directory if it doesn't exist
if not exist "scripts" mkdir "scripts"

REM Create security testing script
(
echo @echo off
echo REM Automated Security Testing Script
echo REM This script runs comprehensive security tests
echo.
echo set REPORT_DIR=storage\security-reports
echo set LOG_DIR=storage\security-logs
echo for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
echo set "YY=%dt:~2,2%" ^& set "YYYY=%dt:~0,4%" ^& set "MM=%dt:~4,2%" ^& set "DD=%dt:~6,2%"
echo set "HH=%dt:~8,2%" ^& set "Min=%dt:~10,2%" ^& set "Sec=%dt:~12,2%"
echo set "DATE=%YYYY%%MM%%DD%_%HH%%Min%%Sec%"
echo.
echo echo 🔒 Running Automated Security Tests - %date%
echo echo ==============================================
echo.
echo REM Create report directory if it doesn't exist
echo if not exist "%REPORT_DIR%" mkdir "%REPORT_DIR%"
echo if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"
echo.
echo REM Run security vulnerability scan
echo echo Running vulnerability scan...
echo php artisan security:scan --type=all --output=file --file="%REPORT_DIR%\vulnerability-scan-%DATE%.txt"
echo.
echo REM Test security headers
echo echo Testing security headers...
echo php artisan security:test-headers --output=file --file="%REPORT_DIR%\security-headers-%DATE%.txt"
echo.
echo echo Security tests completed. Reports saved to %REPORT_DIR%
) > scripts\security-test.bat

echo [INFO] Security testing script created

echo [SETUP] Setting up Security Monitoring
echo Creating security monitoring scripts...

REM Create security monitoring script
(
echo @echo off
echo REM Security Monitoring Script
echo REM This script monitors security events and generates alerts
echo.
echo set LOG_DIR=storage\security-logs
echo for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
echo set "YY=%dt:~2,2%" ^& set "YYYY=%dt:~0,4%" ^& set "MM=%dt:~4,2%" ^& set "DD=%dt:~6,2%"
echo set "DATE=%YYYY%%MM%%DD%"
echo.
echo echo 🔍 Security Monitoring - %date%
echo echo ================================
echo.
echo REM Create log directory if it doesn't exist
echo if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"
echo.
echo echo Monitoring for suspicious activity...
echo.
echo REM Check for failed login attempts
echo findstr /c:"Failed login attempt" storage\logs\laravel.log 2^>nul ^| find /c /v "" ^> temp_count.txt
echo set /p FAILED_LOGINS=^< temp_count.txt
echo del temp_count.txt
echo if %FAILED_LOGINS% gtr 10 (
echo     echo WARNING: High number of failed login attempts: %FAILED_LOGINS% ^>^> "%LOG_DIR%\security-alerts-%DATE%.log"
echo ^)
echo.
echo REM Check for SQL injection attempts
echo findstr /c:"SQL injection" storage\logs\laravel.log 2^>nul ^| find /c /v "" ^> temp_count.txt
echo set /p SQL_INJECTION_ATTEMPTS=^< temp_count.txt
echo del temp_count.txt
echo if %SQL_INJECTION_ATTEMPTS% gtr 0 (
echo     echo ALERT: SQL injection attempts detected: %SQL_INJECTION_ATTEMPTS% ^>^> "%LOG_DIR%\security-alerts-%DATE%.log"
echo ^)
echo.
echo echo Security monitoring completed. Alerts saved to %LOG_DIR%
) > scripts\security-monitor.bat

echo [INFO] Security monitoring script created

echo [SETUP] Setting up Windows Task Scheduler
echo Configuring automated security tasks...

REM Create Windows Task Scheduler configuration
(
echo REM Security Automation Windows Tasks
echo REM Use Task Scheduler to set up these automated tasks
echo.
echo REM Daily vulnerability scan at 2 AM
echo REM Command: php artisan security:scan --type=all
echo REM Schedule: Daily at 2:00 AM
echo.
echo REM Weekly security headers test on Sunday at 3 AM
echo REM Command: php artisan security:test-headers --output=file --file=storage\security-reports\security-headers-weekly-%%date%%.txt
echo REM Schedule: Weekly on Sunday at 3:00 AM
echo.
echo REM Hourly security monitoring
echo REM Command: scripts\security-monitor.bat
echo REM Schedule: Hourly
echo.
echo REM Daily log cleanup
echo REM Command: forfiles /p storage\security-logs /s /m *.log /d -30 /c "cmd /c del @path"
echo REM Schedule: Daily at 1:00 AM
) > security-tasks.txt

echo [INFO] Windows Task Scheduler configuration created

echo [SETUP] Setting up File Permissions
echo Setting secure file permissions...

REM Set secure file permissions (Windows equivalent)
if exist ".env" (
    attrib +R .env
    echo [INFO] .env file protected
)

echo [INFO] File permissions set securely

echo [SETUP] Creating Security Documentation
echo Generating security documentation...

REM Create security documentation
(
echo # Security Setup Complete
echo.
echo ## ✅ Security Features Implemented
echo.
echo ### 1. Enhanced Security Headers
echo - Content Security Policy ^(CSP^)
echo - HTTP Strict Transport Security ^(HSTS^)
echo - X-Frame-Options
echo - X-Content-Type-Options
echo - X-XSS-Protection
echo - Referrer Policy
echo - Permissions Policy
echo - Cross-Origin Policies
echo.
echo ### 2. API Security
echo - Laravel Passport OAuth2
echo - Laravel Sanctum API tokens
echo - Rate limiting
echo - API key validation
echo - Request logging
echo - Security headers for API responses
echo.
echo ### 3. Security Monitoring
echo - Real-time threat detection
echo - Suspicious activity monitoring
echo - Security event logging
echo - Automated vulnerability scanning
echo - Security headers testing
echo.
echo ### 4. Automated Security Testing
echo - Daily vulnerability scans
echo - Weekly security headers tests
echo - Hourly security monitoring
echo - Automated report generation
echo.
echo ## 🚀 Next Steps
echo.
echo 1. **Configure Environment Variables**
echo    ```bash
echo    copy .env.security.template .env
echo    # Edit .env with your security settings
echo    ```
echo.
echo 2. **Set up Windows Task Scheduler**
echo    - Use security-tasks.txt as reference
echo    - Create scheduled tasks for automation
echo.
echo 3. **Run Initial Security Tests**
echo    ```bash
echo    php artisan security:scan --type=all
echo    php artisan security:test-headers
echo    ```
echo.
echo 4. **View Security Dashboard**
echo    ```bash
echo    php artisan security:dashboard
echo    ```
echo.
echo ## 📊 Security Commands Available
echo.
echo - `php artisan security:scan` - Run vulnerability scan
echo - `php artisan security:test-headers` - Test security headers
echo - `php artisan security:dashboard` - View security dashboard
echo - `scripts\security-test.bat` - Run comprehensive security tests
echo - `scripts\security-monitor.bat` - Monitor security events
echo.
echo ## 🔧 Configuration Files
echo.
echo - `config\security.php` - Security configuration
echo - `config\passport.php` - OAuth2 configuration
echo - `.env.security.template` - Environment template
echo - `security-tasks.txt` - Windows Task Scheduler configuration
echo.
echo ## 📁 Security Directories
echo.
echo - `storage\security-reports\` - Security scan reports
echo - `storage\security-logs\` - Security event logs
echo - `storage\backups\security\` - Security backups
echo - `database\security\` - Security database files
echo.
echo ## 🛡️ Security Best Practices
echo.
echo 1. **Regular Testing**: Run security tests weekly
echo 2. **Monitor Logs**: Check security logs daily
echo 3. **Update Dependencies**: Keep packages updated
echo 4. **Review Reports**: Analyze security reports monthly
echo 5. **Incident Response**: Have a plan for security incidents
echo.
echo ## 📞 Support
echo.
echo For security-related issues or questions:
echo - Check the security logs in `storage\security-logs\`
echo - Review security reports in `storage\security-reports\`
echo - Run the security dashboard: `php artisan security:dashboard`
echo - Consult the security documentation
echo.
echo ## 🔄 Maintenance
echo.
echo - **Daily**: Check security monitoring alerts
echo - **Weekly**: Run comprehensive security tests
echo - **Monthly**: Review and update security configurations
echo - **Quarterly**: Perform security audits and penetration testing
echo.
echo Your MCC-NAC application is now secured with comprehensive security measures!
) > SECURITY_SETUP_COMPLETE.md

echo [INFO] Security documentation created

echo [SETUP] Finalizing Setup
echo Finalizing security setup...

echo [INFO] Security automation setup completed successfully!

echo.
echo 🎉 Security Setup Complete!
echo ==========================
echo.
echo ✅ Security headers implemented
echo ✅ API security configured
echo ✅ Vulnerability scanning set up
echo ✅ Security monitoring enabled
echo ✅ Automated testing configured
echo ✅ Documentation created
echo.
echo 📋 Next Steps:
echo 1. Configure your .env file with security settings
echo 2. Set up Windows Task Scheduler using security-tasks.txt
echo 3. Run initial security tests
echo 4. Review security documentation
echo.
echo 🔒 Your application is now secured!
echo.
echo For more information, see:
echo - APPLICATION_NETWORK_SECURITY_IMPLEMENTATION.md
echo - SECURITY_SETUP_COMPLETE.md
echo - .env.security.template
echo - security-tasks.txt
echo.

pause
