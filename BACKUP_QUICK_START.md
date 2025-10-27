# Quick Start: Backup Configuration

## 1. Add Environment Variables

Add these variables to your `.env` file:

```env
# Backup notifications (Optional)
BACKUP_NOTIFY_ON_SUCCESS=false
BACKUP_NOTIFY_ON_FAILURE=true
BACKUP_NOTIFY_ON_UNHEALTHY=true
BACKUP_NOTIFICATION_EMAIL=admin@mcc.edu.ph

# Backup archive password (Optional but recommended)
BACKUP_ARCHIVE_PASSWORD=your-strong-password-here
```

## 2. Test Backup

Run a test backup to ensure everything works:

```bash
php artisan backup:run
```

## 3. View Backup Files

Check if the backup was created:

```bash
# Windows
dir storage\app\Laravel-backup

# Linux/Mac
ls -lh storage/app/Laravel-backup/
```

## 4. Enable Scheduled Backups

For production, ensure the scheduler runs:

### Option 1: Using Cron (Linux/Mac)

Add to crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Option 2: Using Task Scheduler (Windows)

Create a scheduled task that runs:

```batch
php C:\path-to-project\artisan schedule:run
```

### Option 3: Using Supervisor (Recommended)

Install Supervisor and create a config file:

```ini
[program:laravel-scheduler]
process_name=%(program_name)s
command=/usr/bin/php /path-to-project/artisan schedule:run
directory=/path-to-project
user=www-data
autostart=true
autorestart=true
stdout_logfile=/var/log/laravel-scheduler.log
redirect_stderr=true
```

## 5. Backups Are Automatically Scheduled

The following backups run automatically:

- **Daily Backup**: 2:00 AM every day
- **Cleanup**: 3:00 AM every day
- **Health Check**: 4:00 AM every day

## 6. Backup Commands

```bash
# Manual backup
php artisan backup:run

# Clean old backups
php artisan backup:clean

# Check backup health
php artisan backup:monitor

# List all backups
php artisan backup:list
```

## 7. Storage Locations

Backups are stored in:
- `storage/app/Laravel-backup/` - Local storage

To add cloud storage (recommended for production):

1. Configure your cloud disk in `config/filesystems.php`
2. Update `config/backup.php`:

```php
'disks' => [
    'local',
    's3', // or 'google', 'dropbox', etc.
],
```

## 8. Backup Retention

Current retention policy:
- Last 7 days: All backups kept
- Days 8-16: Daily backups only
- Weeks 3-8: Weekly backups only
- Months 5-8: Monthly backups only
- Years 2-3: Yearly backups only
- Maximum storage: 5 GB

## 9. Monitoring

To receive email notifications:

```env
BACKUP_NOTIFY_ON_FAILURE=true
BACKUP_NOTIFY_ON_UNHEALTHY=true
BACKUP_NOTIFICATION_EMAIL=your-email@mcc.edu.ph
```

## 10. Restore from Backup

To restore from a backup:

```bash
# List available backups
php artisan backup:list

# Restore a specific backup
# (Manual process - extract zip file and restore database)
```

## Troubleshooting

### Backup fails

```bash
# Check logs
tail -f storage/logs/laravel.log

# Run backup with verbose output
php artisan backup:run --only-db -v
```

### Scheduler not running

```bash
# Test the scheduler manually
php artisan schedule:run

# Check if cron is installed
which php
```

### Permission issues

```bash
# Fix storage permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Production Checklist

- [ ] Environment variables configured
- [ ] Test backup successful
- [ ] Scheduler running
- [ ] Email notifications configured
- [ ] Cloud storage configured (recommended)
- [ ] Backup password set
- [ ] Monitor logs regularly

## Need Help?

See full documentation: `DATABASE_SECURITY_IMPLEMENTATION.md`

