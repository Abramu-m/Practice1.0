<?php

namespace App\Jobs;

use App\Models\NhifClaim;
use App\Services\NhifService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PollNhifClaimStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * NHIF status strings observed in claim responses, normalized to our local claim_status values.
     * The exact vocabulary can only be confirmed against a live response — see
     * docs/system-assessment.md verification notes. Unrecognized strings are logged and left
     * as 'submitted' so staff can inspect them rather than silently mis-classifying a claim.
     */
    private const STATUS_MAP = [
        'approved' => 'approved',
        'accepted' => 'approved',
        'paid' => 'approved',
        'rejected' => 'rejected',
        'declined' => 'rejected',
        'denied' => 'rejected',
        'pending' => 'pending',
        'processing' => 'pending',
        'in review' => 'pending',
        'submitted' => 'submitted',
    ];

    public int $tries = 3;

    public function backoff(): array
    {
        return [300, 1800, 3600];
    }

    public function handle(NhifService $nhifService): void
    {
        $groups = NhifClaim::query()
            ->where('claim_status', 'submitted')
            ->whereNotNull('facility_code')
            ->select('facility_code', 'claim_year', 'claim_month')
            ->distinct()
            ->get();

        foreach ($groups as $group) {
            $this->pollGroup($nhifService, $group->facility_code, (int) $group->claim_year, (int) $group->claim_month);
        }
    }

    private function pollGroup(NhifService $nhifService, string $facilityCode, int $claimYear, int $claimMonth): void
    {
        $result = $nhifService->getSubmittedClaims($facilityCode, $claimYear, $claimMonth);

        if (! $result['success']) {
            Log::channel('nhif')->warning('Claim status poll failed for group', [
                'facility_code' => $facilityCode,
                'claim_year' => $claimYear,
                'claim_month' => $claimMonth,
                'message' => $result['message'] ?? null,
            ]);

            return;
        }

        $entries = $this->extractEntries($result['data'] ?? []);

        Log::channel('nhif')->info('Claim status poll response received', [
            'facility_code' => $facilityCode,
            'claim_year' => $claimYear,
            'claim_month' => $claimMonth,
            'entry_count' => count($entries),
            'raw_sample' => array_slice($entries, 0, 3),
        ]);

        $claims = NhifClaim::where('facility_code', $facilityCode)
            ->where('claim_year', $claimYear)
            ->where('claim_month', $claimMonth)
            ->where('claim_status', 'submitted')
            ->get()
            ->keyBy(fn (NhifClaim $claim) => (string) $claim->folio_no);

        foreach ($entries as $entry) {
            $folioNo = (string) ($entry['FolioNo'] ?? $entry['folioNo'] ?? $entry['SerialNo'] ?? $entry['serialNo'] ?? '');

            if ($folioNo === '' || ! $claims->has($folioNo)) {
                continue;
            }

            $claim = $claims->get($folioNo);
            $newStatus = $this->normalizeStatus($entry);

            if ($newStatus && $newStatus !== $claim->claim_status) {
                $claim->update([
                    'claim_status' => $newStatus,
                    'response_data' => $entry,
                ]);

                Log::channel('nhif')->info('Claim status updated from NHIF poll', [
                    'claim_id' => $claim->id,
                    'folio_no' => $claim->folio_no,
                    'new_status' => $newStatus,
                ]);
            }
        }
    }

    /**
     * NHIF may return the list directly, or nested under a key such as Claims/Folios/Data.
     */
    private function extractEntries(mixed $data): array
    {
        if (is_array($data) && array_is_list($data)) {
            return $data;
        }

        if (is_array($data)) {
            foreach (['Claims', 'Folios', 'Data', 'FolioList', 'Items'] as $key) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    return $data[$key];
                }
            }
        }

        return [];
    }

    private function normalizeStatus(array $entry): ?string
    {
        $raw = $entry['ClaimStatus'] ?? $entry['Status'] ?? $entry['ApprovalStatus'] ?? $entry['Remarks'] ?? null;

        if (! is_string($raw)) {
            return null;
        }

        $normalized = self::STATUS_MAP[strtolower(trim($raw))] ?? null;

        if (! $normalized) {
            Log::channel('nhif')->warning('Unrecognized NHIF claim status string — left unchanged', [
                'raw_status' => $raw,
            ]);
        }

        return $normalized;
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('nhif')->error('Claim status poll job permanently failed', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
