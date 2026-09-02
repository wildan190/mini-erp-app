<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Auth\Controllers\PlatformLoginController;
use App\Domain\Auth\Controllers\PlatformRegisterController;
use App\Domain\Auth\Controllers\PlatformLogoutController;
use App\Domain\Auth\Controllers\PlatformMeController;

/*
|--------------------------------------------------------------------------
| API Routes  —  routes/api.php
|--------------------------------------------------------------------------
| Auth routes served by the standalone auth-service container.
| Nginx exact-match routing ensures only auth-service ever receives
| requests to these three endpoints.
*/

Route::prefix('platform')->group(function () {
    Route::post('/login',    [PlatformLoginController::class,    'login']);
    Route::post('/register', [PlatformRegisterController::class, 'register']);

    Route::middleware('auth:platform')->group(function () {
        Route::post('/logout', [PlatformLogoutController::class, 'logout']);

        // Returns current user profile with roles + permissions (used for session hydration)
        Route::get('/me', [PlatformMeController::class, 'me']);
    });
});
