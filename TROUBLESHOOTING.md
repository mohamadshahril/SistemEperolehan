# Troubleshooting: Inertia JSON Response Error

## Issue
Getting error: "All Inertia requests must receive a valid Inertia response, however a plain JSON response was received."

## Root Cause Analysis

The error shows JSON data like:
```json
{"current_page":1,"data":[{"id":6,"name":"test2",...}]}
```

This is the response from the **API controller** (`app/Http/Controllers/VendorController.php`), not the **Web controller** (`app/Http/Controllers/Web/VendorController.php`).

## Verified Route Setup ✅

Routes are correctly configured:
- **API routes**: `api/vendors` → `VendorController@index` (returns JSON)
- **Web routes**: `vendors` → `Web\VendorController@index` (returns Inertia)

## Solution Steps

### Step 1: Clear Browser Cache
1. Open your browser
2. Press `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
3. Clear cached images and files
4. Close and reopen browser

### Step 2: Hard Refresh the Page
1. Navigate to `http://sistemperolehan.test/vendors`
2. Press `Ctrl + F5` (Windows) or `Cmd + Shift + R` (Mac)
3. This forces a complete page reload

### Step 3: Verify Vite Dev Server is Running
The Vite dev server should be running on `http://localhost:5174/`

If not running:
```bash
npm run dev
```

Keep this terminal open while testing.

### Step 4: Clear Laravel Caches
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 5: Verify Request in Browser DevTools

1. Open Browser DevTools (F12)
2. Go to Network tab
3. Navigate to `/vendors`
4. Click on the request to `/vendors`
5. Check **Request Headers** - should include:
   ```
   X-Inertia: true
   X-Inertia-Version: [some hash]
   Accept: text/html, application/xhtml+xml
   ```
6. Check **Response Headers** - should include:
   ```
   X-Inertia: true
   Content-Type: application/json (this is normal for Inertia)
   ```
7. Check **Response** - should be Inertia format:
   ```json
   {
     "component": "vendors/Index",
     "props": {...},
     "url": "/vendors",
     "version": "..."
   }
   ```

### Step 6: Check if Request is Going to Correct URL

In DevTools Network tab, verify:
- ❌ **Wrong**: Request URL is `http://sistemperolehan.test/api/vendors`
- ✅ **Correct**: Request URL is `http://sistemperolehan.test/vendors`

If it's going to `/api/vendors`, the Link component is not working correctly.

### Step 7: Verify Link Component Usage

Check that all navigation uses `<Link>` not `<a>`:

**❌ Wrong:**
```vue
<a href="/vendors">Vendors</a>
```

**✅ Correct:**
```vue
<Link href="/vendors">Vendors</Link>
```

And import statement includes Link:
```vue
import { Head, Link, router } from '@inertiajs/vue3'
```

## Additional Checks

### Check 1: Verify Middleware
The web routes should have Inertia middleware. Check `bootstrap/app.php`:

```php
$middleware->web(append: [
    HandleAppearance::class,
    HandleInertiaRequests::class,  // ← This is important
    AddLinkHeadersForPreloadedAssets::class,
]);
```

### Check 2: Test with Direct Browser Navigation
1. Close all browser tabs
2. Open new tab
3. Type directly in address bar: `http://sistemperolehan.test/vendors`
4. Press Enter

This should work because it's a fresh request.

### Check 3: Check for JavaScript Errors
In Browser DevTools Console tab, check for any JavaScript errors that might prevent Inertia from working.

## If Still Not Working

### Option 1: Restart Everything
```bash
# Stop Vite dev server (Ctrl+C in terminal)
# Then restart:
npm run dev

# In another terminal:
php artisan serve
```

### Option 2: Check Inertia Version
```bash
npm list @inertiajs/vue3
```

Should be version 1.x or 2.x

### Option 3: Reinstall Node Modules
```bash
rm -rf node_modules
rm package-lock.json
npm install
npm run dev
```

### Option 4: Test API Route Directly
Open browser and go to: `http://sistemperolehan.test/api/vendors`

This should show JSON data. If this works, it confirms API routes are fine.

### Option 5: Test Web Route Directly
Open browser and go to: `http://sistemperolehan.test/vendors`

This should show the Inertia page (not JSON). If you see JSON here, there's a routing issue.

## Expected Behavior

### When Working Correctly:
1. Click "Vendors" in sidebar
2. URL changes to `/vendors` (not `/api/vendors`)
3. Page updates smoothly without full reload
4. No error messages
5. Vendor list displays in the UI

### Current Behavior (Error):
1. Click "Vendors" in sidebar
2. URL might show `/vendors` but request goes to `/api/vendors`
3. Error: "All Inertia requests must receive a valid Inertia response..."
4. JSON data displayed instead of UI

## Quick Test

Run this in browser console when on the vendors page:
```javascript
console.log(window.location.href);
// Should show: http://sistemperolehan.test/vendors (not /api/vendors)
```

## Contact Points

If issue persists after all steps:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify all files were saved and Vite recompiled
4. Try accessing from incognito/private browsing mode

---

**Last Updated**: November 13, 2025  
**Status**: Troubleshooting in progress
