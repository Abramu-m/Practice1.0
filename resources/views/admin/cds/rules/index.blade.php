@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">📝 CDS Rules Management</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item active">Rules</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.cds.test-patients.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-flask"></i> Test Patients
                    </a>
                    <a href="{{ route('admin.cds.rules.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Rule
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.cds.rules.index') }}" class="row">
                        <div class="col-md-3">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="rule_type">Rule Type</label>
                            <select name="rule_type" id="rule_type" class="form-control">
                                <option value="">All Types</option>
                                @foreach($ruleTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('rule_type') == $type->id ? 'selected' : '' }}>
                                        {{ $type->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search rules..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-1 align-self-end">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Rules Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rules ({{ $rules->total() }} total)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rule</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $rule->name }}</strong>
                                            @if($rule->description)
                                                <br><small class="text-muted">{{ Str::limit($rule->description, 60) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline-secondary">{{ $rule->ruleType->category->name ?? 'No Category' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $rule->ruleType->display_name ?? 'No Type' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $rule->priority >= 8 ? 'danger' : ($rule->priority >= 5 ? 'warning' : 'secondary') }}">
                                            {{ $rule->priority }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }}">
                                            {{ ucfirst($rule->severity) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input rule-toggle" 
                                                   id="rule{{ $rule->id }}" 
                                                   data-rule-id="{{ $rule->id }}"
                                                   {{ $rule->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="rule{{ $rule->id }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $rule->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.cds.rules.show', $rule) }}" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.cds.rules.edit', $rule) }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-success" onclick="testRule({{ $rule->id }})" title="Test">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRule({{ $rule->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No rules found matching your criteria.
                                        <br>
                                        <a href="{{ route('admin.cds.rules.create') }}" class="btn btn-primary mt-2">Create your first rule</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($rules->hasPages())
                        <div class="mt-3">
                            {{ $rules->withQueryString()->links() }}
                        </div>
                    @endif
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
                <h5 class="modal-title">🧪 Test Rule</h5>
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
@endsection

@section('scripts')
<script>
// Define global functions first for inline onclick handlers
window.testRule = function(ruleId) {
    $('#testRuleModal').modal('show');
    
    $.ajax({
        url: `/admin/cds/rules/${ruleId}/test`,
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        success: function(response) {
            $('#testRuleContent').html(response.test_form);
        },
        error: function() {
            $('#testRuleContent').html('<div class="alert alert-danger">Error loading test interface</div>');
        }
    });
};

window.deleteRule = function(ruleId) {
    if (confirm('Are you sure you want to delete this rule? This action cannot be undone.')) {
        $.ajax({
            url: `/admin/cds/rules/${ruleId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Rule deleted successfully');
                    location.reload();
                } else {
                    showAlert('error', 'Failed to delete rule');
                }
            },
            error: function() {
                showAlert('error', 'Error deleting rule');
            }
        });
    }
};

window.showAlert = function(type, message) {
    // Create and show alert
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    
    $('.container-fluid').prepend(alert);
    
    // Auto-hide after 3 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
};

// Document ready handlers
$(document).ready(function() {
    // Rule toggle functionality
    $('.rule-toggle').change(function() {
        const ruleId = $(this).data('rule-id');
        const isActive = $(this).is(':checked');
        
        $.ajax({
            url: `/admin/cds/rules/${ruleId}/toggle`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                is_active: isActive
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Rule status updated successfully');
                } else {
                    showAlert('error', 'Failed to update rule status');
                }
            },
            error: function() {
                showAlert('error', 'Error updating rule status');
                // Revert the toggle
                $(this).prop('checked', !isActive);
            }
        });
    });
});
</script>
@endsection