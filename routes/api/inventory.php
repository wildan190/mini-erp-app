<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Inventory\Controllers\InventoryDashboardController;
use App\Domain\Inventory\Controllers\InventoryProductController;
use App\Domain\Inventory\Controllers\WarehouseController;
use App\Domain\Inventory\Controllers\StockMovementController;
use App\Domain\Inventory\Controllers\TransferOrderController;

/*
|--------------------------------------------------------------------------
| Inventory Microservice Routes  —  routes/api/inventory.php
|--------------------------------------------------------------------------
*/

Route::middleware('auth:platform')->prefix('platform/inventory')->group(function () {

    // Dashboard overview
    Route::get('/dashboard', [InventoryDashboardController::class, 'index']);

    // Warehouses API
    Route::get('/warehouses',         [WarehouseController::class, 'index']);
    Route::post('/warehouses',        [WarehouseController::class, 'store']);
    Route::get('/warehouses/{uuid}',  [WarehouseController::class, 'show']);
    Route::put('/warehouses/{uuid}',  [WarehouseController::class, 'update']);

    // Product Catalog (SKUs)
    Route::get('/categories',        [InventoryProductController::class, 'categories']);
    Route::get('/products',          [InventoryProductController::class, 'index']);
    Route::post('/products',         [InventoryProductController::class, 'store']);
    Route::get('/products/{uuid}',   [InventoryProductController::class, 'show']);
    Route::put('/products/{uuid}',   [InventoryProductController::class, 'update']);

    // Stock Movements & Ledger Audit Log
    Route::get('/movements',         [StockMovementController::class, 'index']);
    Route::post('/movements',        [StockMovementController::class, 'store']);

    // Inter-Warehouse Transfer Orders
    Route::get('/transfers',                [TransferOrderController::class, 'index']);
    Route::post('/transfers',               [TransferOrderController::class, 'store']);
    Route::get('/transfers/{uuid}',         [TransferOrderController::class, 'show']);
    Route::put('/transfers/{uuid}/status',  [TransferOrderController::class, 'updateStatus']);
});
