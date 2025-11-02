# Deployment Guide - MCC News Aggregator

## 🚀 Deploy to Railway.app (Recommended Free Option)

### Prerequisites
- GitHub account (your repo is already on GitHub ✓)
- Railway account (free tier)

### Step-by-Step Deployment

#### 1. Sign up for Railway
1. Go to [Railway.app](https://railway.app/)
2. Click "Login" and sign in with your GitHub account
3. Authorize Railway to access your repositories

#### 2. Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Choose your repository: `Kael717/MCC-News-Aggregator-with-Chatbot`
4. Railway will detect it's a PHP application

#### 3. Add MySQL Database
1. In your project, click "New"
2. Select "Database" → "Add MySQL"
3. Railway will provision a free MySQL database
4. Note down the connection details (available in the database service variables)

#### 4. Configure Environment Variables
Click on your web service → "Variables" tab, add these:

**Required Variables:**
```env
APP_NAME="MCC News Aggregator"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="MCC News Aggregator"

NOCAPTCHA_SITEKEY=your_recaptcha_site_key
NOCAPTCHA_SECRET=your_recaptcha_secret_key

GEMINI_API_KEY=your_gemini_api_key

AZURE_AD_CLIENT_ID=your_ms365_client_id
AZURE_AD_CLIENT_SECRET=your_ms365_client_secret
AZURE_AD_TENANT_ID=your_ms365_tenant_id
AZURE_AD_REDIRECT_URI=https://your-app.up.railway.app/auth/ms365/callback

SECURITY_HSTS_ENABLED=true
SECURITY_HSTS_MAX_AGE=31536000
SECURITY_HSTS_INCLUDE_SUBDOMAINS=true
SECURITY_HSTS_PRELOAD=true
```

**Note:** Railway will auto-fill MySQL variables using `${{MySQL.VARIABLE_NAME}}` syntax.

#### 5. Run Migrations
After deployment, open the Railway console for your service and run:
```bash
php artisan migrate --force
php artisan db:seed --force
```

#### 6. Generate Application Key
In Railway console:
```bash
php artisan key:generate --force
```
Copy the generated key to your `APP_KEY` environment variable.

#### 7. Set Storage Permissions
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 🌐 Your Free Domain
Railway provides: `https://your-app-name.up.railway.app`

You can customize the subdomain in Railway settings.

---

## 🎯 Alternative: Render.com

### Step-by-Step for Render

#### 1. Sign up
- Go to [Render.com](https://render.com/)
- Sign in with GitHub

#### 2. Create Web Service
1. Click "New +" → "Web Service"
2. Connect your GitHub repository
3. Configure:
   - **Name:** mcc-news-aggregator
   - **Environment:** PHP
   - **Build Command:** `composer install --no-dev --optimize-autoloader && php artisan config:cache`
   - **Start Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`

#### 3. Add PostgreSQL Database
1. Click "New +" → "PostgreSQL"
2. Create free PostgreSQL database
3. Link it to your web service

#### 4. Environment Variables
Add the same variables as Railway, but use PostgreSQL connection:
```env
DB_CONNECTION=pgsql
DB_HOST=your-postgres-host.render.com
DB_PORT=5432
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

#### 5. Deploy
Render will automatically deploy from your GitHub repo.

### 🌐 Your Free Domain
Render provides: `https://mcc-news-aggregator.onrender.com`

---

## 🔥 Alternative: InfinityFree (Traditional Hosting)

### Step-by-Step for InfinityFree

#### 1. Sign up
- Go to [InfinityFree.net](https://infinityfree.net/)
- Create a free account

#### 2. Create Website
1. Click "Create Account"
2. Choose a subdomain (e.g., `mccnews.rf.gd`)
3. Wait for account creation

#### 3. Upload Files
1. Open File Manager or use FTP (FileZilla)
2. Upload all files to `/htdocs/` folder
3. Upload vendor folder (if Composer isn't available)

#### 4. Create Database
1. Go to MySQL Databases
2. Create new database
3. Note database name, username, password

#### 5. Configure .env
Edit `.env` file in File Manager:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://mccnews.rf.gd

DB_CONNECTION=mysql
DB_HOST=sqlXXX.infinityfreeapp.com
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

#### 6. Run Installation
Visit: `http://mccnews.rf.gd/install.php` (you'll need to create this file)

**Limitations:**
- ⚠️ May not support PHP 8.2 (usually 7.4-8.1)
- ⚠️ No command line access
- ⚠️ Limited resources
- ⚠️ Ads on free plan

---

## 🎁 Free Domain Options

### 1. Use Hosting Subdomain (Recommended)
- Railway: `yourapp.up.railway.app`
- Render: `yourapp.onrender.com`
- InfinityFree: `yoursite.rf.gd` or similar

### 2. Freenom (Free Domains)
- Go to [Freenom.com](https://freenom.com/)
- Search for available domains (.tk, .ml, .ga, .cf, .gq)
- Register for free (up to 1 year)
- ⚠️ Service can be unreliable

### 3. Free Subdomain Services
- [FreeDNS.afraid.org](https://freedns.afraid.org/) - Free subdomains
- [Duck DNS](https://www.duckdns.org/) - Free dynamic DNS

---

## ✅ Recommended Deployment Path

**Best Option:** Railway.app
1. ✓ Best Laravel support
2. ✓ Easy deployment from GitHub
3. ✓ Free MySQL database
4. ✓ $5/month free credit
5. ✓ Auto-deployments on git push
6. ✓ Professional subdomain

---

## 🔧 Post-Deployment Checklist

- [ ] Run migrations
- [ ] Seed default data (roles, permissions)
- [ ] Test login functionality
- [ ] Test file uploads
- [ ] Configure MS365 OAuth redirect URI
- [ ] Test email notifications
- [ ] Test reCAPTCHA
- [ ] Test chatbot functionality
- [ ] Check security headers
- [ ] Test all admin dashboards

---

## 🆘 Need Help?

If you encounter issues during deployment, check:
1. Application logs in hosting platform
2. Laravel logs: `storage/logs/laravel.log`
3. Database connection
4. Environment variables are set correctly
5. File permissions (storage, bootstrap/cache)

---

## 📊 Resource Usage (Free Tiers)

| Platform | RAM | Storage | Database | Bandwidth |
|----------|-----|---------|----------|-----------|
| Railway | 512MB | 1GB | MySQL | Limited |
| Render | 512MB | - | PostgreSQL | 100GB/mo |
| InfinityFree | Limited | 5GB | MySQL 400MB | Unlimited |

---

For production deployment, consider upgrading to paid plans for better performance and reliability.
