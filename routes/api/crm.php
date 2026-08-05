<?php

use Illuminate\Support\Facades\Route;
use App\Domain\CRM\Controllers\AutomationSalesForce\QuotationController;
use App\Domain\CRM\Controllers\CustomerEnterpriseController;
use App\Domain\CRM\Controllers\Dashboard\CrmDashboardController;
use App\Domain\CRM\Controllers\LeadConversionController;
use App\Domain\CRM\Controllers\MasterData\CustomerDatabaseManagementController;
use App\Domain\CRM\Controllers\ProspectManagement\LeadTrackingController;
use App\Domain\CRM\Controllers\ProspectManagement\ProspectController;
use App\Domain\CRM\Controllers\ProspectManagement\SalesPipeLineController;

/*
|--------------------------------------------------------------------------
| CRM Service Routes  —  routes/api/crm.php
|--------------------------------------------------------------------------
| Served by crm-service container.
| All routes are prefixed /api/platform/crm via bootstrap/app.php.
*/

Route::middleware('auth:platform')
    ->prefix('platform/crm')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [CrmDashboardController::class, 'index']);

        // Automation Sales Force
        Route::get('/quotation',          [QuotationController::class, 'index']);
        Route::get('/quotation/{uuid}',   [QuotationController::class, 'show']);
        Route::post('/quotation',         [QuotationController::class, 'store']);
        Route::post('/quotations',        [QuotationController::class, 'store']);   // alias
        Route::put('/quotation/{uuid}',   [QuotationController::class, 'update']);
        Route::delete('/quotation/{uuid}',[QuotationController::class, 'destroy']);

        // Master Data — Customers
        Route::get('/customers',                          [CustomerDatabaseManagementController::class, 'index']);
        Route::get('/customers/{uuid}',                   [CustomerDatabaseManagementController::class, 'show']);
        Route::post('/customers',                         [CustomerDatabaseManagementController::class, 'store']);
        Route::put('/customers/{uuid}',                   [CustomerDatabaseManagementController::class, 'update']);
        Route::delete('/customers/{uuid}',                [CustomerDatabaseManagementController::class, 'destroy']);
        Route::get('/customers/{uuid}/interactions',      [CustomerDatabaseManagementController::class, 'interactions']);
        Route::get('/customers/{uuid}/orders',            [CustomerDatabaseManagementController::class, 'orders']);
        Route::post('/customers/enterprise',              [CustomerEnterpriseController::class, 'store']);

        // Prospect Management — Leads
        Route::get('/leads',              [LeadTrackingController::class, 'index']);
        Route::get('/leads/{uuid}',       [LeadTrackingController::class, 'show']);
        Route::post('/leads',             [LeadTrackingController::class, 'store']);
        Route::put('/leads/{uuid}',       [LeadTrackingController::class, 'update']);
        Route::delete('/leads/{uuid}',    [LeadTrackingController::class, 'destroy']);
        Route::post('/leads/{uuid}/convert', [LeadTrackingController::class, 'convert']);
        Route::post('/leads/convert-to-prospect', [LeadConversionController::class, 'convertToProspect']);

        // Prospect Management — Prospects
        Route::get('/prospects',              [ProspectController::class, 'index']);
        Route::get('/prospects/{uuid}',       [ProspectController::class, 'show']);
        Route::post('/prospects',             [ProspectController::class, 'store']);
        Route::put('/prospects/{uuid}',       [ProspectController::class, 'update']);
        Route::delete('/prospects/{uuid}',    [ProspectController::class, 'destroy']);
        Route::put('/prospects/{uuid}/status',[ProspectController::class, 'updateStatus']);

        // Sales Pipeline
        Route::get('/sales-pipeline',         [SalesPipeLineController::class, 'index']);
        Route::get('/sales-pipeline/{uuid}',  [SalesPipeLineController::class, 'show']);
        Route::post('/sales-pipeline',        [SalesPipeLineController::class, 'store']);
        Route::delete('/sales-pipeline/{uuid}',[SalesPipeLineController::class, 'destroy']);
    });
