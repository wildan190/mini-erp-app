<?php

use App\Http\Controllers\Platform\Api\CRM\AutomationSalesForce\QuotationController;
use App\Http\Controllers\Platform\Api\CRM\Dashboard\CrmDashboardController;
use App\Http\Controllers\Platform\Api\CRM\MasterData\CustomerDatabaseManagementController;
use App\Http\Controllers\Platform\Api\CRM\ProspectManagement\LeadTrackingController;
use App\Http\Controllers\Platform\Api\CRM\ProspectManagement\ProspectController;
use App\Http\Controllers\Platform\Api\CRM\ProspectManagement\SalesPipeLineController;
use App\Http\Controllers\Platform\Api\Dashboard\PlatformDashboardController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Platform\Api\Auth\{
    PlatformLoginController,
    PlatformRegisterController,
    PlatformLogoutController
};

Route::prefix('platform')->group(function () {

    Route::post('/login', [PlatformLoginController::class, 'login']);
    Route::post('/register', [PlatformRegisterController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [PlatformLogoutController::class, 'logout']);
        Route::get('/dashboard', [PlatformDashboardController::class, 'index']);
    });
});

Route::prefix('platform/crm')
    ->middleware('auth:sanctum')
    ->group(function () {

        Route::get('/dashboard', [CrmDashboardController::class, 'index']);

        // Automation Sales Force
        Route::get('/quotation', [QuotationController::class, 'index']);
        Route::get('/quotation/{uuid}', [QuotationController::class, 'show']);
        Route::post('/quotation', [QuotationController::class, 'store']);
        Route::put('/quotation/{uuid}', [QuotationController::class, 'update']);
        Route::delete('/quotation/{uuid}', [QuotationController::class, 'destroy']);

        // Master Data
        Route::get('/customers', [CustomerDatabaseManagementController::class, 'index']);
        Route::get('/customers/{uuid}', [CustomerDatabaseManagementController::class, 'show']);
        Route::post('/customers', [CustomerDatabaseManagementController::class, 'store']);
        Route::put('/customers/{uuid}', [CustomerDatabaseManagementController::class, 'update']);
        Route::delete('/customers/{uuid}', [CustomerDatabaseManagementController::class, 'destroy']);

        // Prospect Management
        Route::get('/leads', [LeadTrackingController::class, 'index']);
        Route::get('/leads/{uuid}', [LeadTrackingController::class, 'show']);
        Route::post('/leads', [LeadTrackingController::class, 'store']);
        Route::put('/leads/{uuid}', [LeadTrackingController::class, 'update']);
        Route::delete('/leads/{uuid}', [LeadTrackingController::class, 'destroy']);
        Route::post('/leads/{uuid}/convert', [LeadTrackingController::class, 'convert']);

        Route::get('/prospects', [ProspectController::class, 'index']);
        Route::get('/prospects/{uuid}', [ProspectController::class, 'show']);
        Route::post('/prospects', [ProspectController::class, 'store']);
        Route::put('/prospects/{uuid}', [ProspectController::class, 'update']);
        Route::delete('/prospects/{uuid}', [ProspectController::class, 'destroy']);
        Route::put('/prospects/{uuid}/status', [ProspectController::class, 'updateStatus']);

        Route::get('/sales-pipeline', [SalesPipeLineController::class, 'index']);
        Route::get('/sales-pipeline/{uuid}', [SalesPipeLineController::class, 'show']);
        Route::post('/sales-pipeline', [SalesPipeLineController::class, 'store']);
        Route::delete('/sales-pipeline/{uuid}', [SalesPipeLineController::class, 'destroy']);
    });

