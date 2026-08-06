<?php

use Illuminate\Support\Facades\Route;
use App\Domain\System\Controllers\RolePermissionController;
use App\Domain\System\Controllers\ApprovalController;

/*
|--------------------------------------------------------------------------
| System Microservice Routes  —  routes/api/system.php
|--------------------------------------------------------------------------
| Dedicated standalone microservice container for Dynamic RBAC & Approval Engine.
| Served on Port 8009 / via Gateway /api/platform/system/*
*/

Route::middleware('auth:platform')->prefix('platform/system')->group(function () {

    // Dynamic RBAC Management
    Route::get('/roles', [RolePermissionController::class, 'indexRoles']);
    Route::post('/roles', [RolePermissionController::class, 'storeRole']);
    Route::get('/permissions', [RolePermissionController::class, 'indexPermissions']);
    Route::post('/users/{uuid}/roles', [RolePermissionController::class, 'assignUserRole']);

    // Multi-Tier Approval Engine
    Route::get('/approvals/chains', [ApprovalController::class, 'indexChains']);
    Route::post('/approvals/chains', [ApprovalController::class, 'storeChain']);
    Route::get('/approvals/pending', [ApprovalController::class, 'indexPendingRequests']);
    Route::post('/approvals/{uuid}/approve', [ApprovalController::class, 'approve']);
    Route::post('/approvals/{uuid}/reject', [ApprovalController::class, 'reject']);
});
