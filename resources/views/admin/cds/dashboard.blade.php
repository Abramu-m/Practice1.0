@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">🔧 CDS Administration Dashboard</h1>
                <div class="btn-group">
                    <a href="{{ route('admin.cds.rules.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Manage Rules
                    </a>
                    <a href="{{ route('admin.cds.rules.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Rule
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <a href="{{ route('admin.cds.categories.index') }}" class="text-decoration-none">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Categories</h6>
                                <h3 class="mb-0">{{ $stats['categories'] }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-folder fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.cds.types.index') }}" class="text-decoration-none">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Rule Types</h6>
                                <h3 class="mb-0">{{ $stats['rule_types'] }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cogs fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Rules</h6>
                            <h3 class="mb-0">{{ $stats['total_rules'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-rules fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Active Rules</h6>
                            <h3 class="mb-0">{{ $stats['active_rules'] }}/{{ $stats['total_rules'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-toggle-on fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Med Policies</h6>
                            <h3 class="mb-0">{{ $stats['medication_policies'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pills fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Rules -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📋 Recent Rules</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rule</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRules as $rule)
                                <tr>
                                    <td>
                                        <strong>{{ $rule->name }}</strong>
                                        @if($rule->description)
                                            <br><small class="text-muted">{{ Str::limit($rule->description, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $rule->ruleType->display_name }}</span>
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
                                        <span class="badge bg-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                            {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.cds.rules.show', $rule) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.cds.rules.edit', $rule) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No rules found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rules by Category -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📊 Rules by Category</h5>
                </div>
                <div class="card-body">
                    @foreach($rulesByCategory as $category)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $category['name'] }}</span>
                            <span class="badge bg-primary">{{ $category['count'] }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 5px;">
                            <div class="progress-bar" style="width: {{ $stats['total_rules'] > 0 ? ($category['count'] / $stats['total_rules']) * 100 : 0 }}%"></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">⚡ Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.cds.categories.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-folder text-primary"></i> View Categories
                        </a>
                        <a href="{{ route('admin.cds.types.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-cogs text-info"></i> View Rule Types
                        </a>
                        <a href="{{ route('admin.cds.rules.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-primary"></i> View All Rules
                        </a>
                        <a href="{{ route('admin.cds.medication-policies.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-pills text-success"></i> Medication Policies
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="clearCacheModal()">
                            <i class="fas fa-sync text-warning"></i> Clear Rule Cache
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="exportRules()">
                            <i class="fas fa-download text-info"></i> Export Rules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCacheModal() {
    if (confirm('Are you sure you want to clear the CDS rule cache? This may affect performance temporarily.')) {
        // Add cache clearing logic here
        alert('Cache cleared successfully!');
    }
}

function exportRules() {
    alert('Export functionality coming soon!');
}
</script>
@endsection