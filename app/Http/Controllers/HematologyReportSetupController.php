<?php

namespace App\Http\Controllers;

use App\Models\HematologyReportRow;
use App\Models\MedicalService;
use Illuminate\Http\Request;

class HematologyReportSetupController extends Controller
{
    // These rows inherit service_ids directly from the FBP row
    private const FBP_DEPENDENT = [
        'wbc_count', 'wbc_diff', 'platelets', 'reticulocytes',
        'peripheral_blood_film', 'pcv', 'rbc_count',
    ];

    public function index()
    {
        $rows = HematologyReportRow::where('is_section_header', false)
            ->whereNotIn('row_key', self::FBP_DEPENDENT)
            ->orderBy('sort_order')
            ->get();

        $labServices = MedicalService::whereHas('serviceCategory', fn($q) => $q->where('name', 'Laboratory'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $fbpRow = HematologyReportRow::where('row_key', 'fbp')->first();

        return view('admin.lab-settings.hematology-setup', compact('rows', 'labServices', 'fbpRow'));
    }

    public function update(Request $request)
    {
        $rows = HematologyReportRow::where('is_section_header', false)
            ->whereNotIn('row_key', self::FBP_DEPENDENT)
            ->get();

        foreach ($rows as $row) {
            $id = $request->input("service_id_{$row->row_key}");
            $row->service_ids = $id ? [(int) $id] : [];
            $row->save();

            // Propagate FBP service to all dependent rows
            if ($row->row_key === 'fbp') {
                HematologyReportRow::whereIn('row_key', self::FBP_DEPENDENT)
                    ->update(['service_ids' => $row->service_ids ? json_encode($row->service_ids) : null]);
            }
        }

        return redirect()->route('admin.lab-settings.hematology.index')
            ->with('success', 'Hematology report configuration saved.');
    }
}
