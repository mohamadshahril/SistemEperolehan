<?php

use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Vendor Management Routes
Route::apiResource('vendors', VendorController::class)->names([
    'index' => 'api.vendors.index',
    'store' => 'api.vendors.store',
    'show' => 'api.vendors.show',
    'update' => 'api.vendors.update',
    'destroy' => 'api.vendors.destroy',
]);

// Purchase Order Management Routes
Route::apiResource('purchase-orders', PurchaseOrderController::class)->names([
    'index' => 'api.purchase-orders.index',
    'store' => 'api.purchase-orders.store',
    'show' => 'api.purchase-orders.show',
    'update' => 'api.purchase-orders.update',
    'destroy' => 'api.purchase-orders.destroy',
]);
