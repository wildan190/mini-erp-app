<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Project\Controllers\ProjectController;

/*
|--------------------------------------------------------------------------
| Project Management Microservice Gateway Routes (App\Domain\Project)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('platform/project')->group(function () {
    // Dashboard
    Route::get('/dashboard', [ProjectController::class, 'dashboard']);

    // Projects CRUD
    Route::get('/projects',         [ProjectController::class, 'index']);
    Route::post('/projects',        [ProjectController::class, 'store']);
    Route::get('/projects/{uuid}',  [ProjectController::class, 'show']);
    Route::put('/projects/{uuid}',  [ProjectController::class, 'update']);

    // Tasks
    Route::get('/projects/{uuid}/tasks',    [ProjectController::class, 'tasks']);
    Route::post('/projects/{uuid}/tasks',   [ProjectController::class, 'storeTask']);
    Route::patch('/tasks/{task_uuid}',      [ProjectController::class, 'updateTask']);

    // Members
    Route::post('/projects/{uuid}/members', [ProjectController::class, 'storeMember']);

    // Timesheets
    Route::post('/projects/{uuid}/timesheets', [ProjectController::class, 'storeTimesheet']);

    // Costs
    Route::get('/projects/{uuid}/costs',    [ProjectController::class, 'costs']);
    Route::post('/projects/{uuid}/costs',   [ProjectController::class, 'storeCost']);
});
