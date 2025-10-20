# reCAPTCHA Setup Guide for MCC-NAC Portal

## 🔐 Overview

This guide explains how to set up and configure Google reCAPTCHA v2 for your MCC-NAC portal to protect against bots and automated attacks.

## 📋 Prerequisites

- Google reCAPTCHA account
- Access to your website's environment configuration
- Basic understanding of Laravel validation

## 🚀 Step 1: Get reCAPTCHA Keys

### 1.1 Create Google reCAPTCHA Account
1. Go to [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Sign in with your Google account
3. Click **"+"** to create a new site

### 1.2 Configure reCAPTCHA Site
1. **Label**: `MCC-NAC Portal`
2. **reCAPTCHA type**: `reCAPTCHA v2` → `"I'm not a robot" Checkbox`
3. **Domains**: Add your domains:
   - `localhost` (for development)
   - `mcc-nac.com` (for production)
   - `www.mcc-nac.com` (if using www)
4. **Accept Terms**: Check the terms of service
5. Click **Submit**

### 1.3 Get Your Keys
After creating the site, you'll receive:
- **Site Key** (public key)
- **Secret Key** (private key)

## ⚙️ Step 2: Configure Environment Variables

### 2.1 Add to .env File
Add these lines to your `.env` file:

```env
# reCAPTCHA Configuration
NOCAPTCHA_SITEKEY=your-site-key-here
NOCAPTCHA_SECRET=your-secret-key-here
```

### 2.2 Example Configuration
```env
# Development
NOCAPTCHA_SITEKEY=6LcXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
NOCAPTCHA_SECRET=6LcXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Production
NOCAPTCHA_SITEKEY=6LdXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
NOCAPTCHA_SECRET=6LdXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

## 🛠️ Step 3: Package Installation (Already Done)

The reCAPTCHA package has been installed:
```bash
composer require anhskohbo/no-captcha
```

## 📝 Step 4: Implementation Details

### 4.1 Forms with reCAPTCHA

#### **Unified Login Form**
- **File**: `resources/views/auth/unified-login.blade.php`
- **Features**: 
  - Shows reCAPTCHA when login type is selected
  - Validates reCAPTCHA on form submission
  - Error handling for failed verification

#### **MS365 Registration Form**
- **File**: `resources/views/auth/ms365-register.blade.php`
- **Features**:
  - Always visible reCAPTCHA
  - Required for registration completion
  - Integrated with existing form validation

### 4.2 Controller Validation

#### **UnifiedAuthController**
```php
$request->validate([
    'login_type' => 'required|in:user,ms365,superadmin,department-admin,office-admin',
    'ms365_account' => $secureRules['ms365_account'],
    'username' => $secureRules['username'],
    'password' => $secureRules['password'],
    'g-recaptcha-response' => 'required|captcha', // ← reCAPTCHA validation
], $secureMessages);
```

#### **MS365OAuthController**
```php
$request->validate([
    'token' => 'required|string',
    'ms365_account' => [
        'required',
        'email',
        'regex:/^[a-zA-Z0-9._%+-]+@.*\.edu\.ph$/'
    ],
    'g-recaptcha-response' => 'required|captcha', // ← reCAPTCHA validation
    // ... other fields
]);
```

## 🎨 Step 5: Styling and Customization

### 5.1 reCAPTCHA Container Styling
The reCAPTCHA widget is styled to match your portal's design:

```css
.recaptcha-container {
    margin: 1rem 0;
    display: flex;
    justify-content: center;
}

.recaptcha-container .g-recaptcha {
    transform: scale(0.9);
    transform-origin: center;
}
```

### 5.2 Error Message Styling
```css
.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}
```

## 🔧 Step 6: Testing

### 6.1 Development Testing
1. **Start your local server**:
   ```bash
   php artisan serve
   ```

2. **Test login form**:
   - Go to `/login`
   - Select a login type
   - Verify reCAPTCHA appears
   - Test with and without completing reCAPTCHA

3. **Test registration form**:
   - Go to `/ms365/signup`
   - Complete the registration flow
   - Verify reCAPTCHA is required

### 6.2 Production Testing
1. **Deploy to production**
2. **Test with real domains**
3. **Verify reCAPTCHA works with production keys**

## 🚨 Step 7: Troubleshooting

### 7.1 Common Issues

#### **reCAPTCHA Not Showing**
- **Cause**: Missing or incorrect site key
- **Solution**: Verify `NOCAPTCHA_SITEKEY` in `.env`
- **Check**: Domain matches reCAPTCHA configuration

#### **"Invalid reCAPTCHA" Error**
- **Cause**: Incorrect secret key or server-side validation failure
- **Solution**: Verify `NOCAPTCHA_SECRET` in `.env`
- **Check**: Network connectivity to Google servers

#### **reCAPTCHA Not Required**
- **Cause**: Validation rule not added to controller
- **Solution**: Ensure `'g-recaptcha-response' => 'required|captcha'` is in validation rules

### 7.2 Debug Mode
Enable debug mode in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Check logs for reCAPTCHA validation errors:
```bash
tail -f storage/logs/laravel.log
```

## 🔒 Step 8: Security Considerations

### 8.1 Key Security
- **Never commit keys to version control**
- **Use different keys for development/production**
- **Rotate keys periodically**
- **Monitor reCAPTCHA analytics**

### 8.2 Additional Protection
- **Rate limiting**: Already implemented in your controllers
- **CSRF protection**: Laravel's built-in CSRF tokens
- **Input validation**: Comprehensive validation rules
- **Security headers**: CSP and other security headers

## 📊 Step 9: Monitoring and Analytics

### 9.1 reCAPTCHA Analytics
1. **Go to reCAPTCHA Admin Console**
2. **View site statistics**:
   - Total requests
   - Success rate
   - Bot detection rate
   - Geographic distribution

### 9.2 Application Logs
Monitor your application logs for:
- Failed reCAPTCHA attempts
- Suspicious activity patterns
- Error rates

## 🎯 Step 10: Advanced Configuration

### 10.1 Invisible reCAPTCHA (Optional)
For a more seamless user experience, you can implement invisible reCAPTCHA:

```php
// In your Blade template
{!! NoCaptcha::displaySubmit('form-id', 'Submit', ['data-badge' => 'inline']) !!}
```

### 10.2 Custom Styling
```css
/* Custom reCAPTCHA styling */
.g-recaptcha {
    margin: 20px 0;
}

.g-recaptcha iframe {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
```

## ✅ Step 11: Verification Checklist

- [ ] reCAPTCHA keys configured in `.env`
- [ ] Login form shows reCAPTCHA
- [ ] Registration form shows reCAPTCHA
- [ ] Controllers validate reCAPTCHA
- [ ] Error messages display correctly
- [ ] Styling matches portal design
- [ ] Works in development environment
- [ ] Works in production environment
- [ ] Analytics tracking enabled
- [ ] Security monitoring active

## 🚀 Step 12: Go Live!

Once all tests pass:

1. **Deploy to production**
2. **Update DNS if needed**
3. **Monitor for 24-48 hours**
4. **Check analytics dashboard**
5. **Document any issues**

## 📞 Support

If you encounter issues:

1. **Check the logs**: `storage/logs/laravel.log`
2. **Verify configuration**: `.env` file
3. **Test locally first**: Development environment
4. **Check reCAPTCHA console**: Google Admin Console
5. **Review documentation**: [Laravel reCAPTCHA Package](https://github.com/anhskohbo/no-captcha)

## 🎉 Success!

Your MCC-NAC portal now has enterprise-level bot protection with Google reCAPTCHA v2! 

**Security Features Added:**
- ✅ Bot protection on login
- ✅ Bot protection on registration  
- ✅ Automated attack prevention
- ✅ User experience maintained
- ✅ Analytics and monitoring
- ✅ Production-ready implementation

Your portal is now significantly more secure against automated attacks! 🔒✨
