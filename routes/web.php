<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\Web\VendorController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\VotController;
use App\Http\Controllers\FileReferenceController;
use App\Http\Controllers\TypeProcurementController;
use App\Http\Controllers\ItemUnitController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\DeliveryReportController;
use App\Http\Controllers\Web\TenderController;
use App\Http\Controllers\Web\TenderBidController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth','verified'])->group(function () {
    // Purchase Requests
    Route::get('purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
    Route::get('purchase-requests/create', [PurchaseRequestController::class, 'create'])
        ->middleware('can:create purchase requests')
        ->name('purchase-requests.create');
    Route::post('purchase-requests', [PurchaseRequestController::class, 'store'])
        ->middleware('can:create purchase requests')
        ->name('purchase-requests.store');
    Route::get('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');
    Route::get('purchase-requests/{purchaseRequest}/edit', [PurchaseRequestController::class, 'edit'])
        ->middleware('can:create purchase requests')
        ->name('purchase-requests.edit');
    Route::put('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update'])
        ->middleware('can:create purchase requests')
        ->name('purchase-requests.update');
    // Backward compatibility: some clients submit POST to the update endpoint
    // Accept POST and route it to the same controller method to avoid 405 errors
    Route::post('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update'])
        ->middleware('can:create purchase requests')
        ->name('purchase-requests.update.post');
    Route::delete('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'destroy'])
        ->middleware('can:create purchase requests')
        ->name('purchase-requests.destroy');

    // Vendors
    Route::resource('vendors', VendorController::class);

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);

    // Locations CRUD
    Route::resource('locations', LocationController::class);

    // Vots CRUD
    Route::resource('vots', VotController::class);

    // File References CRUD
    Route::resource('file-references', FileReferenceController::class);

    // Type Procurements CRUD
    Route::resource('type-procurements', TypeProcurementController::class);

    // Item Units CRUD
    Route::resource('item-units', ItemUnitController::class);

    // Approvals Module (Managers)
    Route::get('approvals', [PurchaseRequestController::class, 'approvalsIndex'])
        ->middleware('can:view approvals')
        ->name('approvals.index');
    Route::get('approvals/{purchaseRequest}', [PurchaseRequestController::class, 'approvalsShow'])
        ->middleware('can:view approvals')
        ->name('approvals.show');
    Route::post('approvals/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])
        ->middleware('can:approve purchase requests')
        ->name('approvals.approve');
    Route::post('approvals/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])
        ->middleware('can:reject purchase requests')
        ->name('approvals.reject');
    // ... other resource routes (e.g., 'locations')

    // Delivery Orders Resource Route
    Route::resource('delivery-orders', DeliveryOrderController::class);

    // Custom route for Confirm Delivery (PATCH method)
    Route::patch('delivery-orders/{delivery_order}/confirm', [DeliveryOrderController::class, 'confirm'])
        ->middleware('can:confirm delivery orders')
        ->name('delivery-orders.confirm');

    // Custom route for Print Summary (GET method)
    Route::get('delivery-orders/{delivery_order}/print', [DeliveryOrderController::class, 'printSummary'])
        ->middleware('can:print delivery orders')
        ->name('delivery-orders.print');

    // Delivery Reports
    Route::get('delivery-reports', [DeliveryReportController::class, 'index'])
        ->middleware('can:view delivery reports')
        ->name('delivery-reports.index');

    // Tenders
    Route::resource('tenders', TenderController::class);
    Route::post('tenders/{tender}/award', [TenderController::class, 'award'])
        ->middleware('can:award tenders')
        ->name('tenders.award');

    // Tender Bids
    Route::resource('tender-bids', TenderBidController::class)->except(['create', 'edit']);
    Route::get('delivery-reports/export/pdf', [DeliveryReportController::class, 'exportPdf'])
        ->middleware('can:export delivery reports')
        ->name('delivery-reports.export-pdf');

    // User / Role / Permission Management (Settings) - restrict to managers of settings (typically Admin only)
    Route::resource('roles', RoleController::class)->middleware('can:manage roles');
    Route::resource('permissions', PermissionController::class)->except(['show'])->middleware('can:manage permissions');
    Route::resource('users', UserController::class)->only(['index','show','edit','update','destroy'])->middleware('can:manage users');
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore')->middleware('can:manage users');
});

require __DIR__.'/settings.php';
