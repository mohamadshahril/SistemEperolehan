<?php

/**
 * Site Status Checker
 * Run this with: php check-site-status.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SITE STATUS CHECK ===\n\n";

// 1. Check PHP syntax in VendorController
echo "1. Checking VendorController PHP syntax...\n";
$vendorControllerPath = 'app/Http/Controllers/Web/VendorController.php';
$output = [];
$returnVar = 0;
exec("php -l $vendorControllerPath 2>&1", $output, $returnVar);

if ($returnVar === 0) {
    echo "   ✓ VendorController syntax is valid\n";
} else {
    echo "   ✗ VendorController has syntax errors:\n";
    foreach ($output as $line) {
        echo "     $line\n";
    }
}

echo "\n";

// 2. Test a simple route
echo "2. Testing if Laravel can boot...\n";
try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "   ✓ Laravel kernel loaded successfully\n";
} catch (\Exception $e) {
    echo "   ✗ Laravel kernel failed: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Check if Vite manifest exists and is valid
echo "3. Checking Vite build...\n";
if (file_exists('public/build/manifest.json')) {
    $manifest = json_decode(file_get_contents('public/build/manifest.json'), true);
    if ($manifest) {
        echo "   ✓ Vite manifest is valid\n";
        echo "   Build contains " . count($manifest) . " entries\n";
        
        // Check if app.ts is in manifest
        if (isset($manifest['resources/js/app.ts'])) {
            echo "   ✓ app.ts found in manifest\n";
        } else {
            echo "   ✗ app.ts NOT found in manifest\n";
        }
    } else {
        echo "   ✗ Vite manifest is invalid JSON\n";
    }
} else {
    echo "   ✗ Vite manifest not found\n";
    echo "   → Run: npm run build\n";
}

echo "\n";

// 4. Check if there are any PHP errors in recent logs
echo "4. Checking recent Laravel logs...\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -50);
    
    $hasErrors = false;
    foreach ($recentLines as $line) {
        if (stripos($line, 'ParseError') !== false || 
            stripos($line, 'syntax error') !== false ||
            stripos($line, 'Fatal error') !== false) {
            $hasErrors = true;
            echo "   ✗ Found error: " . substr($line, 0, 100) . "...\n";
        }
    }
    
    if (!$hasErrors) {
        echo "   ✓ No recent PHP errors found\n";
    }
} else {
    echo "   ⚠ No log file found\n";
}

echo "\n";

// 5. Test database connection
echo "5. Testing database connection...\n";
try {
    DB::connection()->getPdo();
    echo "   ✓ Database connection successful\n";
} catch (\Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Check if tenders exist
echo "6. Checking tender data...\n";
try {
    $tenderCount = \App\Models\Tender::count();
    echo "   ✓ Found $tenderCount tenders\n";
} catch (\Exception $e) {
    echo "   ✗ Error querying tenders: " . $e->getMessage() . "\n";
}

echo "\n";

// 7. Try to render the tenders index page
echo "7. Testing Tenders Index rendering...\n";
try {
    $request = \Illuminate\Http\Request::create('/tenders', 'GET');
    $request->headers->set('X-Inertia', 'true');
    $request->headers->set('X-Inertia-Version', '1.0');
    
    $response = $app->handle($request);
    
    if ($response->getStatusCode() === 200) {
        echo "   ✓ Tenders page renders successfully (HTTP 200)\n";
        
        $content = $response->getContent();
        if (stripos($content, 'tenders/Index') !== false) {
            echo "   ✓ Response contains tenders/Index component\n";
        } else {
            echo "   ⚠ Response doesn't mention tenders/Index\n";
        }
    } else {
        echo "   ✗ Tenders page returned HTTP " . $response->getStatusCode() . "\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error rendering tenders page: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n";
    echo "   " . $e->getTraceAsString() . "\n";
}

echo "\n";

// 8. Check Herd status
echo "8. Checking Laravel Herd...\n";
if (file_exists('C:/Program Files/Herd/herd.exe')) {
    echo "   ✓ Herd is installed\n";
    
    // Check if site is parked
    $siteName = basename(getcwd());
    echo "   Site name: $siteName\n";
    echo "   Expected URL: http://$siteName.test\n";
} else {
    echo "   ⚠ Herd not found at default location\n";
}

echo "\n";

// Summary
echo "=== SUMMARY ===\n\n";

$issues = [];

// Check critical issues
if ($returnVar !== 0) {
    $issues[] = "PHP syntax error in VendorController";
}

if (!file_exists('public/build/manifest.json')) {
    $issues[] = "Frontend not built";
}

try {
    $tenderCount = \App\Models\Tender::count();
    if ($tenderCount === 0) {
        $issues[] = "No tender data in database";
    }
} catch (\Exception $e) {
    $issues[] = "Cannot query tenders: " . $e->getMessage();
}

if (count($issues) === 0) {
    echo "✓ All checks passed!\n\n";
    echo "If the site still shows a blank screen:\n";
    echo "1. Open browser DevTools (F12)\n";
    echo "2. Check Console tab for JavaScript errors\n";
    echo "3. Check Network tab for failed requests\n";
    echo "4. Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)\n";
    echo "5. Try accessing: http://sistemeperolehan.test/tenders\n";
} else {
    echo "Found " . count($issues) . " issue(s):\n\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". $issue\n";
    }
    
    echo "\nRecommended fixes:\n";
    if (in_array("Frontend not built", $issues)) {
        echo "- Run: npm run build\n";
    }
    if (in_array("No tender data in database", $issues)) {
        echo "- Run: php artisan db:seed --class=TenderSeeder\n";
    }
}

echo "\n=== END OF CHECK ===\n";
