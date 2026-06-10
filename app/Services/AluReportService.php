<?php

namespace App\Services;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AluReportService extends BaseReportService
{
    private const ALU_PACKS = [
        'alu_1x6_medication_id' => 'ALu ya 1x6',
        'alu_2x6_medication_id' => 'ALu ya 2x6',
        'alu_3x6_medication_id' => 'ALu ya 3x6',
        'alu_4x6_medication_id' => 'ALu ya 4x6',
    ];

    public function buildReport(): array
    {
        $baseData = $this->getBaseReportData();

        $aluRows = [];

        foreach (self::ALU_PACKS as $settingKey => $label) {
            $medicationId = (int) SystemSetting::get($settingKey, 0);

            $bands = ['a' => 0, 'b' => 0, 'c' => 0, 'd' => 0];

            if ($medicationId) {
                $rows = DB::table('prescriptions as pr')
                    ->join('consultations as c', 'c.id', '=', 'pr.consultation_id')
                    ->join('patient_visits as pv', 'pv.id', '=', 'c.visit_id')
                    ->join('patients as p', 'p.id', '=', 'pv.patient')
                    ->where('pr.medication_id', $medicationId)
                    ->whereNotNull('pr.dispensed_at')
                    ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
                    ->select('p.date_of_birth', 'pv.visit_date', 'pr.quantity_dispensed')
                    ->get();

                foreach ($rows as $row) {
                    $ageDays = Carbon::parse($row->date_of_birth)->diffInDays(Carbon::parse($row->visit_date));

                    $band = match (true) {
                        $ageDays <= 1095 => 'a',
                        $ageDays <= 2922 => 'b',
                        $ageDays <= 4383 => 'c',
                        default => 'd',
                    };

                    $bands[$band] += (int) $row->quantity_dispensed;
                }
            }

            $aluRows[] = [
                'label' => $label,
                'unit' => 'Vidonge',
                'a' => $bands['a'],
                'b' => $bands['b'],
                'c' => $bands['c'],
                'd' => $bands['d'],
                'total' => array_sum($bands),
            ];
        }

        return array_merge($baseData, [
            'alu_rows' => $aluRows,
        ]);
    }
}
