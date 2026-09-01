<?php

use Illuminate\Support\Facades\Route;
use App\Domain\HRM\Controllers\AttendanceController;
use App\Domain\HRM\Controllers\DepartmentController;
use App\Domain\HRM\Controllers\DesignationController;
use App\Domain\HRM\Controllers\EmployeeController;
use App\Domain\HRM\Controllers\EmployeeDocumentController;
use App\Domain\HRM\Controllers\EmployeeSalaryComponentController;
use App\Domain\HRM\Controllers\FaceRecognitionController;
use App\Domain\HRM\Controllers\LeaveRequestController;
use App\Domain\HRM\Controllers\LeaveTypeController;
use App\Domain\HRM\Controllers\OfficeLocationController;
use App\Domain\HRM\Controllers\PayrollController;
use App\Domain\HRM\Controllers\PayrollPeriodController;
use App\Domain\HRM\Controllers\ReimbursementController;
use App\Domain\HRM\Controllers\ReportController;
use App\Domain\HRM\Controllers\ResignationController;
use App\Domain\HRM\Controllers\SalaryComponentController;
use App\Domain\HRM\Controllers\ShiftController;

/*
|--------------------------------------------------------------------------
| HRM Microservice Gateway Routes (App\Domain\HRM)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:platform')->prefix('platform/hrm')->group(function () {
    // Attendance API
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::post('/attendances/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendances/clock-out', [AttendanceController::class, 'clockOut']);

    // Face Recognition API
    Route::post('/employees/{employee}/enroll-face', [FaceRecognitionController::class, 'enrollFace']);
    Route::delete('/employees/{employee}/face', [FaceRecognitionController::class, 'removeFaceData']);

    // Office Location API
    Route::apiResource('office-locations', OfficeLocationController::class);

    // Shift API
    Route::apiResource('shifts', ShiftController::class);

    // Department & Designation API
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::put('/departments/{department}', [DepartmentController::class, 'update']);
    Route::get('/designations', [DesignationController::class, 'index']);
    Route::post('/designations', [DesignationController::class, 'store']);
    Route::put('/designations/{designation}', [DesignationController::class, 'update']);

    // Employee Management API
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::get('/employees/{employee}/face-status', [EmployeeController::class, 'getFaceStatus']);

    // Document Management API
    Route::get('/employees/{employeeUuid}/documents', [EmployeeDocumentController::class, 'index']);
    Route::post('/employees/{employeeUuid}/documents', [EmployeeDocumentController::class, 'store']);

    // Employee Salary Components API
    Route::get('/employees/{uuid}/salary-components', [EmployeeSalaryComponentController::class, 'index']);
    Route::post('/employees/{uuid}/salary-components', [EmployeeSalaryComponentController::class, 'store']);
    Route::put('/employees/{uuid}/salary-components/{componentUuid}', [EmployeeSalaryComponentController::class, 'update']);
    Route::delete('/employees/{uuid}/salary-components/{componentUuid}', [EmployeeSalaryComponentController::class, 'destroy']);

    // Leave Management API
    Route::get('/leave-types', [LeaveTypeController::class, 'index']);
    Route::post('/leave-types', [LeaveTypeController::class, 'store']);
    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::put('/leave-requests/{uuid}/status', [LeaveRequestController::class, 'updateStatus']);
    Route::get('/leave-balances/my-balance', [LeaveRequestController::class, 'myBalance']);

    // Payroll API
    Route::get('/salary-components', [SalaryComponentController::class, 'index']);
    Route::post('/salary-components', [SalaryComponentController::class, 'store']);
    Route::get('/payroll-periods', [PayrollPeriodController::class, 'index']);
    Route::post('/payroll-periods', [PayrollPeriodController::class, 'store']);
    Route::post('/payroll-periods/generate', [PayrollPeriodController::class, 'generate']);
    Route::get('/payrolls', [PayrollController::class, 'index']);
    Route::post('/payrolls/{uuid}/approve', [PayrollController::class, 'approve']);
    Route::post('/payrolls/batch-approve', [PayrollController::class, 'batchApprove']);
    Route::get('/payrolls/{payroll}/pdf', [PayrollController::class, 'payslip']);
    Route::post('/payrolls/batch-pay', [PayrollController::class, 'batchPay']);

    // Reimbursement API
    Route::get('/reimbursements', [ReimbursementController::class, 'index']);
    Route::post('/reimbursements', [ReimbursementController::class, 'store']);
    Route::get('/reimbursements/my-claims', [ReimbursementController::class, 'myClaims']);
    Route::put('/reimbursements/{reimbursement}/status', [ReimbursementController::class, 'updateStatus']);

    // Resignation API
    Route::get('/resignations', [ResignationController::class, 'index']);
    Route::post('/resignations', [ResignationController::class, 'store']);
    Route::put('/resignations/{resignation}/status', [ResignationController::class, 'updateStatus']);

    // HRM Reports API
    Route::get('/reports/turnover', [ReportController::class, 'turnover']);
    Route::get('/reports/labor-cost', [ReportController::class, 'laborCost']);
});
