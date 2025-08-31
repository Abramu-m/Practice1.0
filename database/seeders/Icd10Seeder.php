<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Icd10Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Expects CSV header: code,description,category,subcategory,notes
     */
    public function run()
    {
        $this->command->info('Starting ICD-10 import...');

        $csv = null;
        $url = env('ICD10_CSV_URL');

        if ($url) {
            $this->command->info("Downloading CSV from: {$url}");
            try {
                $res = Http::timeout(60)->get($url);
            } catch (\Exception $e) {
                $this->command->error('Failed to download CSV: ' . $e->getMessage());
                return;
            }

            if (!$res->successful()) {
                $this->command->error('Failed to download CSV, HTTP status: ' . $res->status());
                return;
            }

            $csv = $res->body();
        } else {
            $path = database_path('seeders/data/icd10.csv');
            if (!file_exists($path)) {
                $this->command->error('No local CSV found at database/seeders/data/icd10.csv and ICD10_CSV_URL is not set.');
                $this->command->info('Place a CSV there or set ICD10_CSV_URL in your .env');
                return;
            }

            $csv = file_get_contents($path);
        }

        if (empty($csv)) {
            $this->command->error('CSV content is empty.');
            return;
        }

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $csv);
        rewind($handle);

    $header = null;
        $batch = [];
        $inserted = 0;
        $chunkSize = 1000;

        while (($row = fgetcsv($handle)) !== false) {
            if (!$header) {
                $header = array_map(function ($h) {
                    return strtolower(trim($h));
                }, $row);
                continue;
            }

            $data = @array_combine($header, $row);
            if ($data === false || !is_array($data)) {
                continue;
            }

            $record = [
                'code' => $data['code'] ?? null,
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'subcategory' => $data['subcategory'] ?? null,
                'chapter' => $data['chapter'] ?? null,
                'is_active' => 1,
                'notes' => $data['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (empty($record['code']) || empty($record['description'])) {
                continue;
            }

            $batch[] = $record;

            if (count($batch) >= $chunkSize) {
                // Use insertOrIgnore if available to avoid unique key errors, fallback to insert inside try/catch
                if (method_exists(DB::table('icd_10'), 'insertOrIgnore')) {
                    DB::table('icd_10')->insertOrIgnore($batch);
                } else {
                    try {
                        DB::table('icd_10')->insert($batch);
                    } catch (\Exception $e) {
                        $this->command->error('Insert error: ' . $e->getMessage());
                    }
                }

                $inserted += count($batch);
                $this->command->info("Inserted {$inserted} records...");
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            if (method_exists(DB::table('icd_10'), 'insertOrIgnore')) {
                DB::table('icd_10')->insertOrIgnore($batch);
            } else {
                try {
                    DB::table('icd_10')->insert($batch);
                } catch (\Exception $e) {
                    $this->command->error('Insert error: ' . $e->getMessage());
                }
            }

            $inserted += count($batch);
        }
        $this->command->info("ICD-10 import complete. Total processed: {$inserted}");

        // Post-import: update existing rows' chapter where provided in CSV but not inserted due to duplicates
        // Create a fresh in-memory stream from the CSV content and iterate it
        $handle2 = fopen('php://memory', 'r+');
        fwrite($handle2, $csv);
        rewind($handle2);

        $header = null;
        $updates = 0;
        while (($row = fgetcsv($handle2)) !== false) {
            if (!$header) {
                $header = array_map(function ($h) {
                    return strtolower(trim($h));
                }, $row);
                continue;
            }

            $data = @array_combine($header, $row);
            if ($data === false || !is_array($data)) {
                continue;
            }

            $code = $data['code'] ?? null;
            $chapter = $data['chapter'] ?? null;
            if ($code && $chapter) {
                $affected = DB::table('icd_10')->where('code', $code)->update(['chapter' => $chapter, 'updated_at' => now()]);
                if ($affected) $updates += $affected;
            }
        }

        fclose($handle2);

        if ($updates > 0) {
            $this->command->info("Updated chapter for {$updates} existing records.");
        }
    }
}
