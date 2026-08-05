<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Purchasing\Controllers\SupplierController;
use App\Domain\Purchasing\Controllers\PurchaseRequestController;
use App\Domain\Purchasing\Controllers\PurchaseOrderController;
use App\Domain\Purchasing\Controllers\GoodsReceiptController;
use App\Domain\Purchasing\Controllers\PurchaseInvoiceController;
use App\Domain\Purchasing\Controllers\PurchasingDashboardController;

/*
|--------------------------------------------------------------------------
| Purchasing Microservice Gateway Routes (App\Domain\Purchasing)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:platform')->prefix('platform/purchasing')->group(function () {
    // Dashboard
    Route::get('/dashboard', [PurchasingDashboardController::class, 'index']);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);

    // Purchase Requests
    Route::apiResource('requests', PurchaseRequestController::class)->only(['index', 'store', 'show']);
    Route::patch('/requests/{uuid}/status', [PurchaseRequestController::class, 'updateStatus']);

    // Purchase Orders
    Route::apiResource('orders', PurchaseOrderController::class)->only(['index', 'store', 'show']);
    Route::patch('/orders/{uuid}/status', [PurchaseOrderController::class, 'updateStatus']);

    // Goods Receipts
    Route::apiResource('goods-receipts', GoodsReceiptController::class)->only(['index', 'store', 'show']);

    // Purchase Invoices
    Route::apiResource('invoices', PurchaseInvoiceController::class)->only(['index', 'store', 'show']);
    Route::patch('/invoices/{uuid}/status', [PurchaseInvoiceController::class, 'updateStatus']);
});
