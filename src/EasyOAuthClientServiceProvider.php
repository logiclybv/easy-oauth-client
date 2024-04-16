<?php

namespace Logicly\EasyOAuthClient;

use Illuminate\Support\ServiceProvider;

class EasyOAuthClientServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
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
        $this->mergeConfigFrom(__DIR__.'/../config/easy-oauth-client.php', 'easy-oauth-client');

        // Register the service the package provides.
        $this->app->singleton('easy-oauth-client', function ($app) {
            return new Client;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['easy-oauth-client'];
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
            __DIR__.'/../config/easy-oauth-client.php' => config_path('easy-oauth-client.php'),
        ], 'easy-oauth-client.config');
    }
}
