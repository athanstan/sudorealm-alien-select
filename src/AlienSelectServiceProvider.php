<?php

namespace Sudorealm\AlienSelect;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AlienSelectServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'alien-select');

        Livewire::component('alien-select', AlienSelect::class);

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/alien-select.php', 'alien-select');

        // Register the service the package provides.
        $this->app->singleton('alien-select', function ($app) {
            return new AlienSelect;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['alien-select'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/alien-select.php' => config_path('alien-select.php'),
        ], 'alien-select.config');

        // Publishing the views.
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/sudorealm'),
        ], 'alien-select.views');

        // Registering package commands.
        // $this->commands([]);
    }
}
