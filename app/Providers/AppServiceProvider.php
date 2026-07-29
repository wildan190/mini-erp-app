<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Resolve factories for models in App\Domain\{Domain}\Models\{Model}
        // to Database\Factories\{Domain}\{Model}Factory (existing factory location).
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            // e.g. App\Domain\HRM\Models\Employee => Database\Factories\HRM\EmployeeFactory
            if (str_starts_with($modelName, 'App\\Domain\\')) {
                $parts = explode('\\', $modelName);
                // parts: ['App', 'Domain', 'HRM', 'Models', 'Employee']
                $domain = $parts[2];   // HRM
                $model  = end($parts); // Employee
                return "Database\\Factories\\{$domain}\\{$model}Factory";
            }
            // Fallback for standard App\Models\X
            return 'Database\\Factories\\' . class_basename($modelName) . 'Factory';
        });
    }
}
