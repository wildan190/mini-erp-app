<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Core\Controllers\Dashboard\PlatformDashboardController;

/*
|--------------------------------------------------------------------------
| Core Platform Routes  —  routes/api/core.php
|--------------------------------------------------------------------------
| Served exclusively by the core-service container.
| Owns the platform dashboard (cross-domain summary), Horizon, Landing.
*/

Route::prefix('platform')
    ->middleware('auth:platform')
    ->group(function () {
        Route::get('/dashboard', [PlatformDashboardController::class, 'index']);
    });
