<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\DomainServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    L5Swagger\L5SwaggerServiceProvider::class,
    Opcodes\LogViewer\LogViewerServiceProvider::class,
];

return $providers;

