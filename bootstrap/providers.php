<?php

$providers = [
    App\Providers\AppServiceProvider::class,
];

if (env('APP_ENV') === 'local'
    && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
    $providers[] = \Laravel\Telescope\TelescopeServiceProvider::class;
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;