<?php

namespace App\Providers;

use App\Services\NhifService;
use Illuminate\Support\ServiceProvider;

class NhifServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(NhifService::class, function ($app) {
            return new NhifService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file if needed
        $this->publishes([
            __DIR__ . '/../../config/nhif.php' => config_path('nhif.php'),
        ], 'nhif-config');
    }
}
