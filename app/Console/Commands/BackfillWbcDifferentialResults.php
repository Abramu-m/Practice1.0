<?php

namespace App\Console\Commands;

use App\Models\InvestigationTemplateResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillWbcDifferentialResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investigations:backfill-wbc-differential-results
        {--dry-run : Report counts without writing any records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy {legacy_medcom_database}.procedures WBC/differential rows into investigation_template_results using the wbc_differential result template';

    /**
     * Fallback user id when the investigation has no resulted_by user.
     */
    private const FALLBACK_USER_ID = 23;

    /**
     * Legacy `procedures.perimeter` -> wbc_differential parameter name. Empty perimeter
     * is the single "Total WBC" value recorded before the differential breakdown
     * (neutrophil/lymphocyte/monocyte/eosinophil/basophil) was tracked.
     */
    private const PERIMETER_MAP = [
        '' => 'Total WBC',
        'wbctot' => 'Total WBC',
        'neutrophil' => 'Neutrophils %',
        'lymphocyte' => 'Lymphocytes %',
        'monocyte' => 'Monocytes %',
        'eosinophil' => 'Eosinophils %',
        'basophil' => 'Basophils %',
    ];

    /**
     * Full ordered parameter list matching resources/views/lab/result_templates/wbc_differential.blade.php,
     * keyed by parameter_name => [unit, normal_range]. Parameters with no legacy source
     * (absolute differential counts, band neutrophils, morphology) are migrated blank.
     */
    private const PARAMETERS = [
        'Total WBC' => ['×10³/µL', '4.0 – 11.0'],
        'Neutrophils' => ['×10³/µL', '1.8 – 7.7'],
        'Neutrophils %' => ['%', '40 – 75'],
        'Lymphocytes' => ['×10³/µL', '1.0 – 4.8'],
        'Lymphocytes %' => ['%', '20 – 45'],
        'Monocytes' => ['×10³/µL', '0.2 – 1.2'],
        'Monocytes %' => ['%', '2 – 10'],
        'Eosinophils' => ['×10³/µL', '0.0 – 0.7'],
        'Eosinophils %' => ['%', '1 – 6'],
        'Basophils' => ['×10³/µL', '0.0 – 0.1'],
        'Basophils %' => ['%', '0 – 1'],
        'Band Neutrophils %' => ['%', '0 – 5'],
        'WBC Morphology / Comment' => ['', 'Normal'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $template = DB::table('result_templates')->where('code', 'wbc_differential')->first();
        if (! $template) {
            $this->error('result_templates row with code=wbc_differential not found.');

            return self::FAILURE;
        }

        $medicalServiceIds = DB::table('medical_services')
            ->where('result_template_id', $template->id)
            ->pluck('id');

        if ($medicalServiceIds->isEmpty()) {
            $this->error('No medical_services rows use the wbc_differential result template.');

            return self::FAILURE;
        }

        $investigationIds = DB::table('investigations')
            ->whereIn('medical_service_id', $medicalServiceIds)
            ->pluck('id');

        $userIds = DB::table('users')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $userNames = DB::table('users')->get()->mapWithKeys(
            fn ($u) => [(int) $u->id => trim($u->first_name . ' ' . $u->last_name)]
        )->all();

        $readingsByInvestigation = DB::table(config('database.legacy_medcom_database') . '.procedures')
            ->whereIn('pl_id', $investigationIds)
            ->orderBy('pl_id')
            ->orderBy('pid')
            ->get()
            ->groupBy('pl_id');

        $stats = [
            'investigations_total' => $investigationIds->count(),
            'skipped_no_legacy_data' => 0,
            'updated' => 0,
            'created' => 0,
        ];

        foreach ($investigationIds as $investigationId) {
            $readings = $readingsByInvestigation->get($investigationId);

            if (! $readings || $readings->isEmpty()) {
                $stats['skipped_no_legacy_data']++;
                continue;
            }

            $investigation = DB::table('investigations')->where('id', $investigationId)->first();
            $formData = $this->buildFormData($readings, $investigation, $userNames);

            $exists = InvestigationTemplateResult::where('investigation_id', $investigationId)->exists();
            $exists ? $stats['updated']++ : $stats['created']++;

            if ($dryRun) {
                continue;
            }

            InvestigationTemplateResult::updateOrCreate(
                ['investigation_id' => $investigationId],
                [
                    'template_name' => $template->name,
                    'template_version' => '1.0',
                    'form_data' => $formData,
                    'form_status' => 'final',
                    'metadata' => [
                        'template_code' => 'wbc_differential',
                        'migrated_from' => 'procedures',
                        'legacy_ids' => $readings->pluck('pid')->values()->all(),
                        'form_fields_count' => count($formData['parameters']),
                    ],
                    'reported_by' => $this->validUserId($investigation->resulted_by, $userIds) ?? self::FALLBACK_USER_ID,
                    'reported_at' => $investigation->resulted_at ?? now(),
                ]
            );
        }

        $this->table(['Metric', 'Value'], [
            ['Investigations using wbc_differential', $stats['investigations_total']],
            ['Skipped (no legacy procedures data)', $stats['skipped_no_legacy_data']],
            ['Results ' . ($dryRun ? 'that would be updated' : 'updated'), $stats['updated']],
            ['Results ' . ($dryRun ? 'that would be created' : 'created'), $stats['created']],
        ]);

        if ($dryRun) {
            $this->comment('Dry run — no records were written.');
        }

        return self::SUCCESS;
    }

    /**
     * Build the form_data object matching resources/views/lab/result_templates/wbc_differential.blade.php,
     * a `parameters[]` array keyed by parameter_name plus a Quality Control block.
     *
     * @param \Illuminate\Support\Collection<int, object> $readings
     * @param array<int, string> $userNames
     */
    protected function buildFormData($readings, object $investigation, array $userNames): array
    {
        $values = [];

        foreach ($readings as $reading) {
            $paramName = self::PERIMETER_MAP[$reading->perimeter] ?? null;
            if ($paramName === null) {
                continue;
            }

            $value = $paramName === 'Total WBC'
                ? $this->normalizeWbcTotal($reading->preports)
                : $this->cleanPercentage($reading->preports);

            if ($value !== null) {
                $values[$paramName] = $value;
            }
        }

        $parameters = [];
        foreach (self::PARAMETERS as $name => [$unit, $range]) {
            $value = $values[$name] ?? null;
            $parameters[] = [
                'parameter_name' => $name,
                'value' => $value,
                'unit' => $unit,
                'normal_range' => $range,
                'status' => $value !== null ? $this->computeStatus($value, $range) : 'unknown',
                'remarks' => null,
            ];
        }

        return [
            'parameters' => $parameters,
            'analyzed_by' => $this->userName($investigation->resulted_by, $userNames),
            'analysis_date' => $investigation->resulted_at,
            'additional_comments' => null,
        ];
    }

    /**
     * Legacy "Total WBC" values are a mix of ×10³/µL (e.g. "15") and absolute counts
     * per µL (e.g. "7600"); normalize anything >= 1000 down to ×10³/µL.
     */
    protected function normalizeWbcTotal(string $raw): ?string
    {
        $value = trim($raw);
        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        $num = (float) $value;
        if ($num >= 1000) {
            $num /= 1000;
        }

        return rtrim(rtrim(sprintf('%.2f', $num), '0'), '.');
    }

    /**
     * Strips a trailing "%" and maps "NIL" (none detected) to "0".
     */
    protected function cleanPercentage(string $raw): ?string
    {
        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        if (strtoupper($value) === 'NIL') {
            return '0';
        }

        $value = trim(rtrim($value, '%'));

        return $value === '' ? null : $value;
    }

    /**
     * Mirrors the wbcCheckNumeric() JS in wbc_differential.blade.php: parses an
     * "lo – hi" normal range and flags low/high/critical (>30% outside range) values.
     */
    protected function computeStatus(string $value, string $range): string
    {
        if (! is_numeric($value)) {
            return 'unknown';
        }

        $val = (float) $value;
        $normalized = preg_replace('/[\x{2013}\x{2014}\x{2212}]/u', '-', $range);

        if (! preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*-\s*(-?\d+(?:\.\d+)?)\s*$/', $normalized, $m)) {
            return 'unknown';
        }

        $lo = (float) $m[1];
        $hi = (float) $m[2];
        $span = $hi - $lo;

        if ($val < $lo) {
            return ($span > 0 && $val < $lo - $span * 0.3) ? 'critical' : 'low';
        }

        if ($val > $hi) {
            return ($span > 0 && $val > $hi + $span * 0.3) ? 'critical' : 'high';
        }

        return 'normal';
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
