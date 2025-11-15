# HBM - Hosting & Billing Manager 🚀

A professional, full-featured billing and hosting management system built with Laravel 11. Designed to rival commercial platforms like WHMCS with enterprise-grade features, security, and automation.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20|%208.3-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production Ready-success.svg)](https://github.com)
[![Multi-Language](https://img.shields.io/badge/Multi--Language-Supported-green.svg)](LANGUAGE_SYSTEM.md)
[![API](https://img.shields.io/badge/REST%20API-Available-blue.svg)](API_DOCUMENTATION.md)
[![Extensions](https://img.shields.io/badge/Plugin%20System-Available-orange.svg)](EXTENSION_SYSTEM.md)

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Architecture](#architecture)
- [Security](#security)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### Admin Panel
- **User Management** - Full CRUD with role-based access control
- **Product Catalog** - Groups, pricing, billing cycles, stock management
- **Service Lifecycle** - Provision, suspend, unsuspend, terminate
- **Invoice Management** - Create, send, track with dynamic line items
- **Payment Processing** - Multiple gateways (Stripe, PayPal, Midtrans, Xendit, etc.)
- **Support Tickets** - Department-based ticketing system
- **Reports & Analytics** - Revenue, sales, MRR/ARR, churn metrics
- **Activity Logs** - Complete audit trail with IP tracking
- **Settings** - Centralized configuration management
- **Extension Management** - Payment gateways and provisioning modules

### Client Portal
- **Service Dashboard** - Manage all services in one place
- **Invoice & Payment** - View and pay invoices online
- **Support Tickets** - Create and track support requests
- **Order System** - Browse and purchase products
- **Account Settings** - Profile, password, 2FA

### Automation
- **Recurring Invoices** - Automatic invoice generation
- **Auto-Provisioning** - Instant service deployment
- **Service Suspension** - Auto-suspend overdue accounts
- **Email Notifications** - 5 professional email templates
- **Queue Jobs** - Background task processing

### Business Intelligence
- **MRR/ARR Calculations** - Monthly and annual recurring revenue
- **Churn Analysis** - Customer retention metrics
- **Revenue Reports** - By gateway, product, date
- **Sales Analytics** - Product performance tracking
- **Client Growth** - Acquisition and retention metrics
- **CSV Export** - Export data for analysis

### 🌍 Multi-Language System
- **Translation Support** - English & Indonesian included (easily add more)
- **Language Switcher** - User-selectable language preference
- **Database Storage** - Language preference saved per user
- **Comprehensive Coverage** - 10 translation files covering all areas
- **Developer Friendly** - Simple `__()` helper for translations
- **Documentation** - Full guide: [LANGUAGE_SYSTEM.md](LANGUAGE_SYSTEM.md)

### 🔌 Extension/Plugin System
- **Payment Gateways** - Add custom payment processors
- **Provisioning Modules** - Automate server provisioning
- **Auto-Discovery** - Automatic extension loading from `extensions/` folder
- **Configuration UI** - Admin panel for extension settings
- **Hook System** - Listen to system events
- **Sample Extension** - Stripe gateway implementation included
- **Documentation** - Full guide: [EXTENSION_SYSTEM.md](EXTENSION_SYSTEM.md)

### 🚀 REST API System
- **Token Authentication** - Laravel Sanctum integration
- **Ability-Based Auth** - Role-specific permissions
- **Rate Limiting** - 60 requests/minute
- **API Resources** - Clean JSON responses
- **Comprehensive Endpoints** - Services, Invoices, Tickets, Admin Stats
- **Code Examples** - cURL, PHP, JavaScript, Python
- **Documentation** - Full guide: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

## 📦 Requirements

- **PHP:** 8.2 or 8.3 (recommended) ⚠️ **PHP 8.1 NOT supported** (Laravel 11 requirement)
- **Composer:** 2.x
- **Node.js:** 18.x or higher (optional, for asset compilation)
- **NPM:** 9.x or higher (optional)
- **Database:** MySQL 8.0+ / PostgreSQL 13+ / SQLite 3+
- **Web Server:** Apache / Nginx
- **PHP Extensions:**
  - BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL
  - Recommended: opcache, redis, imagick, exif, intl, zip, gd

## 🚀 Installation

### ⚡ One-Command Installation (RECOMMENDED - Takes 5 Minutes!)

**The Easiest Way**:

```bash
# 1. Upload project files to your server

# 2. Run automatic installer
cd /www/wwwroot/YOUR_DOMAIN
sudo bash install.sh

# 3. Setup Nginx in aaPanel (point root to /public)
# 4. Visit http://YOUR_DOMAIN/install
# Done! 🎉
```

The installer automatically handles:
- ✅ PHP version & extensions check
- ✅ Composer installation/upgrade
- ✅ All directory creation
- ✅ Correct permissions (no more 403!)
- ✅ Dependencies installation
- ✅ APP_KEY generation
- ✅ Everything configured!

**Time**: 5-10 minutes (mostly waiting for Composer)

---

### 📚 Installation Guides

| Guide | Use Case | Time |
|-------|----------|------|
| **[SETUP.md](SETUP.md)** | ⭐ **START HERE** - Automatic installation | 5-10 min |
| [QUICK_START.md](QUICK_START.md) | Quick reference & commands | Reference |
| [AAPANEL_INSTALLATION.md](AAPANEL_INSTALLATION.md) | Detailed manual step-by-step | 1-2 hours |

### 🛠️ Helper Scripts

| Script | Purpose |
|--------|---------|
| `install.sh` | **Main installer** - Automatic setup |
| `check-requirements.sh` | Check if server is ready before installing |
| `fix-permissions.sh` | Quick fix for permission/403 errors |

---

### 🔧 Manual Installation (Advanced Users)

If you prefer manual setup:

```bash
# 1. Setup database
php artisan migrate:fresh --seed

# 2. Create admin user
php artisan tinker
>>> $user = User::create([
...     'name' => 'Admin',
...     'email' => 'admin@example.com',
...     'password' => bcrypt('password'),
...     'role_id' => 1,
...     'is_active' => true,
...     'email_verified_at' => now()
... ]);

# 3. Run seeder
php artisan db:seed --class=SettingsSeeder

# 4. Clear cache
php artisan config:clear
php artisan cache:clear
```

## ⚙️ Configuration

### Environment Variables

Key `.env` settings:

```env
# Application
APP_NAME="HBM Billing"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hbm_billing
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Queue
QUEUE_CONNECTION=database

# Session
SESSION_LIFETIME=120
```

### Cron Jobs

Add this to your crontab for automated tasks:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This handles:
- Recurring invoice generation
- Service suspension checks
- Service termination
- Cache cleanup

### Queue Workers

Start queue worker for background jobs:

```bash
# Development
php artisan queue:work

# Production (with supervisor)
[program:hbm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/worker.log
```

## 📖 Usage

### Admin Panel

Access: `https://your-domain.com/admin`

**Default workflow:**
1. Setup product groups and products
2. Configure payment gateways
3. Enable provisioning modules
4. Create/import clients
5. Generate invoices
6. Track payments and services

### Client Portal

Access: `https://your-domain.com/`

**Client workflow:**
1. Register/Login
2. Browse products
3. Configure and order
4. Pay invoice
5. Manage services
6. Open support tickets

### API Endpoints

(Future feature - coming soon)

## 🏗️ Architecture

### Directory Structure

```
app/
├── Events/              # Application events
├── Listeners/           # Event listeners
├── Http/
│   ├── Controllers/
│   │   ├── Admin/      # Admin controllers
│   │   ├── Auth/       # Authentication
│   │   └── Client/     # Client controllers
│   ├── Middleware/     # Custom middleware
│   └── Requests/       # Form requests
├── Jobs/               # Queue jobs
├── Mail/               # Mail classes
├── Models/             # Eloquent models
└── Traits/             # Reusable traits

database/
├── migrations/         # Database migrations
└── seeders/           # Data seeders

resources/
├── views/
│   ├── admin/         # Admin views
│   ├── client/        # Client views
│   ├── emails/        # Email templates
│   └── errors/        # Error pages
└── js/                # Frontend assets

routes/
└── web.php            # Web routes

tests/
├── Feature/           # Feature tests
└── Unit/              # Unit tests
```

### Database Schema

**31 Migrations including:**
- Users & Roles
- Clients
- Products & Product Groups
- Services
- Invoices & Invoice Items
- Payments
- Tickets & Ticket Replies
- Activity Logs
- Settings
- And more...

### Key Models

- **User** - System users with roles
- **Client** - Customer accounts
- **Product** - Sellable products/services
- **Service** - Active customer services
- **Invoice** - Billing invoices
- **Payment** - Payment records
- **Ticket** - Support tickets
- **ActivityLog** - Audit trail

## 🔒 Security Features

- ✅ **CSRF Protection** - Laravel's built-in CSRF
- ✅ **Password Hashing** - Bcrypt hashing
- ✅ **2FA Support** - Two-factor authentication
- ✅ **Role-Based Access** - Admin/Client separation
- ✅ **Activity Logging** - Complete audit trail
- ✅ **Encrypted Data** - Sensitive data encryption
- ✅ **Input Validation** - Form request validation
- ✅ **SQL Injection Prevention** - Eloquent ORM
- ✅ **XSS Protection** - Blade escaping
- ✅ **IP Tracking** - Login and activity tracking

### Best Practices

1. Always use HTTPS in production
2. Keep dependencies updated
3. Enable rate limiting
4. Use strong passwords
5. Regular backups
6. Monitor activity logs
7. Configure firewall rules

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test tests/Feature/InvoiceTest.php
```

## 🌐 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database
- [ ] Setup SMTP for emails
- [ ] Configure queue workers
- [ ] Setup cron jobs
- [ ] Enable HTTPS
- [ ] Configure firewall
- [ ] Setup backups
- [ ] Configure monitoring

### Deployment Commands

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
npm run build

# Run migrations
php artisan migrate --force

# Clear old caches
php artisan cache:clear
```

### Shared Hosting

For shared hosting (cPanel, Plesk):
1. Upload files to `public_html` or subdirectory
2. Point domain to `public` folder
3. Import database via phpMyAdmin
4. Configure `.env` file
5. Run `/install` wizard

## 📊 System Requirements

### Minimum
- 1 CPU Core
- 512 MB RAM
- 1 GB Storage
- PHP 8.2

### Recommended
- 2+ CPU Cores
- 2 GB RAM
- 10 GB Storage
- PHP 8.3

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License.

## 🙏 Credits

Built with:
- [Laravel 11](https://laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev) (optional)

## 📞 Support

- **Documentation:** See `/docs` folder
- **Issues:** GitHub Issues
- **Email:** support@your-domain.com

## 🎯 Roadmap

- [ ] API Integration
- [ ] Mobile App
- [ ] Advanced Reporting
- [ ] Multi-currency Support
- [ ] Affiliate System
- [ ] Knowledge Base
- [ ] Live Chat Integration

---

**Made with ❤️ for the hosting industry**

**Version:** 1.0.0
**Last Updated:** 2024
