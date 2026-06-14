<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportReferralDirectoryFromMedcom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referrals:import-from-medcom
        {--dry-run : Report counts without writing any records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import referral hospitals from {legacy_medcom_database}.ref_hospital and the global referral department list from ref_department, preserving legacy IDs';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $legacyDb = config('database.legacy_medcom_database');

        $hospitals = DB::table("{$legacyDb}.ref_hospital")
            ->where('description', '!=', '')
            ->orderBy('hid')
            ->get();

        $departments = DB::table("{$legacyDb}.ref_department")
            ->orderBy('dept_id')
            ->get();

        $hospitalsCreated = 0;
        $hospitalsUpdated = 0;

        foreach ($hospitals as $hospital) {
            $values = [
                'name' => $hospital->description,
                'address' => $this->formatAddress($hospital->box, $hospital->city_region),
                'is_active' => $hospital->status ? 1 : 0,
                'updated_at' => now(),
            ];

            $exists = DB::table('referral_hospitals')->where('id', $hospital->hid)->exists();

            if ($exists) {
                $hospitalsUpdated++;
                if (! $dryRun) {
                    DB::table('referral_hospitals')->where('id', $hospital->hid)->update($values);
                }
            } else {
                $hospitalsCreated++;
                if (! $dryRun) {
                    DB::table('referral_hospitals')->insert($values + [
                        'id' => $hospital->hid,
                        'uuid' => (string) Str::uuid(),
                        'created_at' => now(),
                    ]);
                }
            }
        }

        $departmentsCreated = 0;
        $departmentsUpdated = 0;

        foreach ($departments as $department) {
            $values = [
                'name' => $department->description,
                'is_active' => $department->status ? 1 : 0,
                'updated_at' => now(),
            ];

            $exists = DB::table('referral_departments')->where('id', $department->dept_id)->exists();

            if ($exists) {
                $departmentsUpdated++;
                if (! $dryRun) {
                    DB::table('referral_departments')->where('id', $department->dept_id)->update($values);
                }
            } else {
                $departmentsCreated++;
                if (! $dryRun) {
                    DB::table('referral_departments')->insert($values + [
                        'id' => $department->dept_id,
                        'uuid' => (string) Str::uuid(),
                        'created_at' => now(),
                    ]);
                }
            }
        }

        $this->info("Referral hospitals: {$hospitalsCreated} created, {$hospitalsUpdated} updated.");
        $this->info("Referral departments: {$departmentsCreated} created, {$departmentsUpdated} updated.");

        if ($dryRun) {
            $this->comment('Dry run - no changes written.');
        }

        return self::SUCCESS;
    }

    /**
     * Combine the legacy `box` (P.O. Box number) and `city_region` columns into
     * a single human-readable address, e.g. "P.O. Box 65000, Dar es Salaam".
     */
    private function formatAddress(?string $box, ?string $cityRegion): ?string
    {
        $box = trim((string) $box);
        $cityRegion = trim((string) $cityRegion);

        if ($box !== '' && $cityRegion !== '') {
            return "P.O. Box {$box}, {$cityRegion}";
        }

        if ($cityRegion !== '') {
            return $cityRegion;
        }

        return $box !== '' ? "P.O. Box {$box}" : null;
    }
}
