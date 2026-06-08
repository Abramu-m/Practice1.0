@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">💊 Medication Dosage Limits</h1>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item active">Medication Policies</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-3 g-3">
        <div class="col-sm-4">
            <div class="card border-0 bg-light text-center py-3">
                <div class="fs-2 fw-bold text-dark">{{ $medications->count() }}</div>
                <div class="text-muted small">Total Medications</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 bg-success bg-opacity-10 text-center py-3">
                <div class="fs-2 fw-bold text-success">{{ $medications->filter(fn($m) => $m->dosage_limits_count > 0)->count() }}</div>
                <div class="text-muted small">With CDS Limits</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 bg-warning bg-opacity-10 text-center py-3">
                <div class="fs-2 fw-bold text-warning">{{ $medications->filter(fn($m) => $m->dosage_limits_count === 0)->count() }}</div>
                <div class="text-muted small">Without CDS Limits</div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small fw-bold me-1">Filter:</span>
                        <button type="button" class="btn btn-sm btn-primary" id="filterAll" onclick="applyFilter('all')">
                            <i class="fas fa-list me-1"></i>All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" id="filterWith" onclick="applyFilter('with')">
                            <i class="fas fa-check-circle me-1"></i>With CDS Limits
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="filterWithout" onclick="applyFilter('without')">
                            <i class="fas fa-exclamation-circle me-1"></i>Without CDS Limits
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="medsTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Medication</th>
                                    <th>Strength</th>
                                    <th>Category</th>
                                    <th class="text-center">CDS Status</th>
                                    <th>Max Single (Adults)</th>
                                    <th>Max Daily (Adults)</th>
                                    <th class="text-center">Renal</th>
                                    <th class="text-center">Hepatic</th>
                                    <th class="d-none">_filter</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($medications as $i => $med)
                                @php
                                    $firstLimit = $med->dosageLimits->first();
                                    $hasLimits  = $med->dosage_limits_count > 0;
                                    $filterVal  = $hasLimits ? 'with' : 'without';
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                    <td>
                                        <strong>{{ $med->generic_name ?? '—' }}</strong>
                                        @if($med->brand_name && $med->brand_name !== $med->generic_name)
                                            <br><small class="text-muted">{{ $med->brand_name }}</small>
                                        @endif
                                    </td>
                                    <td><small>{{ $med->strength ?? '—' }}</small></td>
                                    <td><small class="text-muted">{{ optional($med->storeCategory)->name ?? '—' }}</small></td>
                                    <td class="text-center">
                                        @if($hasLimits)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>{{ $med->dosage_limits_count }} limit{{ $med->dosage_limits_count !== 1 ? 's' : '' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No limits</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $firstLimit?->max_single_dose_adults ?? '—' }}</small></td>
                                    <td><small>{{ $firstLimit?->max_daily_dose_adults ?? '—' }}</small></td>
                                    <td class="text-center">
                                        @if($firstLimit && ($firstLimit->renal_function_adults || $firstLimit->renal_function_children))
                                            <span class="badge bg-info text-dark"><i class="fas fa-check"></i></span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($firstLimit && ($firstLimit->liver_function_adults || $firstLimit->liver_function_children))
                                            <span class="badge bg-warning text-dark"><i class="fas fa-check"></i></span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="d-none">{{ $filterVal }}</td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            {{-- Add limit --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Add limit"
                                                    onclick="openAddModal({{ $med->id }}, '{{ addslashes($med->generic_name ?? $med->brand_name) }}')">
                                                <i class="fas fa-plus"></i>
                                            </button>

                                            @foreach($med->dosageLimits as $lim)
                                            @php
                                                $limJson = json_encode([
                                                    'id'                       => $lim->id,
                                                    'medication_id'            => $med->id,
                                                    'medication'               => $med->generic_name ?? $med->brand_name,
                                                    'age_min_years'            => $lim->age_min_years,
                                                    'age_max_years'            => $lim->age_max_years,
                                                    'weight_min_kg'            => $lim->weight_min_kg,
                                                    'weight_max_kg'            => $lim->weight_max_kg,
                                                    'mg_per_kg'                => $lim->mg_per_kg,
                                                    'max_single_dose_adults'   => $lim->max_single_dose_adults,
                                                    'max_daily_dose_adults'    => $lim->max_daily_dose_adults,
                                                    'max_duration_adults'      => $lim->max_duration_adults,
                                                    'max_single_dose_children' => $lim->max_single_dose_children,
                                                    'max_daily_dose_children'  => $lim->max_daily_dose_children,
                                                    'max_duration_children'    => $lim->max_duration_children,
                                                    'renal_function_adults'    => $lim->renal_function_adults,
                                                    'renal_function_children'  => $lim->renal_function_children,
                                                    'liver_function_adults'    => $lim->liver_function_adults,
                                                    'liver_function_children'  => $lim->liver_function_children,
                                                    'lab_results'              => $lim->lab_results,
                                                    'diagnoses'                => $lim->diagnoses,
                                                    'interactions'             => $lim->interactions,
                                                    'is_active'                => $lim->is_active,
                                                ]);
                                            @endphp
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit limit #{{ $lim->id }}"
                                                    data-lim="{{ $limJson }}"
                                                    onclick="openEditModal(this)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST"
                                                  action="{{ route('admin.cds.dosage-limits.destroy', $lim->id) }}"
                                                  onsubmit="return confirm('Delete this dosage limit?')"
                                                  class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete limit #{{ $lim->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Add / Edit Modal ===================== --}}
<div class="modal fade" id="limitFormModal" tabindex="-1" aria-labelledby="limitFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="limitFormModalLabel">
                    <i class="fas fa-capsules me-2 text-primary"></i>
                    <span id="formModalTitle">Dosage Limit</span> — <span id="formMedName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="limitForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="medication_id" id="formMedicationId">
                <input type="hidden" name="renal_function_adults"   id="h_renal_adults">
                <input type="hidden" name="renal_function_children" id="h_renal_children">
                <input type="hidden" name="liver_function_adults"   id="h_liver_adults">
                <input type="hidden" name="liver_function_children" id="h_liver_children">
                <div class="modal-body" style="max-height:75vh;overflow-y:auto;">

                    {{-- Age / Weight / mg_per_kg --}}
                    <h6 class="fw-bold border-bottom pb-1 mb-3">Applicability</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-3">
                            <label class="form-label form-label-sm">Age min (yrs)</label>
                            <input type="number" step="0.1" min="0" name="age_min_years" id="f_age_min" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label form-label-sm">Age max (yrs)</label>
                            <input type="number" step="0.1" min="0" name="age_max_years" id="f_age_max" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label form-label-sm">Weight min (kg)</label>
                            <input type="number" step="0.1" min="0" name="weight_min_kg" id="f_wt_min" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label form-label-sm">Weight max (kg)</label>
                            <input type="number" step="0.1" min="0" name="weight_max_kg" id="f_wt_max" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label form-label-sm">mg / kg (dose calc)</label>
                            <input type="number" step="0.001" min="0" name="mg_per_kg" id="f_mg_per_kg" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-3 d-flex align-items-end">
                            <div class="form-check mb-1">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" id="f_is_active" value="1" checked>
                                <label class="form-check-label" for="f_is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    {{-- Adult Dosing --}}
                    <h6 class="fw-bold border-bottom pb-1 mb-3">Adult Dosing</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <label class="form-label form-label-sm">Max Single Dose</label>
                            <input type="text" name="max_single_dose_adults" id="f_adult_single" class="form-control form-control-sm" placeholder="e.g. 1000">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label form-label-sm">Max Daily Dose</label>
                            <input type="text" name="max_daily_dose_adults" id="f_adult_daily" class="form-control form-control-sm" placeholder="e.g. 4000">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label form-label-sm">Max Duration</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="1" step="1" name="max_duration_adults" id="f_adult_duration" class="form-control form-control-sm" placeholder="e.g. 5">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>

                    {{-- Paediatric Dosing --}}
                    <h6 class="fw-bold border-bottom pb-1 mb-3">Paediatric Dosing</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <label class="form-label form-label-sm">Max Single Dose</label>
                            <input type="text" name="max_single_dose_children" id="f_child_single" class="form-control form-control-sm" placeholder="e.g. 15">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label form-label-sm">Max Daily Dose</label>
                            <input type="text" name="max_daily_dose_children" id="f_child_daily" class="form-control form-control-sm" placeholder="e.g. 60">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label form-label-sm">Max Duration</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="1" step="1" name="max_duration_children" id="f_child_duration" class="form-control form-control-sm" placeholder="e.g. 3">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>

                    {{-- Renal --}}
                    <h6 class="fw-bold border-bottom pb-1 mb-3">Renal Function Limits</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Adults</label>
                            <div class="border rounded p-2">
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">Creatinine</small></div>
                                    <div class="col-3"><select id="ra_creatinine_op" class="form-select form-select-sm"><option value="<" selected>&lt;</option><option value=">">&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-3"><input type="number" step="any" min="0" id="ra_creatinine_val" class="form-control form-control-sm" placeholder="value"></div>
                                    <div class="col-3"><select id="ra_creatinine_unit" class="form-select form-select-sm"><option value="umol/l">µmol/L</option><option value="mg/dl">mg/dL</option></select></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">eGFR</small></div>
                                    <div class="col-3"><select id="ra_egfr_op" class="form-select form-select-sm"><option value="<" selected>&lt;</option><option value=">">&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-3"><input type="number" step="any" min="0" id="ra_egfr_val" class="form-control form-control-sm" placeholder="value"></div>
                                    <div class="col-3"><small class="text-muted">mL/min</small></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">Urea</small></div>
                                    <div class="col-3"><select id="ra_urea_op" class="form-select form-select-sm"><option value="<" selected>&lt;</option><option value=">">&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-3"><input type="number" step="any" min="0" id="ra_urea_val" class="form-control form-control-sm" placeholder="value"></div>
                                    <div class="col-3"><select id="ra_urea_unit" class="form-select form-select-sm"><option value="mmol/l">mmol/L</option><option value="mg/dl">mg/dL</option></select></div>
                                </div>
                                <div class="row g-1 mt-1 mb-1 align-items-center">
                                    <div class="col-3"><small class="text-muted">Action</small></div>
                                    <div class="col-9">
                                        <select id="ra_action" class="form-select form-select-sm" onchange="toggleMaxDaily('ra')">
                                            <option value="">— none —</option>
                                            <option value="reduce">Reduce dose</option>
                                            <option value="avoid">Avoid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-1 align-items-center mt-1" id="ra_max_daily_row" style="display:none">
                                    <div class="col-3"><small class="text-muted">Max daily</small></div>
                                    <div class="col-9"><input type="number" step="any" min="0" id="ra_max_daily" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Children</label>
                            <div class="border rounded p-2">
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">Creatinine</small></div>
                                    <div class="col-3"><select id="rc_creatinine_op" class="form-select form-select-sm"><option value="<" selected>&lt;</option><option value=">">&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-3"><input type="number" step="any" min="0" id="rc_creatinine_val" class="form-control form-control-sm" placeholder="value"></div>
                                    <div class="col-3"><select id="rc_creatinine_unit" class="form-select form-select-sm"><option value="umol/l">µmol/L</option><option value="mg/dl">mg/dL</option></select></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">eGFR</small></div>
                                    <div class="col-3"><select id="rc_egfr_op" class="form-select form-select-sm"><option value="<" selected>&lt;</option><option value=">">&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-3"><input type="number" step="any" min="0" id="rc_egfr_val" class="form-control form-control-sm" placeholder="value"></div>
                                    <div class="col-3"><small class="text-muted">mL/min</small></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">Urea</small></div>
                                    <div class="col-3"><select id="rc_urea_op" class="form-select form-select-sm"><option value="<" selected>&lt;</option><option value=">">&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-3"><input type="number" step="any" min="0" id="rc_urea_val" class="form-control form-control-sm" placeholder="value"></div>
                                    <div class="col-3"><select id="rc_urea_unit" class="form-select form-select-sm"><option value="mmol/l">mmol/L</option><option value="mg/dl">mg/dL</option></select></div>
                                </div>
                                <div class="row g-1 mt-1 mb-1 align-items-center">
                                    <div class="col-3"><small class="text-muted">Action</small></div>
                                    <div class="col-9">
                                        <select id="rc_action" class="form-select form-select-sm" onchange="toggleMaxDaily('rc')">
                                            <option value="">— none —</option>
                                            <option value="reduce">Reduce dose</option>
                                            <option value="avoid">Avoid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-1 align-items-center mt-1" id="rc_max_daily_row" style="display:none">
                                    <div class="col-3"><small class="text-muted">Max daily</small></div>
                                    <div class="col-9"><input type="number" step="any" min="0" id="rc_max_daily" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Liver --}}
                    <h6 class="fw-bold border-bottom pb-1 mb-3">Liver Function Limits</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Adults</label>
                            <div class="border rounded p-2">
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">ALT <span style="font-size:.7em">U/L</span></small></div>
                                    <div class="col-3"><select id="la_alt_op" class="form-select form-select-sm"><option value="<">&lt;</option><option value=">" selected>&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-6"><input type="number" step="any" min="0" id="la_alt_val" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">AST <span style="font-size:.7em">U/L</span></small></div>
                                    <div class="col-3"><select id="la_ast_op" class="form-select form-select-sm"><option value="<">&lt;</option><option value=">" selected>&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-6"><input type="number" step="any" min="0" id="la_ast_val" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">ALP <span style="font-size:.7em">U/L</span></small></div>
                                    <div class="col-3"><select id="la_alp_op" class="form-select form-select-sm"><option value="<">&lt;</option><option value=">" selected>&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-6"><input type="number" step="any" min="0" id="la_alp_val" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">GGT <span style="font-size:.7em">U/L</span></small></div>
                                    <div class="col-3"><select id="la_ggt_op" class="form-select form-select-sm"><option value="<">&lt;</option><option value=">" selected>&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-6"><input type="number" step="any" min="0" id="la_ggt_val" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                                <div class="row g-1 mt-1 align-items-center">
                                    <div class="col-3"><small class="text-muted">Action</small></div>
                                    <div class="col-9">
                                        <select id="la_action" class="form-select form-select-sm">
                                            <option value="">— none —</option>
                                            <option value="reduce">Reduce dose</option>
                                            <option value="avoid">Avoid</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Children</label>
                            <div class="border rounded p-2">
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">ALT <span style="font-size:.7em">U/L</span></small></div>
                                    <div class="col-3"><select id="lc_alt_op" class="form-select form-select-sm"><option value="<">&lt;</option><option value=">" selected>&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-6"><input type="number" step="any" min="0" id="lc_alt_val" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">AST <span style="font-size:.7em">U/L</span></small></div>
                                    <div class="col-3"><select id="lc_ast_op" class="form-select form-select-sm"><option value="<">&lt;</option><option value=">" selected>&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-6"><input type="number" step="any" min="0" id="lc_ast_val" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">ALP <span style="font-size:.7em">U/L</span></small></div>
                                    <div class="col-3"><select id="lc_alp_op" class="form-select form-select-sm"><option value="<">&lt;</option><option value=">" selected>&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-6"><input type="number" step="any" min="0" id="lc_alp_val" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                                <div class="row g-1 mb-2 align-items-center">
                                    <div class="col-3"><small class="text-muted">GGT <span style="font-size:.7em">U/L</span></small></div>
                                    <div class="col-3"><select id="lc_ggt_op" class="form-select form-select-sm"><option value="<">&lt;</option><option value=">" selected>&gt;</option><option value="=">=</option><option value="<=">&lt;=</option><option value=">=">&gt;=</option></select></div>
                                    <div class="col-6"><input type="number" step="any" min="0" id="lc_ggt_val" class="form-control form-control-sm" placeholder="value"></div>
                                </div>
                                <div class="row g-1 mt-1 align-items-center">
                                    <div class="col-3"><small class="text-muted">Action</small></div>
                                    <div class="col-9">
                                        <select id="lc_action" class="form-select form-select-sm">
                                            <option value="">— none —</option>
                                            <option value="reduce">Reduce dose</option>
                                            <option value="avoid">Avoid</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Lab Results --}}
                    <input type="hidden" name="lab_results" id="h_lab_results">
                    <h6 class="fw-bold border-bottom pb-1 mb-3">Lab Result Conditions</h6>
                    <div id="lab-rows" class="mb-2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-4" onclick="addLabRow()">+ Add Lab Condition</button>

                    {{-- Diagnoses --}}
                    <input type="hidden" name="diagnoses" id="h_diagnoses">
                    <h6 class="fw-bold border-bottom pb-1 mb-3">Diagnosis Conditions</h6>
                    <div id="dx-rows" class="mb-2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-4" onclick="addDxRow()">+ Add Diagnosis</button>

                    {{-- Drug Interactions --}}
                    <input type="hidden" name="interactions" id="h_interactions">
                    <h6 class="fw-bold border-bottom pb-1 mb-3">Drug Interactions</h6>
                    <p class="text-muted small mb-2">Flag when this medication is co-prescribed with the following drugs or drug classes.</p>
                    <div id="ix-rows" class="mb-2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addIxRow('class','','','caution',true)">+ Add Interaction</button>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="formSubmitBtn">Save Limit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let medsTable;

document.addEventListener('DOMContentLoaded', function () {
    medsTable = new DataTable('#medsTable', {
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 10] },
            { searchable: true, visible: false, targets: 9 }
        ],
        language: {
            search: 'Search medications:',
            emptyTable: 'No medications found.',
            zeroRecords: 'No medications match the current filter.'
        }
    });
});

function applyFilter(type) {
    document.getElementById('filterAll').className     = 'btn btn-sm btn-outline-primary';
    document.getElementById('filterWith').className    = 'btn btn-sm btn-outline-success';
    document.getElementById('filterWithout').className = 'btn btn-sm btn-outline-warning';

    if (type === 'all') {
        document.getElementById('filterAll').className = 'btn btn-sm btn-primary';
        medsTable.column(9).search('').draw();
    } else if (type === 'with') {
        document.getElementById('filterWith').className = 'btn btn-sm btn-success';
        medsTable.column(9).search('^with$', true, false).draw();
    } else {
        document.getElementById('filterWithout').className = 'btn btn-sm btn-warning';
        medsTable.column(9).search('^without$', true, false).draw();
    }
}

const storeUrl  = "{{ route('admin.cds.dosage-limits.store') }}";
const updateUrl = (id) => storeUrl.replace('/limits', '/limits/' + id);

function openAddModal(medicationId, medicationName) {
    resetForm();
    document.getElementById('formModalTitle').textContent  = 'Add Dosage Limit';
    document.getElementById('formMedName').textContent     = medicationName;
    document.getElementById('formMedicationId').value      = medicationId;
    document.getElementById('limitForm').action            = storeUrl;
    document.getElementById('formMethod').value            = 'POST';
    document.getElementById('formSubmitBtn').textContent   = 'Add Limit';
    new bootstrap.Modal(document.getElementById('limitFormModal')).show();
}

function openEditModal(btn) {
    const data = JSON.parse(btn.getAttribute('data-lim') || '{}');
    resetForm();
    document.getElementById('formModalTitle').textContent  = 'Edit Dosage Limit #' + data.id;
    document.getElementById('formMedName').textContent     = data.medication || '';
    document.getElementById('formMedicationId').value      = data.medication_id;
    document.getElementById('limitForm').action            = updateUrl(data.id);
    document.getElementById('formMethod').value            = 'PUT';
    document.getElementById('formSubmitBtn').textContent   = 'Save Changes';

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
    set('f_age_min',        data.age_min_years);
    set('f_age_max',        data.age_max_years);
    set('f_wt_min',         data.weight_min_kg);
    set('f_wt_max',         data.weight_max_kg);
    set('f_mg_per_kg',      data.mg_per_kg);
    set('f_adult_single',   data.max_single_dose_adults);
    set('f_adult_daily',    data.max_daily_dose_adults);
    set('f_adult_duration', data.max_duration_adults);
    set('f_child_single',   data.max_single_dose_children);
    set('f_child_daily',    data.max_daily_dose_children);
    set('f_child_duration', data.max_duration_children);

    fillRenalInputs('ra', data.renal_function_adults);
    fillRenalInputs('rc', data.renal_function_children);
    fillLiverInputs('la', data.liver_function_adults);
    fillLiverInputs('lc', data.liver_function_children);
    fillLabInputs(data.lab_results);
    fillDxInputs(data.diagnoses);
    fillIxInputs(data.interactions);

    const activeChk = document.getElementById('f_is_active');
    if (activeChk) activeChk.checked = data.is_active == 1 || data.is_active === true;

    new bootstrap.Modal(document.getElementById('limitFormModal')).show();
}

function resetForm() {
    const form = document.getElementById('limitForm');
    form.reset();
    document.getElementById('f_is_active').checked = true;
    resetRenalLiver();
    resetLabDx();
    resetIx();
}

// ── Renal / Liver JSON helpers ───────────────────────────────────────────────
const RENAL_PARAMS = ['creatinine', 'egfr', 'urea'];
const LIVER_PARAMS = ['alt', 'ast', 'alp', 'ggt'];

function toggleMaxDaily(prefix) {
    const row = document.getElementById(prefix + '_max_daily_row');
    if (row) row.style.display = document.getElementById(prefix + '_action')?.value === 'reduce' ? '' : 'none';
}

function buildRenalJson(prefix) {
    const obj = {};
    RENAL_PARAMS.forEach(p => {
        const val = document.getElementById(`${prefix}_${p}_val`)?.value?.trim();
        if (val !== '' && val !== null && val !== undefined) {
            obj[p] = { operator: document.getElementById(`${prefix}_${p}_op`)?.value || '<', value: parseFloat(val) };
            const u = document.getElementById(`${prefix}_${p}_unit`);
            if (u) obj[p].unit = u.value;
        }
    });
    const action = document.getElementById(`${prefix}_action`)?.value;
    if (action) {
        obj.action = action;
        if (action === 'reduce') {
            const md = document.getElementById(`${prefix}_max_daily`)?.value?.trim();
            if (md) obj.max_daily = md;
        }
    }
    return Object.keys(obj).length ? JSON.stringify(obj) : '';
}

function buildLiverJson(prefix) {
    const obj = {};
    LIVER_PARAMS.forEach(p => {
        const val = document.getElementById(`${prefix}_${p}_val`)?.value?.trim();
        if (val !== '' && val !== null && val !== undefined) {
            obj[p] = { operator: document.getElementById(`${prefix}_${p}_op`)?.value || '>', value: parseFloat(val) };
        }
    });
    const action = document.getElementById(`${prefix}_action`)?.value;
    if (action) obj.action = action;
    return Object.keys(obj).length ? JSON.stringify(obj) : '';
}

function fillRenalInputs(prefix, data) {
    if (!data) return;
    RENAL_PARAMS.forEach(p => {
        if (data[p] !== undefined) {
            const opEl   = document.getElementById(`${prefix}_${p}_op`);
            const valEl  = document.getElementById(`${prefix}_${p}_val`);
            const unitEl = document.getElementById(`${prefix}_${p}_unit`);
            if (opEl)            opEl.value  = data[p].operator ?? '<';
            if (valEl)           valEl.value = data[p].value    ?? '';
            if (unitEl && data[p].unit) unitEl.value = data[p].unit;
        }
    });
    const actionEl = document.getElementById(`${prefix}_action`);
    if (actionEl) actionEl.value = data.action ?? '';
    toggleMaxDaily(prefix);
    const mdEl = document.getElementById(`${prefix}_max_daily`);
    if (mdEl) mdEl.value = data.max_daily ?? '';
}

function fillLiverInputs(prefix, data) {
    if (!data) return;
    LIVER_PARAMS.forEach(p => {
        if (data[p] !== undefined) {
            const opEl  = document.getElementById(`${prefix}_${p}_op`);
            const valEl = document.getElementById(`${prefix}_${p}_val`);
            if (opEl)  opEl.value  = data[p].operator ?? '>';
            if (valEl) valEl.value = data[p].value    ?? '';
        }
    });
    const actionEl = document.getElementById(`${prefix}_action`);
    if (actionEl) actionEl.value = data.action ?? '';
}

function resetRenalLiver() {
    ['ra', 'rc'].forEach(prefix => {
        RENAL_PARAMS.forEach(p => {
            const v = document.getElementById(`${prefix}_${p}_val`); if (v) v.value = '';
            const o = document.getElementById(`${prefix}_${p}_op`);  if (o) o.value = '<';
            const u = document.getElementById(`${prefix}_${p}_unit`); if (u) u.selectedIndex = 0;
        });
        const a = document.getElementById(`${prefix}_action`); if (a) a.value = '';
        const m = document.getElementById(`${prefix}_max_daily`); if (m) m.value = '';
        toggleMaxDaily(prefix);
    });
    ['la', 'lc'].forEach(prefix => {
        LIVER_PARAMS.forEach(p => {
            const v = document.getElementById(`${prefix}_${p}_val`); if (v) v.value = '';
            const o = document.getElementById(`${prefix}_${p}_op`);  if (o) o.value = '>';
        });
        const a = document.getElementById(`${prefix}_action`); if (a) a.value = '';
    });
}

document.getElementById('limitForm').addEventListener('submit', function () {
    document.getElementById('h_renal_adults').value   = buildRenalJson('ra');
    document.getElementById('h_renal_children').value = buildRenalJson('rc');
    document.getElementById('h_liver_adults').value   = buildLiverJson('la');
    document.getElementById('h_liver_children').value = buildLiverJson('lc');
    document.getElementById('h_lab_results').value    = buildLabJson();
    document.getElementById('h_diagnoses').value      = buildDxJson();
    document.getElementById('h_interactions').value   = buildIxJson();
});

// ── Lab / Diagnosis structured row helpers ───────────────────────────────────
function addLabRow(inv_id, name, operator, value) {
    inv_id   = inv_id   ?? '';
    name     = name     ?? '';
    operator = operator ?? '>';
    value    = value    ?? '';
    const html = `
    <div class="input-group mb-2 lab-row">
        <select class="form-select form-select-sm lab-svc-select flex-grow-1">${inv_id ? `<option value="${inv_id}" selected>${name}</option>` : ''}</select>
        <select class="form-select form-select-sm" style="max-width:75px">
            <option value=">"  ${operator==='>'  ?'selected':''}>></option>
            <option value=">=" ${operator==='>='?'selected':''}>≥</option>
            <option value="<"  ${operator==='<'  ?'selected':''}><</option>
            <option value="<=" ${operator==='<='?'selected':''}>≤</option>
            <option value="="  ${operator==='='  ?'selected':''}>=</option>
        </select>
        <input type="number" class="form-control form-control-sm" placeholder="value" value="${value}" style="max-width:100px">
        <button type="button" class="btn btn-outline-danger btn-sm px-2" onclick="this.closest('.lab-row').remove()" title="Remove">✕</button>
    </div>`;
    const container = document.getElementById('lab-rows');
    container.insertAdjacentHTML('beforeend', html);
    const selects = container.querySelectorAll('.lab-svc-select');
    const sel = selects[selects.length - 1];
    $(sel).select2({
        dropdownParent: $('#limitFormModal'),
        placeholder: 'Search lab service…',
        minimumInputLength: 2,
        ajax: {
            url: '/api/medical-services/search',
            dataType: 'json',
            data: params => ({ query: params.term, lab_only: true, limit: 20 }),
            processResults: res => ({
                results: (res.data || []).map(s => ({ id: s.id, text: s.name + (s.unit ? ' (' + s.unit + ')' : '') }))
            })
        }
    });
}

function addDxRow(icd_code, description, action) {
    icd_code    = icd_code    ?? '';
    description = description ?? '';
    action      = action      ?? 'caution';
    const label = icd_code ? icd_code + ' \u2013 ' + description : '';
    const html = `
    <div class="input-group mb-2 dx-row">
        <select class="form-select form-select-sm dx-icd-select flex-grow-1">${icd_code ? `<option value="${icd_code}" selected>${label}</option>` : ''}</select>
        <select class="form-select form-select-sm" style="max-width:150px">
            <option value="caution"         ${action==='caution'         ?'selected':''}>Caution</option>
            <option value="avoid"           ${action==='avoid'           ?'selected':''}>Avoid</option>
            <option value="contraindicated" ${action==='contraindicated' ?'selected':''}>Contraindicated</option>
            <option value="note"            ${action==='note'            ?'selected':''}>Note</option>
        </select>
        <button type="button" class="btn btn-outline-danger btn-sm px-2" onclick="this.closest('.dx-row').remove()" title="Remove">✕</button>
    </div>`;
    const container = document.getElementById('dx-rows');
    container.insertAdjacentHTML('beforeend', html);
    const selects = container.querySelectorAll('.dx-icd-select');
    const sel = selects[selects.length - 1];
    $(sel).select2({
        dropdownParent: $('#limitFormModal'),
        placeholder: 'Search ICD-10 code or description…',
        minimumInputLength: 2,
        ajax: {
            url: '/api/icd10/search',
            dataType: 'json',
            data: params => ({ query: params.term, type: 'code', limit: 15 }),
            processResults: res => ({
                results: (res.data || []).map(d => ({ id: d.code, text: d.code + ' \u2013 ' + d.description }))
            })
        }
    });
}

function buildLabJson() {
    const arr = [];
    document.querySelectorAll('#lab-rows .lab-row').forEach(row => {
        const sel = row.querySelector('.lab-svc-select');
        const op  = row.querySelectorAll('select')[1]?.value;
        const val = row.querySelector('input[type="number"]')?.value?.trim();
        if (sel?.value && val !== '' && val !== undefined) {
            arr.push({ inv_id: parseInt(sel.value), operator: op || '>', value: parseFloat(val) });
        }
    });
    return arr.length ? JSON.stringify(arr) : '';
}

function buildDxJson() {
    const arr = [];
    document.querySelectorAll('#dx-rows .dx-row').forEach(row => {
        const sel    = row.querySelector('.dx-icd-select');
        const action = row.querySelectorAll('select')[1]?.value;
        if (sel?.value) {
            const rawText   = sel.options[sel.selectedIndex]?.text || '';
            const sepIdx    = rawText.indexOf(' \u2013 ');
            const description = sepIdx >= 0 ? rawText.slice(sepIdx + 3) : rawText;
            arr.push({ icd_code: sel.value, description, action: action || 'caution' });
        }
    });
    return arr.length ? JSON.stringify(arr) : '';
}

function fillLabInputs(data) {
    document.getElementById('lab-rows').innerHTML = '';
    if (!Array.isArray(data)) return;
    data.forEach(item => addLabRow(item.inv_id || '', item.name || String(item.inv_id || ''), item.operator || '>', item.value ?? ''));
}

function fillDxInputs(data) {
    document.getElementById('dx-rows').innerHTML = '';
    if (!Array.isArray(data)) return;
    data.forEach(item => {
        const code = item.icd_code || '';
        const desc = item.description || item.diagnosis || '';
        addDxRow(code, desc, item.action || 'caution');
    });
}

function resetLabDx() {
    document.getElementById('lab-rows').innerHTML = '';
    document.getElementById('dx-rows').innerHTML  = '';
}

// ── Drug Interaction helpers ──────────────────────────────────────────────────
const IX_SEARCH_URL      = '{{ route("admin.cds.dosage-limits.search-drug-classes") }}';
const IX_MED_SEARCH_URL  = '{{ route("admin.cds.dosage-limits.search-medications") }}';

function addIxRow(type, id, label, severity, autoOpen) {
    type     = type     ?? '';
    id       = id       ?? '';
    label    = label    ?? '';
    severity = severity ?? 'caution';
    autoOpen = autoOpen ?? false;
    const existingOpt = id ? `<option value="${id}" selected>${label}</option>` : '';
    const html = `
    <div class="card mb-2 ix-row border-0 bg-light">
        <div class="card-body p-2">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select class="form-select form-select-sm ix-type-select" style="width:130px" onchange="switchIxType(this)">
                        <option value="class"      ${type==='class'      ?'selected':''}>Drug Class</option>
                        <option value="medication" ${type==='medication' ?'selected':''}>Medication</option>
                    </select>
                </div>
                <div class="col">
                    <select class="form-select form-select-sm ix-target-select">${existingOpt}</select>
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" style="width:135px">
                        <option value="caution"         ${severity==='caution'         ?'selected':''}>Caution</option>
                        <option value="avoid"           ${severity==='avoid'           ?'selected':''}>Avoid</option>
                        <option value="contraindicated" ${severity==='contraindicated' ?'selected':''}>Contraindicated</option>
                        <option value="monitor"         ${severity==='monitor'         ?'selected':''}>Monitor</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm px-2"
                            onclick="this.closest('.ix-row').remove()" title="Remove">✕</button>
                </div>
            </div>
        </div>
    </div>`;
    const container = document.getElementById('ix-rows');
    container.insertAdjacentHTML('beforeend', html);
    const cards   = container.querySelectorAll('.ix-row');
    const newCard = cards[cards.length - 1];
    const sel     = newCard.querySelector('.ix-target-select');
    initIxSelect(sel, type || 'class', autoOpen);
}

function initIxSelect(sel, type, autoOpen) {
    const isClass = type !== 'medication';
    $(sel).select2({
        dropdownParent: $('#limitFormModal'),
        placeholder: isClass ? 'Search drug class…' : 'Search medication…',
        minimumInputLength: isClass ? 0 : 2,
        ajax: {
            url: isClass ? IX_SEARCH_URL : IX_MED_SEARCH_URL,
            dataType: 'json',
            data: params => ({ query: params.term || '' }),
            processResults: res => ({ results: res.results || [] })
        }
    });
    if (autoOpen) $(sel).select2('open');
}

function switchIxType(typeSelect) {
    const row = typeSelect.closest('.ix-row');
    const sel = row.querySelector('.ix-target-select');
    $(sel).select2('destroy');
    $(sel).empty();
    initIxSelect(sel, typeSelect.value);
}

function buildIxJson() {
    const arr = [];
    document.querySelectorAll('#ix-rows .ix-row').forEach(row => {
        const typeEl   = row.querySelector('.ix-type-select');
        const targetEl = row.querySelector('.ix-target-select');
        const sevEl    = row.querySelectorAll('select')[2];
        const val = targetEl?.value;
        if (!val) return;
        arr.push({
            type:     typeEl?.value || 'class',
            id:       val,
            label:    targetEl.options[targetEl.selectedIndex]?.text || '',
            severity: sevEl?.value || 'caution',
        });
    });
    return arr.length ? JSON.stringify(arr) : '';
}

function fillIxInputs(data) {
    document.getElementById('ix-rows').innerHTML = '';
    if (!Array.isArray(data)) return;
    data.forEach(item => addIxRow(item.type || 'class', item.id || '', item.label || '', item.severity || 'caution'));
}

function resetIx() {
    document.getElementById('ix-rows').innerHTML = '';
}
</script>
@endpush
@endsection