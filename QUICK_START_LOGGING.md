# Quick Start: Logging and Monitoring

## 🚀 Quick Setup

### 1. Run Database Migration

```bash
php artisan migrate
```

### 2. Verify Installation

```bash
# Check if table exists
php artisan tinker
>>> \DB::table('activity_logs')->count()
```

### 3. Test Logging

```php
use App\Services\ActivityLogService;

// In any controller
$activityLog = app(ActivityLogService::class);
$activityLog->logActivity('test', 'Testing logging');
```

---

## 📝 Common Usage

### Log User Activity

```php
app(ActivityLogService::class)->logActivity(
    'page_viewed',
    'User viewed news page',
    ['news_id' => 1]
);
```

### Log Sensitive Operation

```php
app(ActivityLogService::class)->logSensitiveActivity(
    'user_deleted',
    'Admin deleted user account',
    ['deleted_user_id' => 5]
);
```

### Log Unauthorized Access

```php
app(ActivityLogService::class)->logUnauthorizedAccess(
    'admin_panel',
    'User attempted admin access without permission'
);
```

### Log Suspicious Activity

```php
app(ActivityLogService::class)->logSuspiciousActivity(
    'Failed login attempts exceeded',
    'high',
    ['attempt_count' => 10]
);
```

---

## 🔍 View Logs

### Database Logs

```sql
SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100;
SELECT * FROM activity_logs WHERE is_sensitive = TRUE;
SELECT * FROM activity_logs WHERE action = 'user_deleted';
```

### File Logs

```bash
# General logs
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Security logs
tail -f storage/logs/security-$(date +%Y-%m-%d).log

# Activity logs
tail -f storage/logs/activity-$(date +%Y-%m-%d).log

# Monitoring logs
tail -f storage/logs/monitoring-$(date +%Y-%m-%d).log
```

---

## 🎯 Integration Points

### Add to Controllers

```php
use App\Services\ActivityLogService;

class YourController extends Controller
{
    protected $activityLog;

    public function __construct(ActivityLogService $activityLog)
    {
        $this->activityLog = $activityLog;
    }

    public function store(Request $request)
    {
        // Your code
        $this->activityLog->logActivity('resource_created', 'Created resource');
    }
}
```

---

## ⚠️ Common Patterns

### Log CRUD Operations

```php
// Create
$activityLog->logActivity('created', 'Resource created', ['id' => $id]);

// Update
$activityLog->logSensitiveActivity('updated', 'Resource updated', ['id' => $id]);

// Delete
$activityLog->logSensitiveActivity('deleted', 'Resource deleted', ['id' => $id]);
```

### Log Authorization Checks

```php
if (!auth()->user()->can('edit', $resource)) {
    app(ActivityLogService::class)->logUnauthorizedAccess(
        'resource.edit',
        'Attempted to edit without permission'
    );
    abort(403);
}
```

### Log Failed Validations

```php
if ($validator->fails()) {
    app(ActivityLogService::class)->logActivity(
        'validation_failed',
        'Form validation failed',
        ['errors' => $validator->errors()->toArray()]
    );
}
```

---

## 🔧 Configuration

### Environment Variables

```env
# Production
APP_DEBUG=false
APP_ENV=production
LOG_LEVEL=error

# Development
APP_DEBUG=true
APP_ENV=local
LOG_LEVEL=debug
```

### Log Retention

Edit `config/logging.php`:
```php
'activity' => [
    'days' => 30, // Change retention period
],
```

---

## 📊 Monitoring Dashboard

### Create View

```php
// Controller
Route::get('admin/logs', function() {
    $logs = \App\Models\ActivityLog::latest()->paginate(50);
    return view('admin.logs', compact('logs'));
});

// View
@foreach($logs as $log)
    <tr>
        <td>{{ $log->created_at }}</td>
        <td>{{ $log->user_id }}</td>
        <td>{{ $log->action }}</td>
        <td>{{ $log->description }}</td>
    </tr>
@endforeach
```

---

## 🐛 Troubleshooting

### Logs Not Appearing

```bash
# Check permissions
chmod -R 775 storage/logs/

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### Database Issues

```bash
# Check migration
php artisan migrate:status

# Rollback and re-run
php artisan migrate:rollback
php artisan migrate
```

---

## 📚 More Information

- **Full Documentation**: See `LOGGING_AND_MONITORING_GUIDE.md`
- **Implementation Summary**: See `LOGGING_MONITORING_IMPLEMENTATION_SUMMARY.md`
- **Configuration**: See `config/logging.php`

---

## ✅ Status

- ✅ Activity Logging - Ready
- ✅ Error Logging - Ready
- ✅ Real-Time Monitoring - Ready
- ✅ Security Alerts - Ready

**All systems operational!** 🎉

