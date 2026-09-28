<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TelescopeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Si Telescope n'est pas installé (prod avec --no-dev), on ne fait rien.
        if (! class_exists(\Laravel\Telescope\Telescope::class)) {
            return;
        }

        // Enregistrer le vrai provider de Telescope
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        \Laravel\Telescope\Telescope::filter(function (\Laravel\Telescope\IncomingEntry $entry) use ($isLocal) {
            return $isLocal
                || $entry->isReportableException()
                || $entry->isFailedRequest()
                || $entry->isFailedJob()
                || $entry->isScheduledTask()
                || $entry->hasMonitoredTag();
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        \Laravel\Telescope\Telescope::hideRequestParameters(['_token']);
        \Laravel\Telescope\Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}