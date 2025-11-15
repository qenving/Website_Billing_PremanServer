# HBM Billing System - Super Simple Setup

## 🚀 One-Command Installation (RECOMMENDED)

### Step 1: Upload Files to Server

Upload project files to: `/www/wwwroot/YOUR_DOMAIN/`

### Step 2: Run Automatic Installer

```bash
cd /www/wwwroot/YOUR_DOMAIN
sudo bash install.sh
```

**That's it!** The installer will:
- ✅ Check PHP version & extensions
- ✅ Install/upgrade Composer if needed
- ✅ Create all required directories
- ✅ Set correct permissions automatically
- ✅ Install all dependencies
- ✅ Generate APP_KEY
- ✅ Everything ready!

**Time**: ~5-10 minutes (mostly waiting for Composer)

---

## 📋 Before Installation (Pre-Requirements)

### Install via aaPanel (Required):

1. **Nginx** - Web server
2. **MySQL 8.0** - Database
3. **PHP 8.3** - PHP runtime (⚠️ NOT 8.1!)
4. **Composer** - Dependency manager

### Install PHP Extensions (via aaPanel):

aaPanel → App Store → PHP 8.3 → Settings → Install Extensions:

- ✅ fileinfo
- ✅ bcmath
- ✅ opcache
- ✅ pdo_mysql
- ✅ mbstring
- ✅ curl
- ✅ xml
- ✅ zip
- ✅ gd or imagick

**Optional but Recommended**:
- redis (for caching)
- intl (for multi-language)
- exif (for images)

---

## ✅ Check Requirements First (Optional)

Before running installer, check if server is ready:

```bash
cd /www/wwwroot/YOUR_DOMAIN
bash check-requirements.sh
```

This will show what's missing (if anything).

---

## 🌐 After Installation

### 1. Setup Nginx in aaPanel

**Important**: Nginx must point to `/public` directory!

1. aaPanel → Website → Your Site → Settings
2. Config File tab
3. Make sure this line exists:
   ```nginx
   root /www/wwwroot/YOUR_DOMAIN/public;
   ```
4. Save → Reload Nginx

### 2. Create Database in aaPanel

1. aaPanel → Database → Add Database
2. Database Name: `hbm_billing` (or any name)
3. Username: `hbm_user` (or any username)
4. Password: (generate or custom)
5. Save credentials (you'll need them!)

### 3. Run Web Installation Wizard

Open browser: `http://YOUR_DOMAIN/install`

Follow the wizard:
- ✅ Requirements check (should all pass)
- ✅ Database setup (enter credentials from step 2)
- ✅ Admin account creation
- ✅ SMTP settings (optional)
- ✅ Complete!

### 4. Login & Use!

**Admin Panel**: `http://YOUR_DOMAIN/admin/login`

Default credentials (if using seeder):
- Email: `admin@example.com`
- Password: `password`

⚠️ **Change password immediately after first login!**

---

## ⚡ Quick Troubleshooting

### Problem: 403 Forbidden

**Solution**:
```bash
cd /www/wwwroot/YOUR_DOMAIN
sudo bash install.sh
```

Re-run installer, it will fix all permissions.

### Problem: Composer errors

**Solution**:
```bash
sudo composer self-update
cd /www/wwwroot/YOUR_DOMAIN
sudo bash install.sh
```

### Problem: Can't access /install

**Solution**: Check Nginx config points to `/public`:

```nginx
root /www/wwwroot/YOUR_DOMAIN/public;
```

### Problem: Database connection error

**Solution**: Check `.env` file has correct database credentials.

---

## 📁 What The Installer Does

```
install.sh automatically:
├── Checks PHP version (8.2+)
├── Checks required PHP extensions
├── Checks/installs/upgrades Composer
├── Creates bootstrap/cache directory
├── Creates storage directories
├── Sets ownership to www:www
├── Sets correct permissions (755/644/775)
├── Copies .env.example to .env
├── Runs composer install
├── Generates APP_KEY
└── Done! Ready for /install wizard
```

---

## 🔒 Security Checklist (After Installation)

- [ ] Change admin password
- [ ] Setup SSL certificate (aaPanel → SSL → Let's Encrypt)
- [ ] Enable firewall (UFW)
- [ ] Setup backups (aaPanel → Cron → Database backup)
- [ ] Set `APP_DEBUG=false` in .env
- [ ] Set `APP_ENV=production` in .env

---

## 📚 Full Documentation

- **Complete Guide**: [AAPANEL_INSTALLATION.md](AAPANEL_INSTALLATION.md)
- **Quick Reference**: [QUICK_START.md](QUICK_START.md)
- **API Docs**: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Language System**: [LANGUAGE_SYSTEM.md](LANGUAGE_SYSTEM.md)
- **Extensions**: [EXTENSION_SYSTEM.md](EXTENSION_SYSTEM.md)

---

## 💡 Pro Tips

1. **Always use the installer** - Don't manually setup permissions
2. **Check requirements first** - Run `check-requirements.sh` before installing
3. **Use PHP 8.3** - Best compatibility & performance
4. **Enable OPcache** - 2-3x performance boost
5. **Setup Redis** - Better caching (optional)
6. **Enable SSL** - Always use HTTPS in production

---

## 🆘 Still Having Issues?

1. **Re-run installer**: `sudo bash install.sh` (fixes 90% of issues)
2. **Check requirements**: `bash check-requirements.sh`
3. **Check error logs**: `tail -f storage/logs/laravel.log`
4. **Check Nginx logs**: `tail -f /www/wwwlogs/YOUR_DOMAIN.error.log`

---

## Summary: 3 Simple Steps

```bash
# 1. Check if ready (optional)
bash check-requirements.sh

# 2. Run installer (automatic!)
sudo bash install.sh

# 3. Setup Nginx in aaPanel & visit /install
```

**That's it!** No more manual setup, no more permission errors! 🎉
