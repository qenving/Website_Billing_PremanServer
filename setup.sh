#!/bin/bash

echo "========================================="
echo "  HBM Billing Manager - Setup Script"
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}File .env tidak ditemukan. Membuat dari .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✓ File .env berhasil dibuat${NC}"
else
    echo -e "${GREEN}✓ File .env sudah ada${NC}"
fi

# Check PHP version
echo ""
echo "Memeriksa versi PHP..."
PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
REQUIRED_VERSION="8.3"

if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PHP_VERSION" | sort -V | head -n1)" != "$REQUIRED_VERSION" ]; then
    echo -e "${RED}✗ PHP version $PHP_VERSION terdeteksi. Minimal PHP $REQUIRED_VERSION diperlukan!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP version $PHP_VERSION OK${NC}"

# Check if composer is installed
echo ""
echo "Memeriksa Composer..."
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Composer tidak ditemukan. Mengunduh Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    COMPOSER_CMD="php composer.phar"
else
    echo -e "${GREEN}✓ Composer sudah terinstall${NC}"
    COMPOSER_CMD="composer"
fi

# Install dependencies
echo ""
echo "Menginstall dependencies..."
$COMPOSER_CMD install --no-dev --optimize-autoloader

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Dependencies berhasil diinstall${NC}"
else
    echo -e "${RED}✗ Gagal menginstall dependencies${NC}"
    exit 1
fi

# Generate APP_KEY
echo ""
echo "Generate Application Key..."
php artisan key:generate --force
echo -e "${GREEN}✓ Application key berhasil di-generate${NC}"

# Set permissions
echo ""
echo "Setting permissions untuk storage dan bootstrap/cache..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Permissions berhasil diset${NC}"
else
    echo -e "${YELLOW}⚠ Warning: Gagal set permissions. Silakan set manual:${NC}"
    echo "  chmod -R 775 storage"
    echo "  chmod -R 775 bootstrap/cache"
fi

# Ask for database configuration
echo ""
echo "========================================="
echo "  Konfigurasi Database"
echo "========================================="
echo ""
read -p "Apakah Anda ingin mengkonfigurasi database sekarang? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Database Host (default: 127.0.0.1): " DB_HOST
    DB_HOST=${DB_HOST:-127.0.0.1}

    read -p "Database Port (default: 3306): " DB_PORT
    DB_PORT=${DB_PORT:-3306}

    read -p "Database Name: " DB_DATABASE
    read -p "Database Username: " DB_USERNAME
    read -sp "Database Password: " DB_PASSWORD
    echo ""

    # Update .env file
    sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env

    echo -e "${GREEN}✓ Konfigurasi database berhasil diupdate${NC}"

    # Run migrations
    echo ""
    read -p "Jalankan database migrations sekarang? (y/n): " -n 1 -r
    echo ""

    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate --force

        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Migrations berhasil dijalankan${NC}"

            # Ask for seeders
            read -p "Jalankan database seeders (data awal)? (y/n): " -n 1 -r
            echo ""

            if [[ $REPLY =~ ^[Yy]$ ]]; then
                php artisan db:seed --force
                echo -e "${GREEN}✓ Seeders berhasil dijalankan${NC}"
            fi
        else
            echo -e "${RED}✗ Gagal menjalankan migrations. Periksa konfigurasi database Anda.${NC}"
        fi
    fi
fi

# Optimize for production
echo ""
echo "========================================="
echo "  Optimasi untuk Production"
echo "========================================="
echo ""
read -p "Apakah ini environment production? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Update environment
    sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
    sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env

    # Cache config
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo -e "${GREEN}✓ Optimasi production selesai${NC}"
else
    echo -e "${YELLOW}Skipping production optimization${NC}"
fi

# Create symbolic link for storage
echo ""
echo "Membuat symbolic link untuk storage..."
php artisan storage:link 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Storage link berhasil dibuat${NC}"
else
    echo -e "${YELLOW}⚠ Storage link mungkin sudah ada atau gagal dibuat${NC}"
fi

# Final message
echo ""
echo "========================================="
echo "  Setup Selesai!"
echo "========================================="
echo ""
echo -e "${GREEN}Instalasi berhasil!${NC}"
echo ""
echo "Langkah selanjutnya:"
echo "1. Pastikan web server mengarah ke folder 'public'"
echo "2. Edit file .env untuk konfigurasi lebih lanjut"
echo "3. Setup cron job untuk scheduler (optional):"
echo "   * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "Untuk informasi lebih lanjut, baca INSTALL.md"
echo ""
echo -e "${GREEN}Terima kasih telah menggunakan HBM Billing Manager!${NC}"
echo ""
