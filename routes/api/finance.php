<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Finance\Controllers\GeneralLedgerController;
use App\Domain\Finance\Controllers\ReportingController;
use App\Domain\Finance\Controllers\AIAnalyticsController;

/*
|--------------------------------------------------------------------------
| Finance Microservice Gateway Routes (App\Domain\Finance)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('platform/finance')->group(function () {
    // Core Ledger
    Route::get('/ledger/accounts', [GeneralLedgerController::class, 'accounts']);
    Route::get('/ledger/items', [GeneralLedgerController::class, 'items']);
    
    // Financial Reporting
    Route::get('/reporting/profit-loss', [ReportingController::class, 'profitAndLoss']);
    Route::get('/reporting/balance-sheet', [ReportingController::class, 'balanceSheet']);
    Route::get('/reporting/cash-flow', [ReportingController::class, 'cashFlow']);
    
    // AI Analytics
    Route::get('/ai/budget-variance/{account_uuid}', [AIAnalyticsController::class, 'budgetVariance']);
    Route::post('/ai/suggest-account', [AIAnalyticsController::class, 'suggestAccount']);
});
