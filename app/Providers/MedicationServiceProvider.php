<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\StockManagementService;
use App\Services\ReconciliationService;
use App\Services\ConsumptionTrackingService;

class MedicationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register StockManagementService as singleton
        $this->app->singleton(StockManagementService::class, function ($app) {
            return new StockManagementService();
        });

        // Register ReconciliationService as singleton
        $this->app->singleton(ReconciliationService::class, function ($app) {
            return new ReconciliationService();
        });

        // Register ConsumptionTrackingService with dependency injection
        $this->app->singleton(ConsumptionTrackingService::class, function ($app) {
            return new ConsumptionTrackingService(
                $app->make(StockManagementService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
