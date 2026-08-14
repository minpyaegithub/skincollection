#!/bin/bash

# Emergency Livewire Fix - Manually Register Service Provider
# Use this ONLY if auto-discovery is failing on production
# This script will manually add Livewire's service provider to config/app.php

set -e

PROJECT_DIR="/www/wwwroot/default/skincollection"
CONFIG_FILE="$PROJECT_DIR/config/app.php"

echo "========================================"
echo "Emergency Livewire Service Provider Registration"
echo "========================================"
echo ""

# Check if config file exists
if [ ! -f "$CONFIG_FILE" ]; then
    echo "Error: config/app.php not found at $CONFIG_FILE"
    exit 1
fi

# Check if Livewire provider already exists
if grep -q "Livewire\\\\LivewireServiceProvider" "$CONFIG_FILE"; then
    echo "Livewire service provider is already registered in config/app.php"
    echo "No changes needed."
    exit 0
fi

echo "Backing up config/app.php..."
cp "$CONFIG_FILE" "${CONFIG_FILE}.backup.$(date +%Y%m%d_%H%M%S)"

echo "Adding Livewire service provider to config/app.php..."

# Use perl to insert the provider after the comment "Package Service Providers..."
perl -i -pe 's/(\/\*\s*\n\s*\* Package Service Providers\.\.\.\s*\n\s*\*\/)/$1\n        Livewire\\LivewireServiceProvider::class,/' "$CONFIG_FILE"

echo "✓ Livewire service provider added"
echo ""
echo "Clearing caches..."
cd "$PROJECT_DIR"
sudo -u www php artisan config:clear
sudo -u www php artisan cache:clear
sudo -u www php artisan route:clear

echo ""
echo "Verifying registration..."
sudo -u www php artisan tinker --execute="dd(array_filter(config('app.providers'), fn(\$p) => strpos(\$p, 'Livewire') !== false));"

echo ""
echo "========================================"
echo "Manual registration complete!"
echo "========================================"
echo ""
echo "Now restart PHP-FPM and Nginx:"
echo "  systemctl restart php-fpm-83"
echo "  systemctl restart nginx"
echo ""
