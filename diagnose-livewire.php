#!/usr/bin/env php
<?php

/**
 * Livewire Diagnostic Script
 * Run this on the server to diagnose Livewire issues
 * Usage: php diagnose-livewire.php
 */

define('LARAVEL_START', microtime(true));

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Livewire Diagnostic Script ===\n\n";

// 1. Check if Livewire is installed
echo "1. Checking Livewire installation...\n";
if (class_exists(\Livewire\Livewire::class)) {
    echo "   ✓ Livewire class exists\n";
    echo "   Version: " . \Composer\InstalledVersions::getVersion('livewire/livewire') . "\n";
} else {
    echo "   ✗ Livewire class NOT found!\n";
    exit(1);
}

// 2. Check service providers
echo "\n2. Checking service providers...\n";
$providers = config('app.providers');
$livewireProviders = array_filter($providers, function($provider) {
    return strpos($provider, 'Livewire') !== false;
});
if (empty($livewireProviders)) {
    echo "   ⚠ No Livewire providers in config/app.php (auto-discovery should handle this)\n";
} else {
    echo "   ✓ Found Livewire providers:\n";
    foreach ($livewireProviders as $provider) {
        echo "     - $provider\n";
    }
}

// 3. Check if LivewireServiceProvider is loaded
echo "\n3. Checking loaded service providers...\n";
$loadedProviders = array_keys(app()->getLoadedProviders());
$livewireLoaded = array_filter($loadedProviders, function($provider) {
    return strpos($provider, 'Livewire') !== false;
});
if (empty($livewireLoaded)) {
    echo "   ✗ Livewire service providers NOT loaded!\n";
} else {
    echo "   ✓ Loaded Livewire providers:\n";
    foreach ($livewireLoaded as $provider) {
        echo "     - $provider\n";
    }
}

// 4. Check routes
echo "\n4. Checking Livewire routes...\n";
$routes = app('router')->getRoutes();
$livewireRoutes = [];
foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'livewire') !== false) {
        $livewireRoutes[] = $uri . ' [' . implode(', ', $route->methods()) . ']';
    }
}
if (empty($livewireRoutes)) {
    echo "   ✗ NO Livewire routes found!\n";
} else {
    echo "   ✓ Found Livewire routes:\n";
    foreach ($livewireRoutes as $route) {
        echo "     - $route\n";
    }
}

// 5. Check bootstrap/cache/packages.php
echo "\n5. Checking package discovery cache...\n";
$packagesFile = base_path('bootstrap/cache/packages.php');
if (file_exists($packagesFile)) {
    $packages = require $packagesFile;
    $hasLivewire = false;
    foreach ($packages as $type => $items) {
        foreach ($items as $key => $value) {
            if (strpos($key, 'Livewire') !== false || 
                (is_array($value) && strpos(json_encode($value), 'Livewire') !== false)) {
                $hasLivewire = true;
                break 2;
            }
        }
    }
    if ($hasLivewire) {
        echo "   ✓ Livewire found in packages cache\n";
    } else {
        echo "   ✗ Livewire NOT found in packages cache!\n";
    }
} else {
    echo "   ⚠ packages.php does not exist (will be auto-generated)\n";
}

// 6. Check Livewire config
echo "\n6. Checking Livewire configuration...\n";
try {
    $config = config('livewire');
    if ($config) {
        echo "   ✓ Livewire config loaded\n";
        echo "   - Class namespace: " . ($config['class_namespace'] ?? 'default') . "\n";
        echo "   - View path: " . ($config['view_path'] ?? 'default') . "\n";
        if (isset($config['asset_url'])) {
            echo "   - Asset URL: " . $config['asset_url'] . "\n";
        }
    } else {
        echo "   ✗ Livewire config NOT loaded\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error loading config: " . $e->getMessage() . "\n";
}

// 7. Check if Livewire can be instantiated
echo "\n7. Testing Livewire instantiation...\n";
try {
    $livewire = app('livewire');
    echo "   ✓ Livewire service can be resolved\n";
} catch (\Exception $e) {
    echo "   ✗ Cannot resolve Livewire: " . $e->getMessage() . "\n";
}

// 8. Check component registration
echo "\n8. Checking registered components...\n";
try {
    $manifest = app(\Livewire\Mechanisms\ComponentRegistry::class);
    echo "   ✓ Component registry exists\n";
    
    // Try to get registered components
    $testComponents = ['clinic-switcher', 'appointments-calendar', 'clinic-management'];
    foreach ($testComponents as $component) {
        try {
            $class = \Livewire\Livewire::getClass($component);
            echo "   ✓ Component '$component' is registered: $class\n";
        } catch (\Exception $e) {
            echo "   ✗ Component '$component' NOT found: " . $e->getMessage() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ Error checking components: " . $e->getMessage() . "\n";
}

// 9. Check public assets
echo "\n9. Checking Livewire assets...\n";
$assetsPath = public_path('vendor/livewire');
if (is_dir($assetsPath)) {
    echo "   ✓ Public assets directory exists: $assetsPath\n";
    $files = scandir($assetsPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "     - $file\n";
        }
    }
} else {
    echo "   ⚠ Public assets directory does not exist: $assetsPath\n";
}

// 10. Summary and recommendations
echo "\n=== SUMMARY ===\n";
if (empty($livewireLoaded)) {
    echo "❌ CRITICAL: Livewire service provider is NOT loaded!\n";
    echo "\nRECOMMENDED ACTIONS:\n";
    echo "1. Clear all caches: php artisan optimize:clear\n";
    echo "2. Delete bootstrap/cache/*.php files\n";
    echo "3. Run: composer dump-autoload -o\n";
    echo "4. Run: php artisan package:discover --ansi\n";
    echo "5. Run: php artisan config:cache\n";
    echo "6. Restart PHP-FPM\n";
} else if (empty($livewireRoutes)) {
    echo "❌ CRITICAL: Livewire routes are NOT registered!\n";
    echo "\nRECOMMENDED ACTIONS:\n";
    echo "1. Check if LivewireServiceProvider boot() method is being called\n";
    echo "2. Clear route cache: php artisan route:clear\n";
    echo "3. Check for route caching: delete bootstrap/cache/routes-v7.php\n";
    echo "4. Verify APP_ENV is not 'production' (or route:cache was run correctly)\n";
} else {
    echo "✅ Livewire appears to be configured correctly!\n";
    echo "\nIf you're still seeing 404 errors, check:\n";
    echo "1. Nginx configuration (rewrite rules)\n";
    echo "2. PHP-FPM is running and properly configured\n";
    echo "3. File permissions on storage and bootstrap/cache\n";
}

echo "\n";
