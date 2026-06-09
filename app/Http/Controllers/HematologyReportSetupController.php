<?php

namespace App\Http\Controllers;

use App\Models\HematologyReportRow;
use App\Models\MedicalService;
use Illuminate\Http\Request;

class HematologyReportSetupController extends Controller
{
    public function index()
    {
        $rows = HematologyReportRow::where('is_section_header', false)
            ->orderBy('sort_order')
            ->get();

        $labServices = MedicalService::whereHas('serviceCategory', fn($q) => $q->where('name', 'Laboratory'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.lab-settings.hematology-setup', compact('rows', 'labServices'));
    }

    public function update(Request $request)
    {
        $rows = HematologyReportRow::where('is_section_header', false)->get();

        foreach ($rows as $row) {
            $id = $request->input("service_id_{$row->row_key}");
            $row->service_ids = $id ? [(int) $id] : [];
            $row->save();
        }

        return redirect()->route('admin.lab-settings.hematology.index')
            ->with('success', 'Hematology report configuration saved.');
    }
}
