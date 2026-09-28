<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TelescopeConditionalProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        if (! class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            return;
        }

        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(\App\Providers\TelescopeServiceProvider::class);
    }
}