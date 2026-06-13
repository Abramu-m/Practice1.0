<?php

namespace App\Console\Commands;

use App\Models\InvestigationTemplateResult;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillBedrestObservation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investigations:backfill-bedrest-observation
        {--dry-run : Report counts without writing any records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy {legacy_medcom_database}.bedrest_observation rows into investigation_template_results using the vital_observations result template';

    /**
     * Fallback user id when no recorded-by/resulted-by user is valid.
     */
    private const FALLBACK_USER_ID = 23;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $templateName = DB::table('result_templates')->where('code', 'vital_observations')->value('name');
        if (! $templateName) {
            $this->error('result_templates row with code=vital_observations not found.');

            return self::FAILURE;
        }

        $userIds = DB::table('users')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $userNames = DB::table('users')->get()->mapWithKeys(
            fn ($u) => [(int) $u->id => trim($u->first_name . ' ' . $u->last_name)]
        )->all();

        $rows = DB::table(config('database.legacy_medcom_database') . '.bedrest_observation')
            ->orderBy('pl_id')
            ->orderBy('time')
            ->orderBy('id')
            ->get()
            ->groupBy('pl_id');

        $stats = [
            'investigations_total' => 0,
            'skipped_no_pl_id' => 0,
            'skipped_no_investigation' => 0,
            'updated' => 0,
            'created' => 0,
        ];

        foreach ($rows as $plId => $readings) {
            $stats['investigations_total']++;

            if (empty($plId)) {
                $stats['skipped_no_pl_id']++;
                continue;
            }

            $investigationId = (int) $plId;
            $investigation = DB::table('investigations')->where('id', $investigationId)->first();

            if (! $investigation) {
                $stats['skipped_no_investigation']++;
                continue;
            }

            $latest = $readings->last();

            $formData = $this->buildFormData($latest, $readings, $userNames);

            $reportedBy = $this->validUserId($investigation->resulted_by, $userIds)
                ?? $this->validUserId($latest->cby, $userIds)
                ?? self::FALLBACK_USER_ID;

            $reportedAt = $this->nullIfEmpty($investigation->resulted_at)
                ?? $this->combineDateTime($latest->con, $latest->cat)
                ?? now();

            $exists = InvestigationTemplateResult::where('investigation_id', $investigationId)->exists();
            $exists ? $stats['updated']++ : $stats['created']++;

            if ($dryRun) {
                continue;
            }

            InvestigationTemplateResult::updateOrCreate(
                ['investigation_id' => $investigationId],
                [
                    'template_name' => $templateName,
                    'template_version' => '1.0',
                    'form_data' => $formData,
                    'form_status' => 'final',
                    'metadata' => [
                        'template_code' => 'vital_observations',
                        'migrated_from' => 'bedrest_observation',
                        'legacy_ids' => $readings->pluck('id')->values()->all(),
                        'reading_count' => $readings->count(),
                        'form_fields_count' => count($formData),
                    ],
                    'reported_by' => $reportedBy,
                    'reported_at' => $reportedAt,
                ]
            );
        }

        $this->table(['Metric', 'Value'], [
            ['Distinct investigations in bedrest_observation', $stats['investigations_total']],
            ['Skipped (no pl_id)', $stats['skipped_no_pl_id']],
            ['Skipped (no matching investigation)', $stats['skipped_no_investigation']],
            ['Results ' . ($dryRun ? 'that would be updated' : 'updated'), $stats['updated']],
            ['Results ' . ($dryRun ? 'that would be created' : 'created'), $stats['created']],
        ]);

        if ($dryRun) {
            $this->comment('Dry run — no records were written.');
        }

        return self::SUCCESS;
    }

    /**
     * Build the flat form_data object matching resources/views/lab/result_templates/vital_observations.blade.php.
     * Uses the latest reading for the vitals snapshot and merges all readings' timestamped
     * comments into nursing_notes.
     *
     * @param object $latest
     * @param \Illuminate\Support\Collection<int, object> $readings
     * @param array<int, string> $userNames
     */
    protected function buildFormData(object $latest, $readings, array $userNames): array
    {
        return [
            'systolic_bp' => $this->nullIfEmpty($latest->systolic),
            'diastolic_bp' => $this->nullIfEmpty($latest->diastolic),
            'heart_rate' => $this->nullIfEmpty($latest->pulse_rate),
            'respiratory_rate' => $this->nullIfEmpty($latest->resp_rate),
            'temperature' => $this->nullIfEmpty($latest->temp),
            'temperature_site' => null,
            'oxygen_saturation' => $this->nullIfEmpty($latest->spo2),
            'oxygen_delivery' => null,
            'weight' => $this->nullIfEmpty($latest->weight),
            'height' => null,
            'bmi' => null,
            'bmi_category' => null,
            'dehydration' => $this->mapDehydration($latest->dehydration),
            'fluid_input' => $this->nullIfEmpty($latest->fluid_input),
            'fluid_output' => $this->nullIfEmpty($latest->fluid_output),
            'blood_sugar' => $this->nullIfEmpty($latest->blood_sugar),
            'urine_ketones' => $this->mapUrineKetones($latest->urine_ketones),
            'pain_scale' => null,
            'pain_location' => null,
            'pain_character' => null,
            'consciousness_level' => null,
            'pupils_reaction' => null,
            'motor_response' => null,
            'nursing_notes' => $this->buildNursingNotes($readings),
            'observation_time' => $this->resolveObservationTime($latest),
            'observer_name' => $this->userName($latest->cby, $userNames),
        ];
    }

    /**
     * Joins each reading's comment with its recorded timestamp, oldest first.
     *
     * @param \Illuminate\Support\Collection<int, object> $readings
     */
    protected function buildNursingNotes($readings): ?string
    {
        $lines = [];

        foreach ($readings as $reading) {
            $comment = $this->nullIfEmpty($reading->comments);
            if ($comment === null) {
                continue;
            }

            $timestamp = $this->resolveObservationTime($reading);
            $lines[] = $timestamp !== null
                ? '[' . Carbon::parse($timestamp)->format('Y-m-d H:i') . '] ' . $comment
                : $comment;
        }

        return $lines === [] ? null : implode("\n", $lines);
    }

    /**
     * The legacy `time` column uses a 1970-01-01 epoch placeholder when no observation
     * time was recorded; fall back to the `con`+`cat` creation timestamp in that case.
     */
    protected function resolveObservationTime(object $reading): ?string
    {
        $time = $this->nullIfEmpty($reading->time);
        if ($time !== null && ! str_starts_with($time, '1970-01-01')) {
            return $time;
        }

        $combined = $this->combineDateTime($reading->con, $reading->cat);

        return $combined?->format('Y-m-d H:i:s');
    }

    /**
     * Maps legacy `dehydration` ("No"/"Mild"/"Moderate"/"Severe"/blank) to the
     * vital_observations `dehydration` select options.
     */
    protected function mapDehydration(?string $value): ?string
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return null;
        }

        return match (strtolower($value)) {
            'no', 'none' => 'none',
            'mild' => 'mild',
            'moderate' => 'moderate',
            'severe' => 'severe',
            default => null,
        };
    }

    /**
     * Maps legacy `urine_ketones` free text to the vital_observations `urine_ketones`
     * select options (Negative/Trace/1+/2+/3+/4+).
     */
    protected function mapUrineKetones(?string $value): ?string
    {
        $value = $this->nullIfEmpty($value);
        if ($value === null) {
            return null;
        }

        $map = [
            'negative' => 'Negative',
            'neg' => 'Negative',
            'trace' => 'Trace',
            '1+' => '1+',
            '2+' => '2+',
            '3+' => '3+',
            '4+' => '4+',
        ];

        return $map[strtolower(trim($value))] ?? null;
    }

    protected function nullIfEmpty(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
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
