<?php

namespace App\Http\Controllers;

use App\Models\MedicalService;
use App\Models\SerologyReportRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SerologyReportSetupController extends Controller
{
    public function index()
    {
        $rows = SerologyReportRow::where('is_configurable', true)
            ->orderBy('sort_order')
            ->get();

        $labServices = MedicalService::whereHas('serviceCategory', fn ($q) => $q->where('name', 'Laboratory'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $templateCodes = DB::table('result_templates')->pluck('code', 'name');

        return view('admin.lab-settings.serology-setup', compact('rows', 'labServices', 'templateCodes'));
    }

    public function update(Request $request)
    {
        $rows = SerologyReportRow::where('is_configurable', true)->get();

        foreach ($rows as $row) {
            $id = $request->input("service_id_{$row->row_key}");
            $row->service_ids = $id ? [(int) $id] : [];
            $row->save();
        }

        return redirect()->route('admin.lab-settings.serology.index')
            ->with('success', 'Serology report configuration saved.');
    }
}
