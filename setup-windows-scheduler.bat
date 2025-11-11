@echo off
REM Windows Task Scheduler Setup for Laravel Backup Automation
REM This script creates a scheduled task to run Laravel scheduler every minute

echo Creating Windows Scheduled Task for Laravel Scheduler...
echo.

schtasks /create /tn "Laravel Backup Scheduler" /tr "php c:\xampp\htdocs\MCC-News-Aggregator-with-Chatbot-main\artisan schedule:run" /sc minute /mo 1 /ru SYSTEM /f

echo.
echo Task created successfully!
echo.
echo The Laravel scheduler will now run every minute, which will:
echo - Create database backups every 5 hours
echo - Clean up old backups daily at midnight
echo.
echo To verify the task was created:
echo   schtasks /query /tn "Laravel Backup Scheduler"
echo.
echo To delete the task:
echo   schtasks /delete /tn "Laravel Backup Scheduler" /f
echo.
pause
