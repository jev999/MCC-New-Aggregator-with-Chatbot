# Login Throttling System - 3 Attempt Warning with 3-Minute Lockout

## Overview
This implementation adds a robust login throttling system to the unified login form that:
- Tracks failed login attempts per user/IP combination
- Shows warnings after failed attempts
- Locks accounts for 3 minutes after 3 failed attempts
- Displays a real-time countdown timer during lockout
- Automatically resets attempts after successful login

## Features Implemented

### 1. Database Structure
- **Migration**: `2025_10_05_170646_create_login_attempts_table.php`
- **Table**: `login_attempts` with fields:
  - `identifier` (email/username)
  - `login_type` (ms365, superadmin, department-admin, office-admin)
  - `ip_address` (for IP-based tracking)
  - `attempts` (failed attempt count)
  - `last_attempt_at` (timestamp of last attempt)
  - `locked_until` (lockout expiration time)

### 2. Models and Services
- **LoginAttempt Model**: Handles database operations and lockout logic
- **LoginThrottleService**: Manages attempt tracking, lockout checks, and cleanup

### 3. Controller Integration
- **UnifiedAuthController**: Enhanced with throttling checks before and after login attempts
- **New Route**: `/check-lockout-status` for AJAX status checks

### 4. Frontend Enhancements
- **Warning Messages**: Visual feedback for approaching limits
- **Lockout Display**: Error message with countdown timer
- **Real-time Updates**: JavaScript countdown and status checking
- **Form Disabling**: Prevents submission during lockout

## Setup Instructions

### 1. Database Migration
```bash
# Run the migration to create the login_attempts table
php artisan migrate
```

### 2. Clear Cache (if needed)
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 3. Optional: Schedule Cleanup Command
Add to `app/Console/Kernel.php` in the `schedule()` method:
```php
$schedule->command('login:cleanup-attempts')->daily();
```

## How It Works

### Login Flow
1. **Pre-Login Check**: System checks if user/IP is locked
2. **Warning Display**: Shows warning if user has previous failed attempts
3. **Login Attempt**: Processes login through appropriate controller
4. **Post-Login Action**:
   - **Success**: Resets attempt counter
   - **Failure**: Increments counter, locks if limit reached

### Lockout Behavior
- **Attempt 1**: "Previous login attempt failed. 2 attempts remaining."
- **Attempt 2**: "Warning: You have 1 login attempt(s) remaining before your account is temporarily locked."
- **Attempt 3**: "Too many failed attempts. Account locked for 3 minutes."

### Timer Display
- Shows countdown in format: "3m 0s" or "45s"
- Updates every second
- Automatically enables form when expired
- Form submit button shows "Account Locked" during lockout

## Testing Instructions

### 1. Test Failed Attempts
1. Go to the login page
2. Select any login type (MS365, Super Admin, etc.)
3. Enter invalid credentials
4. Submit form multiple times to see:
   - Warning messages after 1st and 2nd failures
   - Lockout message after 3rd failure
   - Countdown timer during lockout

### 2. Test Lockout Timer
1. Trigger lockout (3 failed attempts)
2. Observe countdown timer
3. Wait for timer to reach 0
4. Verify form is re-enabled

### 3. Test Successful Login Reset
1. Make 1-2 failed attempts
2. Login successfully with correct credentials
3. Logout and try failed attempts again
4. Verify counter was reset (starts from 0)

### 4. Test Different Login Types
- Test with MS365 accounts
- Test with admin usernames
- Verify each type tracks separately

### 5. Test IP-based Tracking
- Use different browsers/devices
- Verify lockouts are per IP address

## Files Modified/Created

### New Files
- `database/migrations/2025_10_05_170646_create_login_attempts_table.php`
- `app/Models/LoginAttempt.php`
- `app/Services/LoginThrottleService.php`
- `app/Console/Commands/CleanupLoginAttempts.php`

### Modified Files
- `app/Http/Controllers/UnifiedAuthController.php`
- `resources/views/auth/unified-login.blade.php`
- `routes/web.php`

## Security Features

### 1. Input Validation
- All inputs validated before processing
- Secure patterns prevent injection attacks

### 2. IP-based Tracking
- Prevents cross-user lockouts
- Tracks attempts per IP address

### 3. Time-based Lockouts
- Automatic expiration after 3 minutes
- No permanent lockouts

### 4. Cleanup Mechanism
- Console command to remove expired records
- Prevents database bloat

## Maintenance

### Manual Cleanup
```bash
php artisan login:cleanup-attempts
```

### Reset Specific User
```php
// In tinker or custom command
use App\Models\LoginAttempt;
LoginAttempt::where('identifier', 'user@example.com')
    ->where('login_type', 'ms365')
    ->delete();
```

### Monitor Attempts
```php
// View current locked accounts
use App\Models\LoginAttempt;
LoginAttempt::where('locked_until', '>', now())->get();
```

## Troubleshooting

### 1. Migration Issues
- Ensure database connection is configured
- Check `.env` file for correct database credentials

### 2. JavaScript Not Working
- Check browser console for errors
- Verify CSRF token is present
- Ensure route exists: `/check-lockout-status`

### 3. Lockout Not Working
- Verify migration ran successfully
- Check if `LoginThrottleService` is being injected
- Review error logs for exceptions

### 4. Timer Not Updating
- Check if session has `lockout_time`
- Verify JavaScript is not blocked
- Check browser developer tools

## Configuration

### Customization Options
To modify lockout behavior, edit `LoginThrottleService.php`:

```php
// Change attempt limit (default: 3)
if ($loginAttempt->attempts >= 3) {

// Change lockout duration (default: 3 minutes)
$this->update(['locked_until' => now()->addMinutes(3)]);
```

### Environment Variables
No additional environment variables required. Uses existing database configuration.

## Performance Considerations

### 1. Database Indexes
- Indexes added on frequently queried fields
- Efficient lookups by identifier + login_type

### 2. Cleanup Strategy
- Regular cleanup prevents table growth
- Expired records automatically removed

### 3. AJAX Optimization
- Debounced status checks (500ms delay)
- Only checks when identifier changes

This implementation provides a robust, user-friendly login throttling system that enhances security while maintaining good user experience.
