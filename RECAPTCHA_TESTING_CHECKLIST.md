# reCAPTCHA v3 Testing Checklist

## Pre-Testing Setup

- [ ] Verify `.env.local` has correct reCAPTCHA keys
- [ ] Verify `config/services.php` has reCAPTCHA configuration
- [ ] Clear browser cache and cookies
- [ ] Open browser Developer Tools (F12)

## Client-Side Testing

### 1. Script Loading
- [ ] Go to `/login` page
- [ ] Open Developer Tools → Network tab
- [ ] Verify `recaptcha/api.js` script loads successfully
- [ ] Check Console tab for any JavaScript errors
- [ ] Verify no CORS errors

### 2. Form Submission
- [ ] Select a login type (e.g., MS365)
- [ ] Enter any credentials
- [ ] Click Submit button
- [ ] Check Console for "grecaptcha.ready" execution
- [ ] Verify no "Login form not found" error

### 3. Token Generation
- [ ] In Console, verify grecaptcha.execute() is called
- [ ] Check that token is generated (should be long string)
- [ ] Verify hidden input `g-recaptcha-response` is added to form
- [ ] Confirm form submits with token

## Server-Side Testing

### 1. Validation Rule
- [ ] Check `app/Rules/RecaptchaV3.php` exists
- [ ] Verify it implements ValidationRule interface
- [ ] Confirm it makes POST request to Google API

### 2. Controller Integration
- [ ] Open `app/Http/Controllers/UnifiedAuthController.php`
- [ ] Verify reCAPTCHA validation is in login() method
- [ ] Check that validation rule is applied: `new RecaptchaV3()`
- [ ] Confirm error handling for failed validation

### 3. Logging
- [ ] Check `storage/logs/laravel.log`
- [ ] Look for "reCAPTCHA v3 verification response"
- [ ] Verify score is logged
- [ ] Check for any error codes

## Functional Testing

### Test Case 1: Valid Credentials + High Score
1. Go to `/login`
2. Select login type
3. Enter valid credentials
4. Submit form
5. **Expected**: Login succeeds, redirected to dashboard

### Test Case 2: Invalid Credentials + High Score
1. Go to `/login`
2. Select login type
3. Enter invalid credentials
4. Submit form
5. **Expected**: Failed attempt logged, warning message shown

### Test Case 3: Suspicious Request (Low Score)
1. Go to `/login`
2. Use automated tools or VPN
3. Submit form rapidly
4. **Expected**: reCAPTCHA fails, attempt incremented, lockout after 3 attempts

### Test Case 4: Account Lockout
1. Make 3 failed login attempts
2. **Expected**: Account locked for 3 minutes
3. Verify countdown timer appears
4. Wait for timer to expire
5. **Expected**: Form re-enables, can login again

### Test Case 5: Different Login Types
- [ ] Test MS365 login with reCAPTCHA
- [ ] Test Super Admin login with reCAPTCHA
- [ ] Test Department Admin login with reCAPTCHA
- [ ] Test Office Admin login with reCAPTCHA
- [ ] Verify each type works independently

## Error Handling Testing

### Test Case 6: Missing reCAPTCHA Keys
1. Temporarily remove GOOGLE_RECAPTCHA_SITE_KEY from `.env.local`
2. Reload login page
3. **Expected**: Page loads without reCAPTCHA script
4. **Expected**: Login still works (validation skipped)
5. Restore keys

### Test Case 7: Invalid Secret Key
1. Change GOOGLE_RECAPTCHA_SECRET_KEY to invalid value
2. Submit login form
3. **Expected**: Verification fails with error message
4. Check logs for error codes
5. Restore correct key

### Test Case 8: Network Error
1. Disable internet connection
2. Submit login form
3. **Expected**: Graceful error handling
4. Check logs for exception
5. Restore connection

## Performance Testing

### Test Case 9: Response Time
1. Open Developer Tools → Network tab
2. Submit login form
3. Measure time for:
   - [ ] reCAPTCHA token generation (should be ~100ms)
   - [ ] Server verification (should be ~200-300ms)
   - [ ] Total login time (should be ~300-400ms)

### Test Case 10: Multiple Submissions
1. Submit form 10 times rapidly
2. **Expected**: Each submission gets unique token
3. **Expected**: No rate limiting issues
4. Check logs for all verification attempts

## Security Testing

### Test Case 11: Token Reuse
1. Capture token from first submission
2. Try to reuse same token in second submission
3. **Expected**: Second submission fails (token already used)

### Test Case 12: Token Tampering
1. Intercept request and modify token
2. Submit form with modified token
3. **Expected**: Verification fails
4. Check logs for error

### Test Case 13: Missing Token
1. Intercept request and remove token
2. Submit form without token
3. **Expected**: Validation fails with "required" error

## Browser Compatibility

- [ ] Test in Chrome
- [ ] Test in Firefox
- [ ] Test in Safari
- [ ] Test in Edge
- [ ] Test on mobile (iOS Safari)
- [ ] Test on mobile (Android Chrome)

## Monitoring

### Check reCAPTCHA Dashboard
1. Go to https://www.google.com/recaptcha/admin
2. Select your site
3. Verify:
   - [ ] Requests are being tracked
   - [ ] Score distribution is visible
   - [ ] No error codes reported
   - [ ] Traffic looks normal

## Troubleshooting Guide

| Issue | Solution |
|-------|----------|
| reCAPTCHA script not loading | Check site key in `.env.local` |
| Token not being sent | Verify form ID is `unified-form` |
| Verification always fails | Check secret key and network connectivity |
| Score always too low | Adjust threshold or check for VPN usage |
| Form not submitting | Check browser console for JavaScript errors |
| Lockout not working | Verify login attempt tracking is enabled |

## Sign-Off

- [ ] All test cases passed
- [ ] No console errors
- [ ] Logs show successful verification
- [ ] Performance is acceptable
- [ ] Security features working
- [ ] Ready for production

