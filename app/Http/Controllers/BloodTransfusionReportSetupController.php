<?php

namespace App\Http\Controllers;

use App\Models\BloodTransfusionReportRow;
use App\Models\MedicalService;
use Illuminate\Http\Request;

class BloodTransfusionReportSetupController extends Controller
{
    private const MAPPED_ROWS = ['blood_grouping_rh_crossmatch', 'coombs_test'];

    public function index()
    {
        $rows = BloodTransfusionReportRow::whereIn('row_key', self::MAPPED_ROWS)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('row_key');

        $labServices = MedicalService::whereHas('serviceCategory', fn($q) => $q->where('name', 'Laboratory'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.lab-settings.blood-transfusion-setup', compact('rows', 'labServices'));
    }

    public function update(Request $request)
    {
        foreach (self::MAPPED_ROWS as $key) {
            $row = BloodTransfusionReportRow::where('row_key', $key)->first();
            if (!$row) continue;

            $id = $request->input("service_id_{$key}");
            $row->service_ids = $id ? [(int) $id] : [];
            $row->save();
        }

        return redirect()->route('admin.lab-settings.blood-transfusion.index')
            ->with('success', 'Blood transfusion report configuration saved.');
    }
}
