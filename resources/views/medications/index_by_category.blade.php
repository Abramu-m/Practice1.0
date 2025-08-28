@extends('layouts.app_main_layout')

@section('page_title', $category->description)

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $category->description }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('medications.create') }}?category_id={{ $category->id }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New {{ $category->description }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Category Navigation Tabs -->
                    <ul class="nav nav-tabs mb-3">
                        @foreach($categories as $cat)
                            <li class="nav-item">
                                <a class="nav-link {{ $cat->id == $category->id ? 'active' : '' }}" 
                                   href="{{ route('medications.by-category', $cat->id) }}">
                                    @if($cat->description == 'Medications')
                                        <i class="fas fa-pills"></i>
                                    @elseif($cat->description == 'Consumables')
                                        <i class="fas fa-syringe"></i>
                                    @elseif($cat->description == 'Equipment')
                                        <i class="fas fa-stethoscope"></i>
                                    @elseif($cat->description == 'Supplies')
                                        <i class="fas fa-box"></i>
                                    @elseif($cat->description == 'Other')
                                        <i class="fas fa-question-circle"></i>
                                    @else
                                        <i class="fas fa-tag"></i>
                                    @endif
                                    {{ $cat->description }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('medications.by-category', $category->id) }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search {{ $category->description }}..." 
                                       value="{{ request('search') }}">
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
                                <a href="{{ route('medications.by-category', $category->id) }}" class="btn btn-outline-secondary">
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
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->generic_name }}</td>
                                    <td>{{ $item->brand_name }}</td>
                                    <td>{{ $item->strength }}</td>
                                    <td>
                                        <span class="text-black badge badge-{{ $item->stock_badge_class }} text-black">
                                            {{ number_format($item->stock_quantity) }}
                                        </span>
                                        <br><small class="text-muted">{{ $item->stock_status }}</small>
                                    </td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge badge-success text-black">Active</span>
                                        @else
                                            <span class="badge badge-danger text-black">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('medications.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('medications.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('medications.destroy', $item->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i> No {{ strtolower($category->description) }} found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($items, 'links'))
                        <div class="mt-3">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
