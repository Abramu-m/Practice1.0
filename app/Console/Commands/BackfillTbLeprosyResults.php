<?php

namespace App\Console\Commands;

use App\Models\InvestigationFormData;
use App\Models\InvestigationTemplateResult;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillTbLeprosyResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investigations:backfill-tb-leprosy-results
        {--dry-run : Report counts without writing any records}
        {--chunk=200 : Number of legacy rows to process per transaction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy {legacy_medcom_database}.tb_leprosy_form rows into investigation_template_results and investigation_form_data using the genxpert_tb result template';

    /**
     * Appearance select options on the genxpert_tb form, keyed by lowercased legacy value.
     */
    private const APPEARANCE_MAP = [
        'salivary' => 'Salivary',
        'mucous' => 'Mucoid',
        'purulent' => 'Purulent',
        'blood stained' => 'Blood-stained',
        'mucopurulent' => 'Mucopurulent',
    ];

    /**
     * Valid micro/skin result grades on the genxpert_tb form.
     */
    private const VALID_GRADES = ['neg', 'scanty', '1+', '2+', '3+'];

    /**
     * Legacy `xpert` codes -> genxpert_tb `xpert_result` radio values.
     */
    private const XPERT_RESULT_MAP = [
        'N' => 'negative',
        'T' => 'positive',
        'TI' => 'indeterminate',
        'RR' => 'rr',
    ];

    /**
     * Legacy `tb_lam` codes -> genxpert_tb `lflam_result` radio values (no RR option there).
     */
    private const LFLAM_RESULT_MAP = [
        'N' => 'negative',
        'T' => 'positive',
        'TI' => 'indeterminate',
    ];

    /**
     * Legacy `test_reason` -> genxpert_tb request `reason` radio values.
     */
    private const REASON_MAP = [
        'diagnosis' => 'diagnosis',
        'followup' => 'followup',
    ];

    /**
     * Legacy `diagnosis_option` -> genxpert_tb request `diagnosis_type` radio values.
     */
    private const DIAGNOSIS_TYPE_MAP = [
        'tb' => 'tb',
        'mdr' => 'mdr',
        'leprosy' => 'leprosy',
    ];

    /**
     * Legacy `hiv_status` -> genxpert_tb request `hiv_status` radio values.
     */
    private const HIV_STATUS_MAP = [
        'reactive' => 'reactive',
        'non reactive' => 'non_reactive',
        'unknown' => 'unknown',
    ];

    /**
     * Legacy `prev_tb_rx` -> genxpert_tb request `previous_tb` radio values.
     */
    private const PREVIOUS_TB_MAP = [
        'yes' => 'yes',
        'no' => 'no',
    ];

    /**
     * Legacy `specimen_type` -> genxpert_tb request `specimen` radio values.
     * Values with no matching option (e.g. "Stool", "Pleural fluid") fall back to "other_spec".
     */
    private const SPECIMEN_MAP = [
        'sputum' => 'sputum',
        'csf' => 'csf',
        'skin smear' => 'skin',
        'peritoneal fluid' => 'peritoneal',
        'lymph node' => 'lymph',
        'urine' => 'urine',
    ];

    /**
     * Legacy `requested_test` -> genxpert_tb request `test_requested[]` checkbox values.
     */
    private const TEST_REQUESTED_MAP = [
        'xpert mtb/rif' => 'xpert',
        'microscopy' => 'microscopy',
        'tb lf-lam' => 'tb_lf_lam',
    ];

    /**
     * Fallback user id when no requested/accepted/resulted-by user is valid.
     */
    private const FALLBACK_USER_ID = 23;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        $templateName = DB::table('result_templates')->where('code', 'genxpert_tb')->value('name');
        if (! $templateName) {
            $this->error('result_templates row with code=genxpert_tb not found.');

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
            'draft' => 0,
            'form_data_updated' => 0,
            'form_data_created' => 0,
            'form_data_skipped_empty' => 0,
        ];

        DB::table(config('database.legacy_medcom_database') . '.tb_leprosy_form')
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
                $existingFormDataIds = DB::table('investigation_form_data')
                    ->whereIn('investigation_id', $plIds)
                    ->pluck('investigation_id')
                    ->mapWithKeys(fn ($id) => [(int) $id => true])
                    ->all();

                DB::transaction(function () use ($rows, &$stats, $dryRun, $templateName, $userIds, $userNames, $existingInvestigationIds, $existingResultIds, $existingFormDataIds) {
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
                        $formStatus = $this->hasAnyResult($row) ? 'final' : 'draft';
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

                        $requestFormData = $this->buildRequestFormData($row);

                        if (! empty($requestFormData)) {
                            if (isset($existingFormDataIds[$investigationId])) {
                                $stats['form_data_updated']++;
                            } else {
                                $stats['form_data_created']++;
                            }
                        } else {
                            $stats['form_data_skipped_empty']++;
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
                                    'template_code' => 'genxpert_tb',
                                    'migrated_from' => 'tb_leprosy_form',
                                    'legacy_id' => (int) $row->id,
                                    'form_fields_count' => count($formData),
                                ],
                                'reported_by' => $reportedBy,
                                'reported_at' => $reportedAt,
                            ]
                        );

                        if (! empty($requestFormData)) {
                            InvestigationFormData::updateOrCreate(
                                ['investigation_id' => $investigationId],
                                ['form_data' => $requestFormData]
                            );
                        }
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
            ['  - draft', $stats['draft']],
            ['Form data ' . ($dryRun ? 'that would be updated' : 'updated'), $stats['form_data_updated']],
            ['Form data ' . ($dryRun ? 'that would be created' : 'created'), $stats['form_data_created']],
            ['Form data skipped (nothing to map)', $stats['form_data_skipped_empty']],
        ]);

        if ($dryRun) {
            $this->comment('Dry run — no records were written.');
        }

        return self::SUCCESS;
    }

    /**
     * Build the flat form_data object matching resources/views/lab/result_templates/genxpert_tb.blade.php.
     */
    protected function buildFormData(object $row, array $userNames): array
    {
        $receivedBy = $this->userName($row->acceptedby, $userNames);
        $receivedDate = $this->nullIfEmpty($row->acceptedon);
        $receivedTime = $this->timeHM($row->acceptedat);

        $examinedBy = $this->userName($row->resultedby, $userNames);
        $examinedDate = $this->nullIfEmpty($row->resultedon);
        $examinedTime = $this->timeHM($row->resultedat);

        $specimen = $this->nullIfEmpty($row->specimen_type);
        $micro = function (?string $afb, ?string $appearance) use ($specimen, $receivedDate, $receivedTime, $receivedBy) {
            $hasResult = ! empty($this->nullIfEmpty($afb));

            return [
                'date' => $hasResult ? $receivedDate : null,
                'time' => $hasResult ? $receivedTime : null,
                'specimen' => $hasResult ? $specimen : null,
                'received_by' => $hasResult ? $receivedBy : null,
                'appearance' => $this->mapAppearance($appearance),
                'result' => $this->mapGrade($afb),
            ];
        };

        $microA = $micro($row->afb_a, $row->appearance_a);
        $microB = $micro($row->afb_b, $row->appearance_b);

        return [
            'lab_serial_results' => $this->nullIfEmpty($row->lab_sn),
            'date_reception' => $receivedDate,
            'time_reception' => $receivedTime,
            'zn_fm' => strtoupper((string) $this->nullIfEmpty($row->staining_techinque)) === 'ZN' ? 'zn' : null,

            'micro_date_A' => $microA['date'],
            'micro_specimen_A' => $microA['specimen'],
            'micro_received_A' => $microA['received_by'],
            'micro_appearance_A' => $microA['appearance'],
            'micro_result_A' => $microA['result'],

            'micro_date_B' => $microB['date'],
            'micro_specimen_B' => $microB['specimen'],
            'micro_received_B' => $microB['received_by'],
            'micro_appearance_B' => $microB['appearance'],
            'micro_result_B' => $microB['result'],

            'micro_date_C' => null,
            'micro_specimen_C' => null,
            'micro_received_C' => null,
            'micro_appearance_C' => null,
            'micro_result_C' => null,

            'xpert_date' => $receivedDate,
            'xpert_received_by' => $receivedBy,
            'xpert_appearance' => $this->mapAppearance($row->appearance_xpert),
            'xpert_result' => $this->mapCode($row->xpert, self::XPERT_RESULT_MAP),

            'lflam_date' => $receivedDate,
            'lflam_received_by' => $receivedBy,
            'lflam_appearance' => $this->mapAppearance($row->appearance_tb_lam),
            'lflam_result' => $this->mapCode($row->tb_lam, self::LFLAM_RESULT_MAP),

            'skin_date_left_earlobe' => $receivedDate,
            'skin_received_left_earlobe' => $receivedBy,
            'skin_result_left_earlobe' => $this->mapGrade($row->elobe_l),

            'skin_date_right_earlobe' => $receivedDate,
            'skin_received_right_earlobe' => $receivedBy,
            'skin_result_right_earlobe' => $this->mapGrade($row->elobe_r),

            'skin_date_lesion_1' => $receivedDate,
            'skin_received_lesion_1' => $receivedBy,
            'skin_result_lesion_1' => $this->mapGrade($row->lesion1),

            'skin_date_lesion_2' => $receivedDate,
            'skin_received_lesion_2' => $receivedBy,
            'skin_result_lesion_2' => $this->mapGrade($row->lesion2),

            'examined_date' => $examinedDate,
            'examined_time' => $examinedTime,
            'examined_by' => $examinedBy,

            'reviewed_date' => $examinedDate,
            'reviewed_time' => $examinedTime,
            'reviewed_by' => $examinedBy,

            'verified_date' => null,
            'verified_time' => null,
            'verified_by' => null,

            'comments' => null,
        ];
    }

    /**
     * Build the request-side form_data for investigation_form_data, matching the
     * disabled "REQUEST SECTION" field names in resources/views/lab/result_templates/genxpert_tb.blade.php.
     * Null/empty values are omitted entirely (matching the shape of live-submitted records).
     */
    protected function buildRequestFormData(object $row): array
    {
        $data = [
            'date_collection' => $this->nullIfEmpty($row->collectedon),
            'time_collection' => $this->timeHM($row->collectedat),
            'area_leader' => $this->nullIfEmpty($row->area_leader),
            'tb_district_no' => $this->nullIfEmpty($row->tb_district_no),
            'lab_serial_no' => $this->nullIfEmpty($row->lab_sn),
            'reason' => $this->mapLowercase($row->test_reason, self::REASON_MAP),
            'diagnosis_type' => $this->mapLowercase($row->diagnosis_option, self::DIAGNOSIS_TYPE_MAP),
            'followup_month' => $this->nullIfEmpty($row->followup_month),
            'hiv_status' => $this->mapLowercase($row->hiv_status, self::HIV_STATUS_MAP),
            'previous_tb' => $this->mapLowercase($row->prev_tb_rx, self::PREVIOUS_TB_MAP),
            'specimen' => $this->mapSpecimen($row->specimen_type),
            'test_requested' => $this->mapTestRequested($row->requested_test),
        ];

        return array_filter($data, fn ($value) => $value !== null && $value !== []);
    }

    /**
     * Whether any actual result value was recorded in this legacy row.
     */
    protected function hasAnyResult(object $row): bool
    {
        foreach (['xpert', 'afb_a', 'afb_b', 'elobe_l', 'elobe_r', 'lesion1', 'lesion2', 'tb_lam'] as $field) {
            if (! empty($this->nullIfEmpty($row->$field))) {
                return true;
            }
        }

        return false;
    }

    protected function mapAppearance(?string $value): ?string
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return null;
        }

        return self::APPEARANCE_MAP[strtolower($value)] ?? 'Other';
    }

    protected function mapGrade(?string $value): ?string
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return null;
        }

        $value = strtolower($value);

        return in_array($value, self::VALID_GRADES, true) ? $value : null;
    }

    /**
     * @param array<string, string> $map
     */
    protected function mapCode(?string $value, array $map): ?string
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return null;
        }

        return $map[strtoupper($value)] ?? null;
    }

    /**
     * @param array<string, string> $map
     */
    protected function mapLowercase(?string $value, array $map): ?string
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return null;
        }

        return $map[strtolower($value)] ?? null;
    }

    /**
     * Maps legacy `specimen_type` to a genxpert_tb `specimen` radio value, falling back
     * to "other_spec" for legacy values with no matching option (e.g. Stool, Pleural fluid).
     */
    protected function mapSpecimen(?string $value): ?string
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return null;
        }

        return self::SPECIMEN_MAP[strtolower($value)] ?? 'other_spec';
    }

    /**
     * Maps legacy `requested_test` to a genxpert_tb `test_requested[]` checkbox array.
     *
     * @return array<int, string>
     */
    protected function mapTestRequested(?string $value): array
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return [];
        }

        $mapped = self::TEST_REQUESTED_MAP[strtolower($value)] ?? null;

        return $mapped ? [$mapped] : [];
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
