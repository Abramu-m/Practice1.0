<?php

namespace App\Console\Commands;

use App\Models\InvestigationTemplateResult;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillCd4Results extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investigations:backfill-cd4-results
        {--dry-run : Report counts without writing any records}
        {--chunk=200 : Number of legacy rows to process per transaction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy {legacy_medcom_database}.cd4_form rows into investigation_template_results using the cd4 result template';

    /**
     * Legacy `test_reason` -> cd4 `cd4_indication` radio values, keyed by lowercased legacy value.
     */
    private const INDICATION_MAP = [
        'reactive bioline and unigold tests' => 'reactive_bioline_unigold',
        'art 6 months routine test' => 'art_6_months_routine',
        'unknown but needed cd4 test' => 'unknown_but_needed',
        'bad condition of the patient' => 'bad_condition',
    ];

    /**
     * Legacy `formstatus` -> cd4 result `form_status`.
     */
    private const FORM_STATUS_MAP = [
        1 => 'draft',
        2 => 'preliminary',
        3 => 'final',
    ];

    /**
     * Fallback user id when no requested/accepted/resulted-by user is valid.
     */
    private const FALLBACK_USER_ID = 23;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        $templateName = DB::table('result_templates')->where('code', 'cd4')->value('name');
        if (! $templateName) {
            $this->error('result_templates row with code=cd4 not found.');

            return self::FAILURE;
        }

        $userIds = DB::table('users')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $userNames = DB::table('users')->get()->mapWithKeys(
            fn ($u) => [(int) $u->id => trim($u->first_name . ' ' . $u->last_name)]
        )->all();

        $stats = [
            'rows_total' => 0,
            'skipped_no_pl_id' => 0,
            'skipped_no_investigation' => 0,
            'updated' => 0,
            'created' => 0,
            'final' => 0,
            'preliminary' => 0,
            'draft' => 0,
        ];

        DB::table(config('database.legacy_medcom_database') . '.cd4_form')
            ->orderBy('id')
            ->chunk($chunkSize, function ($rows) use (&$stats, $dryRun, $templateName, $userIds, $userNames) {
                $plIds = $rows->pluck('pl_id')->filter()->all();
                $existingInvestigationIds = DB::table('investigations')
                    ->whereIn('id', $plIds)
                    ->pluck('id')
                    ->mapWithKeys(fn ($id) => [(int) $id => true])
                    ->all();
                $existingResultIds = DB::table('investigation_template_results')
                    ->whereIn('investigation_id', $plIds)
                    ->pluck('investigation_id')
                    ->mapWithKeys(fn ($id) => [(int) $id => true])
                    ->all();

                DB::transaction(function () use ($rows, &$stats, $dryRun, $templateName, $userIds, $userNames, $existingInvestigationIds, $existingResultIds) {
                    foreach ($rows as $row) {
                        $stats['rows_total']++;

                        if (empty($row->pl_id)) {
                            $stats['skipped_no_pl_id']++;
                            continue;
                        }

                        $investigationId = (int) $row->pl_id;

                        if (! isset($existingInvestigationIds[$investigationId])) {
                            $stats['skipped_no_investigation']++;
                            continue;
                        }

                        $formData = $this->buildFormData($row, $userNames);
                        $formStatus = self::FORM_STATUS_MAP[(int) $row->formstatus] ?? 'draft';
                        $stats[$formStatus]++;

                        $reportedBy = $this->validUserId($row->resultedby, $userIds)
                            ?? $this->validUserId($row->acceptedby, $userIds)
                            ?? $this->validUserId($row->requestedby, $userIds)
                            ?? self::FALLBACK_USER_ID;

                        $reportedAt = $this->combineDateTime($row->resultedon, $row->resultedat)
                            ?? $this->combineDateTime($row->acceptedon, $row->acceptedat)
                            ?? $this->combineDateTime($row->requestedon, $row->requestedat)
                            ?? now();

                        if (isset($existingResultIds[$investigationId])) {
                            $stats['updated']++;
                        } else {
                            $stats['created']++;
                        }

                        if ($dryRun) {
                            continue;
                        }

                        InvestigationTemplateResult::updateOrCreate(
                            ['investigation_id' => $investigationId],
                            [
                                'template_name' => $templateName,
                                'template_version' => '1.0',
                                'form_data' => $formData,
                                'form_status' => $formStatus,
                                'metadata' => [
                                    'template_code' => 'cd4',
                                    'migrated_from' => 'cd4_form',
                                    'legacy_id' => (int) $row->id,
                                    'form_fields_count' => count($formData),
                                ],
                                'reported_by' => $reportedBy,
                                'reported_at' => $reportedAt,
                            ]
                        );
                    }
                });
            });

        $this->table(['Metric', 'Value'], [
            ['Legacy rows processed', $stats['rows_total']],
            ['Skipped (no pl_id)', $stats['skipped_no_pl_id']],
            ['Skipped (no matching investigation)', $stats['skipped_no_investigation']],
            ['Results ' . ($dryRun ? 'that would be updated' : 'updated'), $stats['updated']],
            ['Results ' . ($dryRun ? 'that would be created' : 'created'), $stats['created']],
            ['  - final', $stats['final']],
            ['  - preliminary', $stats['preliminary']],
            ['  - draft', $stats['draft']],
        ]);

        if ($dryRun) {
            $this->comment('Dry run — no records were written.');
        }

        return self::SUCCESS;
    }

    /**
     * Build the flat form_data object matching resources/views/lab/result_templates/cd4.blade.php.
     */
    protected function buildFormData(object $row, array $userNames): array
    {
        [$indication, $indicationOther] = $this->mapIndication($row->test_reason);
        [$cd4Count, $cd4AdvancedResult] = $this->parseResults($row->results);

        $resultedDate = $this->nullIfEmpty($row->resultedon);
        $resultedTime = $this->timeHM($row->resultedat);

        return [
            'ctc_number' => $this->normalizeCtc($row->ctc_no),
            'cd4_indication' => $indication,
            'cd4_indication_other' => $indicationOther,
            'lab_serial_no' => null,
            'date_received' => $this->nullIfEmpty($row->collectedon) ?? $this->nullIfEmpty($row->acceptedon),
            'date_analyzed' => $resultedDate ?? $this->nullIfEmpty($row->acceptedon),
            'cd4_count' => $cd4Count,
            'cd4_advanced_result' => $cd4AdvancedResult,
            'cd4_percentage' => null,
            'total_lymphocytes' => null,
            'cd8_count' => null,
            'cd4_cd8_ratio' => null,
            'test_method' => null,
            'hiv_category' => null,
            'clinical_significance' => null,
            'technician' => $this->userName($row->resultedby, $userNames) ?? $this->userName($row->acceptedby, $userNames),
            'reviewed_by' => null,
            'result_date' => $resultedDate,
            'result_time' => $resultedTime,
        ];
    }

    /**
     * Maps legacy `test_reason` to the cd4 `cd4_indication` radio value, splitting
     * "Others, <text>" into the "others" option plus its free-text reason.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function mapIndication(?string $value): array
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return [null, null];
        }

        $lower = strtolower($value);
        if (isset(self::INDICATION_MAP[$lower])) {
            return [self::INDICATION_MAP[$lower], null];
        }

        if (preg_match('/^others,\s*(.*)$/i', $value, $m)) {
            return ['others', trim($m[1]) ?: null];
        }

        return ['others', $value];
    }

    /**
     * Splits legacy `results` into the cd4 `cd4_count` text field plus, for the
     * "< 200" / "&ge; 200" advanced-disease shorthand, the `cd4_advanced_result` radio.
     * Any other value (numeric counts, or odd free text) is kept as-is in cd4_count.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function parseResults(?string $value): array
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return [null, null];
        }

        if (is_numeric($value)) {
            return [$value, null];
        }

        if (preg_match('/^<\s*200$/', $value)) {
            return ['<200', 'below_200'];
        }

        if (preg_match('/^[>\x{2265}]=?\s*200$/u', $value)) {
            return ["\u{2265}200", 'above_200'];
        }

        return [$value, null];
    }

    /**
     * Placeholder CTC numbers (all dots/dashes/commas/spaces, e.g. "--", "...") are
     * treated as "not recorded".
     */
    protected function normalizeCtc(?string $value): ?string
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return null;
        }

        return preg_match('/^[.\-,\s]+$/', $value) ? null : $value;
    }

    protected function nullIfEmpty(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function timeHM(?string $time): ?string
    {
        $time = $this->nullIfEmpty($time);

        return $time === null ? null : substr($time, 0, 5);
    }

    protected function combineDateTime(?string $date, ?string $time): ?Carbon
    {
        $date = $this->nullIfEmpty($date);
        if ($date === null) {
            return null;
        }

        return Carbon::parse($date . ' ' . ($this->nullIfEmpty($time) ?? '00:00:00'));
    }

    /**
     * @param array<int, true> $userIds
     */
    protected function validUserId($id, array $userIds): ?int
    {
        $id = (int) $id;
        if ($id === 0 || ! isset($userIds[$id])) {
            return null;
        }

        return $id;
    }

    /**
     * @param array<int, string> $userNames
     */
    protected function userName($id, array $userNames): ?string
    {
        $id = (int) $id;
        if ($id === 0 || ! isset($userNames[$id]) || $userNames[$id] === '') {
            return null;
        }

        return $userNames[$id];
    }
}
