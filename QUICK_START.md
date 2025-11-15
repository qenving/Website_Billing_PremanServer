# HBM Billing System - Quick Start Guide

## 🚀 Install di Ubuntu + aaPanel (Singkat)

### Step 1: Install aaPanel
```bash
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && sudo bash install.sh aapanel
```

### Step 2: Install Software (di aaPanel Dashboard)
- **Nginx** (latest)
- **MySQL 8.0**
- **PHP 8.3** ⚠️ (8.2 minimum, JANGAN 8.1!)
- **Composer**
- **phpMyAdmin** (optional)

### Step 3: Install PHP Extensions
Di PHP 8.3 Settings → Install:
- opcache, redis, imagick, exif, intl, zip, bcmath, gd, curl, xml, mbstring, pdo_mysql, fileinfo

### Step 4: Create Website
1. **Website** → **Add Site**
2. Domain: `billing.yourdomain.com`
3. PHP: **8.3**
4. Database: Create (centang)
   - DB Name: `hbm_billing`
   - Username: `hbm_user`

### Step 5: Upload Project
```bash
cd /www/wwwroot/billing.yourdomain.com
rm -rf *
git clone YOUR_REPO_URL .
```

### Step 6: Set Permissions
```bash
sudo chown -R www:www .
sudo chmod -R 775 storage bootstrap/cache
```

### Step 7: Install & Setup
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Setup environment
cp .env.example .env
php artisan key:generate

# Edit .env (update DB credentials)
nano .env

# Migrate database
php artisan migrate:fresh --seed --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan storage:link
```

### Step 8: Configure Nginx
Update config di Website Settings → Config File (lihat AAPANEL_INSTALLATION.md untuk full config)

Key point:
```nginx
root /www/wwwroot/billing.yourdomain.com/public;
```

### Step 9: Install SSL
Website → SSL → Let's Encrypt → Apply

### Step 10: Setup Cron
**Cron** → Add:
```bash
* * * * * cd /www/wwwroot/billing.yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
```

### Step 11: Setup Queue Worker
```bash
sudo apt-get install supervisor -y
sudo nano /etc/supervisor/conf.d/hbm-worker.conf
```

Paste config (lihat full guide), then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hbm-worker:*
```

### ✅ Done!

Visit: **https://billing.yourdomain.com**

Default login:
- Email: `admin@example.com`
- Password: `password`

**GANTI PASSWORD SEGERA!**

---

## 📋 Checklist Cepat

- [ ] aaPanel + LNMP installed
- [ ] PHP 8.3 + extensions
- [ ] Website created
- [ ] Files uploaded + permissions OK
- [ ] Composer install
- [ ] .env configured
- [ ] Database migrated
- [ ] Nginx configured
- [ ] SSL installed
- [ ] Cron setup
- [ ] Queue worker running
- [ ] Cache optimized

---

## 🔧 Commands Penting

```bash
# Location
cd /www/wwwroot/billing.yourdomain.com

# Clear cache
php artisan cache:clear
php artisan config:clear

# Optimize
php artisan config:cache
php artisan route:cache

# Update
git pull
composer install
php artisan migrate --force
php artisan config:cache

# Logs
tail -f storage/logs/laravel.log
```

---

## ⚡ Troubleshooting Quick Fix

### Error 500
```bash
php artisan cache:clear
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www:www storage bootstrap/cache
```

### Database error
```bash
# Check .env credentials
nano .env

# Test connection
php artisan tinker
DB::connection()->getPdo();
```

### Permission denied
```bash
sudo chown -R www:www .
sudo chmod -R 775 storage bootstrap/cache
```

---

## 📚 Full Documentation

- **Complete Guide**: AAPANEL_INSTALLATION.md
- **README**: README.md
- **API Docs**: API_DOCUMENTATION.md
- **Language System**: LANGUAGE_SYSTEM.md
- **Extension System**: EXTENSION_SYSTEM.md

---

Need help? Check AAPANEL_INSTALLATION.md untuk panduan lengkap step-by-step!
