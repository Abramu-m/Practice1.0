<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Allergy;
use App\Models\Medication;
use App\Models\PastMedicalHistory;
use App\Models\VitalSigns;
use App\Models\Investigation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class CdsTestPatientsController extends Controller
{
    /** Card numbers that identify the three CDS dummy patients */
    private const TEST_CARDS = ['CDS-TEST-001', 'CDS-TEST-002', 'CDS-TEST-003'];

    public function index()
    {
        $patients = Patient::with(['allergies', 'pastMedicalHistory'])
            ->whereIn('card_number', self::TEST_CARDS)
            ->orderBy('card_number')
            ->get()
            ->map(function (Patient $p) {
                $p->vitals         = VitalSigns::where('patient_id', $p->id)->where('visit_id', 0)->latest('recorded_at')->first();
                $p->investigations = Investigation::where('patient_id', $p->id)
                    ->where('status', 'resulted')
                    ->orderBy('resulted_at', 'desc')
                    ->get();
                return $p;
            });

        // Build a flat JS-safe array keyed by patient ID to avoid
        // complex closure syntax inside @json() in Blade.
        $testPatientsJs = $patients->keyBy('id')->map(function (Patient $p) {
            return [
                'id'            => $p->id,
                'first_name'    => $p->first_name,
                'last_name'     => $p->last_name,
                'date_of_birth' => $p->date_of_birth
                    ? Carbon::parse($p->date_of_birth)->format('Y-m-d')
                    : '',
                'gender'        => $p->gender,
                'contact'       => $p->contact,
                'residence'     => $p->residence,
                'occupation'    => $p->occupation,
                // vitals
                'weight'            => optional($p->vitals)->weight,
                'height'            => optional($p->vitals)->height,
                'systolic_bp'       => optional($p->vitals)->systolic_bp,
                'diastolic_bp'      => optional($p->vitals)->diastolic_bp,
                'pulse_rate'        => optional($p->vitals)->pulse_rate,
                'temperature'       => optional($p->vitals)->temperature,
                'respiratory_rate'  => optional($p->vitals)->respiratory_rate,
                'oxygen_saturation' => optional($p->vitals)->oxygen_saturation,
                // pmh
                'allergies_text'      => optional($p->pastMedicalHistory)->allergies,
                'chronic_conditions'  => optional($p->pastMedicalHistory)->chronic_conditions,
                'current_medications' => optional($p->pastMedicalHistory)->current_medications,
                'previous_surgeries'  => optional($p->pastMedicalHistory)->previous_surgeries,
                'family_history'      => optional($p->pastMedicalHistory)->family_history,
                'social_history'      => optional($p->pastMedicalHistory)->social_history,
                'smoking_status'      => optional($p->pastMedicalHistory)->smoking_status,
                'alcohol_use'         => optional($p->pastMedicalHistory)->alcohol_use,
                // allergies list
                'allergies' => $p->allergies->map(fn(Allergy $a) => [
                    'medication_id'  => $a->medication_id,
                    'substance_name' => $a->substance_name,
                    'reaction'       => $a->reaction,
                    'severity'       => $a->severity,
                ])->values(),
            ];
        });

        // All active medications for the allergy dropdown
        $medications = Medication::where('is_active', true)
            ->orderBy('generic_name')
            ->select('id', 'generic_name', 'brand_name')
            ->get();

        return view('admin.cds.test-patients.index', compact('patients', 'testPatientsJs', 'medications'));
    }

    public function update(Request $request, Patient $patient)
    {
        abort_unless(in_array($patient->card_number, self::TEST_CARDS), 403, 'Not a test patient.');

        $validated = $request->validate([
            // Demographics
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender'        => 'required|in:male,female,other',
            'contact'       => 'nullable|string|max:20',
            'residence'     => 'nullable|string|max:255',
            'occupation'    => 'nullable|string|max:100',

            // Vitals
            'weight'            => 'nullable|numeric|min:1|max:300',
            'height'            => 'nullable|numeric|min:30|max:250',
            'systolic_bp'       => 'nullable|integer|min:50|max:300',
            'diastolic_bp'      => 'nullable|integer|min:30|max:200',
            'pulse_rate'        => 'nullable|integer|min:20|max:300',
            'temperature'       => 'nullable|numeric|min:30|max:45',
            'respiratory_rate'  => 'nullable|integer|min:5|max:60',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',

            // Past Medical History
            'allergies_text'        => 'nullable|string',
            'chronic_conditions'    => 'nullable|string',
            'previous_surgeries'    => 'nullable|string',
            'current_medications'   => 'nullable|string',
            'family_history'        => 'nullable|string',
            'social_history'        => 'nullable|string',
            'smoking_status'        => 'nullable|in:non_smoker,former_smoker,current_smoker',
            'alcohol_use'           => 'nullable|string|max:50',

            // Allergies list (JSON encoded array from frontend)
            'allergies_json'        => 'nullable|json',
        ]);

        // Update demographics
        $patient->update([
            'first_name'    => $validated['first_name'],
            'last_name'     => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender'        => $validated['gender'],
            'contact'       => $validated['contact'] ?? $patient->contact,
            'residence'     => $validated['residence'] ?? $patient->residence,
            'occupation'    => $validated['occupation'] ?? $patient->occupation,
        ]);

        // Recalculate BMI if weight/height provided
        $bmi = null;
        if (!empty($validated['weight']) && !empty($validated['height'])) {
            $heightM = $validated['height'] / 100;
            $bmi = round($validated['weight'] / ($heightM * $heightM), 1);
        }

        // Upsert vitals
        VitalSigns::updateOrCreate(
            ['patient_id' => $patient->id, 'visit_id' => 0, 'consultation_id' => 0],
            array_filter([
                'weight'            => $validated['weight'] ?? null,
                'height'            => $validated['height'] ?? null,
                'bmi'               => $bmi,
                'systolic_bp'       => $validated['systolic_bp'] ?? null,
                'diastolic_bp'      => $validated['diastolic_bp'] ?? null,
                'pulse_rate'        => $validated['pulse_rate'] ?? null,
                'temperature'       => $validated['temperature'] ?? null,
                'respiratory_rate'  => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'recorded_at'       => Carbon::now(),
                'status'            => 1,
            ], fn($v) => $v !== null)
        );

        // Upsert past medical history
        PastMedicalHistory::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'allergies'          => $validated['allergies_text'] ?? null,
                'chronic_conditions' => $validated['chronic_conditions'] ?? null,
                'previous_surgeries' => $validated['previous_surgeries'] ?? null,
                'current_medications'=> $validated['current_medications'] ?? null,
                'family_history'     => $validated['family_history'] ?? null,
                'social_history'     => $validated['social_history'] ?? null,
                'smoking_status'     => $validated['smoking_status'] ?? null,
                'alcohol_use'        => $validated['alcohol_use'] ?? null,
            ]
        );

        // Replace allergy records if provided
        if (!empty($validated['allergies_json'])) {
            $allergyList = json_decode($validated['allergies_json'], true);
            // Delete old and re-create
            Allergy::where('patient_id', $patient->id)->delete();
            foreach ($allergyList as $al) {
                $medId = !empty($al['medication_id']) ? (int) $al['medication_id'] : null;
                // Resolve substance_name from medication FK; fall back to provided text
                $substanceName = '';
                if ($medId) {
                    $med = Medication::find($medId);
                    $substanceName = $med ? $med->generic_name : ($al['substance_name'] ?? '');
                } else {
                    $substanceName = trim($al['substance_name'] ?? '');
                }
                if ($substanceName === '') continue;
                Allergy::create([
                    'patient_id'     => $patient->id,
                    'medication_id'  => $medId,
                    'substance_name' => $substanceName,
                    'reaction'       => $al['reaction'] ?? '',
                    'severity'       => $al['severity'] ?? 'moderate',
                    'is_active'      => true,
                    'recorded_at'    => Carbon::now(),
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Test patient updated successfully.']);
        }

        return back()->with('success', 'Test patient updated successfully.');
    }

    /** Re-seed all three test patients back to defaults */
    public function reseed()
    {
        Artisan::call('db:seed', ['--class' => 'CdsTestPatientsSeeder', '--force' => true]);
        return response()->json(['success' => true, 'message' => 'Test patients reset to defaults.']);
    }
}
