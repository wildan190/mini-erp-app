<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        then: function () {
            // Core platform routes: auth, dashboard
            // Served by core-service container.
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/core.php'));

            // Domain service routes — each served by their own container.
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/hrm.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/crm.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/finance.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/purchasing.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/project.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/inventory.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/system.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermissionMiddleware::class,
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\QueryTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }
        });
        
        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request, \Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
    })->create();


