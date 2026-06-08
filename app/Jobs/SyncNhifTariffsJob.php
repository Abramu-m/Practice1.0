<?php

namespace App\Jobs;

use App\Services\NhifService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SyncNhifTariffsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    public ?string $facilityCode;

    public function __construct(?string $facilityCode = null)
    {
        $this->facilityCode = $facilityCode;
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800, 3600, 7200];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(24);
    }

    public function handle(NhifService $nhifService): void
    {
        $result = $nhifService->syncTariffs($this->facilityCode);

        if (! $result['success']) {
            throw new RuntimeException($result['message'] ?? 'NHIF tariff sync failed');
        }

        Log::channel('nhif')->info('Tariff sync job completed', [
            'facility_code' => $this->facilityCode,
            'synced_count' => $result['synced_count'] ?? null,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('nhif')->error('Tariff sync job permanently failed', [
            'facility_code' => $this->facilityCode,
            'exception' => $exception->getMessage(),
        ]);
    }
}
