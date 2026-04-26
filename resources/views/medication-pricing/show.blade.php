@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Pricing Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-pricing.edit', $medicationPricing->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('medication-pricing.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Medication:</label>
                                <p class="form-control-plaintext">
                                    <strong>{{ $medicationPricing->medication->name }}</strong>
                                    @if($medicationPricing->medication->generic_name)
                                        <br><small class="text-muted">{{ $medicationPricing->medication->generic_name }}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Patient Category:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-info text-black">{{ $medicationPricing->patientCategory->description }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Selling Price:</label>
                                <p class="form-control-plaintext">
                                    <strong>${{ number_format($medicationPricing->selling_price, 2) }}</strong>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Markup Percentage:</label>
                                <p class="form-control-plaintext">
                                    @if($medicationPricing->markup_percentage)
                                        <strong>{{ number_format($medicationPricing->markup_percentage, 1) }}%</strong>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Discount Percentage:</label>
                                <p class="form-control-plaintext">
                                    @if($medicationPricing->discount_percentage)
                                        <strong class="text-danger">{{ number_format($medicationPricing->discount_percentage, 1) }}%</strong>
                                    @else
                                        <span class="text-muted">No discount</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Discounted Price:</label>
                                <p class="form-control-plaintext">
                                    <strong>${{ number_format($medicationPricing->discounted_price, 2) }}</strong>
                                    @if($medicationPricing->discount_percentage > 0)
                                        <small class="text-muted">({{ number_format($medicationPricing->discount_percentage, 1) }}% discount applied)</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Status:</label>
                                <p class="form-control-plaintext">
                                    <span class="text-black badge badge-{{ $medicationPricing->is_active ? 'success' : 'danger' }}">
                                        {{ $medicationPricing->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($medicationPricing->isCurrent())
                                        <span class="badge bg-primary text-black">Current</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Effective From:</label>
                                <p class="form-control-plaintext">{{ $medicationPricing->effective_from->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Effective To:</label>
                                <p class="form-control-plaintext">
                                    {{ $medicationPricing->effective_to ? $medicationPricing->effective_to->format('M d, Y') : 'Ongoing' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($medicationPricing->notes)
                    <div class="mb-3">
                        <label>Notes:</label>
                        <p class="form-control-plaintext">{{ $medicationPricing->notes }}</p>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Created:</label>
                                <p class="form-control-plaintext">{{ $medicationPricing->created_at->format('M d, Y \a\t h:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Last Updated:</label>
                                <p class="form-control-plaintext">{{ $medicationPricing->updated_at->format('M d, Y \a\t h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pricing Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-tag"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Selling Price</span>
                            <span class="info-box-number">${{ number_format($medicationPricing->selling_price, 2) }}</span>
                        </div>
                    </div>

                    @if($medicationPricing->markup_percentage)
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-percentage"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Markup</span>
                            <span class="info-box-number">{{ number_format($medicationPricing->markup_percentage, 1) }}%</span>
                        </div>
                    </div>
                    @endif

                    @if($medicationPricing->discount_percentage)
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-minus"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Discount</span>
                            <span class="info-box-number">{{ number_format($medicationPricing->discount_percentage, 1) }}%</span>
                        </div>
                    </div>
                    @endif

                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-calculator"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Final Price</span>
                            <span class="info-box-number">${{ number_format($medicationPricing->discounted_price, 2) }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $medicationPricing->is_active ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $medicationPricing->is_active ? 'check' : 'times' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Status</span>
                            <span class="info-box-number">{{ $medicationPricing->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
