<?php

/**
 * Tender Module Diagnostic Script
 * Run this with: php diagnose-tender-issue.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TENDER MODULE DIAGNOSTIC ===\n\n";

// 1. Check if migrations are run
echo "1. Checking database tables...\n";
try {
    $tables = DB::select("SHOW TABLES LIKE 'tenders'");
    if (count($tables) > 0) {
        echo "   ✓ 'tenders' table exists\n";
    } else {
        echo "   ✗ 'tenders' table NOT found\n";
        echo "   → Run: php artisan migrate\n";
    }
    
    $tables = DB::select("SHOW TABLES LIKE 'tender_bids'");
    if (count($tables) > 0) {
        echo "   ✓ 'tender_bids' table exists\n";
    } else {
        echo "   ✗ 'tender_bids' table NOT found\n";
        echo "   → Run: php artisan migrate\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Database connection error: " . $e->getMessage() . "\n";
    echo "   → Check your .env database settings\n";
}

echo "\n";

// 2. Check if data exists
echo "2. Checking tender data...\n";
try {
    $tenderCount = \App\Models\Tender::count();
    echo "   Total tenders: $tenderCount\n";
    
    if ($tenderCount === 0) {
        echo "   ✗ No tenders found in database\n";
        echo "   → Run: php artisan db:seed --class=TenderSeeder\n";
    } else {
        echo "   ✓ Tenders exist in database\n";
        
        // Show status breakdown
        $statuses = \App\Models\Tender::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        echo "   Status breakdown:\n";
        foreach ($statuses as $status) {
            echo "     - {$status->status}: {$status->count}\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ Error querying tenders: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Check routes
echo "3. Checking routes...\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $tenderRoutes = [];
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'tender')) {
            $tenderRoutes[] = $route->uri();
        }
    }
    
    if (count($tenderRoutes) > 0) {
        echo "   ✓ Found " . count($tenderRoutes) . " tender routes\n";
        echo "   Routes:\n";
        foreach (array_slice($tenderRoutes, 0, 5) as $route) {
            echo "     - /$route\n";
        }
        if (count($tenderRoutes) > 5) {
            echo "     ... and " . (count($tenderRoutes) - 5) . " more\n";
        }
    } else {
        echo "   ✗ No tender routes found\n";
        echo "   → Check routes/web.php\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error checking routes: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Check controller
echo "4. Checking controller...\n";
if (class_exists('\App\Http\Controllers\Web\TenderController')) {
    echo "   ✓ TenderController exists\n";
    
    $controller = new \App\Http\Controllers\Web\TenderController();
    if (method_exists($controller, 'index')) {
        echo "   ✓ index() method exists\n";
    } else {
        echo "   ✗ index() method NOT found\n";
    }
} else {
    echo "   ✗ TenderController NOT found\n";
    echo "   → Check app/Http/Controllers/Web/TenderController.php\n";
}

echo "\n";

// 5. Check model
echo "5. Checking model...\n";
if (class_exists('\App\Models\Tender')) {
    echo "   ✓ Tender model exists\n";
    
    $model = new \App\Models\Tender();
    $fillable = $model->getFillable();
    echo "   Fillable fields: " . count($fillable) . "\n";
    
    // Check relationships
    if (method_exists($model, 'creator')) {
        echo "   ✓ creator() relationship exists\n";
    }
    if (method_exists($model, 'bids')) {
        echo "   ✓ bids() relationship exists\n";
    }
    if (method_exists($model, 'awardedBid')) {
        echo "   ✓ awardedBid() relationship exists\n";
    }
} else {
    echo "   ✗ Tender model NOT found\n";
    echo "   → Check app/Models/Tender.php\n";
}

echo "\n";

// 6. Check frontend files
echo "6. Checking frontend files...\n";
$frontendFiles = [
    'resources/js/pages/tenders/Index.vue',
    'resources/js/pages/tenders/Create.vue',
    'resources/js/pages/tenders/Edit.vue',
    'resources/js/pages/tenders/Show.vue',
];

foreach ($frontendFiles as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file exists\n";
    } else {
        echo "   ✗ $file NOT found\n";
    }
}

echo "\n";

// 7. Check if frontend is built
echo "7. Checking frontend build...\n";
if (file_exists('public/build/manifest.json')) {
    echo "   ✓ Frontend build exists (public/build/manifest.json)\n";
} else {
    echo "   ✗ Frontend NOT built\n";
    echo "   → Run: npm run build\n";
}

echo "\n";

// 8. Check users
echo "8. Checking users...\n";
try {
    $userCount = \App\Models\User::count();
    echo "   Total users: $userCount\n";
    
    if ($userCount === 0) {
        echo "   ✗ No users found\n";
        echo "   → Create a user to access the system\n";
    } else {
        echo "   ✓ Users exist\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error querying users: " . $e->getMessage() . "\n";
}

echo "\n";

// 9. Check vendors (required for tenders)
echo "9. Checking vendors...\n";
try {
    $vendorCount = \App\Models\Vendor::count();
    echo "   Total vendors: $vendorCount\n";
    
    if ($vendorCount === 0) {
        echo "   ⚠ No vendors found (needed for tender bids)\n";
        echo "   → Create vendors or run seeder\n";
    } else {
        echo "   ✓ Vendors exist\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error querying vendors: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary
echo "=== DIAGNOSTIC SUMMARY ===\n\n";

try {
    $issues = [];
    
    // Check critical issues
    $tables = DB::select("SHOW TABLES LIKE 'tenders'");
    if (count($tables) === 0) {
        $issues[] = "Tenders table missing - run migrations";
    }
    
    $tenderCount = \App\Models\Tender::count();
    if ($tenderCount === 0) {
        $issues[] = "No tender data - run seeder";
    }
    
    if (!file_exists('public/build/manifest.json')) {
        $issues[] = "Frontend not built - run npm run build";
    }
    
    if (count($issues) === 0) {
        echo "✓ No critical issues found!\n\n";
        echo "If tenders still not showing:\n";
        echo "1. Clear cache: php artisan cache:clear\n";
        echo "2. Check browser console for JavaScript errors\n";
        echo "3. Verify you are logged in\n";
        echo "4. Check Laravel logs: storage/logs/laravel.log\n";
    } else {
        echo "Found " . count($issues) . " issue(s):\n\n";
        foreach ($issues as $i => $issue) {
            echo ($i + 1) . ". $issue\n";
        }
        
        echo "\n";
        echo "Quick fix commands:\n";
        echo "php artisan migrate\n";
        echo "php artisan db:seed --class=TenderSeeder\n";
        echo "npm run build\n";
        echo "php artisan cache:clear\n";
    }
} catch (\Exception $e) {
    echo "Error generating summary: " . $e->getMessage() . "\n";
}

echo "\n=== END OF DIAGNOSTIC ===\n";
