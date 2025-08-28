@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Pricing</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-pricing.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Pricing
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('medication-pricing.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="medication_id" class="form-control">
                                    <option value="">All Medications</option>
                                    @foreach($medications as $medication)
                                        <option value="{{ $medication->id }}" {{ request('medication_id') == $medication->id ? 'selected' : '' }}>
                                            {{ $medication->generic_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="patient_category_id" class="form-control">
                                    <option value="">All Patient Categories</option>
                                    @foreach($patientCategories as $category)
                                        <option value="{{ $category->id }}" {{ request('patient_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="effective_status" class="form-control">
                                    <option value="">All Effective Status</option>
                                    <option value="current" {{ request('effective_status') == 'current' ? 'selected' : '' }}>Current</option>
                                    <option value="future" {{ request('effective_status') == 'future' ? 'selected' : '' }}>Future</option>
                                    <option value="expired" {{ request('effective_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('medication-pricing.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Pricing Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Patient Category</th>
                                    <th>Selling Price</th>
                                    <th>Markup %</th>
                                    <th>Discount %</th>
                                    <th>Effective Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pricing as $price)
                                    <tr>
                                        <td>
                                            <strong>{{ $price->medication->generic_name }}</strong>
                                            @if($price->medication->brand_name)
                                                <br><small class="text-muted">{{ $price->medication->brand_name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info text-black">{{ $price->patientCategory->description }}</span>
                                        </td>
                                        <td>${{ number_format($price->selling_price, 2) }}</td>
                                        <td>
                                            @if($price->markup_percentage)
                                                {{ number_format($price->markup_percentage, 1) }}%
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($price->discount_percentage)
                                                <span class="text-danger">{{ number_format($price->discount_percentage, 1) }}%</span>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                <strong>From:</strong> {{ $price->effective_from->format('M d, Y') }}<br>
                                                <strong>To:</strong> {{ $price->effective_to ? $price->effective_to->format('M d, Y') : 'Ongoing' }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="text-black badge badge-{{ $price->is_active ? 'success' : 'danger' }}">
                                                {{ $price->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($price->isCurrent())
                                                <span class="badge badge-primary text-black">Current</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('medication-pricing.show', $price->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('medication-pricing.edit', $price->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('medication-pricing.destroy', $price->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this pricing?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No pricing records found.</p>
                                                <a href="{{ route('medication-pricing.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Add New Pricing
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $pricing->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
