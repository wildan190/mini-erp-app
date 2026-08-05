<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\DomainServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    L5Swagger\L5SwaggerServiceProvider::class,
    Opcodes\LogViewer\LogViewerServiceProvider::class,
];

if (env('APP_ENV') !== 'testing' && filter_var(env('TELESCOPE_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
