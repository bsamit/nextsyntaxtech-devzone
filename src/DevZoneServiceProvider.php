<?php

namespace NextSyntaxTech\DevZone;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use NextSyntaxTech\DevZone\Services\DevZoneService;
use NextSyntaxTech\DevZone\Http\Middleware\DevZoneAuthMiddleware;
use NextSyntaxTech\DevZone\Commands\InstallDevZoneCommand;
use NextSyntaxTech\DevZone\View\Components\Alert;

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

        // 3. Register Blade Components (<x-devzone::alert>, <x-devzone-alert>, <x-devzone::dialog>)
        Blade::component('devzone-alert', Alert::class);
        Blade::component('devzone::components.dialog', 'devzone-dialog');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components', 'devzone');

        // 4. Register Route Middleware
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('devzone.auth', DevZoneAuthMiddleware::class);

        // 5. Console Publishing and Commands
        if ($this->app->runningInConsole()) {
            // Publish Configuration
            $this->publishes([
                __DIR__ . '/../config/devzone.php' => config_path('devzone.php'),
            ], 'devzone-config');

            // Publish Blade Views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/devzone'),
            ], 'devzone-views');

            // Publish Local Assets (Offline & Zero-CDN: Bootstrap & Boxicons)
            $this->publishes([
                __DIR__ . '/../resources/assets' => public_path('vendor/devzone'),
            ], 'devzone-assets');

            // Register Commands
            $this->commands([
                InstallDevZoneCommand::class,
            ]);
        }
    }
}
