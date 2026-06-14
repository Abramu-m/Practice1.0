<?php

namespace App\Http\Controllers;

use App\Models\InvestigationFormData;
use Illuminate\Http\Request;

class InvestigationFormRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = InvestigationFormData::with([
            'investigation.patient',
            'investigation.medicalService',
            'investigation.visit',
        ])->latest();

        // Simple search by patient name or service
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('investigation.patient', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            })->orWhereHas('investigation.medicalService', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter to a specific investigation form type (e.g. TB forms)
        if ($request->filled('form_type')) {
            $formType = $request->form_type;
            $query->whereHas('investigation.medicalService', function ($q) use ($formType) {
                $q->where('form_type', $formType);
            });
        }

        $records = $query->paginate(25)->withQueryString();

        return view('investigation_form_records.index', compact('records'));
    }

    public function show($id)
    {
        $record = InvestigationFormData::with([
            'investigation.visit.patientInfo',
            'investigation.visit.doctorInfo',
            'investigation.medicalService',
            'investigation.patient',
            'investigation.results',
        ])->findOrFail($id);

        $investigation = $record->investigation;
        $patientVisit  = $investigation->visit;
        $patient       = $patientVisit->patientInfo ?? $investigation->patient;
        $doctor        = $patientVisit->doctorInfo  ?? null;

        $age = '';
        if ($patient && $patient->date_of_birth) {
            try {
                $birth = \Carbon\Carbon::parse($patient->date_of_birth);
                if ($birth->isPast()) {
                    $d   = $birth->diff(\Carbon\Carbon::now());
                    $age = $d->y . 'y ' . $d->m . 'm ' . $d->d . 'd';
                }
            } catch (\Exception $e) {}
        }

        $visit = (object) [
            'id'         => $patientVisit->id ?? '',
            'date'       => optional($patientVisit)->created_at
                                ? $patientVisit->created_at->format('Y-m-d')
                                : now()->format('Y-m-d'),
            'time'       => optional($patientVisit)->created_at
                                ? $patientVisit->created_at->format('H:i')
                                : now()->format('H:i'),
            'created_at' => optional($patientVisit)->created_at ?? now(),
            'department' => $patientVisit->department ?? 'OPD',
            'patientInfo' => (object) [
                'id'            => $patient->id ?? '',
                'first_name'    => $patient->first_name ?? '',
                'last_name'     => $patient->last_name ?? '',
                'full_name'     => $patient->full_name
                                    ?? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
                'date_of_birth' => $patient->date_of_birth ?? '',
                'age'           => $age,
                'gender'        => ucfirst($patient->gender ?? ''),
                'address'       => $patient->residence ?? '',
                'phone_number'  => $patient->contact ?? '',
                'ctc_number'    => $patient->card_number ?? '',
                'file_number'   => $patient->id ?? '',
            ],
            'doctorInfo' => $doctor ?? (object) [
                'first_name' => '',
                'last_name'  => '',
                'title'      => '',
                'user'       => (object) ['name' => ''],
            ],
            'doctor' => (object) [
                'name'      => $doctor
                    ? ($doctor->title ?? 'Dr.') . ' ' . trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? ''))
                    : '',
                'full_name' => $doctor
                    ? ($doctor->title ?? 'Dr.') . ' ' . trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? ''))
                    : '',
                'title'     => $doctor->title ?? 'Medical Officer',
            ],
            'facility' => (object) [
                'name'      => config('app.clinic_name', 'Medical Facility'),
                'full_name' => config('app.clinic_name', 'Medical Facility'),
                'address'   => config('app.clinic_address', ''),
                'phone'     => config('app.clinic_phone', ''),
            ],
            'laboratory' => (object) [
                'serial_number' => 'LAB-' . date('Y') . '-' . str_pad($patientVisit->id ?? 0, 4, '0', STR_PAD_LEFT),
                'technician'    => '',
                'reviewer'      => '',
            ],
        ];

        $formType = $investigation->medicalService->form_type ?? null;

        // Normalise DB form_type values to result-template file names
        $templateMap = [
            'tb'                        => 'genxpert_tb',
            'genexpert mtb/rif'         => 'genxpert_tb',
            'genexpert'                 => 'genxpert_tb',
            'zn stain microscopy (afb)' => 'zn_stain_tb',
            'zn stain'                  => 'zn_stain_tb',
        ];
        if ($formType && isset($templateMap[strtolower($formType)])) {
            $formType = $templateMap[strtolower($formType)];
        }

        // Latest non-draft result; fall back to any result if all are drafts
        $latestResult = $investigation->results
            ->where('form_status', '!=', 'draft')
            ->sortByDesc('reported_at')
            ->first()
            ?? $investigation->results->sortByDesc('reported_at')->first();

        $resultData       = $latestResult ? ($latestResult->form_data ?? []) : [];
        $resultStatus     = $latestResult ? $latestResult->form_status : null;
        $resultReportedAt = $latestResult ? $latestResult->reported_at : null;

        return view('investigation_form_records.show',
            compact('record', 'visit', 'formType', 'resultData', 'resultStatus', 'resultReportedAt'));
    }
}
