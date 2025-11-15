# HBM Billing Manager

Sistem manajemen billing dan hosting berbasis Laravel - Alternatif untuk WHMCS.

## Fitur Utama

- 📊 Dashboard Admin & Client
- 💰 Manajemen Invoice & Pembayaran
- 🎫 Sistem Tiket Support
- 👥 Manajemen Klien & User
- 🔐 Autentikasi dengan 2FA
- 📧 Notifikasi Email
- 🛡️ Security & Audit Logs
- 🎨 Theme Customization

## Persyaratan Sistem

- PHP >= 8.3
- MySQL >= 5.7 atau MariaDB >= 10.3
- Composer
- Ekstensi PHP: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL, GD/Imagick, Zip

## Instalasi

### Quick Start (dengan SSH)

```bash
# Clone repository
git clone https://github.com/qenving/Website_Billing_PremanServer.git
cd Website_Billing_PremanServer

# Jalankan setup script
chmod +x setup.sh
./setup.sh
```

### Instalasi Manual

Untuk panduan instalasi lengkap di cPanel, aaPanel, atau shared hosting lainnya, silakan baca:

**[📖 INSTALL.md](INSTALL.md) - Panduan Instalasi Lengkap**

## Konfigurasi

1. Copy file `.env.example` ke `.env`
2. Edit file `.env` dan sesuaikan konfigurasi database
3. Generate application key: `php artisan key:generate`
4. Jalankan migrations: `php artisan migrate`
5. (Optional) Jalankan seeders: `php artisan db:seed`

## Hosting Compatibility

Project ini **sudah dikonfigurasi** untuk dapat dijalankan di:

- ✅ cPanel
- ✅ aaPanel
- ✅ Plesk
- ✅ DirectAdmin
- ✅ Shared Hosting lainnya
- ✅ VPS/Dedicated Server

**Catatan Penting:**
- Pastikan Document Root mengarah ke folder `public`
- Jika tidak bisa mengubah Document Root, file `.htaccess` di root akan otomatis redirect ke `public`

## Struktur Project

```
├── app/               # Application logic
├── bootstrap/         # Framework bootstrap
├── config/           # Configuration files
├── database/         # Migrations & seeders
├── public/           # Web root (Document Root harus ke sini)
├── resources/        # Views, assets
├── routes/           # Route definitions
├── storage/          # App storage (logs, cache, uploads)
├── .env              # Environment configuration
├── .htaccess         # Root htaccess (redirect ke public)
├── setup.sh          # Setup script otomatis
└── INSTALL.md        # Panduan instalasi lengkap
```

## Dokumentasi

- [Panduan Instalasi](INSTALL.md)
- [Konfigurasi Email](INSTALL.md#email-configuration)
- [Troubleshooting](INSTALL.md#troubleshooting)

## Keamanan

Jika menemukan vulnerability, mohon laporkan melalui email atau issues.

**Penting:**
- Jangan gunakan `APP_DEBUG=true` di production
- Selalu gunakan HTTPS
- Backup database secara rutin
- Update dependencies secara berkala

## License

Proprietary - All rights reserved

## Support

Untuk bantuan dan dukungan:
1. Baca [INSTALL.md](INSTALL.md) untuk panduan instalasi
2. Periksa [Troubleshooting](INSTALL.md#troubleshooting)
3. Buka issue di repository ini

---

**Dikembangkan dengan ❤️ menggunakan Laravel 11**
