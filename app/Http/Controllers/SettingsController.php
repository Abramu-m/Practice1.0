<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\MedicalService;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $facility = Facility::current();

        // Lab services for report config dropdowns
        $labServices = MedicalService::whereHas('serviceCategory', fn($q) => $q->where('name', 'Laboratory'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $reportConfig = [
            'malaria_mrdt_service_id' => SystemSetting::get('malaria_mrdt_service_id'),
            'malaria_bs_service_id'   => SystemSetting::get('malaria_bs_service_id'),
        ];

        return view('settings.index', compact('facility', 'labServices', 'reportConfig'));
    }

    public function updateFacility(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'slogan'             => 'nullable|string|max:255',
            'country'            => 'nullable|string|max:100',
            'region'             => 'nullable|string|max:100',
            'district'           => 'nullable|string|max:100',
            'locale'             => 'nullable|string|max:100',
            'postal'             => 'nullable|string|max:100',
            'address'            => 'nullable|string|max:255',
            'phone'              => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:255',
            'nhif_facility_code' => 'nullable|string|max:50',
            'hfr_code'           => 'nullable|string|max:50',
        ]);

        $facility = Facility::first();
        if ($facility) {
            $facility->update($data);
        } else {
            Facility::create($data);
        }

        return redirect()->route('settings.index')->with('success', 'Facility details updated successfully.');
    }

    public function updateReportConfig(Request $request)
    {
        $keys = [
            'malaria_mrdt_service_id',
            'malaria_bs_service_id',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                SystemSetting::set($key, $request->input($key));
            }
        }

        return redirect()->route('settings.index', ['#report-config'])->with('success', 'Report configuration saved.');
    }

    public function malariaVipimoSettings()
    {
        $labServices = MedicalService::whereHas('serviceCategory', fn($q) => $q->where('name', 'Laboratory'))
            ->orderBy('name')
            ->get(['id', 'name']);

        // Look up the fixed template display names so the view can show them as read-only info
        $mrdtTemplate = DB::table('result_templates')->where('code', 'mrdt_malaria')->first(['name', 'code']);
        $bsTemplate   = DB::table('result_templates')->where('code', 'pbs_malaria')->first(['name', 'code']);

        $config = [
            'malaria_mrdt_service_id' => SystemSetting::get('malaria_mrdt_service_id'),
            'malaria_bs_service_id'   => SystemSetting::get('malaria_bs_service_id'),
        ];

        return view('settings.reports.malaria_vipimo', compact('labServices', 'mrdtTemplate', 'bsTemplate', 'config'));
    }

    public function updateMalariaVipimoSettings(Request $request)
    {
        SystemSetting::set('malaria_mrdt_service_id', $request->input('malaria_mrdt_service_id', ''));
        SystemSetting::set('malaria_bs_service_id',   $request->input('malaria_bs_service_id',   ''));

        return redirect()->route('settings.reports.malaria-vipimo')->with('success', 'Malaria Vipimo report configuration saved.');
    }

    public function aluReportSettings()
    {
        $medications = \App\Models\Medication::orderBy('generic_name')
            ->get(['id', 'generic_name', 'brand_name', 'strength']);

        $config = [
            'alu_1x6_medication_id' => SystemSetting::get('alu_1x6_medication_id'),
            'alu_2x6_medication_id' => SystemSetting::get('alu_2x6_medication_id'),
            'alu_3x6_medication_id' => SystemSetting::get('alu_3x6_medication_id'),
            'alu_4x6_medication_id' => SystemSetting::get('alu_4x6_medication_id'),
        ];

        return view('settings.reports.alu_monthly', compact('medications', 'config'));
    }

    public function updateAluReportSettings(Request $request)
    {
        foreach (['alu_1x6_medication_id', 'alu_2x6_medication_id', 'alu_3x6_medication_id', 'alu_4x6_medication_id'] as $key) {
            SystemSetting::set($key, $request->input($key, ''));
        }

        return redirect()->route('settings.reports.alu-monthly')->with('success', 'ALu report configuration saved.');
    }
}
