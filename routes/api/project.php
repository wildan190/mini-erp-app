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

        // ── Projects CRUD (supports both / and /projects) ─────────────────
        Route::get('/',                  [ProjectController::class, 'index']);
        Route::post('/',                 [ProjectController::class, 'store']);
        Route::get('/projects',          [ProjectController::class, 'index']);
        Route::post('/projects',         [ProjectController::class, 'store']);
        Route::get('/won-prospects',     [ProjectController::class, 'wonProspects']);
        Route::get('/projects/{uuid}',          [ProjectController::class, 'show']);
        Route::put('/projects/{uuid}',          [ProjectController::class, 'update']);
        Route::patch('/projects/{uuid}/status', [ProjectController::class, 'updateStatus']);

        // ── Tasks (nested under project) ──────────────────────────────────
        Route::get('/projects/{uuid}/tasks',       [ProjectController::class,     'tasks']);
        Route::post('/projects/{uuid}/tasks',      [ProjectController::class,     'storeTask']);
        Route::patch('/tasks/{task_uuid}',         [ProjectController::class,     'updateTask']);

        // ── Tasks (standalone endpoints) ──────────────────────────────────
        Route::get('/tasks',                       [ProjectTaskController::class, 'index']);
        Route::post('/tasks',                      [ProjectTaskController::class, 'store']);
        Route::put('/tasks/reorder',               [ProjectTaskController::class, 'reorder']);
        Route::patch('/tasks/{uuid}/reorder',      [ProjectTaskController::class, 'reorder']);
        Route::put('/tasks/{uuid}',                [ProjectTaskController::class, 'update']);
        Route::patch('/tasks/{uuid}',              [ProjectTaskController::class, 'update']);

        // ── Members & Resources ───────────────────────────────────────────
        Route::get('/members',                     [ProjectMemberController::class, 'index']);
        Route::get('/resources',                   [ProjectMemberController::class, 'index']);
        Route::post('/projects/{uuid}/members',    [ProjectController::class,       'storeMember']);
        Route::post('/members',                    [ProjectMemberController::class, 'store']);
        Route::delete('/members/{uuid}',           [ProjectMemberController::class, 'destroy']);

        // ── Timesheets ────────────────────────────────────────────────────
        Route::get('/timesheets',                  [ProjectController::class, 'timesheets']);
        Route::post('/projects/{uuid}/timesheets', [ProjectController::class, 'storeTimesheet']);
        Route::post('/timesheets',                 function (\Illuminate\Http\Request $request) {
            $projectUuid = $request->input('project_uuid');
            if (!$projectUuid) {
                return response()->json(['message' => 'The project_uuid field is required.'], 422);
            }
            return app(ProjectController::class)->storeTimesheet($request, $projectUuid);
        });

        // ── Costs & Financials ────────────────────────────────────────────
        Route::get('/financials',                  [ProjectCostController::class, 'financials']);
        Route::get('/projects/{uuid}/costs',       [ProjectController::class,     'costs']);
        Route::post('/projects/{uuid}/costs',      [ProjectCostController::class, 'store']);
        Route::post('/costs',                      [ProjectCostController::class, 'store']);
        Route::delete('/costs/{uuid}',             [ProjectCostController::class, 'destroy']);

        // ── Single Project by UUID alias ──────────────────────────────────
        Route::get('/{uuid}',                      [ProjectController::class, 'show']);
        Route::put('/{uuid}',                      [ProjectController::class, 'update']);
    });
