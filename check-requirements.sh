#!/bin/bash

##############################################################################
# HBM Billing System - Requirements Checker
# Run this before installation to check if your server is ready
##############################################################################

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }

echo ""
echo "========================================"
echo "  HBM Billing - Requirements Checker"
echo "========================================"
echo ""

ALL_OK=true

# Check 1: PHP Version
echo "Checking PHP..."
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "0.0")
echo "PHP Version: $PHP_VERSION"

if (( $(echo "$PHP_VERSION >= 8.2" | bc -l) )); then
    print_success "PHP version is compatible"
else
    print_error "PHP 8.2+ required! (You have: $PHP_VERSION)"
    print_info "Install PHP 8.3 via aaPanel → App Store → PHP 8.3"
    ALL_OK=false
fi
echo ""

# Check 2: Required PHP Extensions
echo "Checking PHP Extensions..."
REQUIRED_EXTENSIONS=("bcmath" "ctype" "fileinfo" "json" "mbstring" "openssl" "pdo" "pdo_mysql" "tokenizer" "xml" "curl")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        print_success "$ext"
    else
        print_error "$ext is MISSING"
        ALL_OK=false
    fi
done
echo ""

# Check 3: Composer
echo "Checking Composer..."
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version 2>/dev/null | grep -oP '\d+\.\d+\.\d+' | head -1)
    echo "Composer Version: $COMPOSER_VERSION"

    if (( $(echo "$COMPOSER_VERSION >= 2.2" | bc -l) )); then
        print_success "Composer version is compatible"
    else
        print_warning "Composer 2.2+ recommended (You have: $COMPOSER_VERSION)"
        print_info "Run: composer self-update"
    fi
else
    print_error "Composer not installed"
    print_info "Install via aaPanel → App Store → Composer"
    ALL_OK=false
fi
echo ""

# Check 4: Web Server
echo "Checking Web Server..."
if systemctl is-active --quiet nginx; then
    print_success "Nginx is running"
elif systemctl is-active --quiet apache2; then
    print_success "Apache is running"
else
    print_error "No web server detected (Nginx/Apache)"
    print_info "Install Nginx via aaPanel → App Store → Nginx"
    ALL_OK=false
fi
echo ""

# Check 5: Database
echo "Checking Database..."
if systemctl is-active --quiet mysql || systemctl is-active --quiet mariadb; then
    print_success "MySQL/MariaDB is running"
else
    print_error "MySQL/MariaDB not running"
    print_info "Install MySQL via aaPanel → App Store → MySQL 8.0"
    ALL_OK=false
fi
echo ""

# Check 6: Permissions
echo "Checking Permissions..."
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

if [ -w "$SCRIPT_DIR" ]; then
    print_success "Directory is writable"
else
    print_error "Directory is not writable"
    print_info "Run: sudo chown -R www:www $SCRIPT_DIR"
    ALL_OK=false
fi
echo ""

# Final Result
echo "========================================"
if [ "$ALL_OK" = true ]; then
    print_success "All requirements met! Ready to install."
    echo ""
    print_info "Run installation with:"
    print_info "  sudo bash install.sh"
else
    print_error "Some requirements are missing!"
    echo ""
    print_info "Fix the issues above, then run this checker again."
fi
echo "========================================"
echo ""
