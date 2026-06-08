@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">🔍 View CDS Rule</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.rules.index') }}">Rules</a></li>
                            <li class="breadcrumb-item active">{{ $rule->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.cds.rules.edit', $rule) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Rule
                    </a>
                    <button class="btn btn-outline-success" onclick="testRule()">
                        <i class="fas fa-play"></i> Test Rule
                    </button>
                    <a href="{{ route('admin.cds.rules.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Rules
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Rule Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📝 Rule Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>{{ $rule->name }}</h4>
                            <p class="text-muted mb-0">
                                {{ $rule->description ?: 'No description provided.' }}
                            </p>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-uppercase text-muted fw-bold">Category</small><br>
                            <span class="badge badge-outline-secondary mt-1">{{ $rule->ruleType->category->name ?? 'No Category' }}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-uppercase text-muted fw-bold">Type</small><br>
                            <span class="badge bg-info mt-1">{{ $rule->ruleType->display_name ?? 'No Type' }}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-uppercase text-muted fw-bold">Priority</small><br>
                            <span class="badge bg-{{ $rule->priority >= 8 ? 'danger' : ($rule->priority >= 5 ? 'warning' : 'secondary') }} mt-1">
                                {{ $rule->priority }} / 10
                            </span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-uppercase text-muted fw-bold">Severity</small><br>
                            <span class="badge bg-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }} mt-1">
                                {{ ucfirst($rule->severity) }}
                            </span>
                        </div>
                    </div>

                    @if($rule->ruleType && $rule->ruleType->description)
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-uppercase text-muted fw-bold">Rule Type Description</small>
                        <p class="mb-0 mt-1">{{ $rule->ruleType->description }}</p>
                    </div>
                    @endif

                    <div class="mt-3">
                        <small class="text-uppercase text-muted fw-bold">Alert Message</small>
                        <div class="alert alert-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }} mt-2 mb-0">
                            @if($rule->message)
                                {{ $rule->message }}
                            @else
                                <span class="text-muted fst-italic">No custom alert message — the system default for this rule type will be used.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rule Logic -->
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">🔧 Rule Logic</h5>
                    @if($rule->ruleType && $rule->ruleType->handler_class)
                        <small class="text-muted"><i class="fas fa-code me-1"></i><code>{{ class_basename($rule->ruleType->handler_class) }}</code></small>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <div class="text-muted small mb-1">Trigger Type</div>
                                <strong>{{ $rule->ruleType->display_name ?? '—' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <div class="text-muted small mb-1">Conditions</div>
                                <strong>{{ $rule->conditions->count() }}</strong>
                                <span class="text-muted small"> defined</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <div class="text-muted small mb-1">Parameters</div>
                                <strong>{{ $rule->parameters->count() }}</strong>
                                <span class="text-muted small"> configured</span>
                            </div>
                        </div>
                    </div>
                    @if($rule->ruleType && $rule->ruleType->handler_class)
                    <div class="mt-3">
                        <small class="text-uppercase text-muted fw-bold">Evaluation Handler</small>
                        <div class="mt-1"><code class="bg-light px-2 py-1 rounded d-inline-block">{{ $rule->ruleType->handler_class }}</code></div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Rule Conditions -->
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">🎯 Rule Conditions</h5>
                    <span class="badge bg-secondary">{{ $rule->conditions->count() }}</span>
                </div>
                <div class="card-body">
                    @if($rule->conditions->count() > 0)
                    <p class="text-muted small mb-2">This rule fires when <strong>all</strong> of the following conditions are met:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Field</th>
                                    <th>Operator</th>
                                    <th>Value</th>
                                    <th>Value Type</th>
                                    <th>Logic</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rule->conditions->sortBy('sort_order') as $i => $condition)
                                <tr class="{{ $condition->is_active ? '' : 'text-muted' }}">
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td><code>{{ $condition->field_name ?? $condition->field ?? '—' }}</code></td>
                                    <td>{{ ucwords(str_replace('_', ' ', $condition->operator)) }}</td>
                                    <td><strong>{{ $condition->value }}</strong></td>
                                    <td><span class="badge bg-light text-dark border">{{ $condition->value_type ?? 'string' }}</span></td>
                                    <td>
                                        @if(!$loop->first)
                                            <span class="badge bg-{{ strtolower($condition->logical_operator ?? 'AND') === 'or' ? 'warning' : 'primary' }}">
                                                {{ strtoupper($condition->logical_operator ?? 'AND') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $condition->is_active ? 'success' : 'secondary' }}">
                                            {{ $condition->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-filter fa-2x mb-2 d-block opacity-50"></i>
                        <p class="mb-1">No conditions defined.</p>
                        <small>This rule will evaluate against all applicable records without filtering.</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Rule Parameters -->
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">⚙️ Rule Parameters</h5>
                    <span class="badge bg-secondary">{{ $rule->parameters->count() }}</span>
                </div>
                <div class="card-body">
                    @if($rule->parameters->count() > 0)
                    <p class="text-muted small mb-2">These parameters fine-tune the rule's evaluation logic:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Parameter</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rule->parameters as $parameter)
                                <tr>
                                    <td><code>{{ $parameter->name }}</code></td>
                                    <td><strong>{{ $parameter->value }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-sliders-h fa-2x mb-2 d-block opacity-50"></i>
                        <p class="mb-1">No parameters configured.</p>
                        <small>This rule uses default values defined in its handler class.</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Related Medication Policies -->
            @if($rule->medicationPolicies && $rule->medicationPolicies->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">💊 Related Medication Policies</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Min Dose</th>
                                    <th>Max Dose</th>
                                    <th>Min Age</th>
                                    <th>Max Age</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rule->medicationPolicies as $policy)
                                <tr>
                                    <td><strong>{{ $policy->medication_name }}</strong></td>
                                    <td>{{ $policy->min_dose }} {{ $policy->dose_unit }}</td>
                                    <td>{{ $policy->max_dose }} {{ $policy->dose_unit }}</td>
                                    <td>{{ $policy->min_age ? $policy->min_age . ' years' : 'N/A' }}</td>
                                    <td>{{ $policy->max_age ? $policy->max_age . ' years' : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Rule Metadata -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📊 Rule Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-uppercase text-muted fw-bold">Rule ID</small>
                        <div><code>#{{ $rule->id }}</code></div>
                    </div>

                    <div class="mb-3">
                        <small class="text-uppercase text-muted fw-bold">Status</small>
                        <div>
                            <span class="badge bg-{{ $rule->is_active ? 'success' : 'secondary' }} badge-lg">
                                {{ $rule->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    @if($rule->effective_from)
                        <div class="mb-3">
                            <small class="text-uppercase text-muted fw-bold">Effective From</small>
                            <div>{{ $rule->effective_from->format('M j, Y g:i A') }}</div>
                        </div>
                    @endif

                    @if($rule->effective_until)
                        <div class="mb-3">
                            <small class="text-uppercase text-muted fw-bold">Effective Until</small>
                            <div>{{ $rule->effective_until->format('M j, Y g:i A') }}</div>
                        </div>
                    @endif

                    <hr>

                    <div class="mb-3">
                        <small class="text-uppercase text-muted fw-bold">Created</small>
                        <div>{{ $rule->created_at->format('M j, Y g:i A') }}</div>
                        <small class="text-muted">{{ $rule->created_at->diffForHumans() }}</small>
                        @if($rule->creator)
                            <div class="mt-1"><small><i class="fas fa-user me-1 text-muted"></i>{{ $rule->creator->name }}</small></div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <small class="text-uppercase text-muted fw-bold">Last Updated</small>
                        <div>{{ $rule->updated_at->format('M j, Y g:i A') }}</div>
                        <small class="text-muted">{{ $rule->updated_at->diffForHumans() }}</small>
                        @if($rule->updater)
                            <div class="mt-1"><small><i class="fas fa-user-edit me-1 text-muted"></i>{{ $rule->updater->name }}</small></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">⚡ Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.cds.rules.edit', $rule) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit text-primary"></i> Edit Rule
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="duplicateRule()">
                            <i class="fas fa-copy text-info"></i> Duplicate Rule
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="toggleRule()">
                            <i class="fas fa-toggle-{{ $rule->is_active ? 'off' : 'on' }} text-warning"></i> 
                            {{ $rule->is_active ? 'Disable' : 'Enable' }} Rule
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="exportRule()">
                            <i class="fas fa-download text-success"></i> Export Rule
                        </a>
                        <a href="#" class="list-group-item list-group-item-action text-danger" onclick="deleteRule()">
                            <i class="fas fa-trash"></i> Delete Rule
                        </a>
                    </div>
                </div>
            </div>

            <!-- Rule Usage Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">📈 Usage Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                        <p>Statistics coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Rule Modal -->
<div class="modal fade" id="testRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🧪 Test Rule: {{ $rule->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="testRuleContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status"></div>
                        <p class="mt-2">Loading test interface...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testRule() {
    $('#testRuleModal').modal('show');
    
    $.ajax({
        url: '{{ route("admin.cds.rules.test", $rule) }}',
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        success: function(response) {
            $('#testRuleContent').html(response.test_form);
        },
        error: function() {
            $('#testRuleContent').html('<div class="alert alert-danger">Error loading test interface</div>');
        }
    });
}

function toggleRule() {
    const action = {{ $rule->is_active ? 'false' : 'true' }};
    const actionText = '{{ $rule->is_active ? "disable" : "enable" }}';
    
    if (confirm(`Are you sure you want to ${actionText} this rule?`)) {
        $.ajax({
            url: '{{ route("admin.cds.rules.toggle", $rule) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                is_active: action
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Rule status updated successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', 'Failed to update rule status');
                }
            },
            error: function() {
                showAlert('error', 'Error updating rule status');
            }
        });
    }
}

function duplicateRule() {
    if (confirm('Create a copy of this rule?')) {
        $.ajax({
            url: '{{ route("admin.cds.rules.duplicate", $rule) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Rule duplicated successfully');
                    window.location.href = response.redirect_url;
                } else {
                    showAlert('error', 'Failed to duplicate rule');
                }
            },
            error: function() {
                showAlert('error', 'Error duplicating rule');
            }
        });
    }
}

function deleteRule() {
    if (confirm('Are you sure you want to delete this rule? This action cannot be undone.')) {
        $.ajax({
            url: '{{ route("admin.cds.rules.destroy", $rule) }}',
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Rule deleted successfully');
                    setTimeout(function() {
                        window.location.href = '{{ route("admin.cds.rules.index") }}';
                    }, 1000);
                } else {
                    showAlert('error', 'Failed to delete rule');
                }
            },
            error: function() {
                showAlert('error', 'Error deleting rule');
            }
        });
    }
}

function exportRule() {
    window.open('{{ route("admin.cds.rules.export", $rule) }}', '_blank');
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    
    $('.container-fluid').prepend(alert);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}
</script>
@endpush
@endsection