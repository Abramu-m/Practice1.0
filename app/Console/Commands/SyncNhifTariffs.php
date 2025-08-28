<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NhifService;
use Illuminate\Support\Facades\Log;

class SyncNhifTariffs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nhif:sync-tariffs {--facility=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize NHIF tariffs into local database';

    protected $nhifService;

    public function __construct(NhifService $nhifService)
    {
        parent::__construct();
        $this->nhifService = $nhifService;
    }

    public function handle()
    {
        $facility = $this->option('facility') ?? config('nhif.facility_code');
        if (! $facility) {
            $this->error('Facility code not provided and not configured. Use --facility=');
            return 1;
        }

        $this->info('Starting NHIF tariff sync for facility: ' . $facility);

        $result = $this->nhifService->syncTariffs($facility);

        if ($result['success']) {
            $this->info('Tariff sync completed. ' . ($result['synced_count'] ?? $result['count'] ?? 0) . ' items processed.');
            return 0;
        }

        $this->error('Tariff sync failed: ' . ($result['message'] ?? 'Unknown error'));
        Log::error('nhif:sync-tariffs failed', ['result' => $result]);
        return 1;
    }
}
