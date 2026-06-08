<?php

namespace App\Jobs;

use App\Models\NhifClaim;
use App\Services\NhifService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SubmitNhifClaimJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    public int $claimId;

    public function __construct(int $claimId)
    {
        $this->claimId = $claimId;
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
        if ($this->batch()?->cancelled()) {
            return;
        }

        $claim = NhifClaim::find($this->claimId);

        // Already handled by a previous attempt, or no longer queued (e.g. deleted) — nothing to do.
        if (! $claim || $claim->claim_status !== 'queued') {
            return;
        }

        $result = $nhifService->submitSingleFolio($claim);

        if (! $result['success']) {
            throw new RuntimeException($result['message'] ?? 'NHIF claim submission failed');
        }

        Log::channel('nhif')->info('Claim submission job completed', [
            'claim_id' => $this->claimId,
            'folio_no' => $claim->folio_no,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $claim = NhifClaim::find($this->claimId);

        // Out of retries — return the claim to 'draft' so staff can re-queue it manually.
        if ($claim && $claim->claim_status === 'queued') {
            $claim->update(['claim_status' => 'draft']);
        }

        Log::channel('nhif')->error('Claim submission job permanently failed', [
            'claim_id' => $this->claimId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
