<?php

namespace App\Services;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MalariaVipimoReportService extends BaseReportService
{
    // Hardcoded age group keys matching the official MoH malaria vipimo form
    private const AGE_GROUPS = [
        'under_1m' => 'Umri chini ya mwezi 1',
        '1_to_11m' => 'Umri mwezi 1 hadi 11',
        '1_to_4y'  => 'Umri mwaka 1 hadi miaka 4',
        '5y_plus'  => 'Umri miaka 5 na zaidi',
    ];

    private function emptyAgeRow(): array
    {
        return array_fill_keys(array_keys(self::AGE_GROUPS), ['male' => 0, 'female' => 0]);
    }

    private function classifyAge(string $dob, string $referenceDate): ?string
    {
        $days = Carbon::parse($dob)->diffInDays(Carbon::parse($referenceDate));
        if ($days < 30)   return 'under_1m';
        if ($days < 365)  return '1_to_11m';
        if ($days < 1826) return '1_to_4y';
        return '5y_plus';
    }

    public static function classifyMrdtResult(array $formData): ?string
    {
        $negativeValue = strtolower(SystemSetting::get('malaria_mrdt_negative_value', 'negative'));
        $positiveValue = strtolower(SystemSetting::get('malaria_mrdt_positive_value', 'positive'));

        $values = [];

        // Look inside parameters array (mrdt_malaria template stores value here)
        if (isset($formData['parameters']) && is_array($formData['parameters'])) {
            foreach ($formData['parameters'] as $param) {
                if (isset($param['value']) && is_string($param['value'])) {
                    $values[] = strtolower(trim($param['value']));
                }
            }
        }

        // Fall back to top-level string values
        foreach ($formData as $v) {
            if (is_string($v)) {
                $values[] = strtolower(trim($v));
            }
        }

        foreach ($values as $val) {
            if (str_contains($val, $negativeValue)) return 'negative';
            if (str_contains($val, $positiveValue)) return 'positive';
        }

        return null;
    }

    public static function classifyBsResult(array $formData): ?string
    {
        // pbs_malaria stores the finding in the 'Malaria Parasites' parameter (Seen / Not Seen)
        if (isset($formData['parameters']) && is_array($formData['parameters'])) {
            foreach ($formData['parameters'] as $param) {
                if (($param['parameter_name'] ?? null) !== 'Malaria Parasites') {
                    continue;
                }

                $val = strtolower(trim((string) ($param['value'] ?? '')));
                if ($val === 'seen') return 'positive';
                if ($val === 'not seen') return 'negative';

                return null;
            }
        }

        return null;
    }

    private function processInvestigations(array $rows, callable $classifier, string $negLabel, string $posLabel): array
    {
        $counts = [
            $negLabel => $this->emptyAgeRow(),
            $posLabel => $this->emptyAgeRow(),
        ];

        foreach ($rows as $row) {
            if (!$row->date_of_birth) continue;

            $ageKey = $this->classifyAge($row->date_of_birth, $row->ordered_at ?? $row->visit_date ?? now());
            if (!$ageKey) continue;

            $gender = strtolower($row->gender ?? 'other');
            $genderKey = in_array($gender, ['male', 'm']) ? 'male' : 'female';

            $formData = [];
            if ($row->form_data) {
                $formData = is_array($row->form_data) ? $row->form_data : (json_decode($row->form_data, true) ?? []);
            }

            $result = $classifier($formData);

            if ($result === 'negative') {
                $counts[$negLabel][$ageKey][$genderKey]++;
            } elseif ($result === 'positive') {
                $counts[$posLabel][$ageKey][$genderKey]++;
            }
        }

        return $counts;
    }

    private function queryInvestigations(int $serviceId): array
    {
        return DB::table('investigations as inv')
            ->join('patient_visits as pv', 'pv.id', '=', 'inv.visit_id')
            ->join('patients as p', 'p.id', '=', 'pv.patient')
            ->leftJoin(
                DB::raw('(SELECT investigation_id, form_data FROM investigation_template_results WHERE form_status = "final" ORDER BY id DESC) as itr'),
                'itr.investigation_id', '=', 'inv.id'
            )
            ->where('inv.medical_service_id', $serviceId)
            ->whereNull('inv.cancelled_at')
            ->whereBetween('inv.ordered_at', [$this->startDate, $this->endDate])
            ->select('p.gender', 'p.date_of_birth', 'inv.ordered_at', 'pv.visit_date', 'itr.form_data')
            ->get()
            ->all();
    }

    private function rowTotals(array $ageRow): array
    {
        $male = $female = 0;
        foreach ($ageRow as $grp) {
            $male   += $grp['male'];
            $female += $grp['female'];
        }
        return ['male' => $male, 'female' => $female, 'total' => $male + $female];
    }

    private function grandTotals(array $matrix): array
    {
        $totals = array_fill_keys(array_keys(self::AGE_GROUPS), ['male' => 0, 'female' => 0]);
        foreach ($matrix as $ageRow) {
            foreach (array_keys(self::AGE_GROUPS) as $ag) {
                $totals[$ag]['male']   += $ageRow[$ag]['male'];
                $totals[$ag]['female'] += $ageRow[$ag]['female'];
            }
        }
        return $totals;
    }

    public function buildReport(): array
    {
        $baseData = $this->getBaseReportData();

        $mrdtId = (int) SystemSetting::get('malaria_mrdt_service_id', 0);
        $bsId   = (int) SystemSetting::get('malaria_bs_service_id',   0);

        $mrdtService = $mrdtId ? DB::table('medical_services')->where('id', $mrdtId)->value('name') : null;
        $bsService   = $bsId   ? DB::table('medical_services')->where('id', $bsId)->value('name')   : null;

        // Query investigations
        $mrdtRows = $mrdtId ? $this->queryInvestigations($mrdtId) : [];
        $bsRows   = $bsId   ? $this->queryInvestigations($bsId)   : [];

        // Process results
        $mrdtCounts = $this->processInvestigations(
            $mrdtRows,
            fn(array $fd) => $this->classifyMrdtResult($fd),
            'mrdt_negative',
            'mrdt_positive'
        );

        $bsCounts = $this->processInvestigations(
            $bsRows,
            fn(array $fd) => $this->classifyBsResult($fd),
            'bs_no_mps',
            'bs_mps_seen'
        );

        $allCounts = array_merge($mrdtCounts, $bsCounts);

        // Grand totals per age group
        $grandAgeTotal = $this->grandTotals($allCounts);

        // Row-level totals (across all age groups)
        $rowTotals = [];
        foreach ($allCounts as $key => $ageRow) {
            $rowTotals[$key] = $this->rowTotals($ageRow);
        }

        $grandTotal = ['male' => 0, 'female' => 0, 'total' => 0];
        foreach ($rowTotals as $t) {
            $grandTotal['male']   += $t['male'];
            $grandTotal['female'] += $t['female'];
            $grandTotal['total']  += $t['total'];
        }

        return array_merge($baseData, [
            'age_group_keys'  => array_keys(self::AGE_GROUPS),
            'age_group_labels'=> self::AGE_GROUPS,
            'mrdt_service'    => $mrdtService,
            'bs_service'      => $bsService,
            'mrdt_id'         => $mrdtId,
            'bs_id'           => $bsId,
            'counts'          => $allCounts,
            'row_totals'      => $rowTotals,
            'grand_age_total' => $grandAgeTotal,
            'grand_total'     => $grandTotal,
        ]);
    }
}
