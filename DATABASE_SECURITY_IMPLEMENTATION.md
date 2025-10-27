# Database Security Implementation

This document outlines the comprehensive database security measures implemented in the MCC News Aggregator application.

## Overview

The application now implements multiple layers of database security including:
- Prepared statements via Eloquent ORM
- Automatic daily backups
- Encrypted ID transmission
- UUID for sensitive records
- Least privilege database user setup

## 1. Prepared Statements & Eloquent ORM

All database queries now use Eloquent ORM or Query Builder with prepared statements, which automatically prevents SQL injection attacks.

### Models Created

#### PasswordReset Model
- **Location**: `app/Models/PasswordReset.php`
- **Purpose**: Manages password reset tokens securely
- **Methods**:
  - `updateOrCreateToken($email, $token)` - Creates or updates a reset token
  - `verifyToken($email, $token)` - Verifies if a token is valid
  - `deleteToken($email)` - Removes a reset token
  - `isExpired($minutes)` - Checks if token is expired

#### RegistrationToken Model
- **Location**: `app/Models/RegistrationToken.php`
- **Purpose**: Manages registration tokens securely
- **Methods**:
  - `createToken($email, $token, $expiresAt)` - Creates or updates a registration token
  - `findValidToken($token)` - Finds a valid, non-expired token
  - `deleteToken($token)` - Removes a registration token

### Changes Made

All `DB::table()` queries have been replaced with Eloquent model calls:

**Before:**
```php
DB::table('password_resets')->updateOrInsert(
    ['email' => $request->ms365_account],
    ['token' => Hash::make($token), 'created_at' => now()]
);
```

**After:**
```php
PasswordReset::updateOrCreateToken($request->ms365_account, $token);
```

## 2. Automatic Daily Backups

### Installation

The Spatie Laravel Backup package has been installed:

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

### Configuration

- **Config File**: `config/backup.php`
- **Schedule**: Daily at 2:00 AM
- **Cleanup**: Daily at 3:00 AM
- **Health Check**: Daily at 4:00 AM

### Backup Retention Policy

- Keep all backups for: **7 days**
- Keep daily backups for: **16 days**
- Keep weekly backups for: **8 weeks**
- Keep monthly backups for: **4 months**
- Keep yearly backups for: **2 years**
- Maximum storage: **5 GB**

### Environment Variables

Add to your `.env` file:

```env
# Backup notifications
BACKUP_NOTIFY_ON_SUCCESS=false
BACKUP_NOTIFY_ON_FAILURE=true
BACKUP_NOTIFY_ON_UNHEALTHY=true
BACKUP_NOTIFICATION_EMAIL=admin@mcc.edu.ph
BACKUP_ARCHIVE_PASSWORD=your-secure-password-here
```

### Manual Backup Commands

```bash
# Create a backup
php artisan backup:run

# Clean old backups
php artisan backup:clean

# Monitor backup health
php artisan backup:monitor

# List all backups
php artisan backup:list
```

## 3. Encrypted ID Transmission

### EncryptionHelper Class

**Location**: `app/Helpers/EncryptionHelper.php`

Provides secure encryption for IDs and PII when transmitting data.

### Usage

```php
use App\Helpers\EncryptionHelper;

// Encrypt an ID for transmission
$encryptedId = EncryptionHelper::encryptId($user->id);

// Decrypt an encrypted ID
$userId = EncryptionHelper::decryptId($encryptedId);

// Encrypt PII data
$encryptedEmail = EncryptionHelper::encryptPII($email);

// Decrypt PII data
$email = EncryptionHelper::decryptPII($encryptedEmail);

// Generate UUID
$uuid = EncryptionHelper::generateUuid();
```

### Example in Controller

```php
// Instead of exposing raw IDs
return response()->json([
    'user_id' => EncryptionHelper::encryptId($user->id),
    'email' => EncryptionHelper::encryptPII($user->ms365_account)
]);
```

## 4. UUID for Sensitive Records

### UsesUuid Trait

**Location**: `app/Traits/UsesUuid.php`

Provides automatic UUID generation for sensitive models.

### Implementation

To use UUID in your models:

```php
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use UsesUuid;
    
    protected $keyType = 'string';
    public $incrementing = false;
}
```

### Benefits

- Non-guessable IDs
- Cannot enumerate records by incrementing IDs
- Better security for public-facing APIs
- UUID format: `550e8400-e29b-41d4-a716-446655440000`

## 5. Dedicated Database User Setup

### MySQL Setup

Create a dedicated database user with least privilege:

```sql
-- Create database user
CREATE USER 'mcc_app_user'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant only necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON mcc_news_aggregator.* TO 'mcc_app_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;
```

### Update .env Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mcc_news_aggregator
DB_USERNAME=mcc_app_user
DB_PASSWORD=secure_password_here
```

### Security Best Practices

1. **Never grant ALL PRIVILEGES** - Only grant what's necessary
2. **Use strong passwords** - Minimum 16 characters, mix of uppercase, lowercase, numbers, special characters
3. **Limit host access** - Use `@'localhost'` instead of `@'%'` for local applications
4. **Regular audits** - Review user privileges periodically
5. **Separate users** - Use different users for different environments (dev, staging, production)

### Recommended Privileges

For this application:
- ✅ SELECT - Read data
- ✅ INSERT - Create records
- ✅ UPDATE - Modify existing records
- ✅ DELETE - Remove records
- ❌ CREATE - Not needed
- ❌ DROP - Not needed
- ❌ ALTER - Not needed (migrations should be done separately)
- ❌ GRANT - Not needed

### Alternative: Multiple Users for Different Operations

For enhanced security, you can create separate users:

```sql
-- Read-only user for reporting
CREATE USER 'mcc_readonly'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT ON mcc_news_aggregator.* TO 'mcc_readonly'@'localhost';

-- Application user
CREATE USER 'mcc_app'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON mcc_news_aggregator.* TO 'mcc_app'@'localhost';

-- Admin user (only for migrations)
CREATE USER 'mcc_admin'@'localhost' IDENTIFIED BY 'secure_admin_password';
GRANT ALL PRIVILEGES ON mcc_news_aggregator.* TO 'mcc_admin'@'localhost';
```

## 6. Testing the Implementation

### Verify Prepared Statements

Check that all queries use Eloquent:

```bash
grep -r "DB::table" app/
# Should return no results
```

### Test Backup System

```bash
# Run a test backup
php artisan backup:run

# Check if backup was created
ls -lh storage/app/Laravel-backup/

# Verify cleanup
php artisan backup:clean
```

### Test Encryption

Create a test script:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Helpers\EncryptionHelper;

$id = 123;
$encrypted = EncryptionHelper::encryptId($id);
$decrypted = EncryptionHelper::decryptId($encrypted);

echo "Original ID: {$id}\n";
echo "Encrypted: {$encrypted}\n";
echo "Decrypted: {$decrypted}\n";
```

### Verify UUID Generation

Check if models are using UUID:

```php
// In a test
$user = User::create([...]);
echo $user->id; // Should output a UUID string
```

## 7. Security Checklist

- ✅ All database queries use Eloquent ORM
- ✅ No raw SQL queries in application code
- ✅ Daily automatic backups configured
- ✅ Backup encryption enabled
- ✅ Backup retention policy configured
- ✅ Encrypted ID helpers available
- ✅ UUID trait available for sensitive models
- ✅ Database user with least privilege
- ✅ No public-facing database credentials

## 8. Production Deployment

### Before Deploying

1. Create production database user with least privilege
2. Update `.env` with production credentials
3. Test backup system: `php artisan backup:run`
4. Verify encryption: Test encrypted IDs
5. Configure backup notifications
6. Set up SSL for database connections
7. Enable MySQL query logging (optional, for audit)
8. Configure firewall rules for database server

### Backup Storage

Consider configuring cloud storage for backups:

```php
// In config/filesystems.php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
],
```

Then update `config/backup.php`:

```php
'disks' => [
    'local',
    's3', // Add cloud storage
],
```

## 9. Additional Security Recommendations

### SSL Database Connection

Enable SSL for database connections in `.env`:

```env
DB_SSLMODE=require
```

### Regular Updates

- Keep Laravel framework updated
- Keep backup package updated
- Keep PHP version updated
- Keep MySQL version updated

### Monitoring

- Monitor backup success/failure
- Monitor failed login attempts
- Monitor database connection errors
- Monitor slow queries

### Auditing

Enable MySQL audit logging:

```sql
SET GLOBAL general_log = 'ON';
SET GLOBAL general_log_file = '/var/log/mysql/general.log';
```

## 10. Troubleshooting

### Backup Issues

```bash
# Check backup logs
tail -f storage/logs/laravel.log

# Manually test backup
php artisan backup:run --only-db

# Test specific disk
php artisan backup:run --only-files
```

### Encryption Issues

```php
// Check if encryption key exists
php artisan key:generate

// Verify APP_KEY in .env
```

### Database Connection Issues

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check database user privileges
mysql -u mcc_app_user -p
```

## 11. Conclusion

This implementation provides comprehensive database security through:
- Protection against SQL injection via prepared statements
- Automatic backups for disaster recovery
- Encrypted data transmission
- Non-guessable record identifiers
- Least privilege database access

All changes are backward compatible and do not affect existing functionality.

## Support

For issues or questions regarding database security implementation, contact the development team.

