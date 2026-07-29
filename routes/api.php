<?php

use App\Domain\CRM\Controllers\AutomationSalesForce\QuotationController;
use App\Domain\CRM\Controllers\Dashboard\CrmDashboardController;
use App\Domain\CRM\Controllers\MasterData\CustomerDatabaseManagementController;
use App\Domain\CRM\Controllers\ProspectManagement\LeadTrackingController;
use App\Domain\CRM\Controllers\ProspectManagement\ProspectController;
use App\Domain\CRM\Controllers\ProspectManagement\SalesPipeLineController;
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
        Route::get('/customers/{uuid}/interactions', [CustomerDatabaseManagementController::class, 'interactions']);
        Route::get('/customers/{uuid}/orders', [CustomerDatabaseManagementController::class, 'orders']);

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
        Route::get('/departments', [\App\Domain\HRM\Controllers\DepartmentController::class, 'index']);
        Route::get('/departments/{uuid}', [\App\Domain\HRM\Controllers\DepartmentController::class, 'show']);
        Route::post('/departments', [\App\Domain\HRM\Controllers\DepartmentController::class, 'store']);
        Route::put('/departments/{uuid}', [\App\Domain\HRM\Controllers\DepartmentController::class, 'update']);
        Route::delete('/departments/{uuid}', [\App\Domain\HRM\Controllers\DepartmentController::class, 'destroy']);

        // Designations
        Route::get('/designations', [\App\Domain\HRM\Controllers\DesignationController::class, 'index']);
        Route::get('/designations/{uuid}', [\App\Domain\HRM\Controllers\DesignationController::class, 'show']);
        Route::post('/designations', [\App\Domain\HRM\Controllers\DesignationController::class, 'store']);
        Route::put('/designations/{uuid}', [\App\Domain\HRM\Controllers\DesignationController::class, 'update']);
        Route::delete('/designations/{uuid}', [\App\Domain\HRM\Controllers\DesignationController::class, 'destroy']);

        // Employees
        Route::get('/employees', [\App\Domain\HRM\Controllers\EmployeeController::class, 'index']);
        Route::get('/employees/{uuid}', [\App\Domain\HRM\Controllers\EmployeeController::class, 'show']);
        Route::post('/employees', [\App\Domain\HRM\Controllers\EmployeeController::class, 'store']);
        Route::put('/employees/{uuid}', [\App\Domain\HRM\Controllers\EmployeeController::class, 'update']);
        Route::delete('/employees/{uuid}', [\App\Domain\HRM\Controllers\EmployeeController::class, 'destroy']);

        // Employee Salary Components (per-employee assignment)
        Route::get('/employees/{uuid}/salary-components', [\App\Domain\HRM\Controllers\EmployeeSalaryComponentController::class, 'index']);
        Route::post('/employees/{uuid}/salary-components', [\App\Domain\HRM\Controllers\EmployeeSalaryComponentController::class, 'store']);
        Route::put('/employees/{uuid}/salary-components/{componentUuid}', [\App\Domain\HRM\Controllers\EmployeeSalaryComponentController::class, 'update']);
        Route::delete('/employees/{uuid}/salary-components/{componentUuid}', [\App\Domain\HRM\Controllers\EmployeeSalaryComponentController::class, 'destroy']);

        // Employee Documents
        Route::get('/employees/{employeeUuid}/documents', [\App\Domain\HRM\Controllers\EmployeeDocumentController::class, 'index']);
        Route::post('/employees/{employeeUuid}/documents', [\App\Domain\HRM\Controllers\EmployeeDocumentController::class, 'store']);
        Route::delete('/documents/{uuid}', [\App\Domain\HRM\Controllers\EmployeeDocumentController::class, 'destroy']);

        // Shifts
        Route::get('/shifts', [\App\Domain\HRM\Controllers\ShiftController::class, 'index']);
        Route::post('/shifts', [\App\Domain\HRM\Controllers\ShiftController::class, 'store']);
        Route::get('/shifts/{uuid}', [\App\Domain\HRM\Controllers\ShiftController::class, 'show']);
        Route::put('/shifts/{uuid}', [\App\Domain\HRM\Controllers\ShiftController::class, 'update']);
        Route::delete('/shifts/{uuid}', [\App\Domain\HRM\Controllers\ShiftController::class, 'destroy']);

        // Attendance
        Route::get('/attendances', [\App\Domain\HRM\Controllers\AttendanceController::class, 'index']);
        Route::post('/attendances/clock-in', [\App\Domain\HRM\Controllers\AttendanceController::class, 'clockIn']);
        Route::post('/attendances/clock-out', [\App\Domain\HRM\Controllers\AttendanceController::class, 'clockOut']);

        // Leave Management
        Route::get('/leave-types', [\App\Domain\HRM\Controllers\LeaveTypeController::class, 'index']);
        Route::post('/leave-types', [\App\Domain\HRM\Controllers\LeaveTypeController::class, 'store']); // Admin only ideally
    
        Route::get('/leave-requests', [\App\Domain\HRM\Controllers\LeaveRequestController::class, 'index']);
        Route::post('/leave-requests', [\App\Domain\HRM\Controllers\LeaveRequestController::class, 'store']);
        Route::put('/leave-requests/{uuid}/status', [\App\Domain\HRM\Controllers\LeaveRequestController::class, 'updateStatus']); // Manager only ideally
        Route::get('/leave-balances/my-balance', [\App\Domain\HRM\Controllers\LeaveRequestController::class, 'myBalance']);

        // Payroll
        Route::get('/salary-components', [\App\Domain\HRM\Controllers\SalaryComponentController::class, 'index']);
        Route::post('/salary-components', [\App\Domain\HRM\Controllers\SalaryComponentController::class, 'store']);

        Route::get('/payroll-periods', [\App\Domain\HRM\Controllers\PayrollPeriodController::class, 'index']);
        Route::post('/payroll-periods', [\App\Domain\HRM\Controllers\PayrollPeriodController::class, 'store']);
        Route::post('/payroll-periods/generate', [\App\Domain\HRM\Controllers\PayrollPeriodController::class, 'generate']);

        Route::get('/payrolls', [\App\Domain\HRM\Controllers\PayrollController::class, 'index']);
        Route::get('/payrolls/{uuid}', [\App\Domain\HRM\Controllers\PayrollController::class, 'show']);
        Route::get('/payrolls/{uuid}/payslip', [\App\Domain\HRM\Controllers\PayrollController::class, 'payslip']);
        Route::post('/payrolls/batch-pay', [\App\Domain\HRM\Controllers\PayrollController::class, 'batchPay']);
        Route::post('/payrolls/{uuid}/pay', [\App\Domain\HRM\Controllers\PayrollController::class, 'pay']);

        // Reimbursement
        Route::get('/reimbursements/my-claims', [\App\Domain\HRM\Controllers\ReimbursementController::class, 'myClaims']);
        Route::get('/reimbursements', [\App\Domain\HRM\Controllers\ReimbursementController::class, 'index']);
        Route::post('/reimbursements', [\App\Domain\HRM\Controllers\ReimbursementController::class, 'store']);
        Route::get('/reimbursements/{uuid}', [\App\Domain\HRM\Controllers\ReimbursementController::class, 'show']);
        Route::put('/reimbursements/{uuid}/status', [\App\Domain\HRM\Controllers\ReimbursementController::class, 'updateStatus']);

        // Resignation
        Route::get('/resignations', [\App\Domain\HRM\Controllers\ResignationController::class, 'index']);
        Route::post('/resignations', [\App\Domain\HRM\Controllers\ResignationController::class, 'store']);
        Route::get('/resignations/{uuid}', [\App\Domain\HRM\Controllers\ResignationController::class, 'show']);
        Route::put('/resignations/{uuid}/status', [\App\Domain\HRM\Controllers\ResignationController::class, 'updateStatus']);
        // Reports
        Route::get('/reports/turnover', [\App\Domain\HRM\Controllers\ReportController::class, 'turnover']);
        Route::get('/reports/labor-cost', [\App\Domain\HRM\Controllers\ReportController::class, 'laborCost']);

        // Office Locations
        Route::resource('office-locations', \App\Domain\HRM\Controllers\OfficeLocationController::class)->except(['create', 'edit'])->parameters(['office-locations' => 'uuid']);

        // Employee Face Recognition
        Route::get('/employees/{uuid}/face-status', [\App\Domain\HRM\Controllers\EmployeeController::class, 'getFaceStatus']);
        Route::post('/employees/{uuid}/enroll-face', [\App\Domain\HRM\Controllers\EmployeeController::class, 'enrollFace']);
        Route::delete('/employees/{uuid}/face-data', [\App\Domain\HRM\Controllers\EmployeeController::class, 'removeFace']);
    });

Route::prefix('platform/finance')
    ->middleware('auth:sanctum')
    ->group(function () {
        // Core Ledger
        Route::get('/ledger/accounts', [\App\Domain\Finance\Controllers\GeneralLedgerController::class, 'accounts']);
        Route::get('/ledger/items', [\App\Domain\Finance\Controllers\GeneralLedgerController::class, 'items']);
        
        // Financial Reporting
        Route::get('/reporting/profit-loss', [\App\Domain\Finance\Controllers\ReportingController::class, 'profitAndLoss']);
        Route::get('/reporting/balance-sheet', [\App\Domain\Finance\Controllers\ReportingController::class, 'balanceSheet']);
        Route::get('/reporting/cash-flow', [\App\Domain\Finance\Controllers\ReportingController::class, 'cashFlow']);
        
        // AI Analytics
        Route::get('/ai/budget-variance/{account_uuid}', [\App\Domain\Finance\Controllers\AIAnalyticsController::class, 'budgetVariance']);
        Route::post('/ai/suggest-account', [\App\Domain\Finance\Controllers\AIAnalyticsController::class, 'suggestAccount']);

        // FP&A
        Route::get('/fpa/revenue-analysis', [\App\Domain\Finance\Controllers\FPAnalysisController::class, 'revenueAnalysis']);

        // Forecasting
        Route::get('/forecasting/cash-forecast', [\App\Domain\Finance\Controllers\ForecastingController::class, 'cashForecast']);

        // Supply Chain AI
        Route::get('/supply-chain/risk-assessment', [\App\Domain\Finance\Controllers\SupplyChainAIController::class, 'riskAssessment']);
        
        // Legacy / Original
        Route::get('/dashboard', [\App\Domain\Finance\Controllers\FinanceDashboardController::class, 'index']);
    });

// Purchasing routes are now in routes/api/purchasing.php (loaded via bootstrap/app.php)
// Project routes are now in routes/api/project.php (loaded via bootstrap/app.php)

