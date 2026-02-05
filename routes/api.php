<?php

use App\Http\Controllers\Platform\Api\CRM\AutomationSalesForce\QuotationController;
use App\Http\Controllers\Platform\Api\CRM\Dashboard\CrmDashboardController;
use App\Http\Controllers\Platform\Api\CRM\MasterData\CustomerDatabaseManagementController;
use App\Http\Controllers\Platform\Api\CRM\ProspectManagement\LeadTrackingController;
use App\Http\Controllers\Platform\Api\CRM\ProspectManagement\ProspectController;
use App\Http\Controllers\Platform\Api\CRM\ProspectManagement\ProspectStatusController;
use App\Http\Controllers\Platform\Api\CRM\ProspectManagement\SalesPipeLineController;
use App\Http\Controllers\Platform\Api\Dashboard\PlatformDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Platform\Api\Auth\{
    PlatformLoginController,
    PlatformRegisterController,
    PlatformLogoutController
};
use App\Http\Controllers\Platform\Api\Dashboard\PlatformDashboard;

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
        Route::post('/quotation', [QuotationController::class, 'store']);

        // Master Data
        Route::get('/customers', [CustomerDatabaseManagementController::class, 'index']);
        Route::post('/customers', [CustomerDatabaseManagementController::class, 'store']);

        // Prospect Management
        Route::post('/leads', [LeadTrackingController::class, 'store']);
        Route::post('/prospects', [ProspectController::class, 'store']);
        Route::put('/prospects/{id}/status', [ProspectStatusController::class, 'update']);

        Route::get('/sales-pipeline', [SalesPipeLineController::class, 'index']);
        Route::post('/sales-pipeline', [SalesPipeLineController::class, 'store']);
    });

