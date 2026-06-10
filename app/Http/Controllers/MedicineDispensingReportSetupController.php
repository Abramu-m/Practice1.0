<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\MedicineDispensingReportRow;
use Illuminate\Http\Request;

class MedicineDispensingReportSetupController extends Controller
{
    public function index()
    {
        $rows = MedicineDispensingReportRow::orderBy('sort_order')->get();

        $groupLabels = $rows->pluck('drug_label', 'group_key')->filter();

        $rows->each(function ($row) use ($groupLabels) {
            $row->display_label = $row->drug_label ?? $groupLabels[$row->group_key] ?? '';
        });

        $medications = Medication::orderBy('generic_name')
            ->get(['id', 'generic_name', 'brand_name', 'strength']);

        return view('admin.pharmacy-settings.medicine-dispensing-setup', compact('rows', 'medications'));
    }

    public function update(Request $request)
    {
        $rows = MedicineDispensingReportRow::all();

        foreach ($rows as $row) {
            $medicationId = $request->input("medication_id_{$row->row_key}");
            $row->medication_id = $medicationId ?: null;
            $row->save();
        }

        return redirect()->route('admin.pharmacy-settings.medicine-dispensing.index')
            ->with('success', 'Medicine dispensing report configuration saved.');
    }
}
