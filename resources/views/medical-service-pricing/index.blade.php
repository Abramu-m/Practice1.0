@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medical Service Pricing</h3>
                    <div class="card-tools">
                        <a href="{{ route('medical-service-pricing.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Pricing
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('medical-service-pricing.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="medical_service_id" class="form-control">
                                    <option value="">All Medical Services</option>
                                    @foreach($medicalServices as $service)
                                        <option value="{{ $service->id }}" {{ request('medical_service_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
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
                                <a href="{{ route('medical-service-pricing.index') }}" class="btn btn-outline-secondary">
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
                                    <th>Medical Service</th>
                                    <th>Service Category</th>
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
                                            <strong>{{ $price->medicalService->name }}</strong>
                                            @if($price->medicalService->code)
                                                <br><small class="text-muted">{{ $price->medicalService->code }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary text-black">{{ $price->medicalService->serviceCategory->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info text-black">{{ $price->patientCategory->description }}</span>
                                        </td>
                                        <td>TSh {{ number_format($price->selling_price, 2) }}</td>
                                        <td>
                                            @if($price->markup_percentage)
                                                {{ number_format($price->markup_percentage, 1) }}%
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($price->discount_percentage)
                                                {{ number_format($price->discount_percentage, 1) }}%
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                <strong>From:</strong> {{ $price->effective_from ? $price->effective_from->format('M j, Y') : 'Not set' }}
                                                @if($price->effective_to)
                                                    <br><strong>To:</strong> {{ $price->effective_to->format('M j, Y') }}
                                                @else
                                                    <br><strong>To:</strong> <span class="text-muted">Indefinite</span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @php
                                                $status = $price->status;
                                                $badgeClass = $status === 'Active' ? 'success' : 
                                                            ($status === 'Future' ? 'warning' : 
                                                            ($status === 'Expired' ? 'secondary' : 'danger'));
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $status }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('medical-service-pricing.show', $price) }}" 
                                                   class="btn btn-outline-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('medical-service-pricing.edit', $price) }}" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('medical-service-pricing.destroy', $price) }}" 
                                                      method="POST" style="display: inline-block;"
                                                      onsubmit="return confirm('Are you sure you want to delete this pricing?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No pricing records found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($pricing->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $pricing->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit filter form when selections change
    $('select[name="medical_service_id"], select[name="patient_category_id"], select[name="status"], select[name="effective_status"]').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
