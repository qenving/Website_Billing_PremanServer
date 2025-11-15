#!/bin/bash

##############################################################################
# HBM Billing System - Automatic Installer for aaPanel/Ubuntu
# This script will automatically setup everything you need
##############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_header() {
    echo ""
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo ""
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use: sudo bash install.sh)"
    exit 1
fi

# Get the actual user (not root)
ACTUAL_USER=$(logname 2>/dev/null || echo $SUDO_USER)

print_header "HBM Billing System - Automatic Installer"

# Step 1: Detect installation directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
print_info "Installation directory: $SCRIPT_DIR"

# Step 2: Check PHP version
print_header "Step 1: Checking PHP Version"

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "0.0")
print_info "Detected PHP version: $PHP_VERSION"

if (( $(echo "$PHP_VERSION < 8.2" | bc -l) )); then
    print_error "PHP 8.2 or higher is required! Current: $PHP_VERSION"
    print_info "Please install PHP 8.3 via aaPanel first"
    exit 1
fi
print_success "PHP version OK"

# Step 3: Check required PHP extensions
print_header "Step 2: Checking PHP Extensions"

REQUIRED_EXTENSIONS=("bcmath" "ctype" "fileinfo" "json" "mbstring" "openssl" "pdo" "tokenizer" "xml" "curl")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "^$ext$"; then
        print_success "$ext extension installed"
    else
        print_error "$ext extension MISSING"
        MISSING_EXTENSIONS+=($ext)
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
    print_error "Missing extensions: ${MISSING_EXTENSIONS[*]}"
    print_info "Install missing extensions via aaPanel:"
    print_info "1. aaPanel → App Store → PHP ${PHP_VERSION}"
    print_info "2. Settings → Install Extensions"
    print_info "3. Install: ${MISSING_EXTENSIONS[*]}"
    exit 1
fi
print_success "All required PHP extensions installed"

# Step 4: Check Composer
print_header "Step 3: Checking Composer"

if ! command -v composer &> /dev/null; then
    print_error "Composer not found!"
    print_info "Installing Composer..."

    # Install Composer
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
        print_error "Composer installer corrupt"
        rm composer-setup.php
        exit 1
    fi

    php composer-setup.php --quiet
    rm composer-setup.php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer

    print_success "Composer installed"
else
    COMPOSER_VERSION=$(composer --version 2>/dev/null | grep -oP '\d+\.\d+\.\d+' | head -1)
    print_info "Composer version: $COMPOSER_VERSION"

    # Check if Composer >= 2.2
    if (( $(echo "$COMPOSER_VERSION < 2.2" | bc -l) )); then
        print_warning "Composer version too old, upgrading..."
        composer self-update
        print_success "Composer upgraded"
    else
        print_success "Composer version OK"
    fi
fi

# Step 5: Fix Git ownership
print_header "Step 4: Fixing Git Ownership"

if [ -d "$SCRIPT_DIR/.git" ]; then
    git config --global --add safe.directory "$SCRIPT_DIR"
    print_success "Git ownership fixed"
else
    print_info "Not a git repository, skipping"
fi

# Step 6: Create required directories
print_header "Step 5: Creating Required Directories"

REQUIRED_DIRS=(
    "bootstrap/cache"
    "storage/app/public"
    "storage/framework/cache"
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/logs"
    "public/storage"
)

for dir in "${REQUIRED_DIRS[@]}"; do
    mkdir -p "$SCRIPT_DIR/$dir"
    print_success "Created $dir"
done

# Step 7: Set correct permissions
print_header "Step 6: Setting Permissions"

print_info "Setting ownership to www:www..."
chown -R www:www "$SCRIPT_DIR"

print_info "Setting base permissions..."
find "$SCRIPT_DIR" -type d -exec chmod 755 {} \;
find "$SCRIPT_DIR" -type f -exec chmod 644 {} \;

print_info "Setting writable directories..."
chmod -R 775 "$SCRIPT_DIR/storage"
chmod -R 775 "$SCRIPT_DIR/bootstrap/cache"

print_info "Making artisan executable..."
chmod +x "$SCRIPT_DIR/artisan"

print_success "Permissions set correctly"

# Step 8: Setup .env file
print_header "Step 7: Setting up Environment File"

if [ ! -f "$SCRIPT_DIR/.env" ]; then
    if [ -f "$SCRIPT_DIR/.env.example" ]; then
        cp "$SCRIPT_DIR/.env.example" "$SCRIPT_DIR/.env"
        chown www:www "$SCRIPT_DIR/.env"
        chmod 644 "$SCRIPT_DIR/.env"
        print_success ".env file created from .env.example"
    else
        print_error ".env.example not found!"
        exit 1
    fi
else
    print_info ".env file already exists"
fi

# Step 9: Install Composer dependencies
print_header "Step 8: Installing Composer Dependencies"

print_info "This may take a few minutes..."

cd "$SCRIPT_DIR"

# Clear composer cache
su - www -s /bin/bash -c "cd $SCRIPT_DIR && composer clear-cache" 2>/dev/null || composer clear-cache

# Install dependencies as www user
print_info "Installing dependencies (this may take 2-5 minutes)..."
if su - www -s /bin/bash -c "cd $SCRIPT_DIR && composer install --optimize-autoloader --no-dev --no-interaction" 2>&1 | tee /tmp/composer-install.log; then
    print_success "Dependencies installed successfully"
else
    print_error "Composer install failed!"
    print_info "Check log: /tmp/composer-install.log"
    exit 1
fi

# Step 10: Generate application key
print_header "Step 9: Generating Application Key"

if grep -q "APP_KEY=base64:" "$SCRIPT_DIR/.env"; then
    print_info "APP_KEY already exists"
else
    php artisan key:generate --force
    print_success "APP_KEY generated"
fi

# Step 11: Final permission fix
print_header "Step 10: Final Permission Check"

chown -R www:www "$SCRIPT_DIR"
chmod -R 775 "$SCRIPT_DIR/storage"
chmod -R 775 "$SCRIPT_DIR/bootstrap/cache"

print_success "Final permissions set"

# Step 12: Create Nginx config helper
print_header "Step 11: Nginx Configuration"

cat > /tmp/hbm-nginx-config.txt << 'EOF'
========================================
NGINX CONFIGURATION FOR AAPANEL
========================================

1. Login to aaPanel → Website → Your Site → Settings

2. Click "Config File" tab

3. Make sure these settings are correct:

   root /www/wwwroot/YOUR_PROJECT_PATH/public;

   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }

   location ~ \.php$ {
       fastcgi_pass unix:/tmp/php-cgi-83.sock;
       fastcgi_index index.php;
       fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       include fastcgi_params;
   }

4. Save and Reload Nginx

5. Visit your website!

========================================
EOF

print_success "Installation Complete!"
echo ""
print_header "Next Steps"
echo ""
print_info "1. Configure Nginx in aaPanel (see instructions below)"
print_info "2. Setup database in aaPanel"
print_info "3. Visit your website to run the installation wizard"
echo ""
print_warning "IMPORTANT: Make sure Nginx root points to: $SCRIPT_DIR/public"
echo ""
cat /tmp/hbm-nginx-config.txt
echo ""
print_success "Installation script finished successfully!"
echo ""
print_info "Default URLs after Nginx setup:"
print_info "  - Installation: http://YOUR_DOMAIN/install"
print_info "  - Admin Login:  http://YOUR_DOMAIN/admin/login"
print_info "  - Client Login: http://YOUR_DOMAIN/login"
echo ""
print_warning "Remember to:"
print_warning "  1. Setup Nginx configuration in aaPanel"
print_warning "  2. Create database in aaPanel"
print_warning "  3. Setup SSL certificate (optional but recommended)"
echo ""

# Cleanup
rm -f /tmp/hbm-nginx-config.txt
rm -f /tmp/composer-install.log 2>/dev/null

print_success "✓ All done! Your HBM Billing System is ready!"
echo ""
