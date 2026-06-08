@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">⚠️ Allergy Check Rules</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item">Medication Safety</li>
                            <li class="breadcrumb-item active">Allergy Checks</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.cds.rules.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> New Rule
                    </a>
                    <a href="{{ route('admin.cds.rules.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-1"></i> All Rules
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Allergy Check Rules ({{ $rules->total() }} total)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Rule Name</th>
                                    <th>Description</th>
                                    <th class="text-center">Priority</th>
                                    <th class="text-center">Severity</th>
                                    <th class="text-center">Conditions</th>
                                    <th class="text-center">Status</th>
                                    <th>Updated</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $i => $rule)
                                @php
                                    $ruleData = json_encode([
                                        'id'           => $rule->id,
                                        'name'         => $rule->name,
                                        'description'  => $rule->description,
                                        'severity'     => $rule->severity,
                                        'priority'     => $rule->priority,
                                        'is_active'    => $rule->is_active,
                                        'message'      => $rule->message ?? null,
                                        'type_display' => $rule->ruleType->display_name ?? '—',
                                        'conditions'   => $rule->conditions->map(fn($c) => [
                                            'field_name'       => $c->field_name,
                                            'operator'         => $c->operator,
                                            'value'            => $c->value,
                                            'value_type'       => $c->value_type,
                                            'logical_operator' => $c->logical_operator,
                                        ]),
                                        'parameters'   => $rule->parameters->map(fn($p) => [
                                            'name'  => $p->name,
                                            'value' => $p->value,
                                        ]),
                                        'edit_url' => route('admin.cds.rules.edit', $rule),
                                        'show_url' => route('admin.cds.rules.show', $rule),
                                    ]);
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $rules->firstItem() + $i }}</td>
                                    <td><strong>{{ $rule->name }}</strong></td>
                                    <td><small class="text-muted">{{ Str::limit($rule->description, 60) ?? '—' }}</small></td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $rule->priority >= 8 ? 'danger' : ($rule->priority >= 5 ? 'warning' : 'secondary') }}">
                                            {{ $rule->priority }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }}">
                                            {{ ucfirst($rule->severity) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $rule->conditions->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                            {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td><small class="text-muted">{{ $rule->updated_at->diffForHumans() }}</small></td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#ruleDetailModal"
                                                data-rule='{{ $ruleData }}'>
                                            <i class="fas fa-eye me-1"></i> View
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                                        No allergy check rules found.
                                        <br>
                                        <a href="{{ route('admin.cds.rules.create') }}" class="btn btn-primary btn-sm mt-2">Create one</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($rules->hasPages())
                        <div class="px-3 py-2">{{ $rules->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.cds.rules._rule_modal')
@endsection
