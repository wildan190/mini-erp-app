<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Finance\Controllers\GeneralLedgerController;
use App\Domain\Finance\Controllers\ReportingController;
use App\Domain\Finance\Controllers\AIAnalyticsController;
use App\Domain\Finance\Controllers\AccountPayableController;

/*
|--------------------------------------------------------------------------
| Finance Microservice Gateway Routes (App\Domain\Finance)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:platform')->prefix('platform/finance')->group(function () {
    // Dashboard & Approvals
    Route::get('/dashboard', [\App\Domain\Finance\Controllers\FinanceDashboardController::class, 'index']);
    Route::post('/records/{uuid}/approve', [\App\Domain\Finance\Controllers\FinanceDashboardController::class, 'approveRecord']);
    Route::post('/records/{uuid}/reject', [\App\Domain\Finance\Controllers\FinanceDashboardController::class, 'rejectRecord']);

    // Core Ledger
    Route::get('/ledger/accounts', [GeneralLedgerController::class, 'accounts']);
    Route::post('/ledger/accounts', [GeneralLedgerController::class, 'store']);
    Route::put('/ledger/accounts/{uuid}', [GeneralLedgerController::class, 'update']);
    Route::delete('/ledger/accounts/{uuid}', [GeneralLedgerController::class, 'destroy']);
    Route::get('/ledger/items', [GeneralLedgerController::class, 'items']);
    
    // Financial Reporting
    Route::get('/reporting/profit-loss', [ReportingController::class, 'profitAndLoss']);
    Route::get('/reporting/balance-sheet', [ReportingController::class, 'balanceSheet']);
    Route::get('/reporting/cash-flow', [ReportingController::class, 'cashFlow']);
    
    // AI Analytics
    Route::get('/ai/budget-variance/{account_uuid}', [AIAnalyticsController::class, 'budgetVariance']);
    Route::post('/ai/suggest-account', [AIAnalyticsController::class, 'suggestAccount']);

    // FP&A
    Route::get('/fpa/revenue-analysis', [\App\Domain\Finance\Controllers\FPAnalysisController::class, 'revenueAnalysis']);

    // Forecasting
    Route::get('/forecasting/cash-forecast', [\App\Domain\Finance\Controllers\ForecastingController::class, 'cashForecast']);

    // Supply Chain AI
    Route::get('/supply-chain/risk-assessment', [\App\Domain\Finance\Controllers\SupplyChainAIController::class, 'riskAssessment']);

    // ── Account Payable ─────────────────────────────────────────────────────
    Route::get('/ap/dashboard', [AccountPayableController::class, 'dashboard']);
    Route::get('/ap/iris/balance', [AccountPayableController::class, 'irisBalance']);

    // Vendors
    Route::get('/ap/vendors', [AccountPayableController::class, 'indexVendors']);
    Route::post('/ap/vendors', [AccountPayableController::class, 'storeVendor']);

    // Bills
    Route::get('/ap/bills', [AccountPayableController::class, 'indexBills']);
    Route::post('/ap/bills', [AccountPayableController::class, 'storeBill']);
    Route::get('/ap/bills/{uuid}', [AccountPayableController::class, 'showBill']);
    Route::post('/ap/bills/{uuid}/approve', [AccountPayableController::class, 'approveBill']);
    Route::post('/ap/bills/{uuid}/pay', [AccountPayableController::class, 'payBill']);

    // Payments
    Route::post('/ap/payments/{paymentUuid}/reconcile', [AccountPayableController::class, 'reconcilePayment']);

    // ── Finance Settings ─────────────────────────────────────────────────────
    Route::get('/settings/midtrans', [\App\Domain\Finance\Controllers\FinanceSettingsController::class, 'getMidtransSettings']);
    Route::post('/settings/midtrans', [\App\Domain\Finance\Controllers\FinanceSettingsController::class, 'saveMidtransSettings']);
    Route::post('/settings/midtrans/test', [\App\Domain\Finance\Controllers\FinanceSettingsController::class, 'testMidtransConnection']);
});
