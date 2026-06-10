<?php

namespace App\Http\Controllers;

use App\Models\MedicalService;
use App\Models\ParasitologyReportRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParasitologyReportSetupController extends Controller
{
    public function index()
    {
        $rows = ParasitologyReportRow::where('is_configurable', true)
            ->orderBy('sort_order')
            ->get();

        $labServices = MedicalService::whereHas('serviceCategory', fn ($q) => $q->where('name', 'LIKE', '%Laboratory%'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $templateCodes = DB::table('result_templates')->pluck('code', 'name');

        return view('admin.lab-settings.parasitology-setup', compact('rows', 'labServices', 'templateCodes'));
    }

    public function update(Request $request)
    {
        $rows = ParasitologyReportRow::where('is_configurable', true)->get();

        foreach ($rows as $row) {
            $id = $request->input("service_id_{$row->row_key}");
            $row->service_ids = $id ? [(int) $id] : [];
            $row->save();
        }

        return redirect()->route('admin.lab-settings.parasitology.index')
            ->with('success', 'Parasitology report configuration saved.');
    }
}
