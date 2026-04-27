@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medical Service Pricing Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('medical-service-pricing.create', ['medical_service_id' => $medicalServicePricing->medical_service_id]) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add New Pricing
                        </a>
                        <a href="{{ route('medical-service-pricing.edit', $medicalServicePricing) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('medical-service-pricing.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Medical Service:</th>
                                    <td>
                                        <strong>{{ $medicalServicePricing->medicalService->name }}</strong>
                                        @if($medicalServicePricing->medicalService->code)
                                            <br><small class="text-muted">Code: {{ $medicalServicePricing->medicalService->code }}</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Service Category:</th>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $medicalServicePricing->medicalService->serviceCategory->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Patient Category:</th>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $medicalServicePricing->patientCategory->description }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @php
                                            $status = $medicalServicePricing->status;
                                            $badgeClass = $status === 'Active' ? 'success' : 
                                                        ($status === 'Future' ? 'warning' : 
                                                        ($status === 'Expired' ? 'secondary' : 'danger'));
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Selling Price:</th>
                                    <td><strong class="text-success">TSh {{ number_format($medicalServicePricing->selling_price, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Markup Percentage:</th>
                                    <td>
                                        @if($medicalServicePricing->markup_percentage)
                                            {{ number_format($medicalServicePricing->markup_percentage, 2) }}%
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Discount Percentage:</th>
                                    <td>
                                        @if($medicalServicePricing->discount_percentage)
                                            {{ number_format($medicalServicePricing->discount_percentage, 2) }}%
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Active Status:</th>
                                    <td>
                                        @if($medicalServicePricing->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5>Effective Period</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="20%">Effective From:</th>
                                    <td>
                                        @if($medicalServicePricing->effective_from)
                                            {{ $medicalServicePricing->effective_from->format('F j, Y') }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Effective To:</th>
                                    <td>
                                        @if($medicalServicePricing->effective_to)
                                            {{ $medicalServicePricing->effective_to->format('F j, Y') }}
                                        @else
                                            <span class="text-primary">Indefinite</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($medicalServicePricing->notes)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5>Notes</h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                {{ $medicalServicePricing->notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5>Service Details</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Description:</th>
                                                    <td>{{ $medicalServicePricing->medicalService->description ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Requires Sample:</th>
                                                    <td>
                                                        @if($medicalServicePricing->medicalService->requires_sample)
                                                            <span class="badge bg-warning">Yes</span>
                                                        @else
                                                            <span class="badge bg-secondary">No</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if($medicalServicePricing->medicalService->sample_type)
                                                <tr>
                                                    <th>Sample Type:</th>
                                                    <td>{{ $medicalServicePricing->medicalService->sample_type }}</td>
                                                </tr>
                                                @endif
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                @if($medicalServicePricing->medicalService->turnaround_time_hours)
                                                <tr>
                                                    <th>Turnaround Time:</th>
                                                    <td>{{ $medicalServicePricing->medicalService->turnaround_time_hours }} hours</td>
                                                </tr>
                                                @endif
                                                @if($medicalServicePricing->medicalService->min_value || $medicalServicePricing->medicalService->max_value)
                                                <tr>
                                                    <th>Reference Range:</th>
                                                    <td>
                                                        @if($medicalServicePricing->medicalService->min_value && $medicalServicePricing->medicalService->max_value)
                                                            {{ $medicalServicePricing->medicalService->min_value }} - {{ $medicalServicePricing->medicalService->max_value }}
                                                            @if($medicalServicePricing->medicalService->unit)
                                                                {{ $medicalServicePricing->medicalService->unit }}
                                                            @endif
                                                        @elseif($medicalServicePricing->medicalService->min_value)
                                                            ≥ {{ $medicalServicePricing->medicalService->min_value }}
                                                            @if($medicalServicePricing->medicalService->unit)
                                                                {{ $medicalServicePricing->medicalService->unit }}
                                                            @endif
                                                        @elseif($medicalServicePricing->medicalService->max_value)
                                                            ≤ {{ $medicalServicePricing->medicalService->max_value }}
                                                            @if($medicalServicePricing->medicalService->unit)
                                                                {{ $medicalServicePricing->medicalService->unit }}
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Created: {{ $medicalServicePricing->created_at->format('F j, Y g:i A') }}
                                @if($medicalServicePricing->updated_at != $medicalServicePricing->created_at)
                                    | Updated: {{ $medicalServicePricing->updated_at->format('F j, Y g:i A') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
