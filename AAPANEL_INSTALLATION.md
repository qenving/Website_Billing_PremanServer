# HBM Billing System - Installation Guide for Ubuntu with aaPanel

## Prerequisites

- Ubuntu Server (20.04 / 22.04 / 24.04)
- aaPanel installed
- Root or sudo access
- Domain name (optional, bisa pakai IP dulu)

---

## Step 1: Install aaPanel (Kalau Belum)

```bash
# Install aaPanel
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && sudo bash install.sh aapanel

# Setelah install selesai, akan muncul:
# - aaPanel URL: http://YOUR_IP:7800
# - Username: admin
# - Password: random_password
```

**Login ke aaPanel**: http://YOUR_IP:7800

---

## Step 2: Install Required Software di aaPanel

### 2.1 Login ke aaPanel Dashboard

1. Buka browser → http://YOUR_IP:7800
2. Login dengan credentials yang diberikan saat install
3. Pilih bahasa (English/Indonesian)

### 2.2 Install LNMP Stack

Di aaPanel dashboard, klik **App Store** → Install:

#### A. **Nginx** (Web Server)
- Klik **Install**
- Pilih versi: **1.24.x** (latest stable)
- Tunggu sampai selesai

#### B. **MySQL** (Database)
- Klik **Install**
- Pilih versi: **8.0.x** (recommended) atau **5.7.x**
- Tunggu sampai selesai

#### C. **PHP**
- Klik **Install**
- Pilih versi: **8.3** (RECOMMENDED) atau **8.2**
- ⚠️ **JANGAN pilih 8.1** (Laravel 11 tidak support)
- Tunggu sampai selesai

#### D. **phpMyAdmin** (Optional, untuk manage database)
- Klik **Install**
- Tunggu sampai selesai

---

## Step 3: Install PHP Extensions

Setelah PHP terinstall, install extensions yang diperlukan:

1. **App Store** → **PHP 8.3** → **Settings** → **Install Extensions**

Install extensions berikut:
- ✅ **opcache** (untuk performance)
- ✅ **redis** (untuk caching - optional tapi recommended)
- ✅ **imagick** (untuk image processing)
- ✅ **exif** (untuk image metadata)
- ✅ **intl** (untuk internationalization)
- ✅ **zip** (untuk compression)
- ✅ **bcmath** (untuk billing calculations)
- ✅ **gd** (untuk image processing)
- ✅ **curl** (untuk HTTP requests)
- ✅ **xml** (untuk XML parsing)
- ✅ **mbstring** (untuk multibyte strings)
- ✅ **pdo_mysql** (untuk database)
- ✅ **fileinfo** (untuk file operations)

### 3.1 Install Composer (via aaPanel)

1. **App Store** → Search **"Composer"**
2. Klik **Install**
3. Tunggu sampai selesai

**Atau install manual via SSH**:
```bash
# SSH ke server
ssh root@YOUR_IP

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify
composer --version
```

---

## Step 4: Upload & Setup Project

### 4.1 Create Website di aaPanel

1. **Website** → **Add Site**
2. **Domain**: `billing.yourdomain.com` (atau pakai IP dulu)
3. **Root Directory**: `/www/wwwroot/billing.yourdomain.com`
4. **PHP Version**: Pilih **PHP 8.3**
5. **Database**: Create (centang)
   - Database Name: `hbm_billing`
   - Username: `hbm_user`
   - Password: (generate/custom)
6. Klik **Submit**

### 4.2 Upload Project Files

**Opsi A: Via Git (RECOMMENDED)**

```bash
# SSH ke server
ssh root@YOUR_IP

# Navigate to web directory
cd /www/wwwroot/billing.yourdomain.com

# Backup & hapus file default
rm -rf *

# Clone project
git clone https://github.com/YOUR_USERNAME/Website_Billing_PremanServer.git .

# Atau kalau dari branch specific
git clone -b claude/hbm-billing-system-setup-011CV5xW95aYQAWdxCS2fYUK https://github.com/YOUR_USERNAME/Website_Billing_PremanServer.git .
```

**Opsi B: Via File Manager**

1. Download project sebagai ZIP dari GitHub
2. Di aaPanel → **Files** → Navigate ke `/www/wwwroot/billing.yourdomain.com`
3. Upload ZIP file
4. Extract ZIP file
5. Move semua file dari subfolder ke root directory

**Opsi C: Via FTP**

1. **FTP** → Create FTP Account
2. Use FileZilla/WinSCP untuk upload files
3. Upload semua files ke `/www/wwwroot/billing.yourdomain.com`

### 4.3 Set File Permissions

```bash
# SSH ke server
cd /www/wwwroot/billing.yourdomain.com

# Set ownership
sudo chown -R www:www .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;

# Set file permissions
sudo find . -type f -exec chmod 644 {} \;

# Set writable directories
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 775 public

# Set ownership to web user
sudo chown -R www:www storage bootstrap/cache
```

---

## Step 5: Install Dependencies

```bash
# SSH ke server
cd /www/wwwroot/billing.yourdomain.com

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Kalau error permission, run as www user:
sudo -u www composer install --optimize-autoloader --no-dev
```

---

## Step 6: Configure Environment

### 6.1 Copy Environment File

```bash
cd /www/wwwroot/billing.yourdomain.com

# Copy .env.example to .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 6.2 Edit .env File

**Via aaPanel File Manager**:
1. **Files** → Navigate ke project folder
2. Edit `.env` file
3. Update values:

```env
APP_NAME="HBM Billing"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://billing.yourdomain.com
APP_LOCALE=id

# Database (dari aaPanel saat create site)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hbm_billing
DB_USERNAME=hbm_user
DB_PASSWORD=YOUR_DATABASE_PASSWORD

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Queue (akan setup nanti)
QUEUE_CONNECTION=database

# Cache (redis kalau sudah install)
CACHE_DRIVER=file
SESSION_DRIVER=file

# Filesystem
FILESYSTEM_DISK=local
```

**Atau via SSH**:
```bash
nano .env
# Edit values above
# Ctrl+X, Y, Enter to save
```

---

## Step 7: Setup Database

```bash
cd /www/wwwroot/billing.yourdomain.com

# Run migrations
php artisan migrate --force

# Seed database with initial data
php artisan db:seed --force

# Atau gabung dalam 1 command
php artisan migrate:fresh --seed --force
```

**Troubleshooting Database**:

Kalau error connection:
1. Cek database credentials di `.env`
2. Di aaPanel → **Database** → Verify database exists
3. Test connection:
```bash
php artisan tinker
DB::connection()->getPdo();
# Should show PDO object
```

---

## Step 8: Configure Nginx

### 8.1 Update Nginx Config di aaPanel

1. **Website** → Find your site → **Settings**
2. Klik **Config File** tab
3. Replace dengan config berikut:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name billing.yourdomain.com;

    # Redirect HTTP to HTTPS (setelah SSL installed)
    # return 301 https://$server_name$request_uri;

    root /www/wwwroot/billing.yourdomain.com/public;
    index index.php index.html index.htm;

    # Increase upload size
    client_max_body_size 100M;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-83.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;

        # Increase timeout for long operations
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ /\.env {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }

    # Access & Error logs
    access_log /www/wwwlogs/billing.yourdomain.com.log;
    error_log /www/wwwlogs/billing.yourdomain.com.error.log;
}
```

4. Klik **Save**
5. **Service** → **Nginx** → **Reload**

---

## Step 9: Install SSL Certificate (HTTPS)

### 9.1 Via Let's Encrypt (FREE)

1. **Website** → Your site → **Settings**
2. Klik **SSL** tab
3. Pilih **Let's Encrypt**
4. Check **Force HTTPS**
5. Klik **Apply**
6. Tunggu beberapa detik

### 9.2 Verify SSL

Visit: https://billing.yourdomain.com

Should show HTTPS dengan padlock icon ✅

---

## Step 10: Setup Cron Jobs (Scheduler)

Laravel scheduler perlu cron job untuk jalan.

### Via aaPanel:

1. **Cron** → **Add**
2. **Type**: Shell Script
3. **Name**: Laravel Scheduler
4. **Execution Cycle**: Every minute (N minutes, value: 1)
5. **Script**:
```bash
cd /www/wwwroot/billing.yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
```
6. Klik **Add**

### Via SSH (Manual):

```bash
crontab -e

# Add this line:
* * * * * cd /www/wwwroot/billing.yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 11: Setup Queue Worker (Background Jobs)

### 11.1 Create Supervisor Config

```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/hbm-worker.conf
```

Paste:
```ini
[program:hbm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/billing.yourdomain.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www
numprocs=2
redirect_stderr=true
stdout_logfile=/www/wwwroot/billing.yourdomain.com/storage/logs/worker.log
stopwaitsecs=3600
```

### 11.2 Start Queue Worker

```bash
# Install supervisor kalau belum
sudo apt-get install supervisor -y

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start worker
sudo supervisorctl start hbm-worker:*

# Check status
sudo supervisorctl status
```

**Atau via aaPanel**:

1. **App Store** → Install **Supervisor Manager**
2. Open Supervisor Manager
3. Add process dengan config di atas

---

## Step 12: Optimize for Production

```bash
cd /www/wwwroot/billing.yourdomain.com

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize --no-dev

# Link storage
php artisan storage:link
```

---

## Step 13: First Time Setup (Via Web Installer)

1. Buka browser: https://billing.yourdomain.com
2. Akan redirect ke `/install`
3. Follow installation wizard:
   - Requirements check
   - Database setup (skip kalau sudah migrate)
   - Admin account creation
   - SMTP configuration
   - Complete!

**Atau create admin via CLI**:

```bash
cd /www/wwwroot/billing.yourdomain.com

php artisan tinker

# Create admin user
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('admin123'),
    'role_id' => 1,
    'is_active' => true
]);

exit
```

---

## Step 14: Security Checklist

### ✅ Must Do:

1. **Change default passwords**:
   - aaPanel password
   - Database password
   - Admin account password

2. **Setup Firewall**:
```bash
# Install UFW
sudo apt-get install ufw -y

# Allow SSH
sudo ufw allow 22

# Allow HTTP & HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Allow aaPanel
sudo ufw allow 7800

# Enable firewall
sudo ufw enable
```

3. **Disable directory listing**:
   - Already configured in Nginx config above

4. **Setup backup**:
   - aaPanel → **Cron** → Add database backup daily
   - aaPanel → **Cron** → Add files backup weekly

5. **Update `.env`**:
```env
APP_DEBUG=false
APP_ENV=production
```

6. **Secure aaPanel**:
   - aaPanel → **Panel** → Change default port 7800
   - Enable 2FA
   - Change username

---

## Step 15: Testing

### 15.1 Test Website

1. Visit: https://billing.yourdomain.com
2. Should load installation page or login page ✅

### 15.2 Test Database

```bash
cd /www/wwwroot/billing.yourdomain.com
php artisan tinker
DB::connection()->getPdo();
# Should show PDO object
```

### 15.3 Test Queue

```bash
php artisan queue:work --once
# Should process without errors
```

### 15.4 Test Email

```bash
php artisan tinker
Mail::raw('Test email', function($msg) {
    $msg->to('your-email@example.com')->subject('Test');
});
# Check your inbox
```

---

## Troubleshooting

### Error: 500 Internal Server Error

**Check logs**:
```bash
tail -f /www/wwwroot/billing.yourdomain.com/storage/logs/laravel.log
tail -f /www/wwwlogs/billing.yourdomain.com.error.log
```

**Common fixes**:
```bash
# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Fix permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www:www storage bootstrap/cache
```

### Error: Class not found

```bash
composer dump-autoload
php artisan clear-compiled
```

### Error: Database connection refused

1. Check database credentials in `.env`
2. Verify database exists in aaPanel → Database
3. Check MySQL is running: `sudo systemctl status mysql`

### Error: Permission denied

```bash
# Fix all permissions
cd /www/wwwroot/billing.yourdomain.com
sudo chown -R www:www .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

### Slow Performance

```bash
# Enable OPcache
# aaPanel → PHP 8.3 → Settings → Performance → Enable OPcache

# Install Redis (optional)
# aaPanel → App Store → Install Redis

# Update .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## Maintenance Commands

### Update Application

```bash
cd /www/wwwroot/billing.yourdomain.com

# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear & recache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
sudo supervisorctl restart hbm-worker:*
```

### Backup Database

```bash
# Via aaPanel
# Database → Select DB → Backup

# Via CLI
mysqldump -u hbm_user -p hbm_billing > backup.sql
```

### Monitor Logs

```bash
# Application logs
tail -f storage/logs/laravel.log

# Nginx error logs
tail -f /www/wwwlogs/billing.yourdomain.com.error.log

# Queue worker logs
tail -f storage/logs/worker.log
```

---

## Performance Optimization

### 1. Enable OPcache

aaPanel → **PHP 8.3** → **Settings** → **Configuration**

Edit `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. Install Redis

```bash
# Via aaPanel
# App Store → Install Redis

# Update .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Enable Gzip

Already configured in Nginx config above ✅

### 4. Optimize Images

Install ImageMagick:
```bash
sudo apt-get install imagemagick -y
```

aaPanel → PHP 8.3 → Install **imagick** extension

---

## Additional Resources

- **aaPanel Docs**: https://www.aapanel.com/reference.html
- **Laravel Deployment**: https://laravel.com/docs/11.x/deployment
- **HBM Billing Docs**: See README.md in project

---

## Summary Checklist

- [ ] aaPanel installed
- [ ] Nginx, MySQL, PHP 8.3 installed
- [ ] PHP extensions installed
- [ ] Composer installed
- [ ] Website created in aaPanel
- [ ] Project files uploaded
- [ ] Permissions set correctly
- [ ] Dependencies installed
- [ ] .env configured
- [ ] Database migrated & seeded
- [ ] Nginx configured
- [ ] SSL certificate installed
- [ ] Cron job added
- [ ] Queue worker running
- [ ] Production optimizations applied
- [ ] Security measures implemented
- [ ] Tested and working!

---

**Selamat! HBM Billing System sudah running di production! 🎉**

Default login (kalau pakai seeder):
- URL: https://billing.yourdomain.com/admin/login
- Email: admin@example.com
- Password: password

**SEGERA GANTI PASSWORD!**
