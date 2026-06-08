<?php

namespace App\Console\Commands;

use App\Models\LabCode;
use App\Models\MsdCode;
use Illuminate\Console\Command;

class ImportCodeLibrary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'codes:import {type : msd, loinc, or snomed} {file : path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import/upsert a clinical coding-standard library (MSD item codes or LOINC/SNOMED lab codes) from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = strtolower((string) $this->argument('type'));
        $path = $this->argument('file');

        if (! in_array($type, ['msd', 'loinc', 'snomed'], true)) {
            $this->error("Unknown type '{$type}'. Expected one of: msd, loinc, snomed.");
            return self::FAILURE;
        }

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("File not found or not readable: {$path}");
            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->error("Could not open file: {$path}");
            return self::FAILURE;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            $this->error('CSV file is empty.');
            fclose($handle);
            return self::FAILURE;
        }
        $header = array_map(fn ($column) => strtolower(trim((string) $column)), $header);

        $required = $type === 'msd' ? ['code', 'name'] : ['code', 'display_name'];
        $missing = array_diff($required, $header);
        if (! empty($missing)) {
            $this->error('CSV header is missing required column(s): ' . implode(', ', $missing));
            fclose($handle);
            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $record = array_combine($header, array_pad($row, count($header), null));
            $code = trim((string) ($record['code'] ?? ''));

            if ($code === '') {
                $skipped++;
                continue;
            }

            if ($type === 'msd') {
                $attributes = ['code' => $code];
                $values = [
                    'name' => trim((string) ($record['name'] ?? '')),
                    'unit' => $this->nullableValue($record['unit'] ?? null),
                    'category' => $this->nullableValue($record['category'] ?? null),
                ];

                $existed = MsdCode::where($attributes)->exists();
                MsdCode::updateOrCreate($attributes, $values);
            } else {
                $attributes = ['coding_system' => $type, 'code' => $code];
                $values = [
                    'display_name' => trim((string) ($record['display_name'] ?? '')),
                ];

                $existed = LabCode::where($attributes)->exists();
                LabCode::updateOrCreate($attributes, $values);
            }

            $existed ? $updated++ : $created++;
        }

        fclose($handle);

        $this->info("Import complete for '{$type}': {$created} created, {$updated} updated, {$skipped} skipped.");

        return self::SUCCESS;
    }

    private function nullableValue(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
