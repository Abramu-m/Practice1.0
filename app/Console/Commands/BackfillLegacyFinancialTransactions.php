<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillLegacyFinancialTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:backfill-legacy-transactions
        {--dry-run : Report counts and totals without inserting any records}
        {--only= : Comma-separated sources to process (visits,investigations,prescriptions). Default: all}
        {--chunk=2000 : Number of rows to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill financial_transactions for paid patient_visits, investigations, and prescriptions migrated from medcom1_0';

    /**
     * (source_type:source_id) pairs that already have a financial_transactions row.
     *
     * @var array<string, true>
     */
    protected array $existing = [];

    /**
     * Last used sequence number per day, keyed by Ymd, for transaction_number generation.
     *
     * @var array<string, int>
     */
    protected array $dailySequence = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        $available = ['visits', 'investigations', 'prescriptions'];
        $only = $this->option('only')
            ? array_map('trim', explode(',', $this->option('only')))
            : $available;

        if ($invalid = array_diff($only, $available)) {
            $this->error('Unknown --only value(s): ' . implode(', ', $invalid) . '. Expected: ' . implode(', ', $available));
            return self::FAILURE;
        }

        $this->loadExistingTransactions();
        $this->loadDailySequences();

        $now = Carbon::now();
        $results = [];

        if (in_array('visits', $only, true)) {
            $results['consultation (visits)'] = $this->processVisits($dryRun, $chunkSize, $now);
        }

        if (in_array('investigations', $only, true)) {
            $results['investigation'] = $this->processInvestigations($dryRun, $chunkSize, $now);
        }

        if (in_array('prescriptions', $only, true)) {
            $results['prescription'] = $this->processPrescriptions($dryRun, $chunkSize, $now);
        }

        $this->newLine();
        $this->table(
            ['Source', $dryRun ? 'Would create' : 'Created', 'Total Amount (Tsh)'],
            collect($results)->map(fn ($r, $label) => [$label, $r['count'], number_format($r['amount'], 2)])->values()->all()
        );

        if ($dryRun) {
            $this->comment('Dry run — no records were inserted.');
        }

        return self::SUCCESS;
    }

    /**
     * Pre-load (source_type, source_id) pairs that already have a transaction so we never duplicate.
     */
    protected function loadExistingTransactions(): void
    {
        DB::table('financial_transactions')
            ->whereIn('source_type', ['consultation', 'investigation', 'prescription'])
            ->select('source_type', 'source_id')
            ->get()
            ->each(function ($row) {
                $this->existing["{$row->source_type}:{$row->source_id}"] = true;
            });
    }

    /**
     * Pre-seed per-day sequence counters from existing TXN{Ymd}{seq} transaction numbers
     * so backfilled numbers never collide with live-generated ones.
     */
    protected function loadDailySequences(): void
    {
        DB::table('financial_transactions')
            ->where('transaction_number', 'like', 'TXN%')
            ->select('transaction_number')
            ->get()
            ->each(function ($row) {
                if (preg_match('/^TXN(\d{8})(\d+)$/', $row->transaction_number, $m)) {
                    $date = $m[1];
                    $seq = (int) $m[2];
                    if (($this->dailySequence[$date] ?? 0) < $seq) {
                        $this->dailySequence[$date] = $seq;
                    }
                }
            });
    }

    protected function nextTransactionNumber(Carbon $date): string
    {
        $key = $date->format('Ymd');
        $seq = ($this->dailySequence[$key] ?? 0) + 1;
        $this->dailySequence[$key] = $seq;

        return 'TXN' . $key . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{count: int, amount: float}
     */
    protected function processVisits(bool $dryRun, int $chunkSize, Carbon $now): array
    {
        $count = 0;
        $amount = 0.0;
        $chunks = 0;

        DB::table('patient_visits')
            ->select(['id', 'patient', 'created_by', 'visit_date', 'amount_cash', 'amount_covered',
                DB::raw('(amount_cash + amount_covered) as total_amount')])
            ->whereRaw('(amount_cash + amount_covered) > 0')
            ->chunkById($chunkSize, function ($visits) use (&$count, &$amount, &$chunks, $now, $dryRun) {
                $rows = [];

                foreach ($visits as $visit) {
                    if (isset($this->existing["consultation:{$visit->id}"])) {
                        continue;
                    }

                    $count++;
                    $amount += (float) $visit->total_amount;

                    if ($dryRun) {
                        continue;
                    }

                    $transactionDate = Carbon::parse($visit->visit_date);

                    $rows[] = [
                        'transaction_number' => $this->nextTransactionNumber($transactionDate),
                        'transaction_date' => $transactionDate,
                        'transaction_type' => 'income',
                        'category' => 'consultation',
                        'subcategory' => 'consultation_fee',
                        'amount' => $visit->total_amount,
                        'description' => "Consultation fee for patient visit #{$visit->id}",
                        'source_type' => 'consultation',
                        'source_id' => $visit->id,
                        'patient_id' => $visit->patient,
                        'visit_id' => $visit->id,
                        'payment_method' => (float) $visit->amount_covered > 0 ? 'insurance_cash' : 'cash',
                        'payment_reference' => null,
                        'insurance_covered_amount' => $visit->amount_covered,
                        'patient_paid_amount' => $visit->amount_cash,
                        'status' => 'completed',
                        'created_by' => $visit->created_by,
                        'approved_by' => null,
                        'approved_at' => null,
                        'notes' => 'Backfilled from legacy migration (medcom1_0)',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! $dryRun && ! empty($rows)) {
                    DB::table('financial_transactions')->insert($rows);
                }

                $chunks++;
                if ($chunks % 5 === 0) {
                    $this->info("  visits: processed {$chunks} chunks...");
                }
            });

        $this->info("Visits -> consultation: {$count} transaction(s)" . ($dryRun ? ' (dry run)' : ' created') . ', total Tsh ' . number_format($amount, 2));

        return ['count' => $count, 'amount' => $amount];
    }

    /**
     * @return array{count: int, amount: float}
     */
    protected function processInvestigations(bool $dryRun, int $chunkSize, Carbon $now): array
    {
        $count = 0;
        $amount = 0.0;
        $chunks = 0;

        DB::table('investigations as i')
            ->join('medical_services as ms', 'ms.id', '=', 'i.medical_service_id')
            ->join('patients as p', 'p.id', '=', 'i.patient_id')
            ->select([
                'i.id as id',
                'i.patient_id',
                'i.visit_id',
                'i.cash_amount',
                'i.insurance_covered_amount',
                DB::raw('(i.cash_amount + i.insurance_covered_amount) as total_amount'),
                'i.paid_at',
                'i.paid_by',
                'ms.name as service_name',
                'p.first_name',
                'p.last_name',
            ])
            ->whereRaw('i.is_paid = 1 AND (i.cash_amount + i.insurance_covered_amount) > 0')
            ->chunkById($chunkSize, function ($investigations) use (&$count, &$amount, &$chunks, $now, $dryRun) {
                $rows = [];

                foreach ($investigations as $inv) {
                    if (isset($this->existing["investigation:{$inv->id}"])) {
                        continue;
                    }

                    $count++;
                    $amount += (float) $inv->total_amount;

                    if ($dryRun) {
                        continue;
                    }

                    $transactionDate = Carbon::parse($inv->paid_at);
                    $patientName = trim($inv->first_name . ' ' . $inv->last_name);

                    $rows[] = [
                        'transaction_number' => $this->nextTransactionNumber($transactionDate),
                        'transaction_date' => $transactionDate,
                        'transaction_type' => 'income',
                        'category' => 'investigation_services',
                        'subcategory' => $inv->service_name,
                        'amount' => $inv->total_amount,
                        'description' => "Payment for {$inv->service_name} - {$patientName}",
                        'source_type' => 'investigation',
                        'source_id' => $inv->id,
                        'patient_id' => $inv->patient_id,
                        'visit_id' => $inv->visit_id,
                        'payment_method' => (float) $inv->insurance_covered_amount > 0 ? 'insurance_cash' : 'cash',
                        'payment_reference' => "INV-{$inv->id}",
                        'insurance_covered_amount' => $inv->insurance_covered_amount,
                        'patient_paid_amount' => $inv->cash_amount,
                        'status' => 'completed',
                        'created_by' => $inv->paid_by,
                        'approved_by' => null,
                        'approved_at' => null,
                        'notes' => 'Auto-generated from investigation payment (legacy backfill)',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! $dryRun && ! empty($rows)) {
                    DB::table('financial_transactions')->insert($rows);
                }

                $chunks++;
                if ($chunks % 10 === 0) {
                    $this->info("  investigations: processed {$chunks} chunks...");
                }
            }, 'i.id', 'id');

        $this->info("Investigations -> investigation: {$count} transaction(s)" . ($dryRun ? ' (dry run)' : ' created') . ', total Tsh ' . number_format($amount, 2));

        return ['count' => $count, 'amount' => $amount];
    }

    /**
     * @return array{count: int, amount: float}
     */
    protected function processPrescriptions(bool $dryRun, int $chunkSize, Carbon $now): array
    {
        $count = 0;
        $amount = 0.0;
        $chunks = 0;

        DB::table('prescriptions as pr')
            ->join('patients as p', 'p.id', '=', 'pr.patient_id')
            ->select([
                'pr.id as id',
                'pr.patient_id',
                'pr.visit_id',
                'pr.cash_amount',
                'pr.insurance_covered_amount',
                DB::raw('(pr.cash_amount + pr.insurance_covered_amount) as total_amount'),
                'pr.paid_at',
                'pr.paid_by',
                'p.first_name',
                'p.last_name',
            ])
            ->whereRaw('pr.is_paid = 1 AND (pr.cash_amount + pr.insurance_covered_amount) > 0')
            ->chunkById($chunkSize, function ($prescriptions) use (&$count, &$amount, &$chunks, $now, $dryRun) {
                $rows = [];

                foreach ($prescriptions as $presc) {
                    if (isset($this->existing["prescription:{$presc->id}"])) {
                        continue;
                    }

                    $count++;
                    $amount += (float) $presc->total_amount;

                    if ($dryRun) {
                        continue;
                    }

                    $transactionDate = Carbon::parse($presc->paid_at);
                    $patientName = trim($presc->first_name . ' ' . $presc->last_name);

                    $rows[] = [
                        'transaction_number' => $this->nextTransactionNumber($transactionDate),
                        'transaction_date' => $transactionDate,
                        'transaction_type' => 'income',
                        'category' => 'medication_sales',
                        'subcategory' => 'prescription_payment',
                        'amount' => $presc->total_amount,
                        'description' => "Prescription payment - {$patientName}",
                        'source_type' => 'prescription',
                        'source_id' => $presc->id,
                        'patient_id' => $presc->patient_id,
                        'visit_id' => $presc->visit_id,
                        'payment_method' => (float) $presc->insurance_covered_amount > 0 ? 'insurance_cash' : 'cash',
                        'payment_reference' => "PRESC-{$presc->id}",
                        'insurance_covered_amount' => $presc->insurance_covered_amount,
                        'patient_paid_amount' => $presc->cash_amount,
                        'status' => 'completed',
                        'created_by' => $presc->paid_by,
                        'approved_by' => null,
                        'approved_at' => null,
                        'notes' => 'Auto-generated from prescription payment (legacy backfill)',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! $dryRun && ! empty($rows)) {
                    DB::table('financial_transactions')->insert($rows);
                }

                $chunks++;
                if ($chunks % 10 === 0) {
                    $this->info("  prescriptions: processed {$chunks} chunks...");
                }
            }, 'pr.id', 'id');

        $this->info("Prescriptions -> prescription: {$count} transaction(s)" . ($dryRun ? ' (dry run)' : ' created') . ', total Tsh ' . number_format($amount, 2));

        return ['count' => $count, 'amount' => $amount];
    }
}
