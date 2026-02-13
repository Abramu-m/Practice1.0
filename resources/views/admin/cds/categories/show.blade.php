@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">📁 {{ $category->display_name }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.categories.index') }}">Categories</a></li>
                            <li class="breadcrumb-item active">{{ $category->display_name }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.cds.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>
            </div>
        </div>
    </div>

    <!-- Category Details -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Category Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Name:</dt>
                        <dd class="col-sm-9">{{ $category->name }}</dd>

                        <dt class="col-sm-3">Display Name:</dt>
                        <dd class="col-sm-9">{{ $category->display_name }}</dd>

                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{{ $category->description }}</dd>

                        <dt class="col-sm-3">Sort Order:</dt>
                        <dd class="col-sm-9">{{ $category->sort_order }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            @if($category->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Created:</dt>
                        <dd class="col-sm-9">{{ $category->created_at->format('Y-m-d H:i:s') }}</dd>

                        <dt class="col-sm-3">Updated:</dt>
                        <dd class="col-sm-9">{{ $category->updated_at->format('Y-m-d H:i:s') }}</dd>
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
                        <h6 class="text-muted">Rule Types</h6>
                        <h3 class="mb-0">{{ $category->ruleTypes->count() }}</h3>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-muted">Active Rule Types</h6>
                        <h3 class="mb-0">{{ $category->ruleTypes->where('is_active', true)->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rule Types in this Category -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rule Types ({{ $category->ruleTypes->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">Order</th>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 40%;">Description</th>
                                    <th style="width: 10%;">Rules</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($category->ruleTypes as $ruleType)
                                <tr>
                                    <td>{{ $ruleType->sort_order }}</td>
                                    <td>
                                        <strong>{{ $ruleType->display_name }}</strong><br>
                                        <small class="text-muted">{{ $ruleType->name }}</small>
                                    </td>
                                    <td>{{ $ruleType->description }}</td>
                                    <td>
                                        <span class="badge badge-primary">{{ $ruleType->rules_count }} rules</span>
                                    </td>
                                    <td>
                                        @if($ruleType->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
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
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-muted mb-0">No rule types found in this category.</p>
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
