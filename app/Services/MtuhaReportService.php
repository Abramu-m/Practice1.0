<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class MtuhaReportService
{
    /**
     * Build a lightweight MTUHA report data structure for a given year/month.
     * Returns hospital info, and diagnosis groups with age/gender groups.
     */
    public function buildReport(int $year, int $month, bool $useCache = true): array
    {
    $cacheKey = "mtuha_report:{$year}:{$month}";
        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        // Prefer querying the hospital table when it exists; otherwise use config fallback.
        if (Schema::hasTable('hospital')) {
            $hospital = DB::table('hospital')->first();
        } else {
            // Build a minimal hospital object from config values in config/app.php
            $hospital = (object) [
                'description' => Config::get('app.clinic_name'),
                'mkoa' => Config::get('app.clinic_region'),
                'wilaya' => Config::get('app.clinic_district'),
                'kijiji' => Config::get('app.clinic_locale'),
                'phone' => Config::get('app.clinic_phone'),
                'email' => Config::get('app.clinic_email'),
                'address' => Config::get('app.clinic_address'),
            ];
        }

    // age groups in days
    $buckets = [
            ['min' => 0, 'max' => 30],
            ['min' => 31, 'max' => 365],
            ['min' => 366, 'max' => 1825],
            ['min' => 1826, 'max' => 21900],
            ['min' => 21901, 'max' => 36500],
        ];

        // General patient statistics for the month
        $visitDateCol = 'visit_date';
        $totalVisits = (int) DB::table('patient_visits')
            ->whereYear($visitDateCol, $year)
            ->whereMonth($visitDateCol, $month)
            ->count();

        $totalPatients = (int) DB::table('patient_visits')
            ->whereYear($visitDateCol, $year)
            ->whereMonth($visitDateCol, $month)
            ->distinct()
            ->count('patient');

        $malePatients = (int) DB::table('patient_visits as v')
            ->join('patients as p', 'p.id', '=', 'v.patient')
            ->whereYear('v.' . $visitDateCol, $year)
            ->whereMonth('v.' . $visitDateCol, $month)
            ->where('p.gender', 'male')
            ->distinct()
            ->count('v.patient');

        $femalePatients = (int) DB::table('patient_visits as v')
            ->join('patients as p', 'p.id', '=', 'v.patient')
            ->whereYear('v.' . $visitDateCol, $year)
            ->whereMonth('v.' . $visitDateCol, $month)
            ->where('p.gender', 'female')
            ->distinct()
            ->count('v.patient');

        $consultationsCount = (int) DB::table('consultations as c')
            ->join('patient_visits as v', 'v.id', '=', 'c.visit_id')
            ->whereYear('v.' . $visitDateCol, $year)
            ->whereMonth('v.' . $visitDateCol, $month)
            ->count();

        $diagnosesCount = (int) DB::table('icd_diagnoses as idg')
            ->join('consultations as c', 'c.id', '=', 'idg.consultation_id')
            ->join('patient_visits as v', 'v.id', '=', 'c.visit_id')
            ->whereYear('v.' . $visitDateCol, $year)
            ->whereMonth('v.' . $visitDateCol, $month)
            ->distinct()
            ->count('idg.id');

        // Totals per age bucket across all diagnoses (distinct patients)
        $bucketTotals = [];
    foreach ($buckets as $group) {
            $male = (int) DB::table('patient_visits as v')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->whereYear('v.' . $visitDateCol, $year)
                ->whereMonth('v.' . $visitDateCol, $month)
                ->where('p.gender', 'male')
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) > ?", [$group['min']])
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) <= ?", [$group['max']])
                ->distinct()
                ->count('v.patient');

            $female = (int) DB::table('patient_visits as v')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->whereYear('v.' . $visitDateCol, $year)
                ->whereMonth('v.' . $visitDateCol, $month)
                ->where('p.gender', 'female')
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) > ?", [$group['min']])
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) <= ?", [$group['max']])
                ->distinct()
                ->count('v.patient');

            $bucketTotals[] = ['male' => $male, 'female' => $female, 'both' => $male + $female];
        }

        // Also compute raw visit counts and consultation counts per bucket (not distinct patients)
        $bucketVisits = [];
        $bucketConsultations = [];
        foreach ($buckets as $group) {
            // visits
            $visits_male = (int) DB::table('patient_visits as v')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->whereYear('v.' . $visitDateCol, $year)
                ->whereMonth('v.' . $visitDateCol, $month)
                ->where('p.gender', 'male')
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) > ?", [$group['min']])
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) <= ?", [$group['max']])
                ->count();

            $visits_female = (int) DB::table('patient_visits as v')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->whereYear('v.' . $visitDateCol, $year)
                ->whereMonth('v.' . $visitDateCol, $month)
                ->where('p.gender', 'female')
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) > ?", [$group['min']])
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) <= ?", [$group['max']])
                ->count();

            $bucketVisits[] = ['male' => $visits_male, 'female' => $visits_female, 'both' => $visits_male + $visits_female];

            // consultations
            $consultations_male = (int) DB::table('consultations as c')
                ->join('patient_visits as v', 'v.id', '=', 'c.visit_id')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->whereYear('v.' . $visitDateCol, $year)
                ->whereMonth('v.' . $visitDateCol, $month)
                ->where('p.gender', 'male')
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) > ?", [$group['min']])
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) <= ?", [$group['max']])
                ->count();

            $consultations_female = (int) DB::table('consultations as c')
                ->join('patient_visits as v', 'v.id', '=', 'c.visit_id')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->whereYear('v.' . $visitDateCol, $year)
                ->whereMonth('v.' . $visitDateCol, $month)
                ->where('p.gender', 'female')
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) > ?", [$group['min']])
                ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) <= ?", [$group['max']])
                ->count();

            $bucketConsultations[] = ['male' => $consultations_male, 'female' => $consultations_female, 'both' => $consultations_male + $consultations_female];
        }

    // We'll fetch all diagnosis groups (mtuha_diagnoses). The view can filter/group as needed.
    $groups = DB::table('mtuha_diagnoses')->orderBy('id')->get();

        $diagnoses = [];
        foreach ($groups as $g) {
            $row = [
                'id' => $g->id,
                'description' => $g->description ?? $g->catname ?? $g->name ?? '',
                'buckets' => [],
                'totals' => ['male' => 0, 'female' => 0, 'both' => 0],
            ];

            foreach ($buckets as $group) {
                // Use explicit gender strings to match database values
                $male = $this->diagnoses('male', $year, $month, $g->id, $group['min'], $group['max']);
                $female = $this->diagnoses('female', $year, $month, $g->id, $group['min'], $group['max']);
                $row['buckets'][] = ['male' => $male, 'female' => $female, 'both' => $male + $female];
                $row['totals']['male'] += $male;
                $row['totals']['female'] += $female;
                $row['totals']['both'] += ($male + $female);
            }

            $diagnoses[] = $row;
        }

        $payload = [
            'hospital' => $hospital,
            'year' => $year,
            'month' => $month,
            'groups' => $groups,
            'diagnosisGroups' => $diagnoses,
        ];

        // General stats
        $payload['stats'] = [
            'total_visits' => $totalVisits,
            'total_patients' => $totalPatients,
            'male_patients' => $malePatients,
            'female_patients' => $femalePatients,
            'consultations' => $consultationsCount,
            'diagnoses' => $diagnosesCount,
            'age_bucket_totals' => $bucketTotals,
        ];

        // Row 1: patients seen for the first time in the year (their first visit in this year occurred in this month)
        // Use a derived table of first visits in the year to avoid HAVING with p.date_of_birth
        $firstVisitsInYear = DB::table(DB::raw("(SELECT v.patient, MIN(v.visit_date) AS first_visit FROM patient_visits v WHERE YEAR(v.visit_date) = {$year} GROUP BY v.patient) as fv"));

        $firstTimeThisYearMale = (int) DB::table(DB::raw("(SELECT v.patient, MIN(v.visit_date) AS first_visit FROM patient_visits v WHERE YEAR(v.visit_date) = {$year} GROUP BY v.patient) as fv"))
            ->join('patients as p', 'p.id', '=', 'fv.patient')
            ->whereRaw('MONTH(fv.first_visit) = ?', [$month])
            ->where('p.gender', 'male')
            ->count();

        $firstTimeThisYearFemale = (int) DB::table(DB::raw("(SELECT v.patient, MIN(v.visit_date) AS first_visit FROM patient_visits v WHERE YEAR(v.visit_date) = {$year} GROUP BY v.patient) as fv"))
            ->join('patients as p', 'p.id', '=', 'fv.patient')
            ->whereRaw('MONTH(fv.first_visit) = ?', [$month])
            ->where('p.gender', 'female')
            ->count();

        // Row 2: first visits at this facility for the problem (approximate: first-ever visit date equals this month)
        // First ever visit per patient (derived table) - first visit month/year equals selected
        $firstEverMale = (int) DB::table(DB::raw("(SELECT v.patient, MIN(v.visit_date) AS first_visit FROM patient_visits v GROUP BY v.patient) as fv"))
            ->join('patients as p', 'p.id', '=', 'fv.patient')
            ->whereRaw('MONTH(fv.first_visit) = ? AND YEAR(fv.first_visit) = ?', [$month, $year])
            ->where('p.gender', 'male')
            ->count();

        $firstEverFemale = (int) DB::table(DB::raw("(SELECT v.patient, MIN(v.visit_date) AS first_visit FROM patient_visits v GROUP BY v.patient) as fv"))
            ->join('patients as p', 'p.id', '=', 'fv.patient')
            ->whereRaw('MONTH(fv.first_visit) = ? AND YEAR(fv.first_visit) = ?', [$month, $year])
            ->where('p.gender', 'female')
            ->count();

    // Row 3: repeat visits — define as patients seen in the month who are NOT first-ever visits
    // Simpler: distinct patients seen in the month by gender minus those whose first-ever visit is in the same month/year
    $repeatMale = max(0, $malePatients - $firstEverMale);
    $repeatFemale = max(0, $femalePatients - $firstEverFemale);

        $payload['rows'] = [
            'row1' => ['male' => $firstTimeThisYearMale, 'female' => $firstTimeThisYearFemale, 'both' => $firstTimeThisYearMale + $firstTimeThisYearFemale],
            'row2' => ['male' => $firstEverMale, 'female' => $firstEverFemale, 'both' => $firstEverMale + $firstEverFemale],
            'row3' => ['male' => $repeatMale, 'female' => $repeatFemale, 'both' => $repeatMale + $repeatFemale],
        ];

        // Per-bucket breakdown for legacy rows
        $row1_groups = [];
        $row2_groups = [];
        $row3_groups = [];

    foreach ($buckets as $group) {
            $min = $group['min']; $max = $group['max'];

            // Row1: first visit in the year that happened in this month, age computed at that first visit
            // Derived table of first visits in the year
            $fvYearTable = DB::raw("(SELECT v.patient, MIN(v.{$visitDateCol}) AS first_visit FROM patient_visits v WHERE YEAR(v.{$visitDateCol}) = {$year} GROUP BY v.patient) as fv");
            $r1m = (int) DB::table($fvYearTable)
                ->join('patients as p', 'p.id', '=', 'fv.patient')
                ->whereRaw('MONTH(fv.first_visit) = ?', [$month])
                ->whereRaw('DATEDIFF(fv.first_visit, p.date_of_birth) > ? AND DATEDIFF(fv.first_visit, p.date_of_birth) <= ?', [$min, $max])
                ->where('p.gender', 'male')
                ->count();

            $r1f = (int) DB::table($fvYearTable)
                ->join('patients as p', 'p.id', '=', 'fv.patient')
                ->whereRaw('MONTH(fv.first_visit) = ?', [$month])
                ->whereRaw('DATEDIFF(fv.first_visit, p.date_of_birth) > ? AND DATEDIFF(fv.first_visit, p.date_of_birth) <= ?', [$min, $max])
                ->where('p.gender', 'female')
                ->count();

            $row1_groups[] = ['male' => $r1m, 'female' => $r1f, 'both' => $r1m + $r1f];

            // Row2: first ever visit (first_visit month/year equals current)
            // Derived table of first ever visits
            $fvEverTable = DB::raw("(SELECT v.patient, MIN(v.{$visitDateCol}) AS first_visit FROM patient_visits v GROUP BY v.patient) as fv");
            $r2m = (int) DB::table($fvEverTable)
                ->join('patients as p', 'p.id', '=', 'fv.patient')
                ->whereRaw('MONTH(fv.first_visit) = ? AND YEAR(fv.first_visit) = ?', [$month, $year])
                ->whereRaw('DATEDIFF(fv.first_visit, p.date_of_birth) > ? AND DATEDIFF(fv.first_visit, p.date_of_birth) <= ?', [$min, $max])
                ->where('p.gender', 'male')
                ->count();

            $r2f = (int) DB::table($fvEverTable)
                ->join('patients as p', 'p.id', '=', 'fv.patient')
                ->whereRaw('MONTH(fv.first_visit) = ? AND YEAR(fv.first_visit) = ?', [$month, $year])
                ->whereRaw('DATEDIFF(fv.first_visit, p.date_of_birth) > ? AND DATEDIFF(fv.first_visit, p.date_of_birth) <= ?', [$min, $max])
                ->where('p.gender', 'female')
                ->count();

            $row2_groups[] = ['male' => $r2m, 'female' => $r2f, 'both' => $r2m + $r2f];

            // Row3 per-bucket: patients seen in this month in the bucket minus those whose first-ever visit falls in this same month/year and bucket
            // Count distinct patients seen in the month in this age bucket by gender
            $seenM = (int) DB::table('patient_visits as v')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->whereYear('v.' . $visitDateCol, $year)
                ->whereMonth('v.' . $visitDateCol, $month)
                ->where('p.gender', 'male')
                ->whereRaw('DATEDIFF(v.' . $visitDateCol . ', p.date_of_birth) > ? AND DATEDIFF(v.' . $visitDateCol . ', p.date_of_birth) <= ?', [$min, $max])
                ->distinct()
                ->count('v.patient');

            $seenF = (int) DB::table('patient_visits as v')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->whereYear('v.' . $visitDateCol, $year)
                ->whereMonth('v.' . $visitDateCol, $month)
                ->where('p.gender', 'female')
                ->whereRaw('DATEDIFF(v.' . $visitDateCol . ', p.date_of_birth) > ? AND DATEDIFF(v.' . $visitDateCol . ', p.date_of_birth) <= ?', [$min, $max])
                ->distinct()
                ->count('v.patient');

            // Count patients whose first-ever visit is in this month/year and falls in this bucket
            $firstEverMInBucket = (int) DB::table(DB::raw("(SELECT v.patient, MIN(v.{$visitDateCol}) AS first_visit FROM patient_visits v GROUP BY v.patient) as fv"))
                ->join('patients as p', 'p.id', '=', 'fv.patient')
                ->whereRaw('MONTH(fv.first_visit) = ? AND YEAR(fv.first_visit) = ?', [$month, $year])
                ->where('p.gender', 'male')
                ->whereRaw('DATEDIFF(fv.first_visit, p.date_of_birth) > ? AND DATEDIFF(fv.first_visit, p.date_of_birth) <= ?', [$min, $max])
                ->count();

            $firstEverFInBucket = (int) DB::table(DB::raw("(SELECT v.patient, MIN(v.{$visitDateCol}) AS first_visit FROM patient_visits v GROUP BY v.patient) as fv"))
                ->join('patients as p', 'p.id', '=', 'fv.patient')
                ->whereRaw('MONTH(fv.first_visit) = ? AND YEAR(fv.first_visit) = ?', [$month, $year])
                ->where('p.gender', 'female')
                ->whereRaw('DATEDIFF(fv.first_visit, p.date_of_birth) > ? AND DATEDIFF(fv.first_visit, p.date_of_birth) <= ?', [$min, $max])
                ->count();

            $repM = max(0, $seenM - $firstEverMInBucket);
            $repF = max(0, $seenF - $firstEverFInBucket);

            $row3_groups[] = ['male' => $repM, 'female' => $repF, 'both' => $repM + $repF];
        }

        $payload['rows']['row1_groups'] = $row1_groups;
        $payload['rows']['row2_groups'] = $row2_groups;
        $payload['rows']['row3_groups'] = $row3_groups;

    // Include the raw per-bucket visit/consultation counts so views can approximate legacy rows
    $payload['bucketVisits'] = $bucketVisits;
    $payload['bucketConsultations'] = $bucketConsultations;

        // Load section definitions from config if available, otherwise fall back to built-in defs
        $sectionDefs = config('mtuha.sections');
        if (empty($sectionDefs) || !is_array($sectionDefs)) {
            $sectionDefs = [
                ['index' => null, 'title' => 'Diagnosis za OPD', 'ranges' => []],
                ['index' => 'I', 'title' => 'Infections and Parasitic diseases', 'ranges' => [['min' => 1, 'max' => 13], ['min' => 15, 'max' => 20]]],
                ['index' => 'II', 'title' => 'Neoplasms', 'ranges' => [['min' => 21, 'max' => 21]]],
                ['index' => 'III', 'title' => 'Diseases of Blood and blood forming Organs', 'ranges' => [['min' => 22, 'max' => 26]]],
                ['index' => 'IV', 'title' => 'Endocrine, Nutritional and Metabolic Diseases', 'ranges' => [['min' => 27, 'max' => 35]]],
                ['index' => 'V', 'title' => 'Mental and Behavioral Disorders', 'ranges' => [['min' => 36, 'max' => 40]]],
                ['index' => 'VI', 'title' => 'Diseases of the Nervous System', 'ranges' => [['min' => 41, 'max' => 42]]],
                ['index' => 'VII', 'title' => 'Diseases of the Eye', 'ranges' => [['min' => 43, 'max' => 46]]],
                ['index' => 'VIII', 'title' => 'Diseases of the Ear and Mastoid Process', 'ranges' => [['min' => 47, 'max' => 49]]],
                ['index' => 'IX', 'title' => 'Diseases of the Circulatory System', 'ranges' => [['min' => 50, 'max' => 52]]],
                ['index' => 'X', 'title' => 'Diseases of the Respiratory System', 'ranges' => [['min' => 53, 'max' => 57]]],
                ['index' => 'XI', 'title' => 'Diseases of the Digestive System', 'ranges' => [['min' => 58, 'max' => 67]]],
                ['index' => 'XII', 'title' => 'Diseases of the Skin and Subcutaneous Tissue', 'ranges' => [['min' => 68, 'max' => 72]]],
                ['index' => 'XIII', 'title' => 'Diseases of the Musculoskeletal System and Connective Tissue', 'ranges' => [['min' => 73, 'max' => 78]]],
                ['index' => 'XIV', 'title' => 'Diseases of the Genitourinary System and Pelvic Infalammatory diseases', 'ranges' => [['min' => 79, 'max' => 87]]],
                ['index' => 'XV', 'title' => 'Pregnancy, Childbirth and the Puerperium', 'ranges' => [['min' => 88, 'max' => 95]]],
                ['index' => 'XVI', 'title' => 'Certain Conditions Originating in the Perinatal Period', 'ranges' => [['min' => 96, 'max' => 99]]],
                ['index' => 'XVII', 'title' => 'Congenital Malformations, Deformations and Chromosomal Abnormalities', 'ranges' => [['min' => 100, 'max' => 101]]],
                ['index' => 'XVIII', 'title' => 'Symptoms, Signs and Abnormal Clinical and Laboratory Findings, Not Elsewhere Classified', 'ranges' => [['min' => 0, 'max' => 0]]],
                ['index' => 'XIX', 'title' => 'Injury, Poisoning and Certain Other Consequences of External Causes', 'ranges' => [['min' => 102, 'max' => 111]]],
                ['index' => 'XX', 'title' => 'External Causes of Morbidity and Mortality', 'ranges' => [['min' => 112, 'max' => 115]]],
                ['index' => null, 'title' => 'Matokeo', 'ranges' => [['min' => 116, 'max' => 117]]],
                ['index' => null, 'title' => 'Ugharamiaji wa Matibabu', 'ranges' => [['min' => 118, 'max' => 122]]],
            ];
        }

        // Build a lookup of diagnoses by id for quick grouping
        $diagnosesById = [];
        foreach ($diagnoses as $d) {
            $diagnosesById[(int)$d['id']] = $d;
        }

        $sections = [];
        $assignedIds = [];

        foreach ($sectionDefs as $sd) {
            $sec = ['index' => $sd['index'], 'title' => $sd['title'], 'diagnoses' => []];
            foreach ($sd['ranges'] as $r) {
                $min = $r['min'];
                $max = $r['max'];
                for ($id = $min; $id <= $max; $id++) {
                    if (isset($diagnosesById[$id])) {
                        $sec['diagnoses'][] = $diagnosesById[$id];
                        $assignedIds[] = $id;
                    }
                }
            }
            $sections[] = $sec;
        }

        // Any diagnoses not assigned to a section go into an "Other" section
        $other = ['index' => null, 'title' => 'Other', 'diagnoses' => []];
        foreach ($diagnoses as $d) {
            if (!in_array((int)$d['id'], $assignedIds, true)) {
                $other['diagnoses'][] = $d;
            }
        }
        if (!empty($other['diagnoses'])) {
            $sections[] = $other;
        }

        $payload['sections'] = $sections;

        if ($useCache) {
            // Cache for 30 minutes
            Cache::put($cacheKey, $payload, now()->addMinutes(30));
        }

        return $payload;
    }

    /**
     * Count diagnoses for a group/category
     * Returns integer count.
     */
    public function diagnoses(string $gender, int $year, int $month, int $category, int $date1, int $date2): int
    {
        // Helper: choose date column on patient_visits
        $visitDateCol = 'visit_date';

                $qd = \App\Models\IcdDiagnosis::query()
                    ->selectRaw('icd_diagnoses.id')
                    ->join('consultations as c', 'c.id', '=', 'icd_diagnoses.consultation_id')
                    ->join('patient_visits as v', 'v.id', '=', 'c.visit_id')
                    ->join('patients as p', 'p.id', '=', 'v.patient')
                    ->join('icd_10 as icd', 'icd.code', '=', 'icd_diagnoses.icd_code')
                    ->whereYear('v.' . $visitDateCol, $year)
                    ->whereMonth('v.' . $visitDateCol, $month)
                    ->where('p.gender', $gender)
                    ->where('icd_diagnoses.type', 'final')
                    ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) > ?", [$date1])
                    ->whereRaw("DATEDIFF(v.{$visitDateCol}, p.date_of_birth) <= ?", [$date2])
                    ->where('icd.mtuha_diagnosis', $category);

                $count = (int) $qd->distinct()->count('icd_diagnoses.id');

                return $count;
    }

    /**
     * Convenience: format month name
     */
    public function monthName(int $month): string
    {
        return date('F', mktime(0, 0, 0, $month, 10));
    }
}
