<<<<<<< HEAD
# MS365 OAuth2 Authentication System Setup Guide

## Overview
This system implements Microsoft 365 OAuth2 authentication for MCC News Aggregator, allowing students and faculty to log in using their MS365 accounts. The system validates accounts against a pre-imported `ms365_accounts` table and sends registration links via Microsoft Graph API.

## Prerequisites
- Laravel 12.x
- PHP 8.2+
- MySQL database with `ms365_accounts` table
- Microsoft Azure App Registration

## Installation

### 1. Install Required Packages
```bash
composer require laravel/socialite
composer require socialiteproviders/microsoft
composer require microsoft/microsoft-graph
```

### 2. Environment Configuration
Add the following to your `.env` file:
```env
MS_CLIENT_ID=your-azure-app-client-id
MS_CLIENT_SECRET=your-azure-app-client-secret
MS_TENANT_ID=your-azure-tenant-id
MS_REDIRECT_URI=http://localhost:8000/auth/ms365/callback
```

### 3. Database Tables
The system requires two tables:

#### ms365_accounts table
```sql
CREATE TABLE ms365_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(255),
    user_principal_name VARCHAR(255) UNIQUE,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### registration_tokens table
```sql
CREATE TABLE registration_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    token VARCHAR(64) UNIQUE,
    expires_at TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## System Components

### 1. Models
- **Ms365Account**: Manages MS365 account validation
- **User**: Extended user model with MS365 integration

### 2. Controllers
- **MS365OAuthController**: Handles OAuth2 flow and registration
- **MS365AuthController**: Legacy controller for manual authentication

### 3. Services
- **MicrosoftGraphService**: Manages Microsoft Graph API interactions

### 4. Routes
```php
// OAuth2 Routes
Route::get('/auth/ms365/redirect', [MS365OAuthController::class, 'redirectToProvider']);
Route::get('/auth/ms365/callback', [MS365OAuthController::class, 'handleProviderCallback']);

// Registration Routes
Route::get('/register/{token}', [MS365OAuthController::class, 'showRegisterForm']);
Route::post('/register', [MS365OAuthController::class, 'handleRegister']);
```

## Authentication Flow

### 1. Login Flow
1. User clicks "Sign in with Microsoft 365" button
2. Redirected to Microsoft OAuth2 consent page
3. User authorizes the application
4. System validates email against `ms365_accounts` table
5. If valid, user is logged in or account is created
6. If invalid, user is redirected to signup

### 2. Registration Flow
1. User enters MS365 email on signup form
2. System validates email exists in `ms365_accounts` table
3. If valid, registration link is sent via Microsoft Graph API
4. User clicks link in Outlook email
5. User completes registration form
6. Account is created and user is logged in

## Microsoft Graph API Integration

### 1. Email Sending
The system uses Microsoft Graph API to send registration emails:
- Endpoint: `/users/{user}/sendMail`
- Authentication: Client Credentials flow
- Fallback: Laravel Mail system

### 2. User Validation
- Endpoint: `/users/{email}`
- Purpose: Validate MS365 account exists and is active

## Configuration

### 1. Azure App Registration
1. Go to Azure Portal > App Registrations
2. Create new registration
3. Add redirect URI: `http://localhost:8000/auth/ms365/callback`
4. Grant API permissions:
   - Microsoft Graph > User.Read.All
   - Microsoft Graph > Mail.Send
5. Create client secret
6. Note Client ID, Tenant ID, and Client Secret

### 2. Laravel Configuration
```php
// config/services.php
'microsoft' => [
    'client_id' => env('MS_CLIENT_ID'),
    'client_secret' => env('MS_CLIENT_SECRET'),
    'tenant_id' => env('MS_TENANT_ID'),
    'redirect' => env('MS_REDIRECT_URI'),
],
```

## Testing

### 1. Test Route
Visit `/test-ms365-oauth` to verify system components are working.

### 2. Sample Data
Use the `Ms365AccountsSeeder` to add test accounts:
```bash
php artisan db:seed --class=Ms365AccountsSeeder
```

## Security Features

1. **Token Expiration**: Registration links expire after 30 minutes
2. **Email Validation**: Only pre-authorized MS365 accounts can register
3. **OAuth2 Security**: Uses Microsoft's secure authentication flow
4. **CSRF Protection**: All forms include CSRF tokens

## Troubleshooting

### Common Issues

1. **"Table already exists"**: Tables are already created, skip migrations
2. **"Column not found"**: Check actual table structure vs. expected
3. **OAuth2 errors**: Verify Azure app configuration and environment variables
4. **Email sending fails**: Check Microsoft Graph API permissions and credentials

### Debug Steps

1. Check `/test-ms365-oauth` route for system status
2. Verify environment variables are set correctly
3. Check Azure app registration permissions
4. Review Laravel logs for detailed error messages

## Production Deployment

1. Update redirect URIs to production domain
2. Use production database credentials
3. Configure proper email settings
4. Set up monitoring and logging
5. Test with real MS365 accounts

## Support

For issues or questions:
1. Check Laravel logs in `storage/logs/`
2. Verify Azure app configuration
3. Test individual components using test routes
4. Review this documentation for configuration details


=======
# MS365 OAuth2 Authentication System Setup Guide

## Overview
This system implements Microsoft 365 OAuth2 authentication for MCC News Aggregator, allowing students and faculty to log in using their MS365 accounts. The system validates accounts against a pre-imported `ms365_accounts` table and sends registration links via Microsoft Graph API.

## Prerequisites
- Laravel 12.x
- PHP 8.2+
- MySQL database with `ms365_accounts` table
- Microsoft Azure App Registration

## Installation

### 1. Install Required Packages
```bash
composer require laravel/socialite
composer require socialiteproviders/microsoft
composer require microsoft/microsoft-graph
```

### 2. Environment Configuration
Add the following to your `.env` file:
```env
MS_CLIENT_ID=your-azure-app-client-id
MS_CLIENT_SECRET=your-azure-app-client-secret
MS_TENANT_ID=your-azure-tenant-id
MS_REDIRECT_URI=http://localhost:8000/auth/ms365/callback
```

### 3. Database Tables
The system requires two tables:

#### ms365_accounts table
```sql
CREATE TABLE ms365_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(255),
    user_principal_name VARCHAR(255) UNIQUE,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### registration_tokens table
```sql
CREATE TABLE registration_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    token VARCHAR(64) UNIQUE,
    expires_at TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## System Components

### 1. Models
- **Ms365Account**: Manages MS365 account validation
- **User**: Extended user model with MS365 integration

### 2. Controllers
- **MS365OAuthController**: Handles OAuth2 flow and registration
- **MS365AuthController**: Legacy controller for manual authentication

### 3. Services
- **MicrosoftGraphService**: Manages Microsoft Graph API interactions

### 4. Routes
```php
// OAuth2 Routes
Route::get('/auth/ms365/redirect', [MS365OAuthController::class, 'redirectToProvider']);
Route::get('/auth/ms365/callback', [MS365OAuthController::class, 'handleProviderCallback']);

// Registration Routes
Route::get('/register/{token}', [MS365OAuthController::class, 'showRegisterForm']);
Route::post('/register', [MS365OAuthController::class, 'handleRegister']);
```

## Authentication Flow

### 1. Login Flow
1. User clicks "Sign in with Microsoft 365" button
2. Redirected to Microsoft OAuth2 consent page
3. User authorizes the application
4. System validates email against `ms365_accounts` table
5. If valid, user is logged in or account is created
6. If invalid, user is redirected to signup

### 2. Registration Flow
1. User enters MS365 email on signup form
2. System validates email exists in `ms365_accounts` table
3. If valid, registration link is sent via Microsoft Graph API
4. User clicks link in Outlook email
5. User completes registration form
6. Account is created and user is logged in

## Microsoft Graph API Integration

### 1. Email Sending
The system uses Microsoft Graph API to send registration emails:
- Endpoint: `/users/{user}/sendMail`
- Authentication: Client Credentials flow
- Fallback: Laravel Mail system

### 2. User Validation
- Endpoint: `/users/{email}`
- Purpose: Validate MS365 account exists and is active

## Configuration

### 1. Azure App Registration
1. Go to Azure Portal > App Registrations
2. Create new registration
3. Add redirect URI: `http://localhost:8000/auth/ms365/callback`
4. Grant API permissions:
   - Microsoft Graph > User.Read.All
   - Microsoft Graph > Mail.Send
5. Create client secret
6. Note Client ID, Tenant ID, and Client Secret

### 2. Laravel Configuration
```php
// config/services.php
'microsoft' => [
    'client_id' => env('MS_CLIENT_ID'),
    'client_secret' => env('MS_CLIENT_SECRET'),
    'tenant_id' => env('MS_TENANT_ID'),
    'redirect' => env('MS_REDIRECT_URI'),
],
```

## Testing

### 1. Test Route
Visit `/test-ms365-oauth` to verify system components are working.

### 2. Sample Data
Use the `Ms365AccountsSeeder` to add test accounts:
```bash
php artisan db:seed --class=Ms365AccountsSeeder
```

## Security Features

1. **Token Expiration**: Registration links expire after 30 minutes
2. **Email Validation**: Only pre-authorized MS365 accounts can register
3. **OAuth2 Security**: Uses Microsoft's secure authentication flow
4. **CSRF Protection**: All forms include CSRF tokens

## Troubleshooting

### Common Issues

1. **"Table already exists"**: Tables are already created, skip migrations
2. **"Column not found"**: Check actual table structure vs. expected
3. **OAuth2 errors**: Verify Azure app configuration and environment variables
4. **Email sending fails**: Check Microsoft Graph API permissions and credentials

### Debug Steps

1. Check `/test-ms365-oauth` route for system status
2. Verify environment variables are set correctly
3. Check Azure app registration permissions
4. Review Laravel logs for detailed error messages

## Production Deployment

1. Update redirect URIs to production domain
2. Use production database credentials
3. Configure proper email settings
4. Set up monitoring and logging
5. Test with real MS365 accounts

## Support

For issues or questions:
1. Check Laravel logs in `storage/logs/`
2. Verify Azure app configuration
3. Test individual components using test routes
4. Review this documentation for configuration details


>>>>>>> 9f65cd005f129908c789f8b201ffb45b77651557
