@extends('layouts.app_main_layout')

@section('page_title', 'Medications')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medications</h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <a href="{{ route('medications.formulations.index') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-tags"></i> Manage Formulations
                            </a>
                            <a href="{{ route('medications.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Medication
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Store Navigation Tabs -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group" role="group" aria-label="Store Items">
                                <a href="{{ route('medications.index') }}" class="btn btn-outline-primary active">
                                    <i class="fas fa-th-large"></i> All Items
                                </a>
                                @foreach($categories as $category)
                                    @if($category->description == 'Medications')
                                        <a href="{{ route('medications.by-category', $category->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-pills"></i> {{ $category->description }}
                                        </a>
                                    @elseif($category->description == 'Consumables')
                                        <a href="{{ route('medications.by-category', $category->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-syringe"></i> {{ $category->description }}
                                        </a>
                                    @elseif($category->description == 'Equipment')
                                        <a href="{{ route('medications.by-category', $category->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-stethoscope"></i> {{ $category->description }}
                                        </a>
                                    @elseif($category->description == 'Supplies')
                                        <a href="{{ route('medications.by-category', $category->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-box"></i> {{ $category->description }}
                                        </a>
                                    @elseif($category->description == 'Other')
                                        <a href="{{ route('medications.by-category', $category->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-question-circle"></i> {{ $category->description }}
                                        </a>
                                    @else
                                        <a href="{{ route('medications.by-category', $category->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-tag"></i> {{ $category->description }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('medications.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search all items..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="category_id" class="form-control">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->description ?? $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="stock_status" class="form-control">
                                    <option value="">All Stock Status</option>
                                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('medications.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Generic Name</th>
                                    <th>Brand Name</th>
                                    <th>Strength</th>
                                    <th>Dispensing Unit</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($medications as $medication)
                                <tr>
                                    <td>
                                        <strong>{{ $medication->generic_name }}</strong>
                                        @if($medication->formulation)
                                            <br><small class="text-muted">{{ $medication->formulation->description }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $medication->brand_name }}</td>
                                    <td>{{ $medication->strength }}</td>
                                    <td>
                                        @if($medication->dispensingUnit)
                                            <span class="badge badge-secondary">{{ $medication->dispensingUnit->unit_code }}</span>
                                            <small class="text-muted d-block">{{ $medication->dispensingUnit->unit_name }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($medication->storeCategory)
                                           {{ $medication->storeCategory->description ?? $medication->storeCategory->description }}
                                        @else
                                            <span class="text-muted">No Category</span>
                                        @endif
                                    </td>
                                    <td>
                                            {{ number_format($medication->stock_quantity) }}
                                        <br>
                                        <small class="text-muted">{{ $medication->stock_status }}</small>
                                    </td>
                                    <td>
                                        @if($medication->is_active)
                                            <span class="badge badge-success text-black">Active</span>
                                        @else
                                            <span class="badge badge-danger text-black">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('medications.show', $medication->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('medications.edit', $medication->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('medications.destroy', $medication->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No medications found.</p>
                                            <a href="{{ route('medications.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add First Medication
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $medications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ]
    });
});
</script>
@endsection

@section('extra_footer_content')

@endsection
