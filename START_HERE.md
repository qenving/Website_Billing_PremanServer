# 🚀 HBM Billing System - START HERE!

## Super Quick Installation (5 Minutes)

### Step 1: Install Required Software via aaPanel

**Open aaPanel** → **App Store** → Install:

1. **Nginx** (web server)
2. **MySQL 8.0** (database)
3. **PHP 8.3** (⚠️ NOT 8.1!)
4. **Composer** (dependency manager)

### Step 2: Install PHP Extensions

**aaPanel** → **App Store** → **PHP 8.3** → **Settings** → **Install Extensions**

Required (install all these):
- ✅ fileinfo
- ✅ bcmath
- ✅ opcache
- ✅ pdo_mysql
- ✅ mbstring
- ✅ curl
- ✅ xml
- ✅ zip

### Step 3: Upload Project Files

Upload all files to: `/www/wwwroot/YOUR_DOMAIN/`

(Use Git, FTP, or aaPanel File Manager)

### Step 4: Run Automatic Installer

```bash
# SSH to your server
ssh root@YOUR_SERVER_IP

# Go to project directory
cd /www/wwwroot/YOUR_DOMAIN

# Run installer (this does EVERYTHING automatically!)
sudo bash install.sh
```

**Wait 5-10 minutes** while it installs everything.

### Step 5: Setup Nginx

**aaPanel** → **Website** → **Your Domain** → **Settings** → **Config File**

Make sure this line exists:
```nginx
root /www/wwwroot/YOUR_DOMAIN/public;
```

Click **Save** → **Reload Nginx**

### Step 6: Create Database

**aaPanel** → **Database** → **Add Database**

- Database Name: `hbm_billing`
- Username: `hbm_user`
- Password: (your choice)

Save the credentials!

### Step 7: Run Installation Wizard

**Open browser**: `http://YOUR_DOMAIN/install`

Follow the wizard (5 easy steps):
1. Requirements check ✅
2. Database setup (use credentials from Step 6)
3. Admin account creation
4. SMTP settings (optional - can skip)
5. Complete!

### Step 8: Login & Use!

**Admin Panel**: `http://YOUR_DOMAIN/admin/login`

Default credentials (if using seeder):
- Email: `admin@example.com`
- Password: `password`

⚠️ **Change password immediately!**

---

## ✅ Done! Your Billing System is Running! 🎉

---

## 🆘 Troubleshooting

### Problem: Permission errors or 403 Forbidden

**Solution**:
```bash
cd /www/wwwroot/YOUR_DOMAIN
sudo bash fix-permissions.sh
```

### Problem: Composer errors

**Solution**:
```bash
sudo composer self-update
cd /www/wwwroot/YOUR_DOMAIN
sudo bash install.sh
```

### Problem: Can't access website

**Solution**: Check Nginx config points to `/public`:
```nginx
root /www/wwwroot/YOUR_DOMAIN/public;
```

---

## 📚 Need More Help?

- **[SETUP.md](SETUP.md)** - Complete automatic installation guide
- **[AAPANEL_INSTALLATION.md](AAPANEL_INSTALLATION.md)** - Detailed manual guide
- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - API reference
- **[LANGUAGE_SYSTEM.md](LANGUAGE_SYSTEM.md)** - Multi-language guide
- **[EXTENSION_SYSTEM.md](EXTENSION_SYSTEM.md)** - Plugin development

---

## 💡 Pro Tips

✅ **Use PHP 8.3** - Best compatibility
✅ **Enable OPcache** - 2-3x faster
✅ **Setup SSL** - Free with Let's Encrypt
✅ **Enable Redis** - Better caching (optional)
✅ **Regular backups** - Database + files

---

**Questions?** Check the documentation files above or re-run `install.sh` to fix issues!
