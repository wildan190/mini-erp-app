<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\HRM\Contracts\HRMServiceInterface;
use App\Domain\HRM\Services\AttendanceService;
use App\Domain\Purchasing\Contracts\PurchasingServiceInterface;
use App\Domain\Purchasing\Services\PurchasingService;
use App\Domain\Project\Contracts\ProjectServiceInterface;
use App\Domain\Project\Services\ProjectService;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register domain interface contracts to their respective implementations.
     * When ready to break into separate microservice repositories, simply replace
     * these bindings with HTTP/gRPC API Clients.
     */
    public function register(): void
    {
        $this->app->bind(HRMServiceInterface::class, AttendanceService::class);
        $this->app->bind(PurchasingServiceInterface::class, PurchasingService::class);
        $this->app->bind(ProjectServiceInterface::class, ProjectService::class);
    }

    public function boot(): void
    {
        //
    }
}
