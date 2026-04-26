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
                            @if($rule->description)
                                <p class="text-muted">{{ $rule->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3">
                            <strong>Category:</strong><br>
                            <span class="badge badge-outline-secondary">{{ $rule->ruleType->category->name ?? 'No Category' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Type:</strong><br>
                            <span class="badge bg-info">{{ $rule->ruleType->display_name ?? 'No Type' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Priority:</strong><br>
                            <span class="badge badge-{{ $rule->priority >= 8 ? 'danger' : ($rule->priority >= 5 ? 'warning' : 'secondary') }}">
                                {{ $rule->priority }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Severity:</strong><br>
                            <span class="badge badge-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }}">
                                {{ ucfirst($rule->severity) }}
                            </span>
                        </div>
                    </div>

                    @if($rule->message)
                        <div class="mt-3">
                            <strong>Alert Message:</strong>
                            <div class="alert alert-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }} mt-2">
                                {{ $rule->message }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Rule Conditions -->
            @if($rule->conditions->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">🎯 Rule Conditions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Field</th>
                                    <th>Operator</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rule->conditions as $condition)
                                <tr>
                                    <td><code>{{ $condition->field }}</code></td>
                                    <td>{{ ucwords(str_replace('_', ' ', $condition->operator)) }}</td>
                                    <td><strong>{{ $condition->value }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Rule Parameters -->
            @if($rule->parameters->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">⚙️ Rule Parameters</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
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
                </div>
            </div>
            @endif

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
                        <label>Status:</label>
                        <div>
                            <span class="badge badge-{{ $rule->is_active ? 'success' : 'secondary' }} badge-lg">
                                {{ $rule->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    @if($rule->effective_from)
                        <div class="mb-3">
                            <label>Effective From:</label>
                            <div>{{ $rule->effective_from->format('M j, Y g:i A') }}</div>
                        </div>
                    @endif

                    @if($rule->effective_until)
                        <div class="mb-3">
                            <label>Effective Until:</label>
                            <div>{{ $rule->effective_until->format('M j, Y g:i A') }}</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label>Created:</label>
                        <div>{{ $rule->created_at->format('M j, Y g:i A') }}</div>
                        <small class="text-muted">{{ $rule->created_at->diffForHumans() }}</small>
                    </div>

                    <div class="mb-3">
                        <label>Last Updated:</label>
                        <div>{{ $rule->updated_at->format('M j, Y g:i A') }}</div>
                        <small class="text-muted">{{ $rule->updated_at->diffForHumans() }}</small>
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
        success: function(response) {
            $('#testRuleContent').html(response);
        },
        error: function() {
            $('#testRuleContent').html('<div class="alert alert-danger">Error loading test interface</div>');
        }
    });
}

function toggleRule() {
    const action = {{ $rule->is_active ? 'false' : 'true' }};
    const actionText = {{ $rule->is_active ? '"disable"' : '"enable"' }};
    
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