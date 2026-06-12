<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillLegacyCashSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medications:backfill-legacy-cash-sales
        {--dry-run : Report counts and totals without inserting any records}
        {--chunk=500 : Number of sale groups to process per transaction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy walk-in cash sales (medcom1_0.pat_pharm with visit_id=0/pat_id=NULL) into medication_cash_sales + medication_cash_sale_items';

    /**
     * sale_number sequence per year, e.g. [2021 => 12].
     *
     * @var array<int, int>
     */
    protected array $yearSeq = [];

    /**
     * notes values of already-migrated groups, for idempotency.
     *
     * @var array<string, true>
     */
    protected array $existingNotes = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        $medicationIds = $this->idSet('medications');
        $routeIds = $this->idSet('administration_routes');
        $frequencyIds = $this->idSet('medication_frequencies');
        $userIds = $this->idSet('users');

        $this->loadExistingNotes();
        $this->loadYearSequences();

        $rows = DB::table(config('database.legacy_medcom_database') . '.pat_pharm')
            ->where('visit_id', 0)
            ->whereNull('pat_id')
            ->orderBy('refname')
            ->orderBy('createdon')
            ->orderBy('id')
            ->get();

        $groups = $rows->groupBy(fn ($row) => $row->refname . '|' . $row->createdon);

        $stats = [
            'groups_total' => $groups->count(),
            'groups_already_migrated' => 0,
            'groups_skipped_no_valid_items' => 0,
            'items_excluded_bad_dcode' => 0,
            'sales_created' => 0,
            'items_created' => 0,
            'amount_total' => 0.0,
            'dispensed_sales' => 0,
            'pending_sales' => 0,
            'paid_sales' => 0,
            'unpaid_sales' => 0,
        ];

        $processed = 0;

        foreach ($groups->chunk($chunkSize) as $groupChunk) {
            DB::transaction(function () use ($groupChunk, $medicationIds, $routeIds, $frequencyIds, $userIds, $dryRun, &$stats) {
                foreach ($groupChunk as $key => $items) {
                    [$refname, $createdon] = explode('|', $key, 2);

                    $notes = "Legacy OTC backfill — {$refname} — {$createdon}";
                    if (isset($this->existingNotes[$notes])) {
                        $stats['groups_already_migrated']++;
                        continue;
                    }

                    $validItems = $items->filter(fn ($i) => isset($medicationIds[(int) $i->dcode]) && (int) $i->dcode !== 0);
                    $stats['items_excluded_bad_dcode'] += $items->count() - $validItems->count();

                    if ($validItems->isEmpty()) {
                        $stats['groups_skipped_no_valid_items']++;
                        continue;
                    }

                    $headerItem = $validItems->sortBy('id')->first();

                    $totalAmount = $validItems->sum(fn ($i) => (float) ($i->dprice ?? 0));
                    $allDispensed = $validItems->every(fn ($i) => (int) $i->pdstatus > 4);
                    $allPaid = $validItems->every(fn ($i) => (int) $i->pdstatus > 3);

                    $status = $allDispensed ? 'dispensed' : 'pending';
                    $isPaid = $allPaid ? 1 : 0;

                    $createdAt = $this->combineDateTime($headerItem->createdon, $headerItem->createdat);

                    $dispensedBy = $allDispensed ? $this->validUserId((int) $headerItem->issuedby, $userIds) : null;
                    $dispensedAt = $allDispensed ? $this->combineDateTime($headerItem->issuedon, $headerItem->issuedat) : null;

                    $paidBy = $allPaid ? $this->validUserId((int) $headerItem->cashier, $userIds) : null;
                    $paidAt = $allPaid ? $this->combineDateTime($headerItem->cashon, $headerItem->cashat) : null;

                    $stats['sales_created']++;
                    $stats['items_created'] += $validItems->count();
                    $stats['amount_total'] += $totalAmount;
                    $stats[$allDispensed ? 'dispensed_sales' : 'pending_sales']++;
                    $stats[$allPaid ? 'paid_sales' : 'unpaid_sales']++;

                    if ($dryRun) {
                        continue;
                    }

                    $year = (int) Carbon::parse($createdon)->year;
                    $saleId = DB::table('medication_cash_sales')->insertGetId([
                        'sale_number' => $this->nextSaleNumber($year),
                        'sale_type' => 'otc',
                        'external_prescription_details' => null,
                        'patient_category_id' => 1,
                        'total_amount' => $totalAmount,
                        'discount_amount' => 0,
                        'final_amount' => $totalAmount,
                        'status' => $status,
                        'is_paid' => $isPaid,
                        'created_by' => (int) $headerItem->createdby,
                        'dispensed_by' => $dispensedBy,
                        'dispensed_at' => $dispensedAt,
                        'paid_by' => $paidBy,
                        'paid_at' => $paidAt,
                        'cancelled_by' => null,
                        'cancelled_at' => null,
                        'cancellation_reason' => null,
                        'refund_required' => 0,
                        'payment_method' => 'cash',
                        'amount_paid' => $isPaid ? $totalAmount : null,
                        'notes' => $notes,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $itemRows = [];
                    foreach ($validItems as $item) {
                        $itemDispensed = (int) $item->pdstatus > 4;
                        $itemDispensedBy = $itemDispensed ? $this->validUserId((int) $item->issuedby, $userIds) : null;
                        $itemDispensedAt = $itemDispensed ? $this->combineDateTime($item->issuedon, $item->issuedat) : null;
                        $itemCreatedAt = $this->combineDateTime($item->createdon, $item->createdat);

                        $frequencyRaw = (int) $item->frequency;
                        $medicationFrequencyId = isset($frequencyIds[$frequencyRaw]) ? $frequencyRaw : 2;

                        $routeRaw = (int) $item->route;
                        $administrationRouteId = isset($routeIds[$routeRaw]) ? $routeRaw : 1;

                        $itemRows[] = [
                            'cash_sale_id' => $saleId,
                            'medication_id' => (int) $item->dcode,
                            'quantity' => $item->dqty,
                            'dosage' => $item->dosage,
                            'unit_price' => $item->uprice,
                            'total_price' => $item->dprice,
                            'batches_used' => null,
                            'status' => $itemDispensed ? 'dispensed' : 'pending',
                            'dispensing_type' => 'individual',
                            'quantity_dispensed' => $itemDispensed ? $item->dqty : 0,
                            'dispensed_at' => $itemDispensedAt,
                            'cancelled_at' => null,
                            'cancelled_by' => null,
                            'notes' => null,
                            'medication_frequency_id' => $medicationFrequencyId,
                            'administration_route_id' => $administrationRouteId,
                            'duration_days' => $item->days,
                            'instructions' => null,
                            'dispensed_by' => $itemDispensedBy,
                            'created_at' => $itemCreatedAt,
                            'updated_at' => $itemCreatedAt,
                        ];
                    }

                    DB::table('medication_cash_sale_items')->insert($itemRows);
                }
            });

            $processed += $groupChunk->count();
            $this->info("  processed {$processed} / {$stats['groups_total']} groups...");
        }

        $this->newLine();
        $this->table(['Metric', 'Value'], [
            ['Groups found', $stats['groups_total']],
            ['Groups already migrated (skipped)', $stats['groups_already_migrated']],
            ['Groups skipped (no valid items)', $stats['groups_skipped_no_valid_items']],
            ['Items excluded (bad dcode)', $stats['items_excluded_bad_dcode']],
            ['Sales ' . ($dryRun ? 'that would be created' : 'created'), $stats['sales_created']],
            ['Items ' . ($dryRun ? 'that would be created' : 'created'), $stats['items_created']],
            ['  - dispensed / pending', "{$stats['dispensed_sales']} / {$stats['pending_sales']}"],
            ['  - paid / unpaid', "{$stats['paid_sales']} / {$stats['unpaid_sales']}"],
            ['Total amount (Tsh)', number_format($stats['amount_total'], 2)],
        ]);

        if ($dryRun) {
            $this->comment('Dry run — no records were inserted.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, true>
     */
    protected function idSet(string $table): array
    {
        return DB::table($table)
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }

    /**
     * Pre-load notes of groups already migrated, for idempotency.
     */
    protected function loadExistingNotes(): void
    {
        DB::table('medication_cash_sales')
            ->where('notes', 'like', 'Legacy OTC backfill —%')
            ->pluck('notes')
            ->each(function ($notes) {
                $this->existingNotes[$notes] = true;
            });
    }

    /**
     * Pre-seed per-year sale_number counters from existing CS-{year}-{seq} sale numbers.
     */
    protected function loadYearSequences(): void
    {
        DB::table('medication_cash_sales')
            ->where('sale_number', 'like', 'CS-%')
            ->pluck('sale_number')
            ->each(function ($saleNumber) {
                if (preg_match('/^CS-(\d{4})-(\d+)$/', $saleNumber, $m)) {
                    $year = (int) $m[1];
                    $seq = (int) $m[2];
                    if (($this->yearSeq[$year] ?? 0) < $seq) {
                        $this->yearSeq[$year] = $seq;
                    }
                }
            });
    }

    protected function nextSaleNumber(int $year): string
    {
        $seq = ($this->yearSeq[$year] ?? 0) + 1;
        $this->yearSeq[$year] = $seq;

        return 'CS-' . $year . '-' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    protected function combineDateTime(?string $date, ?string $time): ?Carbon
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date . ' ' . ($time ?: '00:00:00'));
    }

    /**
     * @param array<int, true> $userIds
     */
    protected function validUserId(?int $id, array $userIds): ?int
    {
        if ($id === null || $id === 0 || ! isset($userIds[$id])) {
            return null;
        }

        return $id;
    }
}
