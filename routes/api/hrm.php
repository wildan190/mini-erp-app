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
    // ── Employee Self-Service (ESS Profile & Face Enrollment for all authenticated employees) ──
    Route::get('/employees/me', [EmployeeController::class, 'me']);
    Route::post('/employees/me/enroll-face', [EmployeeController::class, 'enrollMyFace']);

    // ── Attendance (ESS — all employees) ──────────────────────────────────
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::post('/attendances/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendances/clock-out', [AttendanceController::class, 'clockOut']);

    // ── Face Recognition (hrm.employees.manage) ───────────────────────────
    Route::middleware('permission:hrm.employees.manage')->group(function () {
        Route::post('/employees/{employee}/enroll-face', [FaceRecognitionController::class, 'enrollFace']);
        Route::delete('/employees/{employee}/face', [FaceRecognitionController::class, 'removeFaceData']);
    });

    // ── Office Locations (hrm.locations.manage) ───────────────────────────
    // GET is allowed for all authenticated users to populate attendance dropdowns; write actions are protected.
    Route::get('/office-locations', [OfficeLocationController::class, 'index']);
    Route::get('/office-locations/{office_location}', [OfficeLocationController::class, 'show']);
    Route::middleware('permission:hrm.locations.manage')->group(function () {
        Route::post('/office-locations', [OfficeLocationController::class, 'store']);
        Route::put('/office-locations/{office_location}', [OfficeLocationController::class, 'update']);
        Route::patch('/office-locations/{office_location}', [OfficeLocationController::class, 'update']);
        Route::delete('/office-locations/{office_location}', [OfficeLocationController::class, 'destroy']);
    });

    // ── Shifts (hrm.shifts.manage) ────────────────────────────────────────
    // GET is allowed for all authenticated users to view assigned shift info; write actions are protected.
    Route::get('/shifts', [ShiftController::class, 'index']);
    Route::get('/shifts/{shift}', [ShiftController::class, 'show']);
    Route::middleware('permission:hrm.shifts.manage')->group(function () {
        Route::post('/shifts', [ShiftController::class, 'store']);
        Route::put('/shifts/{shift}', [ShiftController::class, 'update']);
        Route::patch('/shifts/{shift}', [ShiftController::class, 'update']);
        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy']);
    });

    // ── Departments (hrm.departments.manage) ──────────────────────────────
    // GET is allowed for all to populate dropdowns; write actions are protected.
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::middleware('permission:hrm.departments.manage')->group(function () {
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{department}', [DepartmentController::class, 'update']);
    });

    // ── Designations (hrm.designations.manage) ────────────────────────────
    Route::get('/designations', [DesignationController::class, 'index']);
    Route::middleware('permission:hrm.designations.manage')->group(function () {
        Route::post('/designations', [DesignationController::class, 'store']);
        Route::put('/designations/{designation}', [DesignationController::class, 'update']);
    });

    // ── Employees (hrm.employees.view | hrm.employees.manage) ────────────
    Route::middleware('permission:hrm.employees.view|hrm.employees.manage')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
        Route::get('/employees/{employee}/face-status', [EmployeeController::class, 'getFaceStatus']);
    });
    Route::middleware('permission:hrm.employees.manage')->group(function () {
        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
    });

    // ── Employee Documents (hrm.employees.manage) ─────────────────────────
    Route::middleware('permission:hrm.employees.manage')->group(function () {
        Route::get('/employees/{employeeUuid}/documents', [EmployeeDocumentController::class, 'index']);
        Route::post('/employees/{employeeUuid}/documents', [EmployeeDocumentController::class, 'store']);
    });

    // ── Salary Components (hrm.payroll.manage) ────────────────────────────
    Route::middleware('permission:hrm.payroll.manage')->group(function () {
        Route::get('/employees/{uuid}/salary-components', [EmployeeSalaryComponentController::class, 'index']);
        Route::post('/employees/{uuid}/salary-components', [EmployeeSalaryComponentController::class, 'store']);
        Route::put('/employees/{uuid}/salary-components/{componentUuid}', [EmployeeSalaryComponentController::class, 'update']);
        Route::delete('/employees/{uuid}/salary-components/{componentUuid}', [EmployeeSalaryComponentController::class, 'destroy']);
    });

    // ── Leave (ESS submit; hrm.leave.approve for status update) ──────────
    Route::get('/leave-types', [LeaveTypeController::class, 'index']);
    Route::post('/leave-types', [LeaveTypeController::class, 'store']);
    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::get('/leave-balances/my-balance', [LeaveRequestController::class, 'myBalance']);
    Route::middleware('permission:hrm.leave.approve')->group(function () {
        Route::put('/leave-requests/{uuid}/status', [LeaveRequestController::class, 'updateStatus']);
    });

    // ── Payroll (hrm.payroll.manage) ──────────────────────────────────────
    Route::middleware('permission:hrm.payroll.manage')->group(function () {
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
    });

    // ── Reimbursement (ESS submit; hrm.reimbursement.approve for status) ─
    Route::get('/reimbursements', [ReimbursementController::class, 'index']);
    Route::post('/reimbursements', [ReimbursementController::class, 'store']);
    Route::get('/reimbursements/my-claims', [ReimbursementController::class, 'myClaims']);
    Route::middleware('permission:hrm.reimbursement.approve')->group(function () {
        Route::put('/reimbursements/{reimbursement}/status', [ReimbursementController::class, 'updateStatus']);
    });

    // ── Resignations (ESS submit; hrm.resignation.approve for status) ────
    Route::get('/resignations', [ResignationController::class, 'index']);
    Route::post('/resignations', [ResignationController::class, 'store']);
    Route::middleware('permission:hrm.resignation.approve')->group(function () {
        Route::put('/resignations/{resignation}/status', [ResignationController::class, 'updateStatus']);
    });

    // ── HRM Reports (hrm.employees.view | hrm.employees.manage) ──────────
    Route::middleware('permission:hrm.employees.view|hrm.employees.manage')->group(function () {
        Route::get('/reports/turnover', [ReportController::class, 'turnover']);
        Route::get('/reports/labor-cost', [ReportController::class, 'laborCost']);
        Route::get('/reports/kpi', [ReportController::class, 'employeeKpi']);
    });

    // ── Talent Acquisition (hrm.recruitment.manage) ───────────────────────
    Route::middleware('permission:hrm.recruitment.manage')->group(function () {
        Route::get('/recruitment/jobs', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'indexJobPosts']);
        Route::post('/recruitment/jobs', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'storeJobPost']);
        Route::put('/recruitment/jobs/{uuid}', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'updateJobPost']);
        Route::delete('/recruitment/jobs/{uuid}', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'destroyJobPost']);

        Route::get('/recruitment/applicants', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'indexApplicants']);
        Route::post('/recruitment/applicants', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'storeApplicant']);
        Route::put('/recruitment/applicants/{uuid}/stage', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'updateApplicantStage']);
        Route::delete('/recruitment/applicants/{uuid}', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'destroyApplicant']);

        Route::get('/recruitment/interviewers', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'indexInterviewers']);
        Route::get('/recruitment/interviews', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'indexInterviews']);
        Route::post('/recruitment/interviews', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'storeInterview']);
        Route::put('/recruitment/interviews/{uuid}', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'updateInterview']);
        Route::delete('/recruitment/interviews/{uuid}', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'destroyInterview']);
        Route::post('/recruitment/interviews/{uuid}/evaluations', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'storeEvaluation']);

        Route::get('/recruitment/offerings', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'indexOfferingLetters']);
        Route::post('/recruitment/offerings', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'storeOfferingLetter']);
        Route::put('/recruitment/offerings/{uuid}/status', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'updateOfferingLetterStatus']);

        Route::post('/recruitment/applicants/{uuid}/convert-employee', [\App\Domain\HRM\Controllers\RecruitmentController::class, 'convertToEmployee']);
    });
});