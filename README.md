# Billing Manager - Production-Ready System

A complete, production-ready billing management system with web-based installer, advanced security features, and no dependencies on Laravel or Composer.

## ✨ Features

### 🔧 Web-Based Installer
- **5-Step Installation Process**
  - Step 1: System Requirements Check (PHP version, extensions, permissions)
  - Step 2: Database Configuration & Connection Test
  - Step 3: First Admin User Creation
  - Step 4: Security & Anti-Bot Setup
  - Step 5: Installation Finalization
- Auto-generates configuration files (`config.php`, `.env`)
- Automatic database schema import
- One-time installation with permanent lock
- Works completely in browser - no terminal required

### 🔒 Enterprise-Grade Security

#### Password Security
- **ARGON2ID** hashing algorithm
- Minimum 9 characters with complexity requirements:
  - At least 1 uppercase letter
  - At least 1 number
  - At least 1 special character
  - Cannot contain name or email
  - Blocks 500+ common passwords
- Real-time password strength meter

#### Multi-Layer Protection
- **CSRF Protection** - Token validation on all forms
- **Rate Limiting** - 5 login attempts per 15 minutes
- **Honeypot Fields** - Hidden bot detection
- **IP Throttling** - 60 requests per minute per IP
- **XSS Filtering** - Input sanitization
- **SQL Injection Protection** - Prepared statements only

#### Anti-Bot System (Modular)
Choose one during installation:
- Google reCAPTCHA (v2 & v3)
- Cloudflare Turnstile
- hCaptcha
- Or disable for later setup

#### Session Security
- HttpOnly cookies
- SameSite=Strict policy
- Secure flag on HTTPS
- Session regeneration on login
- Custom session storage

#### Directory Protection
Auto-blocks public access to:
- `/vendor`
- `/storage`
- `/bootstrap/cache`
- `/resources`
- `/app`
- `/modules`
- `.env`, `config.php`, `install.lock`

### 📊 Admin Dashboard
- User management
- Service catalog
- Order tracking
- Invoice management
- Payment processing
- Support ticket system
- Activity logs
- System settings

### 👤 Client Portal
- Service browsing
- Order management
- Invoice viewing & payment
- Support tickets
- Account settings

### 📝 Comprehensive Logging
- Login attempts (success/failure)
- User activity tracking
- Admin actions
- Security events
- IP address logging with Cloudflare/proxy support

## 🚀 Installation

### Requirements
- PHP >= 8.1
- MySQL/MariaDB
- Apache/Nginx with mod_rewrite
- PHP Extensions:
  - pdo_mysql
  - openssl
  - mbstring
  - tokenizer
  - json
  - fileinfo
  - xml
  - curl
  - zip
  - gd

### Quick Start

1. **Upload Files**
   - Upload all files to your web hosting
   - Point domain/subdomain to the `public` folder

2. **Set Permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   chmod -R 755 public/uploads/
   ```

3. **Run Installer**
   - Navigate to your domain in browser
   - Follow the 5-step installation wizard
   - Enter database credentials
   - Create first admin account
   - Configure security settings

4. **Done!**
   - Installation automatically locks
   - Login with admin credentials
   - Start managing your billing system

## 🗂️ File Structure

```
/
├── app/
│   ├── core/               # MVC framework core
│   ├── controllers/        # Application controllers
│   ├── models/            # Database models
│   ├── security/          # Security modules
│   └── helpers/           # Utility functions
├── resources/
│   └── views/             # PHP templates
├── public/
│   ├── assets/            # CSS, JS, images
│   ├── uploads/           # User uploads
│   └── .htaccess          # Security rules
├── storage/
│   ├── logs/              # Application logs
│   └── sessions/          # Session files
├── modules/
│   └── captcha/           # Captcha providers
├── installer/             # Web installer
├── database.sql           # Database schema
├── index.php              # Application entry
├── config.php             # Config (auto-generated)
└── .env                   # Environment (auto-generated)
```

## 🔐 Security Features Breakdown

### CSRF Protection
All forms include CSRF tokens validated server-side.

```php
<?php echo csrf_field(); ?>
```

### Rate Limiting
Automatic brute-force protection:
- 5 failed login attempts = 15-minute lockout
- Configurable per endpoint
- IP-based tracking

### Password Policy
Enforced rules:
- Minimum 9 characters
- Uppercase + Number + Special character
- Blacklist of 500+ common passwords
- Cannot contain user info
- ARGON2ID hashing

### IP Resolution
Correctly detects real IP behind:
- Cloudflare (`CF-Connecting-IP`)
- Load balancers (`X-Forwarded-For`)
- Reverse proxies (`X-Real-IP`)

### Activity Logging
Tracks:
- All login attempts (success/fail with reason)
- User actions
- Admin operations
- Security events
- Full IP & User-Agent data

## 🌐 Hosting Compatibility

### ✅ Tested & Compatible
- cPanel
- DirectAdmin
- Plesk (includes `web.config` for IIS)
- aaPanel
- CyberPanel
- Shared hosting environments
- VPS/Dedicated servers

### Apache Configuration
Includes comprehensive `.htaccess`:
- URL rewriting
- Security headers
- Directory protection
- Bot blocking
- SQL injection prevention
- XSS protection

### Nginx Configuration
For Nginx, add this to your site config:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ ^/(vendor|storage|bootstrap|resources|app|modules|installer)/ {
    deny all;
}

location ~ /\.(env|git) {
    deny all;
}
```

## 🎨 Customization

### Adding Captcha Keys Later
1. Go to Admin Panel → Settings
2. Select Captcha Provider
3. Enter Site Key & Secret Key
4. Save

### Database Configuration
Edit `config.php` (auto-generated during install):

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'billing_db');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_password');
```

### Security Keys
Auto-generated during installation:
- `APP_KEY` - Application encryption key
- `JWT_SECRET` - JWT token signing

## 📋 Database Schema

### Core Tables
- `users` - User accounts (admin/client)
- `login_attempts` - Login history & security
- `activity_logs` - User activity tracking
- `sessions` - Session management

### Billing Tables
- `services` - Service catalog
- `orders` - Client orders
- `invoices` - Invoice management
- `payments` - Payment processing

### Support Tables
- `tickets` - Support tickets
- `ticket_replies` - Ticket responses
- `settings` - System configuration

## 🛠️ Development

### Custom MVC Framework
Lightweight, no Composer dependencies:
- **Router** - Pattern-based routing
- **Controller** - Base controller with helpers
- **Model** - Query builder & ORM
- **View** - Template rendering
- **Database** - PDO wrapper
- **Request/Response** - HTTP handling
- **Session** - Secure session management

### Adding Routes
Edit `app/routes.php`:

```php
$router->get('/path', 'Controller@method');
$router->post('/path', 'Controller@method');
```

### Creating Controllers
Extend `Controller` class:

```php
class MyController extends Controller {
    public function index() {
        $this->view('view.name', ['data' => $value]);
    }
}
```

### Creating Models
Extend `Model` class:

```php
class MyModel extends Model {
    protected $table = 'my_table';
}
```

## 🔧 Troubleshooting

### Installation Issues

**"Database connection failed"**
- Check credentials in Step 2
- Verify MySQL is running
- Ensure database exists

**"Permission denied"**
- Set folder permissions: `chmod -R 755 storage/ bootstrap/cache/ public/uploads/`

**"500 Internal Server Error"**
- Check Apache mod_rewrite is enabled
- Verify .htaccess is uploaded
- Check PHP error logs

### Post-Installation

**"Too many redirects"**
- Clear browser cache
- Check .htaccess rewrite rules

**"CSRF token mismatch"**
- Clear browser cookies
- Check session storage is writable

**"Cannot login"**
- Verify password meets requirements
- Check if rate-limited (wait 15 min)
- Review login_attempts table

## 📞 Support

### Logs Location
- Application: `storage/logs/activity.log`
- Login attempts: `storage/logs/login.log`
- Bots: `storage/logs/bots.log`
- PHP errors: `storage/logs/php_errors.log`

### Security Checklist
- [ ] Install SSL certificate (HTTPS)
- [ ] Change default database password
- [ ] Enable captcha on login
- [ ] Review activity logs regularly
- [ ] Keep PHP updated
- [ ] Backup database regularly
- [ ] Set strong admin password

## 📄 License

This is a custom-built billing management system designed for production use.

## 🎯 Key Advantages

1. **No Composer Required** - Pure PHP, upload and run
2. **Zero Terminal Access Needed** - Everything via web interface
3. **Production-Ready** - Enterprise security out of the box
4. **Shared Hosting Compatible** - Works on any hosting
5. **Complete System** - No missing pieces, no placeholders
6. **Self-Contained** - All dependencies included
7. **Secure by Default** - Security features active from day one

---

**Built for production. Secured by design. Ready to deploy.**
