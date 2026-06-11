<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CdsRule;
use App\Models\CdsRuleType;
use App\Models\CdsRuleCategory;
use App\Models\CdsRuleCondition;
use App\Models\CdsRuleParameter;
use App\Models\CdsDosageLimit;
use App\Models\Patient;
use App\Models\Medication;
use App\Models\VitalSigns;
use App\Services\CDS\Calculators\EgfrCalculator;
use App\Services\CDS\CdsRuleCache;
use App\Services\CDS\ResultParameterCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CdsRuleController extends Controller
{
    public function __construct(private CdsRuleCache $ruleCache)
    {
    }

    /**
     * Show the CDS dashboard with statistics
     */
    public function dashboard()
    {
        $stats = [
            'categories' => CdsRuleCategory::count(),
            'rule_types' => CdsRuleType::count(),
            'total_rules' => CdsRule::count(),
            'active_rules' => CdsRule::where('is_active', true)->count(),
            'dosage_limits' => CdsDosageLimit::count(),
        ];

        $recentRules = CdsRule::with(['ruleCategory', 'ruleType'])
            ->latest()
            ->limit(10)
            ->get();

        $rulesByCategory = CdsRuleCategory::withCount('rules')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->rules_count
                ];
            });

        return view('admin.cds.dashboard', compact('stats', 'recentRules', 'rulesByCategory'));
    }

    public function index(Request $request)
    {
        $query = CdsRule::with(['ruleType.category', 'creator']);

        // Apply filters
        if ($request->filled('category')) {
            $query->whereHas('ruleType.category', function($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        if ($request->filled('rule_type')) {
            $query->where('rule_type_id', $request->rule_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $rules = $query->orderBy('priority', 'desc')->paginate(20);

        $categories = CdsRuleCategory::active()->get();
        $ruleTypes = CdsRuleType::with('category')->active()->get();

        $stats = [
            'total_rules' => CdsRule::count(),
            'active_rules' => CdsRule::where('is_active', true)->count(),
            'critical_rules' => CdsRule::where('severity', 'critical')->count(),
            'rule_types' => CdsRuleType::count(),
        ];

        return view('admin.cds.rules.index', compact('rules', 'categories', 'ruleTypes', 'stats'));
    }

    public function create()
    {
        $ruleTypes = CdsRuleType::with('category')->active()->get();
        $categories = CdsRuleCategory::active()->get();
        
        return view('admin.cds.rules.create', compact('ruleTypes', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->ruleValidationRules());

        $validated['is_active'] = $request->has('is_active');

        $rule = CdsRule::create([
            'rule_type_id' => $validated['rule_type_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'severity' => $validated['severity'],
            'is_active' => $validated['is_active'],
        ]);

        $this->persistConditionsAndParameters($rule, $request);

        // Clear cache for this rule type
        $ruleType = $rule->ruleType;
        $this->ruleCache->clearRuleTypeCache($ruleType->name);

        return redirect()
            ->route('admin.cds.rules.index')
            ->with('success', 'CDS Rule created successfully');
    }

    /**
     * Shared validation rules for store()/update().
     */
    private function ruleValidationRules(bool $requireRuleType = true): array
    {
        return [
            'rule_type_id' => ($requireRuleType ? 'required' : 'nullable') . '|exists:cds_rule_types,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'priority' => 'required|integer|between:1,10',
            'severity' => 'required|in:info,warning,critical',
            'is_active' => 'boolean',
            'alert_message' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'conditions' => 'nullable|array',
            'conditions.*.field' => 'nullable|string|max:100',
            'conditions.*.operator' => 'nullable|in:equals,not_equals,contains,not_contains,greater_than,less_than,greater_equal,less_equal,in,not_in,regex',
            'conditions.*.value' => 'nullable|string',
            'conditions.*.value_type' => 'nullable|in:string,integer,float,boolean,array,json',
            'lab_medical_service_id' => 'nullable|integer|exists:medical_services,id',
            'lab_parameter_key' => 'nullable|string|max:100',
            'lab_operator' => 'nullable|in:equals,not_equals,greater_than,less_than,greater_equal,less_equal',
            'lab_threshold' => 'nullable|numeric',
        ];
    }

    /**
     * Persist conditions/parameters/alert-content for a rule, full-replace style.
     * For "lab_critical" rules, conditions are built from the locked lab picker
     * fields instead of the generic conditions[] array.
     */
    private function persistConditionsAndParameters(CdsRule $rule, Request $request): void
    {
        $rule->conditions()->delete();
        $rule->parameters()->delete();

        if ($rule->ruleType->name === 'lab_critical') {
            $this->persistLabCriticalConditions($rule, $request);
        } else {
            foreach ($request->input('conditions', []) as $index => $condition) {
                if (empty($condition['field']) || empty($condition['operator'])) {
                    continue;
                }

                CdsRuleCondition::create([
                    'rule_id' => $rule->id,
                    'field_name' => $condition['field'],
                    'operator' => $condition['operator'],
                    'value' => $condition['value'] ?? '',
                    'value_type' => $condition['value_type'] ?? 'string',
                    'logical_operator' => $condition['logical_operator'] ?? 'AND',
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        foreach (['alert_message', 'recommendation'] as $reserved) {
            $value = $request->input($reserved);
            if ($value !== null && $value !== '') {
                CdsRuleParameter::create([
                    'rule_id' => $rule->id,
                    'parameter_name' => $reserved,
                    'parameter_value' => $value,
                    'parameter_type' => 'general',
                ]);
            }
        }
    }

    /**
     * Build the two locked conditions for a "Lab Critical Value" rule:
     * (1) investigation.medical_service_id equals <picked test>
     * (2) result.parameters.<picked parameter> <operator> <threshold>
     */
    private function persistLabCriticalConditions(CdsRule $rule, Request $request): void
    {
        $serviceId = $request->input('lab_medical_service_id');
        $parameterKey = $request->input('lab_parameter_key');
        $operator = $request->input('lab_operator');
        $threshold = $request->input('lab_threshold');

        if (!$serviceId || !$parameterKey || !$operator || $threshold === null || $threshold === '') {
            return;
        }

        CdsRuleCondition::create([
            'rule_id' => $rule->id,
            'field_name' => 'investigation.medical_service_id',
            'operator' => 'equals',
            'value' => $serviceId,
            'value_type' => 'integer',
            'logical_operator' => 'AND',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        CdsRuleCondition::create([
            'rule_id' => $rule->id,
            'field_name' => "result.parameters.{$parameterKey}",
            'operator' => $operator,
            'value' => $threshold,
            'value_type' => 'float',
            'logical_operator' => 'AND',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    /**
     * Returns the constrained {key, label, unit} parameter list for a medical
     * service's result template, used by the Lab Critical builder's parameter picker.
     */
    public function labParameters(Request $request)
    {
        $request->validate([
            'medical_service_id' => 'required|integer|exists:medical_services,id',
        ]);

        $catalog = app(ResultParameterCatalog::class);

        return response()->json([
            'parameters' => $catalog->forMedicalService((int) $request->input('medical_service_id')),
        ]);
    }

    public function show(CdsRule $rule)
    {
        $rule->load(['ruleType.category', 'conditions', 'parameters', 'creator', 'updater']);
        
        return view('admin.cds.rules.show', compact('rule'));
    }

    public function edit(CdsRule $rule)
    {
        $rule->load(['ruleType.category', 'conditions', 'parameters']);
        $ruleTypes = CdsRuleType::with('category')->active()->get();

        $labCritical = null;
        if ($rule->ruleType->name === 'lab_critical') {
            $serviceCondition = $rule->conditions->firstWhere('field_name', 'investigation.medical_service_id');
            $paramCondition = $rule->conditions->first(
                fn($c) => str_starts_with($c->field_name, 'result.parameters.')
            );

            if ($serviceCondition) {
                $service = \App\Models\MedicalService::find($serviceCondition->value);

                $labCritical = [
                    'medical_service_id' => $serviceCondition->value,
                    'medical_service_name' => $service->name ?? '',
                    'parameter_key' => $paramCondition
                        ? str_replace('result.parameters.', '', $paramCondition->field_name)
                        : null,
                    'operator' => $paramCondition->operator ?? 'less_than',
                    'threshold' => $paramCondition?->getTypedValue(),
                    'parameters' => $service
                        ? app(ResultParameterCatalog::class)->forMedicalService($service->id)
                        : [],
                ];
            }
        }

        $conditionsForDisplay = old('conditions', $rule->conditions->map(fn($c) => [
            'field' => $c->field_name,
            'operator' => $c->operator,
            'value' => $c->value,
            'value_type' => $c->value_type,
        ])->toArray());

        return view('admin.cds.rules.edit', compact('rule', 'ruleTypes', 'labCritical', 'conditionsForDisplay'));
    }

    public function update(Request $request, CdsRule $rule)
    {
        $validated = $request->validate($this->ruleValidationRules(false));

        $validated['is_active'] = $request->has('is_active');

        $rule->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'severity' => $validated['severity'],
            'is_active' => $validated['is_active'],
        ]);

        $this->persistConditionsAndParameters($rule, $request);

        // Clear cache for this rule type
        $this->ruleCache->clearRuleTypeCache($rule->ruleType->name);

        return redirect()
            ->route('admin.cds.rules.index')
            ->with('success', 'CDS Rule updated successfully');
    }

    public function destroy(CdsRule $rule)
    {
        $ruleTypeName = $rule->ruleType->name;
        $rule->delete();

        // Clear cache for this rule type
        $this->ruleCache->clearRuleTypeCache($ruleTypeName);

        return redirect()
            ->route('admin.cds.rules.index')
            ->with('success', 'CDS Rule deleted successfully');
    }

    public function testRule(Request $request, CdsRule $rule)
    {
        $input = $request->validate([
            'patient_id'           => 'nullable|integer|exists:patients,id',
            'medication_id'        => 'nullable|integer|exists:medications,id',
            'medication_name'      => 'nullable|string|max:200',
            'dose_amount'          => 'nullable|numeric|min:0',
            'dose_frequency'       => 'nullable|integer|min:1|max:24',
            'dose_duration'        => 'nullable|integer|min:1',
            'patient_age_override' => 'nullable|integer|min:0|max:150',
            'patient_weight_override' => 'nullable|numeric|min:0|max:500',
        ]);

        try {
            // --- 1. Build base order ---
            $medId   = $input['medication_id'] ?? null;
            $medName = $input['medication_name'] ?? '';
            if ($medId && $medName === '') {
                $med = Medication::find($medId);
                $medName = $med ? ($med->generic_name ?: $med->brand_name ?? '') : '';
            } elseif (!$medId && $medName !== '') {
                $med = Medication::where('generic_name', 'like', "%{$medName}%")
                    ->orWhere('brand_name', 'like', "%{$medName}%")
                    ->first();
                $medId = $med?->id;
            }

            $order = [
                'medication_id'   => $medId,
                'medication_name' => $medName,
                'dosage'          => ($input['dose_amount'] ?? '') . ' mg',
                'dose_amount'     => $input['dose_amount'] ?? null,
                'dose_frequency'  => $input['dose_frequency'] ?? null,
                'dose_duration'   => $input['dose_duration'] ?? null,
            ];

            // --- 2. Build patient context (from real patient or anonymous) ---
            $patientId = $input['patient_id'] ?? null;
            $context   = ['visit_id' => 0, 'order' => $order];

            if ($patientId) {
                $patient = Patient::with(['allergies', 'pastMedicalHistory'])->find($patientId);
                if ($patient) {
                    $vitals = VitalSigns::where('patient_id', $patientId)
                        ->where('visit_id', 0)
                        ->latest('recorded_at')
                        ->first();

                    $dob = $patient->date_of_birth
                        ? \Carbon\Carbon::parse($patient->date_of_birth)->age
                        : ($input['patient_age_override'] ?? 35);

                    $context['patient_id']     = $patientId;
                    $context['patient_age']    = $dob;
                    $context['patient_weight'] = $input['patient_weight_override']
                        ?? optional($vitals)->weight
                        ?? 70;
                    $context['patient_gender'] = $patient->gender ?? 'unknown';
                    $context['patient'] = [
                        'id'      => $patient->id,
                        'name'    => $patient->full_name,
                        'age'     => $dob,
                        'gender'  => $patient->gender,
                        'weight'  => optional($vitals)->weight,
                        'height'  => optional($vitals)->height,
                        'bmi'     => optional($vitals)->bmi,
                        'bp'      => optional($vitals)->systolic_bp
                            ? optional($vitals)->systolic_bp . '/' . optional($vitals)->diastolic_bp
                            : null,
                        'allergies' => $patient->allergies
                            ->where('is_active', true)
                            ->map(fn($a) => [
                                'id'             => $a->id,
                                'medication_id'  => $a->medication_id,
                                'substance_name' => $a->substance_name,
                                'severity'       => $a->severity,
                                'reaction'       => $a->reaction,
                            ])->values()->toArray(),
                        'chronic_conditions'  => optional($patient->pastMedicalHistory)->chronic_conditions,
                        'current_medications' => optional($patient->pastMedicalHistory)->current_medications,
                    ];

                    // --- Extract creatinine from lab results and compute eGFR ---
                    $investigations = DB::table('investigations')
                        ->where('patient_id', $patientId)
                        ->where('status', 'resulted')
                        ->get(['clinical_data']);

                    $creatinineUmol = null;
                    $creatinineMgDl = null;
                    foreach ($investigations as $inv) {
                        $data   = json_decode($inv->clinical_data, true) ?? [];
                        $result = $data['result'] ?? '';
                        // Match: "Creatinine 412 μmol/L" or "Creatinine 412 umol/L"
                        if (preg_match('/creatinine[\s:]+([0-9]+(?:\.[0-9]+)?)\s*(?:\xce\xbcmol\/L|umol\/L)/i', $result, $m)) {
                            $creatinineUmol = (float) $m[1];
                            $creatinineMgDl = $creatinineUmol / 88.4;
                            break;
                        }
                        // Match: "Creatinine 1.2 mg/dL"
                        if (preg_match('/creatinine[\s:]+([0-9]+(?:\.[0-9]+)?)\s*mg\/dL/i', $result, $m)) {
                            $creatinineMgDl = (float) $m[1];
                            $creatinineUmol = $creatinineMgDl * 88.4;
                            break;
                        }
                    }

                    if ($creatinineMgDl !== null) {
                        $context['patient']['creatinine_umol_l'] = round($creatinineUmol, 1);
                        $context['patient']['creatinine_mg_dl']  = round($creatinineMgDl, 4);
                        // Pre-compute eGFR so rule handlers get it without re-querying
                        $context['patient']['egfr'] = EgfrCalculator::cockcroftGault(
                            (float) $context['patient_age'],
                            (float) ($context['patient_weight'] ?? 70),
                            (float) $creatinineMgDl,
                            $patient->gender ?? 'male'
                        );
                    }
                }
            } else {
                $context['patient_id']     = 0;
                $context['patient_age']    = $input['patient_age_override'] ?? 35;
                $context['patient_weight'] = $input['patient_weight_override'] ?? 70;
            }

            // --- 3. Run the rule handler ---
            $handler = $rule->ruleType->getHandlerInstance();
            if (method_exists($handler, 'setRuleConfiguration')) {
                $handler->setRuleConfiguration($rule);
            }

            $result           = $handler->evaluate($context);
            $matchesConditions = $rule->matchesContext($context);

            return response()->json([
                'success'           => true,
                'alert_fired'       => $result !== null,
                'matches_conditions' => $matchesConditions,
                'rule_result'       => $result,
                'context_summary'   => [
                    'patient_id'         => $context['patient_id'],
                    'patient_age'        => $context['patient_age'],
                    'patient_weight'     => $context['patient_weight'],
                    'patient_egfr'       => isset($context['patient']['egfr'])
                        ? round($context['patient']['egfr'], 1)
                        : null,
                    'patient_creatinine' => isset($context['patient']['creatinine_umol_l'])
                        ? $context['patient']['creatinine_umol_l'] . ' μmol/L'
                        : null,
                    'medication_id'      => $medId,
                    'medication_name'    => $medName,
                    'dosage'             => $order['dosage'],
                    'dose_frequency'     => $order['dose_frequency'],
                    'dose_duration'      => $order['dose_duration'],
                    'allergy_count'      => count($context['patient']['allergies'] ?? []),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

        public function test(CdsRule $rule)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'rule' => $rule->load(['conditions', 'parameters', 'ruleType']),
                'test_form' => view('admin.cds.rules.test-form', compact('rule'))->render()
            ]);
        }
        
        return view('admin.cds.rules.test', compact('rule'));
    }

    public function medicationPoliciesIndex()
    {
        $medications = Medication::with(['dosageLimits', 'storeCategory'])
            ->withCount('dosageLimits')
            ->orderBy('generic_name')
            ->get();

        $drugClasses = \App\Models\DrugClass::where('is_active', true)
            ->orderBy('category')->orderBy('name')->get();

        return view('admin.cds.medication-policies.index', compact('medications', 'drugClasses'));
    }

    public function dosageLimitsStore(Request $request)
    {
        $validated = $request->validate([
            'medication_id'            => 'required|integer|exists:medications,id',
            'age_min_years'            => 'nullable|numeric|min:0',
            'age_max_years'            => 'nullable|numeric|min:0',
            'weight_min_kg'            => 'nullable|numeric|min:0',
            'weight_max_kg'            => 'nullable|numeric|min:0',
            'mg_per_kg'                => 'nullable|numeric|min:0',
            'max_single_dose_adults'   => 'nullable|string|max:100',
            'max_daily_dose_adults'    => 'nullable|string|max:100',
            'max_duration_adults'      => 'nullable|string|max:100',
            'max_single_dose_children' => 'nullable|string|max:100',
            'max_daily_dose_children'  => 'nullable|string|max:100',
            'max_duration_children'    => 'nullable|string|max:100',
            'renal_function_adults'    => 'nullable|string',
            'renal_function_children'  => 'nullable|string',
            'liver_function_adults'    => 'nullable|string',
            'liver_function_children'  => 'nullable|string',
            'lab_results'              => 'nullable|string',
            'diagnoses'                => 'nullable|string',
            'interactions'             => 'nullable|string',
            'is_active'                => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        foreach (['renal_function_adults','renal_function_children','liver_function_adults','liver_function_children','lab_results','diagnoses','interactions'] as $jsonField) {
            if (!empty($validated[$jsonField])) {
                $decoded = json_decode($validated[$jsonField], true);
                $validated[$jsonField] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            } else {
                $validated[$jsonField] = null;
            }
        }

        CdsDosageLimit::create($validated);

        return redirect()->route('admin.cds.medication-policies.index')
            ->with('success', 'Dosage limit added successfully.');
    }

    public function dosageLimitsUpdate(Request $request, CdsDosageLimit $limit)
    {
        $validated = $request->validate([
            'age_min_years'            => 'nullable|numeric|min:0',
            'age_max_years'            => 'nullable|numeric|min:0',
            'weight_min_kg'            => 'nullable|numeric|min:0',
            'weight_max_kg'            => 'nullable|numeric|min:0',
            'mg_per_kg'                => 'nullable|numeric|min:0',
            'max_single_dose_adults'   => 'nullable|string|max:100',
            'max_daily_dose_adults'    => 'nullable|string|max:100',
            'max_duration_adults'      => 'nullable|string|max:100',
            'max_single_dose_children' => 'nullable|string|max:100',
            'max_daily_dose_children'  => 'nullable|string|max:100',
            'max_duration_children'    => 'nullable|string|max:100',
            'renal_function_adults'    => 'nullable|string',
            'renal_function_children'  => 'nullable|string',
            'liver_function_adults'    => 'nullable|string',
            'liver_function_children'  => 'nullable|string',
            'lab_results'              => 'nullable|string',
            'diagnoses'                => 'nullable|string',
            'interactions'             => 'nullable|string',
            'is_active'                => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        foreach (['renal_function_adults','renal_function_children','liver_function_adults','liver_function_children','lab_results','diagnoses','interactions'] as $jsonField) {
            if (!empty($validated[$jsonField])) {
                $decoded = json_decode($validated[$jsonField], true);
                $validated[$jsonField] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            } else {
                $validated[$jsonField] = null;
            }
        }

        $limit->update($validated);

        return redirect()->route('admin.cds.medication-policies.index')
            ->with('success', 'Dosage limit updated successfully.');
    }

    public function dosageLimitsDestroy(CdsDosageLimit $limit)
    {
        $limit->delete();

        return redirect()->route('admin.cds.medication-policies.index')
            ->with('success', 'Dosage limit deleted.');
    }

    public function searchDrugClasses(Request $request)
    {
        $q = $request->get('query', '');
        $classes = \App\Models\DrugClass::where('is_active', true)
            ->where(function($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('category', 'LIKE', "%{$q}%");
            })
            ->orderBy('category')->orderBy('name')
            ->limit(30)->get(['id','name','category']);
        return response()->json([
            'results' => $classes->map(fn($c) => [
                'id'   => 'class:' . $c->id,
                'text' => $c->name . ' (' . $c->category . ')',
            ])
        ]);
    }

    public function searchMedicationsSimple(Request $request)
    {
        $q = $request->get('query', '');
        if (strlen($q) < 2) return response()->json(['results' => []]);
        $meds = Medication::where('is_active', true)
            ->where(function($query) use ($q) {
                $query->where('generic_name', 'LIKE', "%{$q}%")
                      ->orWhere('brand_name',  'LIKE', "%{$q}%");
            })
            ->orderBy('generic_name')
            ->limit(20)->get(['id','generic_name','brand_name','strength']);
        return response()->json([
            'results' => $meds->map(fn($m) => [
                'id'   => 'med:' . $m->id,
                'text' => $m->generic_name . ($m->brand_name ? ' (' . $m->brand_name . ')' : '') . ($m->strength ? ' ' . $m->strength : ''),
            ])
        ]);
    }

    public function drugInteractionsIndex()
    {
        $rules = CdsRule::with(['ruleType.category', 'conditions', 'parameters'])
            ->whereHas('ruleType', fn($q) => $q->where('display_name', 'like', '%Drug Interaction%')
                ->orWhere('name', 'like', '%drug_interaction%'))
            ->orderBy('priority', 'desc')
            ->paginate(20);

        return view('admin.cds.rules.drug-interactions', compact('rules'));
    }

    public function allergyChecksIndex()
    {
        $rules = CdsRule::with(['ruleType.category', 'conditions', 'parameters'])
            ->whereHas('ruleType', fn($q) => $q->where('display_name', 'like', '%Allerg%')
                ->orWhere('name', 'like', '%allerg%'))
            ->orderBy('priority', 'desc')
            ->paginate(20);

        return view('admin.cds.rules.allergy-checks', compact('rules'));
    }

    public function doseRangeRulesIndex()
    {
        $rules = CdsRule::with(['ruleType.category', 'conditions', 'parameters'])
            ->whereHas('ruleType', fn($q) => $q->where('display_name', 'like', '%Dose%')
                ->orWhere('name', 'like', '%dose%'))
            ->orderBy('priority', 'desc')
            ->paginate(20);

        return view('admin.cds.rules.dose-range-rules', compact('rules'));
    }

    /**
     * Display a listing of rule categories
     */
    public function categoriesIndex()
    {
        $categories = CdsRuleCategory::withCount(['ruleTypes', 'rules'])
            ->ordered()
            ->paginate(20);

        return view('admin.cds.categories.index', compact('categories'));
    }

    /**
     * Display a specific rule category
     */
    public function categoriesShow(CdsRuleCategory $category)
    {
        $category->load(['ruleTypes' => function($query) {
            $query->withCount('rules')->orderBy('sort_order');
        }]);

        return view('admin.cds.categories.show', compact('category'));
    }

    /**
     * Display a listing of rule types
     */
    public function typesIndex()
    {
        $ruleTypes = CdsRuleType::with('category')
            ->withCount(['rules', 'activeRules'])
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.cds.types.index', compact('ruleTypes'));
    }

    /**
     * Display a specific rule type
     */
    public function typesShow(CdsRuleType $ruleType)
    {
        $ruleType->load(['category', 'rules' => function($query) {
            $query->orderBy('priority', 'desc');
        }]);

        return view('admin.cds.types.show', compact('ruleType'));
    }

    public function toggle(CdsRule $rule, Request $request)
    {
        $isActive = $request->boolean('is_active');
        $rule->update(['is_active' => $isActive]);
        
        // Clear cache for this rule type
        $this->ruleCache->clearRuleTypeCache($rule->ruleType->name);

        $status = $rule->is_active ? 'activated' : 'deactivated';
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Rule '{$rule->name}' has been {$status}",
                'is_active' => $rule->is_active
            ]);
        }
        
        return redirect()
            ->back()
            ->with('success', "Rule '{$rule->name}' has been {$status}");
    }

    public function toggleRule(CdsRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);
        
        // Clear cache for this rule type
        $this->ruleCache->clearRuleTypeCache($rule->ruleType->name);

        $status = $rule->is_active ? 'activated' : 'deactivated';
        
        return redirect()
            ->back()
            ->with('success', "Rule '{$rule->name}' has been {$status}");
    }

    public function duplicate(CdsRule $rule, Request $request)
    {
        try {
            // Create a duplicate of the rule
            $newRule = $rule->replicate();
            $newRule->name = $rule->name . ' (Copy)';
            $newRule->is_active = false; // Start inactive to allow review
            $newRule->save();

            // Duplicate conditions
            foreach ($rule->conditions as $condition) {
                $newCondition = $condition->replicate();
                $newCondition->rule_id = $newRule->id;
                $newCondition->save();
            }

            // Duplicate parameters
            foreach ($rule->parameters as $parameter) {
                $newParameter = $parameter->replicate();
                $newParameter->rule_id = $newRule->id;
                $newParameter->save();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Rule duplicated successfully as '{$newRule->name}'",
                    'redirect_url' => route('admin.cds.rules.edit', $newRule)
                ]);
            }

            return redirect()
                ->route('admin.cds.rules.edit', $newRule)
                ->with('success', "Rule duplicated successfully as '{$newRule->name}'");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to duplicate rule: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to duplicate rule: ' . $e->getMessage());
        }
    }

    public function export(CdsRule $rule)
    {
        try {
            // Load all related data
            $rule->load(['ruleType.category', 'conditions', 'parameters', 'creator', 'updater']);

            // Prepare export data
            $exportData = [
                'rule' => [
                    'name' => $rule->name,
                    'description' => $rule->description,
                    'rule_type' => $rule->ruleType->name,
                    'category' => $rule->ruleType->category->name,
                    'priority' => $rule->priority,
                    'severity' => $rule->severity,
                    'is_active' => $rule->is_active,
                ],
                'conditions' => $rule->conditions->map(function ($condition) {
                    return [
                        'field_name' => $condition->field_name,
                        'operator' => $condition->operator,
                        'value' => $condition->value,
                        'value_type' => $condition->value_type,
                        'logical_operator' => $condition->logical_operator,
                        'is_active' => $condition->is_active,
                    ];
                })->toArray(),
                'parameters' => $rule->parameters->map(function ($parameter) {
                    return [
                        'parameter_name' => $parameter->parameter_name,
                        'parameter_value' => $parameter->parameter_value,
                        'parameter_type' => $parameter->parameter_type,
                        'description' => $parameter->description,
                    ];
                })->toArray(),
                'metadata' => [
                    'exported_at' => now()->toIso8601String(),
                    'exported_by' => \Illuminate\Support\Facades\Auth::user()?->name ?? 'Unknown',
                    'system_version' => config('app.version', '1.0'),
                ]
            ];

            $filename = 'cds_rule_' . $rule->id . '_' . now()->format('Y-m-d_His') . '.json';

            return response()->json($exportData)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to export rule: ' . $e->getMessage());
        }
    }
}
