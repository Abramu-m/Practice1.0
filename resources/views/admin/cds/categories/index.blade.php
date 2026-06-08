@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">📁 CDS Rule Categories</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item active">Categories</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rule Categories ({{ $categories->total() }} total)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">Order</th>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 10%;">Rule Types</th>
                                    <th style="width: 10%;">Rules</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->sort_order }}</td>
                                    <td>
                                        <strong>{{ $category->display_name }}</strong><br>
                                        <small class="text-muted">{{ $category->name }}</small>
                                    </td>
                                    <td>{{ $category->description }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $category->rule_types_count }} types</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $category->rules_count }} rules</span>
                                    </td>
                                    <td>
                                        @if($category->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.cds.categories.show', $category) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <p class="text-muted mb-0">No rule categories found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($categories->hasPages())
                <div class="card-footer">
                    {{ $categories->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
