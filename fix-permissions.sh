#!/bin/bash

##############################################################################
# HBM Billing System - Quick Permission Fix
# Use this if you get permission errors or 403 Forbidden
##############################################################################

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  HBM Billing - Permission Fixer${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "Fixing permissions for: $SCRIPT_DIR"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root: sudo bash fix-permissions.sh"
    exit 1
fi

echo "Step 1: Creating required directories..."
mkdir -p "$SCRIPT_DIR/bootstrap/cache"
mkdir -p "$SCRIPT_DIR/storage/framework/cache"
mkdir -p "$SCRIPT_DIR/storage/framework/sessions"
mkdir -p "$SCRIPT_DIR/storage/framework/views"
mkdir -p "$SCRIPT_DIR/storage/logs"
echo -e "${GREEN}✓ Directories created${NC}"

echo ""
echo "Step 2: Setting ownership to www:www..."
chown -R www:www "$SCRIPT_DIR"
echo -e "${GREEN}✓ Ownership set${NC}"

echo ""
echo "Step 3: Setting base permissions..."
find "$SCRIPT_DIR" -type d -exec chmod 755 {} \;
find "$SCRIPT_DIR" -type f -exec chmod 644 {} \;
echo -e "${GREEN}✓ Base permissions set${NC}"

echo ""
echo "Step 4: Setting writable directories..."
chmod -R 775 "$SCRIPT_DIR/storage"
chmod -R 775 "$SCRIPT_DIR/bootstrap/cache"
echo -e "${GREEN}✓ Writable directories set${NC}"

echo ""
echo "Step 5: Making artisan executable..."
chmod +x "$SCRIPT_DIR/artisan"
echo -e "${GREEN}✓ Artisan executable${NC}"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ All permissions fixed!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "You can now:"
echo "  1. Visit your website (should work now)"
echo "  2. Run: php artisan list"
echo "  3. Access: http://YOUR_DOMAIN/install"
echo ""
