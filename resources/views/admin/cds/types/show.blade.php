@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">⚙️ {{ $ruleType->display_name }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.types.index') }}">Rule Types</a></li>
                            <li class="breadcrumb-item active">{{ $ruleType->display_name }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.cds.types.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Rule Types
                </a>
            </div>
        </div>
    </div>

    <!-- Rule Type Details -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rule Type Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Name:</dt>
                        <dd class="col-sm-9">{{ $ruleType->name }}</dd>

                        <dt class="col-sm-3">Display Name:</dt>
                        <dd class="col-sm-9">{{ $ruleType->display_name }}</dd>

                        <dt class="col-sm-3">Category:</dt>
                        <dd class="col-sm-9">
                            <a href="{{ route('admin.cds.categories.show', $ruleType->category) }}">
                                {{ $ruleType->category->display_name }}
                            </a>
                        </dd>

                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{{ $ruleType->description }}</dd>

                        <dt class="col-sm-3">Handler Class:</dt>
                        <dd class="col-sm-9"><code>{{ $ruleType->handler_class }}</code></dd>

                        <dt class="col-sm-3">Sort Order:</dt>
                        <dd class="col-sm-9">{{ $ruleType->sort_order }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            @if($ruleType->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Created:</dt>
                        <dd class="col-sm-9">{{ $ruleType->created_at->format('Y-m-d H:i:s') }}</dd>

                        <dt class="col-sm-3">Updated:</dt>
                        <dd class="col-sm-9">{{ $ruleType->updated_at->format('Y-m-d H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Total Rules</h6>
                        <h3 class="mb-0">{{ $ruleType->rules->count() }}</h3>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-muted">Active Rules</h6>
                        <h3 class="mb-0">{{ $ruleType->rules->where('is_active', true)->count() }}</h3>
                    </div>
                    <hr>
                    <div>
                        <h6 class="text-muted">Inactive Rules</h6>
                        <h3 class="mb-0">{{ $ruleType->rules->where('is_active', false)->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rules in this Type -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Rules ({{ $ruleType->rules->count() }})</h5>
                    <a href="{{ route('admin.cds.rules.create') }}?rule_type_id={{ $ruleType->id }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Create Rule
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Name</th>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 10%;">Priority</th>
                                    <th style="width: 10%;">Severity</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ruleType->rules as $rule)
                                <tr>
                                    <td>
                                        <strong>{{ $rule->name }}</strong>
                                    </td>
                                    <td>{{ Str::limit($rule->description, 80) }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $rule->priority }}</span>
                                    </td>
                                    <td>
                                        @if($rule->severity == 'critical')
                                            <span class="badge badge-danger">Critical</span>
                                        @elseif($rule->severity == 'warning')
                                            <span class="badge badge-warning">Warning</span>
                                        @else
                                            <span class="badge badge-info">Info</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rule->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.cds.rules.show', $rule) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.cds.rules.edit', $rule) }}" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-muted mb-0">No rules found for this rule type.</p>
                                        <a href="{{ route('admin.cds.rules.create') }}?rule_type_id={{ $ruleType->id }}" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-plus"></i> Create First Rule
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
