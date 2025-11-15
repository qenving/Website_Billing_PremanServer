# HBM - Hosting & Billing Manager 🚀

A professional, full-featured billing and hosting management system built with Laravel 11. Designed to rival commercial platforms like WHMCS with enterprise-grade features, security, and automation.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production Ready-success.svg)](https://github.com)

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

## 📦 Requirements

- **PHP:** 8.2 or higher
- **Composer:** 2.x
- **Node.js:** 18.x or higher
- **NPM:** 9.x or higher
- **Database:** MySQL 8.0+ / PostgreSQL 13+ / SQLite 3+
- **Web Server:** Apache / Nginx
- **PHP Extensions:**
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - cURL

## 🚀 Installation

### Quick Install (Recommended)

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/hbm-billing.git
   cd hbm-billing
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run installation wizard**
   ```bash
   # Open browser and navigate to:
   http://your-domain.com/install
   ```

5. **Follow the 5-step wizard:**
   - ✅ Requirements check
   - ✅ Database configuration
   - ✅ Admin user creation
   - ✅ SMTP configuration
   - ✅ Complete!

### Manual Installation

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
