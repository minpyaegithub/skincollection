#!/bin/bash

# Comprehensive Livewire Fix Script for Production Server
# Usage: sudo bash fix-livewire-production.sh

set -e  # Exit on error

echo "========================================"
echo "Livewire Production Fix Script"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/www/wwwroot/default/skincollection"
WEB_USER="www"
WEB_GROUP="www"

# Check if running with appropriate permissions
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

echo -e "${GREEN}Step 1: Stopping services...${NC}"
systemctl stop nginx
systemctl stop php-fpm-83

echo -e "${GREEN}Step 2: Navigating to project directory...${NC}"
cd "$PROJECT_DIR" || exit 1

echo -e "${GREEN}Step 3: Clearing all Laravel caches...${NC}"
sudo -u $WEB_USER php artisan optimize:clear || true
sudo -u $WEB_USER php artisan cache:clear || true
sudo -u $WEB_USER php artisan config:clear || true
sudo -u $WEB_USER php artisan route:clear || true
sudo -u $WEB_USER php artisan view:clear || true

echo -e "${GREEN}Step 4: Removing bootstrap cache files...${NC}"
rm -f bootstrap/cache/*.php
echo "Removed bootstrap cache files"

echo -e "${GREEN}Step 5: Clearing Composer cache...${NC}"
sudo -u $WEB_USER composer clear-cache

echo -e "${GREEN}Step 6: Reinstalling Composer dependencies (without dev)...${NC}"
sudo -u $WEB_USER composer install --no-dev --optimize-autoloader --no-scripts

echo -e "${GREEN}Step 7: Running package discovery...${NC}"
sudo -u $WEB_USER composer dump-autoload -o
sudo -u $WEB_USER php artisan package:discover --ansi

echo -e "${GREEN}Step 8: Publishing Livewire assets...${NC}"
sudo -u $WEB_USER php artisan vendor:publish --tag=livewire:assets --force || true

echo -e "${GREEN}Step 9: Ensuring Livewire config exists...${NC}"
if [ ! -f "config/livewire.php" ]; then
    echo -e "${YELLOW}Warning: livewire.php config missing, publishing...${NC}"
    sudo -u $WEB_USER php artisan vendor:publish --tag=livewire:config --force
fi

echo -e "${GREEN}Step 10: Caching optimized configuration...${NC}"
sudo -u $WEB_USER php artisan config:cache
sudo -u $WEB_USER php artisan route:cache
sudo -u $WEB_USER php artisan view:cache

echo -e "${GREEN}Step 11: Setting proper file permissions...${NC}"
# Set ownership
chown -R $WEB_USER:$WEB_GROUP "$PROJECT_DIR"

# Set directory permissions
find "$PROJECT_DIR" -type d -exec chmod 755 {} \;

# Set file permissions
find "$PROJECT_DIR" -type f -exec chmod 644 {} \;

# Set specific permissions for writable directories
chmod -R 775 storage bootstrap/cache
chown -R $WEB_USER:$WEB_GROUP storage bootstrap/cache

# Set permissions for public assets
if [ -d "public/vendor/livewire" ]; then
    chmod -R 755 public/vendor/livewire
    chown -R $WEB_USER:$WEB_GROUP public/vendor/livewire
fi

echo -e "${GREEN}Step 12: Verifying Livewire installation...${NC}"
if sudo -u $WEB_USER php diagnose-livewire.php > /tmp/livewire-diagnostic.log 2>&1; then
    echo -e "${GREEN}Diagnostic completed. Check /tmp/livewire-diagnostic.log${NC}"
    cat /tmp/livewire-diagnostic.log
else
    echo -e "${YELLOW}Diagnostic script had issues, but continuing...${NC}"
fi

echo -e "${GREEN}Step 13: Starting services...${NC}"
systemctl start php-fpm-83
systemctl start nginx

echo -e "${GREEN}Step 14: Checking service status...${NC}"
if systemctl is-active --quiet nginx && systemctl is-active --quiet php-fpm-83; then
    echo -e "${GREEN}✓ Services are running${NC}"
else
    echo -e "${RED}✗ Warning: Some services may not be running properly${NC}"
    systemctl status nginx --no-pager
    systemctl status php-fpm-83 --no-pager
fi

echo ""
echo "========================================"
echo -e "${GREEN}Fix script completed!${NC}"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Visit your site: http://skincollections-aesthetic.com"
echo "2. Check browser console for any JavaScript errors"
echo "3. Verify /livewire/livewire.js is accessible"
echo "4. Test Livewire components (modals, etc.)"
echo ""
echo "If issues persist, check:"
echo "- /tmp/livewire-diagnostic.log for detailed diagnostic info"
echo "- Nginx error log: /www/wwwlogs/skincollections-aesthetic.com.error.log"
echo "- PHP-FPM error log: /www/server/php/83/var/log/php-fpm.log"
echo ""
