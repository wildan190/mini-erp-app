<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Project\Controllers\ProjectController;
use App\Domain\Project\Controllers\ProjectTaskController;
use App\Domain\Project\Controllers\ProjectMemberController;
use App\Domain\Project\Controllers\ProjectCostController;

/*
|--------------------------------------------------------------------------
| Project Management Routes  —  routes/api/project.php
|--------------------------------------------------------------------------
| Served by project-service container.
| All controllers live under App\Domain\Project\Controllers\.
*/

Route::middleware('auth:platform')
    ->prefix('platform/project')
    ->group(function () {

        // ── Dashboard ─────────────────────────────────────────────────────
        Route::get('/dashboard', [ProjectController::class, 'dashboard']);

        // ── Projects CRUD ─────────────────────────────────────────────────
        Route::get('/projects',          [ProjectController::class, 'index']);
        Route::post('/projects',         [ProjectController::class, 'store']);
        Route::get('/projects/{uuid}',   [ProjectController::class, 'show']);
        Route::put('/projects/{uuid}',   [ProjectController::class, 'update']);

        // ── Tasks (nested under project) ──────────────────────────────────
        Route::get('/projects/{uuid}/tasks',       [ProjectController::class,     'tasks']);
        Route::post('/projects/{uuid}/tasks',      [ProjectController::class,     'storeTask']);
        Route::patch('/tasks/{task_uuid}',         [ProjectController::class,     'updateTask']);

        // ── Tasks (standalone endpoints) ──────────────────────────────────
        Route::get('/tasks',                       [ProjectTaskController::class, 'index']);
        Route::post('/tasks',                      [ProjectTaskController::class, 'store']);
        Route::patch('/tasks/{uuid}/reorder',      [ProjectTaskController::class, 'reorder']);

        // ── Members ───────────────────────────────────────────────────────
        Route::get('/members',                     [ProjectMemberController::class, 'index']);
        Route::post('/projects/{uuid}/members',    [ProjectController::class,       'storeMember']);
        Route::post('/members',                    [ProjectMemberController::class, 'store']);
        Route::delete('/members/{uuid}',           [ProjectMemberController::class, 'destroy']);

        // ── Timesheets ────────────────────────────────────────────────────
        Route::post('/projects/{uuid}/timesheets', [ProjectController::class, 'storeTimesheet']);

        // ── Costs ─────────────────────────────────────────────────────────
        Route::get('/projects/{uuid}/costs',       [ProjectController::class,     'costs']);
        Route::post('/projects/{uuid}/costs',      [ProjectController::class,     'storeCost']);
        Route::post('/costs',                      [ProjectCostController::class, 'store']);
        Route::delete('/costs/{uuid}',             [ProjectCostController::class, 'destroy']);
    });
