<?php

namespace App\Services;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MtuhaReportService
{
    /**
     * Age buckets in days, matching the columns of the official MTUHA monthly form.
     */
    private const AGE_BUCKETS = [
        ['min' => 0, 'max' => 30],
        ['min' => 31, 'max' => 365],
        ['min' => 366, 'max' => 1825],
        ['min' => 1826, 'max' => 21900],
        ['min' => 21901, 'max' => 36500],
    ];

    /**
     * Build a lightweight MTUHA report data structure for a given year/month.
     * Returns diagnosis groups with age/gender breakdowns plus the legacy
     * attendance rows (first-time-this-year / new-at-facility / repeat).
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

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->addMonthNoOverflow();
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = $yearStart->copy()->addYear();

        $visitDateCol = 'visit_date';
        $buckets = self::AGE_BUCKETS;
        $bucketCount = count($buckets);

        $visitAgeBucket = $this->ageBucketCase("DATEDIFF(v.{$visitDateCol}, p.date_of_birth)", $buckets);
        $firstVisitAgeBucket = $this->ageBucketCase('DATEDIFF(fv.first_visit, p.date_of_birth)', $buckets);

        // Diagnosis category definitions (mtuha_diagnoses)
        $groups = DB::table('mtuha_diagnoses')->orderBy('id')->get();

        // Diagnosis breakdown: a single grouped query covering every
        // (category, gender, age bucket) combination at once, instead of
        // running diagnoses() once per category/bucket/gender (was 1,200 queries).
        $diagnosisCounts = DB::table('icd_diagnoses')
            ->join('consultations as c', 'c.id', '=', 'icd_diagnoses.consultation_id')
            ->join('patient_visits as v', 'v.id', '=', 'c.visit_id')
            ->join('patients as p', 'p.id', '=', 'v.patient')
            ->join('icd_10 as icd', 'icd.code', '=', 'icd_diagnoses.icd_code')
            ->whereNotNull('icd.mtuha_diagnosis')
            ->where('icd_diagnoses.type', 'final')
            ->where('v.' . $visitDateCol, '>=', $monthStart)
            ->where('v.' . $visitDateCol, '<', $monthEnd)
            ->selectRaw("icd.mtuha_diagnosis as category_id, p.gender as gender, {$visitAgeBucket} as bucket, COUNT(DISTINCT icd_diagnoses.id) as total")
            ->groupBy('category_id', 'gender', 'bucket')
            ->get();

        $diagnosisLookup = [];
        foreach ($diagnosisCounts as $row) {
            if ($row->bucket === null) {
                continue;
            }
            $diagnosisLookup[(int) $row->category_id][(int) $row->bucket][$row->gender] = (int) $row->total;
        }

        // "Ugharamiaji wa Matibabu" (rows 118-122) classifies patients by how
        // they paid — a different axis than ICD-diagnosis counts, so it's
        // computed from patient_visits/patient_categories and merged into the
        // same [category][bucket][gender] lookup the assembly loop below reads
        // from. CHF (119) and Msamaha/exemption (122) have no reliable signal
        // in the data, so the CASE simply never matches them — they stay zero.
        $financingCategory = "CASE
            WHEN pc.id = 2 THEN 118
            WHEN pc.type = 'insurance' THEN 120
            WHEN pc.type = 'cash' THEN 121
            ELSE NULL
        END";

        $financingCounts = DB::table('patient_visits as v')
            ->join('patients as p', 'p.id', '=', 'v.patient')
            ->join('patient_categories as pc', 'pc.id', '=', 'v.visit_category')
            ->where('v.' . $visitDateCol, '>=', $monthStart)
            ->where('v.' . $visitDateCol, '<', $monthEnd)
            ->selectRaw("{$financingCategory} as category_id, p.gender as gender, {$visitAgeBucket} as bucket, COUNT(DISTINCT v.id) as total")
            ->groupBy('category_id', 'gender', 'bucket')
            ->get();

        foreach ($financingCounts as $row) {
            if ($row->category_id === null || $row->bucket === null) {
                continue;
            }
            $diagnosisLookup[(int) $row->category_id][(int) $row->bucket][$row->gender] = (int) $row->total;
        }

        $diagnoses = [];
        foreach ($groups as $g) {
            $row = [
                'id' => $g->id,
                'description' => $g->description ?? $g->catname ?? $g->name ?? '',
                'buckets' => [],
                'totals' => ['male' => 0, 'female' => 0, 'both' => 0],
            ];

            for ($i = 0; $i < $bucketCount; $i++) {
                $male = $diagnosisLookup[$g->id][$i]['male'] ?? 0;
                $female = $diagnosisLookup[$g->id][$i]['female'] ?? 0;
                $row['buckets'][] = ['male' => $male, 'female' => $female, 'both' => $male + $female];
                $row['totals']['male'] += $male;
                $row['totals']['female'] += $female;
                $row['totals']['both'] += $male + $female;
            }

            $diagnoses[] = $row;
        }

        $payload = [
            'year' => $year,
            'month' => $month,
            'diagnosisGroups' => $diagnoses,
        ];

        // Row 1: patients whose first visit *in the year* fell in this month,
        // bucketed by their age at that visit.
        $firstVisitInYear = DB::table('patient_visits as v')
            ->select('v.patient', DB::raw("MIN(v.{$visitDateCol}) as first_visit"))
            ->where('v.' . $visitDateCol, '>=', $yearStart)
            ->where('v.' . $visitDateCol, '<', $yearEnd)
            ->groupBy('v.patient');

        $row1Groups = $this->genderBucketGrid(
            DB::query()->fromSub($firstVisitInYear, 'fv')
                ->join('patients as p', 'p.id', '=', 'fv.patient')
                ->where('fv.first_visit', '>=', $monthStart)
                ->where('fv.first_visit', '<', $monthEnd)
                ->selectRaw("p.gender as gender, {$firstVisitAgeBucket} as bucket, COUNT(*) as total")
                ->groupBy('gender', 'bucket')
                ->get(),
            $bucketCount
        );

        // Row 2: patients whose first-EVER visit (at this facility) fell in
        // this month/year, bucketed by their age at that visit.
        $firstVisitEver = DB::table('patient_visits as v')
            ->select('v.patient', DB::raw("MIN(v.{$visitDateCol}) as first_visit"))
            ->groupBy('v.patient');

        $row2Groups = $this->genderBucketGrid(
            DB::query()->fromSub($firstVisitEver, 'fv')
                ->join('patients as p', 'p.id', '=', 'fv.patient')
                ->where('fv.first_visit', '>=', $monthStart)
                ->where('fv.first_visit', '<', $monthEnd)
                ->selectRaw("p.gender as gender, {$firstVisitAgeBucket} as bucket, COUNT(*) as total")
                ->groupBy('gender', 'bucket')
                ->get(),
            $bucketCount
        );

        // Row 3: repeat attendances = everyone seen this month minus those
        // whose first-ever visit falls in this month — and "first-ever visit
        // this month, by age bucket and gender" is exactly row2_groups, so we
        // derive row 3 from it rather than running a third derived-table pass.
        $seenGroups = $this->genderBucketGrid(
            DB::table('patient_visits as v')
                ->join('patients as p', 'p.id', '=', 'v.patient')
                ->where('v.' . $visitDateCol, '>=', $monthStart)
                ->where('v.' . $visitDateCol, '<', $monthEnd)
                ->selectRaw("p.gender as gender, {$visitAgeBucket} as bucket, COUNT(DISTINCT v.patient) as total")
                ->groupBy('gender', 'bucket')
                ->get(),
            $bucketCount
        );

        $row3Groups = [];
        for ($i = 0; $i < $bucketCount; $i++) {
            $male = max(0, $seenGroups[$i]['male'] - $row2Groups[$i]['male']);
            $female = max(0, $seenGroups[$i]['female'] - $row2Groups[$i]['female']);
            $row3Groups[] = ['male' => $male, 'female' => $female, 'both' => $male + $female];
        }

        $payload['rows'] = [
            'row1_groups' => $row1Groups,
            'row2_groups' => $row2Groups,
            'row3_groups' => $row3Groups,
        ];

        // Official-form row 14 ("Malaria") is a lab-classification breakdown,
        // not an ICD-diagnosis-category count — computed separately and
        // spliced into Section I below (see buildMalariaRows()).
        $malariaRows = $this->buildMalariaRows($monthStart, $monthEnd, $buckets, $bucketCount);

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
            $diagnosesById[(int) $d['id']] = $d;
        }

        $sections = [];
        $assignedIds = [];

        foreach ($sectionDefs as $sd) {
            $sec = ['index' => $sd['index'], 'title' => $sd['title'], 'diagnoses' => []];
            foreach ($sd['ranges'] as $r) {
                for ($id = $r['min']; $id <= $r['max']; $id++) {
                    if (isset($diagnosesById[$id])) {
                        $sec['diagnoses'][] = $diagnosesById[$id];
                        $assignedIds[] = $id;
                    }
                }
            }
            $sections[] = $sec;
        }

        // Splice the malaria breakdown into Section I as row 14, immediately
        // before category 15 — exactly where the official form places it —
        // and mark id 14 as assigned so the unused "Category 14" placeholder
        // from mtuha_diagnoses doesn't also show up in the "Other" section.
        foreach ($sections as &$sec) {
            if ($sec['index'] === 'I') {
                $insertAt = count($sec['diagnoses']);
                foreach ($sec['diagnoses'] as $idx => $d) {
                    if ((int) $d['id'] === 15) {
                        $insertAt = $idx;
                        break;
                    }
                }
                array_splice($sec['diagnoses'], $insertAt, 0, $malariaRows);
                break;
            }
        }
        unset($sec);
        $assignedIds[] = 14;

        // Any diagnoses not assigned to a section go into an "Other" section
        $other = ['index' => null, 'title' => 'Other', 'diagnoses' => []];
        foreach ($diagnoses as $d) {
            if (!in_array((int) $d['id'], $assignedIds, true)) {
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
     * Build the 4 official-form sub-rows for "Malaria" (row 14): lab-confirmed
     * cases (blood slide / mRDT positive) classified the same way
     * MalariaVipimoReportService does, plus two placeholders ("clinical [No
     * Test]" and "Cases (Referral in)") whose classification — diagnosis
     * without a matching investigation, and patient_referrals lookups — is
     * deferred, so they report zero for now.
     */
    private function buildMalariaRows(Carbon $monthStart, Carbon $monthEnd, array $buckets, int $bucketCount): array
    {
        $investigationAgeBucket = $this->ageBucketCase('DATEDIFF(inv.ordered_at, p.date_of_birth)', $buckets);

        $bsId = (int) SystemSetting::get('malaria_bs_service_id', 0);
        $mrdtId = (int) SystemSetting::get('malaria_mrdt_service_id', 0);

        $bsGrid = $this->malariaPositiveGrid($bsId, $monthStart, $monthEnd, $investigationAgeBucket, $bucketCount, [MalariaVipimoReportService::class, 'classifyBsResult']);
        $mrdtGrid = $this->malariaPositiveGrid($mrdtId, $monthStart, $monthEnd, $investigationAgeBucket, $bucketCount, [MalariaVipimoReportService::class, 'classifyMrdtResult']);

        $emptyGrid = [];
        for ($i = 0; $i < $bucketCount; $i++) {
            $emptyGrid[] = ['male' => 0, 'female' => 0, 'both' => 0];
        }
        $emptyTotals = ['male' => 0, 'female' => 0, 'both' => 0];

        return [
            ['id' => 14, 'description' => 'Malaria blood slide positive', 'buckets' => $bsGrid, 'totals' => $this->bucketGridTotals($bsGrid)],
            ['id' => '', 'description' => 'Malaria mRDT positive', 'buckets' => $mrdtGrid, 'totals' => $this->bucketGridTotals($mrdtGrid)],
            ['id' => '', 'description' => 'Malaria clinical [No Test]', 'buckets' => $emptyGrid, 'totals' => $emptyTotals],
            ['id' => '', 'description' => 'Cases (Referral in)', 'buckets' => $emptyGrid, 'totals' => $emptyTotals],
        ];
    }

    /**
     * Count "positive" classifications (per the given classifier callback,
     * mirroring MalariaVipimoReportService::classifyBsResult/MrdtResult) for
     * a given malaria-test medical_service_id, bucketed by age and gender.
     */
    private function malariaPositiveGrid(int $serviceId, Carbon $monthStart, Carbon $monthEnd, string $ageBucketCase, int $bucketCount, callable $classifier): array
    {
        $grid = [];
        for ($i = 0; $i < $bucketCount; $i++) {
            $grid[$i] = ['male' => 0, 'female' => 0];
        }

        if ($serviceId <= 0) {
            return array_map(fn ($g) => ['male' => $g['male'], 'female' => $g['female'], 'both' => $g['male'] + $g['female']], $grid);
        }

        $rows = DB::table('investigations as inv')
            ->join('patient_visits as pv', 'pv.id', '=', 'inv.visit_id')
            ->join('patients as p', 'p.id', '=', 'pv.patient')
            ->leftJoin(
                DB::raw('(SELECT investigation_id, form_data FROM investigation_template_results WHERE form_status = "final" ORDER BY id DESC) as itr'),
                'itr.investigation_id', '=', 'inv.id'
            )
            ->where('inv.medical_service_id', $serviceId)
            ->whereNull('inv.cancelled_at')
            ->where('inv.ordered_at', '>=', $monthStart)
            ->where('inv.ordered_at', '<', $monthEnd)
            ->selectRaw("p.gender as gender, {$ageBucketCase} as bucket, itr.form_data as form_data")
            ->get();

        foreach ($rows as $row) {
            if ($row->bucket === null) {
                continue;
            }

            $formData = [];
            if ($row->form_data) {
                $formData = is_array($row->form_data) ? $row->form_data : (json_decode($row->form_data, true) ?? []);
            }

            if ($classifier($formData) !== 'positive') {
                continue;
            }

            $genderKey = in_array(strtolower($row->gender ?? ''), ['male', 'm'], true) ? 'male' : 'female';
            $grid[(int) $row->bucket][$genderKey]++;
        }

        return array_map(fn ($g) => ['male' => $g['male'], 'female' => $g['female'], 'both' => $g['male'] + $g['female']], $grid);
    }

    /**
     * Sum a [{male, female, both}, ...] bucket grid into row-level totals.
     */
    private function bucketGridTotals(array $grid): array
    {
        $totals = ['male' => 0, 'female' => 0, 'both' => 0];
        foreach ($grid as $b) {
            $totals['male'] += $b['male'];
            $totals['female'] += $b['female'];
            $totals['both'] += $b['both'];
        }

        return $totals;
    }

    /**
     * Build a SQL CASE expression mapping an age-in-days expression to a
     * zero-based bucket index, preserving the legacy `> min AND <= max`
     * boundaries (ages that fall in none of the buckets resolve to NULL,
     * matching the original per-bucket whereRaw behaviour).
     */
    private function ageBucketCase(string $ageInDaysExpr, array $buckets): string
    {
        $whens = [];
        foreach ($buckets as $i => $bucket) {
            $whens[] = "WHEN {$ageInDaysExpr} > {$bucket['min']} AND {$ageInDaysExpr} <= {$bucket['max']} THEN {$i}";
        }

        return 'CASE ' . implode(' ', $whens) . ' END';
    }

    /**
     * Turn a (gender, bucket, total) result set into a zero-based array of
     * ['male' => x, 'female' => y, 'both' => x + y] grids, defaulting any
     * missing gender/bucket combination to zero.
     */
    private function genderBucketGrid(Collection $rows, int $bucketCount): array
    {
        $grid = [];
        foreach ($rows as $row) {
            if ($row->bucket === null) {
                continue;
            }
            $grid[(int) $row->bucket][$row->gender] = (int) $row->total;
        }

        $result = [];
        for ($i = 0; $i < $bucketCount; $i++) {
            $male = $grid[$i]['male'] ?? 0;
            $female = $grid[$i]['female'] ?? 0;
            $result[] = ['male' => $male, 'female' => $female, 'both' => $male + $female];
        }

        return $result;
    }

    /**
     * Convenience: format month name
     */
    public function monthName(int $month): string
    {
        return date('F', mktime(0, 0, 0, $month, 10));
    }
}
