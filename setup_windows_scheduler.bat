@echo off
echo Setting up Windows Task Scheduler for Laravel backups...

REM Create a task to run Laravel scheduler every minute
schtasks /create /tn "Laravel Scheduler" /tr "cd /d C:\xampp\htdocs\CAPSTONE MCC-NAC && php artisan schedule:run" /sc minute /mo 1 /ru SYSTEM /f

REM Create a task for daily database backups at 3:00 AM
schtasks /create /tn "Laravel Daily Backup" /tr "cd /d C:\xampp\htdocs\CAPSTONE MCC-NAC && php artisan db:backup --compress --encrypt" /sc daily /st 03:00 /ru SYSTEM /f

REM Create a task for weekly off-site backups on Sundays at 4:00 AM
schtasks /create /tn "Laravel Weekly Offsite Backup" /tr "cd /d C:\xampp\htdocs\CAPSTONE MCC-NAC && php artisan db:backup --compress --encrypt --offsite" /sc weekly /d SUN /st 04:00 /ru SYSTEM /f

echo.
echo ✅ Windows Task Scheduler tasks created successfully!
echo.
echo Tasks created:
echo - Laravel Scheduler (runs every minute)
echo - Laravel Daily Backup (runs daily at 3:00 AM)
echo - Laravel Weekly Offsite Backup (runs Sundays at 4:00 AM)
echo.
echo To view tasks: schtasks /query /tn "Laravel*"
echo To delete tasks: schtasks /delete /tn "Laravel*" /f
echo.
pause
