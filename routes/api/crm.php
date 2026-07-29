<?php

use Illuminate\Support\Facades\Route;
use App\Domain\CRM\Controllers\CustomerEnterpriseController;
use App\Domain\CRM\Controllers\LeadConversionController;
use App\Domain\CRM\Controllers\AutomationSalesForce\QuotationController;
use App\Domain\CRM\Controllers\ProspectManagement\SalesPipeLineController;

/*
|--------------------------------------------------------------------------
| CRM Microservice Gateway Routes (App\Domain\CRM)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('platform/crm')->group(function () {
    Route::post('/customers/enterprise', [CustomerEnterpriseController::class, 'store']);
    Route::post('/leads/convert-to-prospect', [LeadConversionController::class, 'convertToProspect']);
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::post('/sales-pipeline', [SalesPipeLineController::class, 'store']);
    Route::get('/sales-pipeline/{uuid}', [SalesPipeLineController::class, 'show']);
});
