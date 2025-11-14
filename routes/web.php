<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\Web\VendorController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\LocationController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth','verified'])->group(function () {
    // Purchase Requests
    Route::get('purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
    Route::get('purchase-requests/create', [PurchaseRequestController::class, 'create'])->name('purchase-requests.create');
    Route::post('purchase-requests', [PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
    Route::get('purchase-requests/{purchaseRequest}/edit', [PurchaseRequestController::class, 'edit'])->name('purchase-requests.edit');
    Route::put('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update'])->name('purchase-requests.update');
    Route::delete('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'destroy'])->name('purchase-requests.destroy');

    // Vendors
    Route::resource('vendors', VendorController::class);

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);

    // Locations CRUD
    Route::resource('locations', LocationController::class);

    // Approvals Module (Managers)
    Route::get('approvals', [PurchaseRequestController::class, 'approvalsIndex'])->name('approvals.index');
    Route::post('approvals/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])->name('approvals.reject');
});

require __DIR__.'/settings.php';
