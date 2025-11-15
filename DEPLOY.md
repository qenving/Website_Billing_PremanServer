# 🚀 DEPLOYMENT GUIDE - HBM Billing Manager

## ✅ **CARA DEPLOY (PALING MUDAH)**

### **STEP 1: Upload Files**

Upload semua file ke server Anda:
- **Shared Hosting**: Upload ke `public_html/` atau `htdocs/`
- **VPS**: Upload ke `/var/www/html/` atau `/home/user/public_html/`
- **aaPanel**: Upload ke `/www/wwwroot/domain.com/`

### **STEP 2: Set Permissions (Jika VPS/aaPanel)**

```bash
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www:www .
```

### **STEP 3: Install Dependencies**

```bash
composer install --no-dev --optimize-autoloader
```

### **STEP 4: Buka Website - Installer Otomatis!**

Buka browser dan akses domain Anda:
```
http://yourdomain.com
```

Anda akan otomatis diarahkan ke:
```
http://yourdomain.com/install
```

### **STEP 5: Ikuti Wizard Installer**

#### **Page 1: Welcome**
Klik "Get Started" / "Mulai"

#### **Page 2: Requirements Check**
Sistem akan cek:
- ✅ PHP Version >= 8.2
- ✅ PHP Extensions (openssl, pdo, mbstring, dll)
- ✅ Directory Permissions (storage, bootstrap/cache)

Jika semua hijau ✅, klik "Next"

#### **Page 3: Database Configuration**
Isi kredensial database:
- **Database Host**: `localhost` (atau IP database server)
- **Database Port**: `3306`
- **Database Name**: Nama database Anda (contoh: `hbm_billing`)
- **Database Username**: Username MySQL/PostgreSQL
- **Database Password**: Password database

Klik "Test Connection" → Jika sukses, klik "Next"

#### **Page 4: Admin Account**
Buat akun admin pertama:
- **Full Name**: Nama lengkap Anda
- **Email**: Email admin
- **Password**: Password kuat (min. 8 karakter)

Klik "Create Admin"

#### **Page 5: SMTP Configuration** (Optional)
Setup email untuk invoice, notifikasi, dll:
- **SMTP Driver**: `smtp` / `sendmail` / `log`
- **SMTP Host**: `smtp.gmail.com` (contoh)
- **SMTP Port**: `587` (TLS) atau `465` (SSL)
- **SMTP Username**: Email pengirim
- **SMTP Password**: Password email

Klik "Save & Continue" atau "Skip" jika nanti

#### **Page 6: Complete!**
Instalasi selesai! Klik "Go to Login"

---

## 🎯 **SETELAH INSTALASI**

### **Login ke Admin Panel**
```
http://yourdomain.com/admin
```
Login dengan email & password yang dibuat tadi.

### **Setup Payment Gateways**
1. Masuk ke **Admin Panel** → **Extensions**
2. Pilih payment gateway (Stripe, Midtrans, dll)
3. Klik "Configure"
4. Isi API keys
5. Save & Enable

### **Setup Provisioning Modules**
1. **Admin Panel** → **Extensions**
2. Pilih provisioning module (Pterodactyl, Proxmox, dll)
3. Configure API credentials
4. Test connection
5. Enable

---

## 📋 **DEPLOYMENT CHECKLIST**

Sebelum Go Live:

- [ ] Upload semua files ke server
- [ ] Set permissions: `chmod 775 storage bootstrap/cache`
- [ ] Run `composer install --no-dev`
- [ ] Buat database di cPanel/phpMyAdmin
- [ ] Jalankan installer via browser
- [ ] Setup admin account
- [ ] Configure SMTP settings
- [ ] Enable payment gateways
- [ ] Setup provisioning modules
- [ ] Test order flow end-to-end
- [ ] Backup database pertama kali

---

## 🛠️ **TROUBLESHOOTING**

### **Error: "500 Internal Server Error"**
```bash
# Check permissions
chmod -R 775 storage bootstrap/cache

# Check .htaccess exists di public/
# Check DocumentRoot pointing ke public/ folder
```

### **Error: "composer: command not found"**
```bash
# Install composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

### **Error: Database connection failed**
```
1. Pastikan database sudah dibuat
2. Cek DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD benar
3. Test koneksi manual via phpMyAdmin
```

### **Error: "Installer not found"**
```
Pastikan file routes/web.php sudah include installer routes:
require __DIR__.'/installer.php';
```

### **Installer loop (redirect terus ke /install)**
```bash
# Cek .env:
HBM_INSTALLED=true

# Atau manual set di database
```

---

## 🔒 **SECURITY (PRODUCTION)**

Setelah install, **WAJIB**:

```bash
# 1. Set APP_ENV=production
# Edit .env:
APP_ENV=production
APP_DEBUG=false

# 2. Generate new APP_KEY jika belum
php artisan key:generate

# 3. Optimize untuk production
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Set proper permissions
chmod 644 .env
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

---

## 📁 **STRUKTUR FOLDER**

```
yourdomain.com/
├── public/           ← DocumentRoot harus point ke sini
│   └── index.php
├── app/
├── extensions/
├── storage/
├── vendor/
├── .env              ← Configuration file (JANGAN commit ke Git!)
└── artisan
```

**PENTING:** Web server harus point ke folder `public/`, bukan root!

---

## 🌐 **VIRTUAL HOST (Apache/Nginx)**

### **Apache (.htaccess sudah ada di public/)**
Pastikan DocumentRoot:
```apache
DocumentRoot /var/www/html/yourdomain.com/public
```

### **Nginx**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/yourdomain.com/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔄 **UPDATE SYSTEM**

Untuk update di masa depan:

```bash
# 1. Backup database & files
mysqldump -u root -p hbm_billing > backup.sql

# 2. Pull latest code
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoload

# 4. Run migrations
php artisan migrate --force

# 5. Clear cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📞 **SUPPORT**

Jika ada masalah:
1. Check logs: `storage/logs/laravel.log`
2. Enable debug: `APP_DEBUG=true` di .env (hanya untuk testing!)
3. Clear cache: `php artisan optimize:clear`

---

**Selamat! HBM Billing Manager siap digunakan!** 🎉
