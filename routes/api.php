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

Route::prefix('platform/hrm')
    ->middleware('auth:sanctum')
    ->group(function () {
        // Departments
        Route::get('/departments', [\App\Http\Controllers\Platform\Api\HRM\DepartmentController::class, 'index']);
        Route::get('/departments/{id}', [\App\Http\Controllers\Platform\Api\HRM\DepartmentController::class, 'show']);
        Route::post('/departments', [\App\Http\Controllers\Platform\Api\HRM\DepartmentController::class, 'store']);
        Route::put('/departments/{id}', [\App\Http\Controllers\Platform\Api\HRM\DepartmentController::class, 'update']);
        Route::delete('/departments/{id}', [\App\Http\Controllers\Platform\Api\HRM\DepartmentController::class, 'destroy']);

        // Designations
        Route::get('/designations', [\App\Http\Controllers\Platform\Api\HRM\DesignationController::class, 'index']);
        Route::get('/designations/{id}', [\App\Http\Controllers\Platform\Api\HRM\DesignationController::class, 'show']);
        Route::post('/designations', [\App\Http\Controllers\Platform\Api\HRM\DesignationController::class, 'store']);
        Route::put('/designations/{id}', [\App\Http\Controllers\Platform\Api\HRM\DesignationController::class, 'update']);
        Route::delete('/designations/{id}', [\App\Http\Controllers\Platform\Api\HRM\DesignationController::class, 'destroy']);

        // Employees
        Route::get('/employees', [\App\Http\Controllers\Platform\Api\HRM\EmployeeController::class, 'index']);
        Route::get('/employees/{id}', [\App\Http\Controllers\Platform\Api\HRM\EmployeeController::class, 'show']);
        Route::post('/employees', [\App\Http\Controllers\Platform\Api\HRM\EmployeeController::class, 'store']);
        Route::put('/employees/{id}', [\App\Http\Controllers\Platform\Api\HRM\EmployeeController::class, 'update']);
        Route::delete('/employees/{id}', [\App\Http\Controllers\Platform\Api\HRM\EmployeeController::class, 'destroy']);

        // Employee Documents
        Route::get('/employees/{employeeId}/documents', [\App\Http\Controllers\Platform\Api\HRM\EmployeeDocumentController::class, 'index']);
        Route::post('/employees/{employeeId}/documents', [\App\Http\Controllers\Platform\Api\HRM\EmployeeDocumentController::class, 'store']);
        Route::delete('/documents/{id}', [\App\Http\Controllers\Platform\Api\HRM\EmployeeDocumentController::class, 'destroy']);

        // Shifts
        Route::get('/shifts', [\App\Http\Controllers\Platform\Api\HRM\ShiftController::class, 'index']);
        Route::post('/shifts', [\App\Http\Controllers\Platform\Api\HRM\ShiftController::class, 'store']);
        Route::get('/shifts/{id}', [\App\Http\Controllers\Platform\Api\HRM\ShiftController::class, 'show']);
        Route::put('/shifts/{id}', [\App\Http\Controllers\Platform\Api\HRM\ShiftController::class, 'update']);
        Route::delete('/shifts/{id}', [\App\Http\Controllers\Platform\Api\HRM\ShiftController::class, 'destroy']);

        // Attendance
        Route::get('/attendances', [\App\Http\Controllers\Platform\Api\HRM\AttendanceController::class, 'index']);
        Route::post('/attendances/clock-in', [\App\Http\Controllers\Platform\Api\HRM\AttendanceController::class, 'clockIn']);
        Route::post('/attendances/clock-out', [\App\Http\Controllers\Platform\Api\HRM\AttendanceController::class, 'clockOut']);

        // Leave Management
        Route::get('/leave-types', [\App\Http\Controllers\Platform\Api\HRM\LeaveTypeController::class, 'index']);
        Route::post('/leave-types', [\App\Http\Controllers\Platform\Api\HRM\LeaveTypeController::class, 'store']); // Admin only ideally
    
        Route::get('/leave-requests', [\App\Http\Controllers\Platform\Api\HRM\LeaveRequestController::class, 'index']);
        Route::post('/leave-requests', [\App\Http\Controllers\Platform\Api\HRM\LeaveRequestController::class, 'store']);
        Route::put('/leave-requests/{id}/status', [\App\Http\Controllers\Platform\Api\HRM\LeaveRequestController::class, 'updateStatus']); // Manager only ideally
        Route::get('/leave-balances/my-balance', [\App\Http\Controllers\Platform\Api\HRM\LeaveRequestController::class, 'myBalance']);
    });

