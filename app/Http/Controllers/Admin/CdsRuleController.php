<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CdsRule;
use App\Models\CdsRuleType;
use App\Models\CdsRuleCategory;
use App\Models\CdsMedicationPolicy;
use App\Services\CDS\CdsRuleCache;
use Illuminate\Http\Request;

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
            'medication_policies' => CdsMedicationPolicy::count(),
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
        $validated = $request->validate([
            'rule_type_id' => 'required|exists:cds_rule_types,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'priority' => 'required|integer|between:1,10',
            'severity' => 'required|in:info,warning,critical',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $rule = CdsRule::create($validated);

        // Clear cache for this rule type
        $ruleType = $rule->ruleType;
        $this->ruleCache->clearRuleTypeCache($ruleType->name);

        return redirect()
            ->route('admin.cds.rules.index')
            ->with('success', 'CDS Rule created successfully');
    }

    public function show(CdsRule $rule)
    {
        $rule->load(['ruleType.category', 'conditions', 'parameters', 'creator', 'updater']);
        
        return view('admin.cds.rules.show', compact('rule'));
    }

    public function edit(CdsRule $rule)
    {
        $rule->load(['ruleType.category']);
        $ruleTypes = CdsRuleType::with('category')->active()->get();
        
        return view('admin.cds.rules.edit', compact('rule', 'ruleTypes'));
    }

    public function update(Request $request, CdsRule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'priority' => 'required|integer|between:1,10',
            'severity' => 'required|in:info,warning,critical',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $rule->update($validated);

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
        $testContext = $request->validate([
            'patient_id' => 'required|integer',
            'visit_id' => 'nullable|integer',
            'order' => 'required|array',
            'order.medication_name' => 'required|string',
            'order.dosage' => 'nullable|string',
        ]);

        try {
            $handler = $rule->ruleType->getHandlerInstance();
            
            if (method_exists($handler, 'setRuleConfiguration')) {
                $handler->setRuleConfiguration($rule);
            }

            $result = $handler->evaluate($testContext);

            return response()->json([
                'success' => true,
                'matches_conditions' => $rule->matchesContext($testContext),
                'rule_result' => $result,
                'test_context' => $testContext
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
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
        $policies = CdsMedicationPolicy::with('dosageLimits')
            ->paginate(20);

        return view('admin.cds.medication-policies.index', compact('policies'));
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
