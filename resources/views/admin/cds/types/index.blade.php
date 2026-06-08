@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">⚙️ CDS Rule Types</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item active">Rule Types</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Rule Types Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rule Types ({{ $ruleTypes->total() }} total)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">Order</th>
                                    <th style="width: 15%;">Category</th>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 30%;">Description</th>
                                    <th style="width: 8%;">Rules</th>
                                    <th style="width: 8%;">Active</th>
                                    <th style="width: 8%;">Status</th>
                                    <th style="width: 6%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ruleTypes as $ruleType)
                                <tr>
                                    <td>{{ $ruleType->sort_order }}</td>
                                    <td>
                                        <a href="{{ route('admin.cds.categories.show', $ruleType->category) }}">
                                            {{ $ruleType->category->display_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <strong>{{ $ruleType->display_name }}</strong><br>
                                        <small class="text-muted">{{ $ruleType->name }}</small>
                                    </td>
                                    <td>{{ $ruleType->description }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $ruleType->rules_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $ruleType->active_rules_count }}</span>
                                    </td>
                                    <td>
                                        @if($ruleType->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.cds.types.show', $ruleType) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <p class="text-muted mb-0">No rule types found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($ruleTypes->hasPages())
                <div class="card-footer">
                    {{ $ruleTypes->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
