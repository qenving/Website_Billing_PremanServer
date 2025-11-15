# Panduan Instalasi HBM Billing Manager

Panduan ini akan membantu Anda menginstal HBM Billing Manager di berbagai platform hosting termasuk cPanel, aaPanel, Plesk, dan shared hosting lainnya.

## Persyaratan Sistem

- PHP >= 8.3
- MySQL >= 5.7 atau MariaDB >= 10.3
- Ekstensi PHP yang diperlukan:
  - BCMath
  - Ctype
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - cURL
  - GD atau Imagick
  - Zip

## Cara Instalasi

### Metode 1: Instalasi di cPanel/aaPanel/Plesk

#### Langkah 1: Upload Files

1. **Download atau Clone Repository**
   - Download project ini sebagai ZIP atau clone menggunakan Git
   - Extract semua file jika dalam bentuk ZIP

2. **Upload ke Hosting**
   - Upload semua file ke direktori root website Anda (biasanya `public_html` atau `www`)
   - Pastikan semua file ter-upload dengan lengkap

#### Langkah 2: Set Document Root

**Penting:** Website harus mengarah ke folder `public`

**Untuk cPanel:**
1. Login ke cPanel
2. Cari menu "Domains" atau "Addon Domains"
3. Edit domain Anda
4. Ubah "Document Root" menjadi `/public_html/public` (atau sesuaikan dengan lokasi folder Anda)
5. Save

**Untuk aaPanel:**
1. Login ke aaPanel
2. Pergi ke "Website" > Pilih website Anda > "Settings"
3. Ubah "Site Directory" atau "Root Directory" ke folder `public`
4. Save

**Untuk Plesk:**
1. Login ke Plesk
2. Pergi ke "Hosting Settings"
3. Ubah "Document Root" ke folder `public`
4. Save

**Jika tidak bisa mengubah Document Root:**
- File `.htaccess` di root folder akan otomatis redirect ke folder `public`
- Namun untuk keamanan dan performa yang lebih baik, sangat disarankan untuk mengubah Document Root

#### Langkah 3: Set Permissions

Set permission folder-folder berikut menjadi **775** atau **777**:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

Atau melalui File Manager:
1. Klik kanan folder `storage` > Change Permissions > Set ke 775
2. Centang "Recurse into subdirectories"
3. Ulangi untuk folder `bootstrap/cache`

#### Langkah 4: Install Dependencies

**Menggunakan SSH (Recommended):**

```bash
# Pastikan Anda berada di direktori project
cd /path/to/your/project

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Generate APP_KEY
php artisan key:generate

# Jalankan migrations
php artisan migrate --force

# Jalankan seeders (optional, untuk data awal)
php artisan db:seed
```

**Jika tidak ada akses SSH:**

1. Install Composer secara lokal di komputer Anda
2. Jalankan `composer install --no-dev` di lokal
3. Upload folder `vendor` yang sudah ter-generate ke hosting

#### Langkah 5: Konfigurasi Database

1. Buat database MySQL melalui cPanel/aaPanel:
   - Login ke panel hosting
   - Cari menu "MySQL Databases" atau "Database"
   - Buat database baru (contoh: `hbm_billing`)
   - Buat user database dan set password
   - Tambahkan user ke database dengan privilege "All"

2. Edit file `.env`:
   ```env
   APP_NAME="HBM Billing"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=hbm_billing
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```

3. Generate Application Key (jika belum):
   - Via SSH: `php artisan key:generate`
   - Manual: Buat random string 32 karakter dan isi di `APP_KEY=base64:xxx`

#### Langkah 6: Jalankan Migrations

**Via SSH:**
```bash
php artisan migrate --force
php artisan db:seed
```

**Via Browser (jika tidak ada SSH):**
- Akses: `https://domain-anda.com/install.php` (jika ada installer)
- Atau gunakan phpMyAdmin untuk import file SQL jika tersedia

#### Langkah 7: Optimize untuk Production

Jika ada akses SSH, jalankan:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Metode 2: Instalasi Otomatis (Script)

Jika Anda memiliki akses SSH, jalankan:

```bash
chmod +x setup.sh
./setup.sh
```

Script akan otomatis:
- Menginstall dependencies
- Generate APP_KEY
- Set permissions
- Menjalankan migrations

## Konfigurasi Tambahan

### Email Configuration

Edit file `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
```

### Cron Jobs (Optional tapi Disarankan)

Tambahkan cron job berikut di cPanel/aaPanel:

**Di cPanel:**
1. Cari menu "Cron Jobs"
2. Tambahkan:
   - Minute: `*`
   - Hour: `*`
   - Day: `*`
   - Month: `*`
   - Weekday: `*`
   - Command: `cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1`

**Di aaPanel:**
1. Pergi ke "Cron"
2. Add cron job dengan command yang sama

### Queue Worker (Optional)

Untuk menjalankan queue worker di background:

```bash
php artisan queue:work --daemon
```

Atau tambahkan ke supervisor/systemd jika tersedia.

## Troubleshooting

### Error 500 - Internal Server Error

1. Pastikan folder `storage` dan `bootstrap/cache` writable (775/777)
2. Pastikan file `.env` sudah dikonfigurasi dengan benar
3. Generate APP_KEY jika belum: `php artisan key:generate`
4. Periksa log error di `storage/logs/laravel.log`

### Error 404 - Not Found

1. Pastikan Document Root mengarah ke folder `public`
2. Pastikan file `.htaccess` ada di folder `public`
3. Pastikan mod_rewrite Apache aktif

### Database Connection Error

1. Periksa kredensial database di `.env`
2. Pastikan database sudah dibuat
3. Pastikan user database memiliki akses ke database
4. Coba gunakan `127.0.0.1` bukan `localhost` untuk DB_HOST

### Permission Denied

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Atau ubah owner sesuai dengan user web server Anda.

### Composer Not Found

Download Composer:
```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev
```

## Keamanan

1. **Jangan** set `APP_DEBUG=true` di production
2. **Pastikan** folder `storage` dan `bootstrap/cache` tidak bisa diakses dari browser
3. **Gunakan** HTTPS (SSL Certificate)
4. **Update** dependencies secara berkala
5. **Backup** database secara rutin

## Login Pertama Kali

Setelah instalasi selesai:

1. Akses website Anda: `https://domain-anda.com`
2. Jika sudah ada seeder admin:
   - Username: `admin@example.com`
   - Password: `password` (segera ubah!)
3. Jika belum, buat admin melalui:
   ```bash
   php artisan make:admin
   ```

## Support

Jika mengalami kesulitan:
1. Periksa log di `storage/logs/laravel.log`
2. Periksa PHP error log di hosting panel
3. Hubungi support

---

**Selamat! Website billing Anda sudah siap digunakan!** 🎉
