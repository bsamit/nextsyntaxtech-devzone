<?php

namespace NextSyntaxTech\DevZone;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use NextSyntaxTech\DevZone\Services\DevZoneService;
use NextSyntaxTech\DevZone\Http\Middleware\DevZoneAuthMiddleware;
use NextSyntaxTech\DevZone\Commands\InstallDevZoneCommand;

class DevZoneServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/devzone.php', 'devzone');

        // Bind Service singleton
        $this->app->singleton(DevZoneService::class, function () {
            return new DevZoneService();
        });
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // 1. Register Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/devzone.php');

        // 2. Register Views under namespace 'devzone'
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'devzone');

        // 3. Register Route Middleware
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('devzone.auth', DevZoneAuthMiddleware::class);

        // 4. Console Publishing and Commands
        if ($this->app->runningInConsole()) {
            // Publish Configuration
            $this->publishes([
                __DIR__ . '/../config/devzone.php' => config_path('devzone.php'),
            ], 'devzone-config');

            // Publish Blade Views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/devzone'),
            ], 'devzone-views');

            // Register Commands
            $this->commands([
                InstallDevZoneCommand::class,
            ]);
        }
    }
}
