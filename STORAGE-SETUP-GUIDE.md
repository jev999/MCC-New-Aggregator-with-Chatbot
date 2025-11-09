# 📦 Laravel Media Storage Setup Guide

This guide helps you configure Laravel's media storage to make uploaded files (images, videos) accessible from the web.

## ✅ Completed Automatically

The following steps have been completed for you:

1. ✅ **Symbolic Link Created**: `public/storage` → `storage/app/public`
2. ✅ **.htaccess Updated**: Added storage file access rules
3. ✅ **Laravel Caches Cleared**: Config, routes, and views cleared and recached
4. ✅ **Diagnostic Script Created**: `storage-diagnostics.php` for testing

---

## 🔧 Manual Steps Required

### 1. Verify APP_URL in .env File

Open your `.env` file and ensure `APP_URL` matches your domain:

**For localhost (XAMPP):**
```env
APP_URL=http://localhost/MCC-News-Aggregator-with-Chatbot-main
```

**For live server:**
```env
APP_URL=https://mcc-nac.com
```

After changing `.env`, run:
```bash
php artisan config:cache
```

### 2. Check File Permissions (Important!)

Ensure the following directories have correct permissions:

**On Windows (XAMPP):**
- No action needed usually, but ensure your web server user has write access

**On Linux/Mac or cPanel:**
```bash
# From your Laravel project root
chmod -R 755 storage
chmod -R 755 public
chmod -R 775 storage/app/public
chmod -R 775 storage/logs
chmod -R 775 storage/framework
```

**Via cPanel File Manager:**
1. Navigate to `storage/` folder
2. Right-click → Permissions → Set to `755`
3. Check "Recurse into subdirectories"
4. Navigate to `public/` folder
5. Repeat the process

### 3. Run the Diagnostic Script

Visit the diagnostic script in your browser to verify everything is working:

**Localhost:**
```
http://localhost/MCC-News-Aggregator-with-Chatbot-main/storage-diagnostics.php
```

**Live Server:**
```
https://mcc-nac.com/storage-diagnostics.php
```

The script will check:
- ✅ PHP configuration
- ✅ Directory structure
- ✅ Symbolic link validity
- ✅ File write permissions
- ✅ Existing uploaded files
- ✅ URL accessibility

**⚠️ IMPORTANT: Delete the diagnostic script after testing!**
```bash
rm storage-diagnostics.php
```

### 4. Test File Upload

1. Log into your admin panel
2. Create a new announcement/event/news with an image
3. Save and view the content
4. Check if the image displays correctly

### 5. Verify Uploaded Files Are Accessible

After uploading a file, test direct URL access:

**Example URLs:**
```
http://localhost/MCC-News-Aggregator-with-Chatbot-main/storage/announcement-images/filename.jpg
https://mcc-nac.com/storage/announcement-images/filename.jpg
```

If the image loads ✅ → Everything is working!

If you get 404 ❌ → Check the troubleshooting section below

---

## 🚀 How File Uploads Work in Your Application

### Controller Logic (Already Implemented)

Your controllers use the correct storage method:

```php
// Example from AnnouncementController.php
if ($request->hasFile('images')) {
    foreach ($request->file('images') as $image) {
        $imagePaths[] = $image->store('announcement-images', 'public');
    }
}
```

This saves files to: `storage/app/public/announcement-images/`

### View Logic (Already Implemented)

Your Blade templates use the correct URL generation:

```blade
{{-- Example from show.blade.php --}}
<img src="{{ asset('storage/' . $announcement->image_path) }}" alt="Image">
```

This generates URL: `https://yourdomain.com/storage/announcement-images/filename.jpg`

### Storage Directory Structure

```
storage/
├── app/
│   ├── public/               ← Files stored here
│   │   ├── announcement-images/
│   │   ├── event-images/
│   │   └── profile_pictures/
│   └── private/              ← Private files (not web-accessible)
│
public/
└── storage/                  ← Symbolic link → storage/app/public
```

---

## 🔍 Troubleshooting Common Issues

### Issue 1: Images Show 404 Error

**Symptoms:** Uploaded images return 404 Not Found

**Solutions:**

1. **Verify symbolic link exists:**
   ```bash
   # Windows (check in File Explorer)
   # Look for storage folder inside public/
   # It should show as a shortcut/link
   
   # Linux/Mac
   ls -la public/storage
   # Should show: public/storage -> ../storage/app/public
   ```

2. **Recreate symbolic link if broken:**
   ```bash
   # Delete existing link
   rm public/storage  # Linux/Mac
   rmdir public\storage  # Windows
   
   # Recreate link
   php artisan storage:link
   ```

3. **Check .htaccess rules:**
   - Ensure the storage access rules are present (already added)
   - Verify `mod_rewrite` is enabled in Apache

### Issue 2: Permission Denied Errors

**Symptoms:** Cannot upload files, "Permission denied" errors

**Solutions:**

```bash
# Set correct ownership (Linux/Mac)
chown -R www-data:www-data storage
chown -R www-data:www-data public

# Set correct permissions
chmod -R 755 storage
chmod -R 755 public
```

**cPanel users:**
- Use File Manager → Permissions → 755 (with recursion)

### Issue 3: Files Upload But Don't Display

**Symptoms:** Files save successfully but images don't show in browser

**Possible Causes & Solutions:**

1. **Wrong APP_URL:**
   - Check `.env` → `APP_URL` must match your domain
   - Run `php artisan config:cache` after changing

2. **Wrong file path in database:**
   - File paths should NOT include `/storage/app/public/`
   - Correct: `announcement-images/file.jpg`
   - Wrong: `/storage/app/public/announcement-images/file.jpg`

3. **HTTPS/HTTP mismatch:**
   - If site uses HTTPS, ensure `APP_URL=https://...`
   - Mixed content warnings can block image loading

### Issue 4: Symlink Already Exists Error

**Symptoms:** `php artisan storage:link` says link already exists

**This is normal!** It means the link is already created. To verify it's correct:

```bash
# View diagnostic script in browser (see Step 3 above)
```

If link is broken, remove and recreate:
```bash
rm public/storage
php artisan storage:link
```

### Issue 5: Working on Localhost but Not on Live Server

**Solutions:**

1. **Update APP_URL in .env:**
   ```env
   # Change from:
   APP_URL=http://localhost
   
   # To:
   APP_URL=https://yourdomain.com
   ```

2. **Recreate storage link on server:**
   ```bash
   php artisan storage:link
   ```

3. **Check file permissions on server:**
   ```bash
   chmod -R 755 storage
   chmod -R 755 public
   ```

4. **Ensure .htaccess uploaded correctly:**
   - Verify `public/.htaccess` exists on server
   - Check if storage access rules are present

---

## 📋 Quick Reference Commands

### Essential Commands

```bash
# Create storage symbolic link
php artisan storage:link

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache configurations
php artisan config:cache
php artisan route:cache

# Fix permissions (Linux/Mac)
chmod -R 755 storage
chmod -R 755 public
chmod -R 775 storage/app/public

# View storage status
ls -la public/storage  # Should show symlink
ls -la storage/app/public  # Should show directories
```

### Diagnostic Commands

```bash
# Check PHP configuration
php -i | grep upload

# Check disk space
df -h

# Check Apache modules (Linux)
apache2ctl -M | grep rewrite

# Test file write permissions
touch storage/app/public/test.txt
rm storage/app/public/test.txt
```

---

## 🎯 Verification Checklist

Use this checklist to ensure everything is configured correctly:

- [ ] Symbolic link exists: `public/storage` → `storage/app/public`
- [ ] APP_URL in `.env` matches your domain
- [ ] `storage/` directory has 755 permissions
- [ ] `public/` directory has 755 permissions
- [ ] Laravel caches cleared and recached
- [ ] Diagnostic script shows all green checkmarks
- [ ] Test upload succeeds
- [ ] Uploaded image displays on page
- [ ] Direct URL to image file works
- [ ] No console errors in browser
- [ ] Diagnostic script deleted (security)

---

## 🔒 Security Notes

1. **Delete diagnostic script after testing:**
   ```bash
   rm storage-diagnostics.php
   ```

2. **Never commit `.env` to version control** (already gitignored)

3. **Private files should go in `storage/app/private/`** not `storage/app/public/`

4. **User-uploaded files should be validated:**
   - File type (MIME type checking)
   - File size limits
   - Virus scanning (for production)

5. **Consider adding to `.htaccess` for extra security:**
   ```apache
   # Prevent PHP execution in uploads folder
   <Directory "storage/app/public">
       php_flag engine off
   </Directory>
   ```

---

## 📞 Need More Help?

If you're still experiencing issues:

1. Run the diagnostic script and share the results
2. Check Laravel logs: `storage/logs/laravel.log`
3. Check Apache error logs
4. Verify exact error messages in browser console (F12)
5. Test with a simple test image first before complex uploads

---

## ✨ Summary

Your Laravel application is now configured with:

- ✅ Symbolic link for storage access
- ✅ Updated .htaccess for media serving
- ✅ Proper file upload controllers
- ✅ Correct asset URL generation in views
- ✅ Diagnostic tools for troubleshooting

**Next Steps:**
1. Update APP_URL in `.env` if needed
2. Run diagnostic script
3. Test file upload
4. Delete diagnostic script
5. Deploy to production (if applicable)

**File URLs Format:**
```
Local:  http://localhost/MCC-News-Aggregator-with-Chatbot-main/storage/announcement-images/file.jpg
Live:   https://mcc-nac.com/storage/announcement-images/file.jpg
```

---

Last Updated: 2025-01-09
Version: 1.0
