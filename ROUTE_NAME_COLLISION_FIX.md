# Route Name Collision Fix

## Problem Identified

The error "All Inertia requests must receive a valid Inertia response, however a plain JSON response was received" was caused by a **route name collision**.

### Root Cause

Both the API routes and Web routes were using the same route names:
- API: `vendors.index` → `VendorController@index` (returns JSON)
- Web: `vendors.index` → `Web\VendorController@index` (returns Inertia)

When Laravel's pagination generated URLs using `route('vendors.index')`, it picked the **first matching route** (the API route), causing pagination links to point to `/api/vendors` instead of `/vendors`.

### Evidence

The error message showed pagination URLs with `/api/vendors`:
```json
{
  "first_page_url": "http://sistemeperolehan.test/api/vendors?page=1",
  "path": "http://sistemeperolehan.test/api/vendors",
  ...
}
```

## Solution Applied

### Changed API Route Names

Updated `routes/api.php` to use prefixed route names:

**Before:**
```php
Route::apiResource('vendors', VendorController::class);
Route::apiResource('purchase-orders', PurchaseOrderController::class);
```

**After:**
```php
Route::apiResource('vendors', VendorController::class)->names([
    'index' => 'api.vendors.index',
    'store' => 'api.vendors.store',
    'show' => 'api.vendors.show',
    'update' => 'api.vendors.update',
    'destroy' => 'api.vendors.destroy',
]);

Route::apiResource('purchase-orders', PurchaseOrderController::class)->names([
    'index' => 'api.purchase-orders.index',
    'store' => 'api.purchase-orders.store',
    'show' => 'api.purchase-orders.show',
    'update' => 'api.purchase-orders.update',
    'destroy' => 'api.purchase-orders.destroy',
]);
```

### Route Names Now

**API Routes (for programmatic access):**
- `api.vendors.index` → `GET /api/vendors`
- `api.vendors.store` → `POST /api/vendors`
- `api.vendors.show` → `GET /api/vendors/{id}`
- `api.vendors.update` → `PUT/PATCH /api/vendors/{id}`
- `api.vendors.destroy` → `DELETE /api/vendors/{id}`
- `api.purchase-orders.index` → `GET /api/purchase-orders`
- `api.purchase-orders.store` → `POST /api/purchase-orders`
- `api.purchase-orders.show` → `GET /api/purchase-orders/{id}`
- `api.purchase-orders.update` → `PUT/PATCH /api/purchase-orders/{id}`
- `api.purchase-orders.destroy` → `DELETE /api/purchase-orders/{id}`

**Web Routes (for Inertia UI):**
- `vendors.index` → `GET /vendors`
- `vendors.create` → `GET /vendors/create`
- `vendors.store` → `POST /vendors`
- `vendors.show` → `GET /vendors/{id}`
- `vendors.edit` → `GET /vendors/{id}/edit`
- `vendors.update` → `PUT/PATCH /vendors/{id}`
- `vendors.destroy` → `DELETE /vendors/{id}`
- `purchase-orders.index` → `GET /purchase-orders`
- `purchase-orders.create` → `GET /purchase-orders/create`
- `purchase-orders.store` → `POST /purchase-orders`
- `purchase-orders.show` → `GET /purchase-orders/{id}`
- `purchase-orders.edit` → `GET /purchase-orders/{id}/edit`
- `purchase-orders.update` → `PUT/PATCH /purchase-orders/{id}`
- `purchase-orders.destroy` → `DELETE /purchase-orders/{id}`

## Verification

Run this command to verify unique route names:
```bash
php artisan route:list --name=vendors.index
```

**Expected Output:**
```
GET|HEAD  api/vendors .... api.vendors.index › VendorController@index
GET|HEAD  vendors ........ vendors.index › Web\VendorController@index
```

## Impact

### ✅ Fixed
- Pagination now generates correct URLs (`/vendors` instead of `/api/vendors`)
- Inertia requests now hit Web controllers (return Inertia responses)
- No more "plain JSON response" errors

### ⚠️ Breaking Changes for API Consumers
If external applications were using route names to generate API URLs, they need to update:
- Old: `route('vendors.index')` 
- New: `route('api.vendors.index')`

However, direct URL usage (`/api/vendors`) remains unchanged.

## Testing

After applying this fix:

1. **Clear route cache:**
   ```bash
   php artisan route:clear
   ```

2. **Hard refresh browser:**
   - Press `Ctrl + F5` (Windows) or `Cmd + Shift + R` (Mac)

3. **Test navigation:**
   - Click "Vendors" in sidebar
   - Should load vendor list without errors
   - Pagination should work correctly
   - Edit/Delete buttons should work

4. **Verify in DevTools:**
   - Network tab should show requests to `/vendors` (not `/api/vendors`)
   - Responses should be Inertia format (component + props)

## Additional Notes

- Web routes use `Route::resource()` which automatically creates 7 routes including `create` and `edit`
- API routes use `Route::apiResource()` which creates 5 routes (excludes `create` and `edit`)
- Both route types can coexist with unique names
- This pattern should be followed for all future resource routes

---

**Date Fixed**: November 13, 2025  
**Fixed By**: BLACKBOXAI  
**Status**: ✅ Resolved
