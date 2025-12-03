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
- **Sandbox Mode**: Run immediately with SQLite (no MySQL required).
- Auto-generates configuration files (`config.php`, `.env`)
- Automatic database schema import
- One-time installation with permanent lock
- Works completely in browser - no terminal required

### 🔒 Enterprise-Grade Security
(See detailed security features below)

## 🚀 Installation

### Requirements
- PHP >= 8.1
- MySQL/MariaDB (Optional, required for Production)
- SQLite (Optional, used for Sandbox Mode)
- Apache/Nginx with mod_rewrite
- PHP Extensions: `pdo_mysql` (or `pdo_sqlite`), `openssl`, `mbstring`, `tokenizer`, `json`, `fileinfo`, `xml`, `curl`, `zip`, `gd`

### Docker Deployment (Recommended)

#### Sandbox Mode (Fastest)
Run the application using the built-in SQLite support. No external database setup needed.

1. **Start Container**
   ```bash
   docker-compose up -d app
   ```
2. **Access Installer**
   - Open `http://localhost:8080`
   - Select **"SQLite (Sandbox Mode)"** in Step 2.

#### Production Mode
Run the application with a dedicated MySQL database container.

1. **Start Containers**
   ```bash
   docker-compose up -d
   ```
2. **Access Installer**
   - Open `http://localhost:8080`
   - Select **"MySQL / MariaDB"** in Step 2.
   - Use these credentials:
     - Host: `db`
     - Database: `billing_db`
     - Username: `db_user`
     - Password: `db_password`

### Manual Installation (Shared Hosting / VPS)

1. **Upload Files**
   - Upload all files to your web hosting.
   - Point domain/subdomain to the `public` folder.

2. **Set Permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   chmod -R 755 public/uploads/
   ```

3. **Run Installer**
   - Navigate to your domain in browser.
   - Follow the 5-step installation wizard.
   - Choose **SQLite** for instant sandbox setup OR **MySQL** for production.

4. **Done!**

## 🗂️ File Structure
... (Rest of the original README content)
