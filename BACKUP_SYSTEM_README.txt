================================================================================
   AUTOMATED DATABASE BACKUP SYSTEM - SETUP & USAGE GUIDE
================================================================================

SYSTEM OVERVIEW
---------------
✅ Automatic backups every 5 hours
✅ Manual backup creation via web interface
✅ Backup download to local computer
✅ Automatic cleanup of old backups
✅ Email notifications on backup failure (configurable)

================================================================================
ACCESSING THE BACKUP SYSTEM
================================================================================

1. Login as Super Admin
2. Navigate to: http://127.0.0.1:8000/super-admin/dashboard
3. Click "Database Backup" in the sidebar
4. Or directly visit: http://127.0.0.1:8000/super-admin/backup

================================================================================
MANUAL BACKUP CREATION
================================================================================

1. Click the "Create Manual Backup" button
2. Wait 5-10 seconds for the backup to complete
3. Success message will appear
4. New backup will appear in the list below

Backup files are stored in: storage/app/Laravel/

================================================================================
AUTOMATED BACKUPS (EVERY 5 HOURS)
================================================================================

To enable automatic backups, you MUST set up Windows Task Scheduler:

METHOD 1: Run the automated script (RECOMMENDED)
------------------------------------------------
1. Right-click "setup-windows-scheduler.bat"
2. Select "Run as Administrator"
3. Task will be created automatically

METHOD 2: Manual Task Scheduler Setup
--------------------------------------
1. Open Task Scheduler (taskschd.msc)
2. Create Basic Task:
   - Name: "Laravel Backup Scheduler"
   - Trigger: Daily, repeat every 1 minute
   - Action: Start a program
   - Program: php
   - Arguments: c:\xampp\htdocs\MCC-News-Aggregator-with-Chatbot-main\artisan schedule:run
   - Start in: c:\xampp\htdocs\MCC-News-Aggregator-with-Chatbot-main
3. Save and enable the task

VERIFY THE TASK:
Open PowerShell and run:
  schtasks /query /tn "Laravel Backup Scheduler"

================================================================================
BACKUP SCHEDULE
================================================================================

✅ Database Backup: Every 5 hours (0:00, 5:00, 10:00, 15:00, 20:00)
✅ Backup Cleanup: Daily at midnight (removes old backups per retention policy)

Retention Policy (configurable in config/backup.php):
- Keep ALL backups for 7 days
- Keep DAILY backups for 16 days
- Keep WEEKLY backups for 8 weeks
- Keep MONTHLY backups for 4 months
- Keep YEARLY backups for 2 years

================================================================================
BACKUP LOCATIONS
================================================================================

Local Storage: storage/app/Laravel/
Backup Config: config/backup.php
Backup Logs: storage/logs/backup.log

To add cloud storage (S3, Dropbox, etc.):
1. Configure disk in config/filesystems.php
2. Add disk name to config/backup.php -> destination -> disks array

================================================================================
TESTING THE BACKUP SYSTEM
================================================================================

Test manual backup via command line:
  php artisan backup:run --only-db

List all backups:
  php artisan backup:list

Check backup status:
  php artisan backup:monitor

Clean old backups:
  php artisan backup:clean

================================================================================
TROUBLESHOOTING
================================================================================

Problem: 500 Error on backup page
Solution: Run these commands:
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  php artisan cache:clear

Problem: Backup creation fails
Solution: 
1. Check MySQL is running (XAMPP Control Panel)
2. Verify database credentials in .env file
3. Check storage/logs/backup.log for errors
4. Ensure storage/app/ directory is writable

Problem: Automated backups not running
Solution:
1. Verify Windows Task Scheduler task exists
2. Check task is enabled and running
3. View task history in Task Scheduler
4. Manually run: php artisan schedule:run

Problem: Cannot download backups
Solution:
1. Check storage/app/Laravel/ directory exists
2. Verify file permissions
3. Check browser console for JavaScript errors

================================================================================
CONFIGURATION
================================================================================

Edit config/backup.php to customize:
- Backup name (line 10)
- Files to include/exclude (lines 17-29)
- Database connections (line 80)
- Storage disks (line 153)
- Email notifications (lines 198-221)
- Cleanup retention policy (lines 290-327)

Email notifications:
Set in .env file:
  BACKUP_NOTIFICATION_EMAIL=admin@example.com
  BACKUP_NOTIFY_ON_SUCCESS=false
  BACKUP_NOTIFY_ON_FAILURE=true
  BACKUP_NOTIFY_ON_UNHEALTHY=true

================================================================================
RESTORING FROM BACKUP
================================================================================

To restore database from backup:

METHOD 1: Using phpMyAdmin
1. Download backup ZIP from web interface
2. Extract the .sql file
3. Open phpMyAdmin
4. Select your database
5. Go to Import tab
6. Choose the .sql file
7. Click "Go"

METHOD 2: Using command line
1. Download and extract backup
2. Run: mysql -u username -p database_name < backup.sql

METHOD 3: Using XAMPP Shell
1. Open XAMPP Shell
2. Navigate to backup location
3. Run: mysql -u root -p mccbot < backup_2025-11-11.sql

⚠️ WARNING: Restoring will OVERWRITE all existing data!
Always create a backup before restoring.

================================================================================
SECURITY NOTES
================================================================================

✅ Only Super Admins can access backup page
✅ All routes protected by SuperAdminAuth middleware
✅ CSRF protection enabled
✅ Backup files stored outside public directory
✅ Direct download via authenticated routes only

Best Practices:
- Regularly download backups to external storage
- Store backups in multiple locations (local + cloud)
- Test restore process periodically
- Keep backup retention policy reasonable for disk space
- Enable email notifications for failures

================================================================================
PACKAGE INFORMATION
================================================================================

Package: spatie/laravel-backup v9.3.6
Documentation: https://spatie.be/docs/laravel-backup
GitHub: https://github.com/spatie/laravel-backup
Support: https://spatie.be/support

================================================================================
SUPPORT & MAINTENANCE
================================================================================

For issues or questions:
1. Check storage/logs/laravel.log
2. Check storage/logs/backup.log
3. Run: php artisan backup:list (shows backup health)
4. Run: php artisan backup:monitor (checks backup status)

Common Commands:
  php artisan backup:run --only-db    # Create database backup
  php artisan backup:list             # List all backups
  php artisan backup:clean            # Remove old backups
  php artisan backup:monitor          # Check backup health

================================================================================
SYSTEM STATUS: ✅ FULLY OPERATIONAL
================================================================================

✓ Backup page accessible
✓ Manual backup creation working
✓ Download functionality working
✓ Delete functionality working
✓ Database connection healthy
✓ Storage directory writable

Next Step: Set up Windows Task Scheduler for automation!

================================================================================
