#!/bin/bash

# Fix Livewire Routes on Server
# Run this script on your Ubuntu server

echo "=== Fixing Livewire Routes Issue ==="

cd /www/wwwroot/default/skincollection

echo ""
echo "Step 1: Pull latest changes..."
git pull origin master

echo ""
echo "Step 2: Clear all cached files..."
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php

echo ""
echo "Step 3: Regenerate Composer autoloader..."
composer dump-autoload --no-scripts --ignore-platform-req=php -o

echo ""
echo "Step 4: Regenerate package discovery..."
php artisan package:discover --ansi

echo ""
echo "Step 5: Check if Livewire is discovered..."
cat bootstrap/cache/packages.php | grep -i livewire || echo "WARNING: Livewire not found in packages.php"

echo ""
echo "Step 6: Rebuild config cache (WITHOUT route cache)..."
php artisan config:cache

echo ""
echo "Step 7: Check if Livewire routes are registered..."
php artisan route:list | grep livewire || echo "WARNING: No Livewire routes found"

echo ""
echo "Step 8: Check Livewire installation..."
composer show livewire/livewire

echo ""
echo "Step 9: Set proper permissions..."
sudo chown -R www:www storage bootstrap/cache vendor
sudo chmod -R 775 storage bootstrap/cache

echo ""
echo "Step 10: Restart services..."
sudo systemctl restart php-fpm-83
sudo systemctl restart nginx

echo ""
echo "Step 11: Test Livewire endpoint..."
curl -I http://skincollections-aesthetic.com/livewire/livewire.js

echo ""
echo "Step 12: Test the main site..."
curl -I http://skincollections-aesthetic.com

echo ""
echo "=== Done! ==="
echo ""
echo "If Livewire routes are still not showing, run manually:"
echo "  php artisan tinker"
echo "  >>> app()->make('livewire')"
echo ""
echo "Check the output above for any errors or warnings."
