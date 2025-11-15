# ⚡ QUICK DEPLOY - 5 MENIT JADI!

## 🎯 **CARA TERCEPAT DEPLOY HBM BILLING**

### **STEP 1: Upload ke Server** (2 menit)
```bash
# Upload semua file via FTP/SFTP ke:
# - Shared Hosting: public_html/
# - aaPanel: /www/wwwroot/domain.com/
# - VPS: /var/www/html/
```

### **STEP 2: Install Dependencies** (1 menit)
```bash
composer install --no-dev
```

### **STEP 3: Buka Browser - SELESAI!** (2 menit)
```
http://yourdomain.com
```

**Otomatis redirect ke installer!** Ikuti 6 langkah:
1. ✅ **Welcome** → Klik "Start"
2. ✅ **Requirements** → Cek hijau semua → "Next"
3. ✅ **Database** → Isi kredensial → "Test" → "Install"
4. ✅ **Admin Account** → Buat akun → "Create"
5. ✅ **SMTP** (opsional) → "Skip" atau isi → "Next"
6. ✅ **Done!** → "Go to Login"

---

## 🚀 **ITU SAJA! SUDAH JALAN!**

Login admin:
```
http://yourdomain.com/admin
```

Login client:
```
http://yourdomain.com/login
```

---

## 🔧 **JIKA ADA MASALAH**

### **Error 500?**
```bash
chmod -R 775 storage bootstrap/cache
```

### **Installer tidak muncul?**
Cek `.env`:
```
HBM_INSTALLED=false  ← Pastikan false
```

### **Composer error?**
```bash
composer install --ignore-platform-reqs
```

---

## 📖 **DOKUMENTASI LENGKAP**

Lihat file **[DEPLOY.md](DEPLOY.md)** untuk:
- Troubleshooting detail
- Security setup production
- Virtual host configuration
- Update procedures

---

## ✅ **CHECKLIST BEFORE GO LIVE**

- [ ] Installer berhasil (database terkoneksi)
- [ ] Login admin bisa
- [ ] Setup 1 payment gateway
- [ ] Setup 1 provisioning module
- [ ] Test order 1 produk
- [ ] Backup database pertama

**Selamat menggunakan HBM Billing Manager!** 🎉
