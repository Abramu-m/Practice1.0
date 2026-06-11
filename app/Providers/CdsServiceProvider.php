<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\DispatchCdsChecks;
use App\Events\MedicationPrescribed;
use App\Events\LabResultRecorded;

class CdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings for CDS can be registered here if needed later
    }

    public function boot(): void
    {
        // Register event listeners for CDS
        Event::listen(
            MedicationPrescribed::class,
            [DispatchCdsChecks::class, 'handle']
        );

        Event::listen(
            LabResultRecorded::class,
            [DispatchCdsChecks::class, 'handle']
        );
    }
}
