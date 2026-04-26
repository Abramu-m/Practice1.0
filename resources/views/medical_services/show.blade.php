@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Medical Service Details' }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Medical Service Details</h3>
                    <div>
                        <a href="{{ route('medical_services.edit', $medicalService) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Service
                        </a>
                        <a href="{{ route('medical_services.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Services
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="30%">Service Name:</th>
                                            <td><strong>{{ $medicalService->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Category:</th>
                                            <td>
                                                @if($medicalService->serviceCategory)
                                                    <span class="badge bg-secondary">{{ $medicalService->serviceCategory->name }}</span>
                                                @else
                                                    <span class="text-muted">No category assigned</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($medicalService->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created:</th>
                                            <td>{{ $medicalService->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated:</th>
                                            <td>{{ $medicalService->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Reference Values -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Reference Values</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Minimum Value:</th>
                                            <td>
                                                @if($medicalService->min_value !== null)
                                                    <strong class="text-success">{{ $medicalService->min_value }}</strong>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Maximum Value:</th>
                                            <td>
                                                @if($medicalService->max_value !== null)
                                                    <strong class="text-success">{{ $medicalService->max_value }}</strong>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Unit:</th>
                                            <td>
                                                @if($medicalService->unit)
                                                    <code>{{ $medicalService->unit }}</code>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Reference Range:</th>
                                            <td>
                                                @if($medicalService->min_value !== null && $medicalService->max_value !== null)
                                                    <strong class="text-primary">{{ $medicalService->min_value }} - {{ $medicalService->max_value }}</strong>
                                                    @if($medicalService->unit)
                                                        <small class="text-muted">{{ $medicalService->unit }}</small>
                                                    @endif
                                                @elseif($medicalService->min_value !== null)
                                                    <strong class="text-primary">≥ {{ $medicalService->min_value }}</strong>
                                                    @if($medicalService->unit)
                                                        <small class="text-muted">{{ $medicalService->unit }}</small>
                                                    @endif
                                                @elseif($medicalService->max_value !== null)
                                                    <strong class="text-primary">≤ {{ $medicalService->max_value }}</strong>
                                                    @if($medicalService->unit)
                                                        <small class="text-muted">{{ $medicalService->unit }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No reference range defined</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Service Requirements -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Service Requirements</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Sample Collection:</strong>
                                        @if($medicalService->requires_sample)
                                            <span class="badge bg-warning">Required</span>
                                            @if($medicalService->sample_type)
                                                <br><small class="text-muted">Type: {{ $medicalService->sample_type }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Not Required</span>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <strong>Clinical Form:</strong>
                                        @if($medicalService->requires_form)
                                            <span class="badge bg-info">Required</span>
                                            @if($medicalService->form_type)
                                                <br><small class="text-muted">Form Type: {{ $medicalService->form_type }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Not Required</span>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <strong>Result Template:</strong>
                                        @if($medicalService->resultTemplate)
                                            <span class="badge bg-success">{{ $medicalService->resultTemplate->name }}</span>
                                            @if($medicalService->resultTemplate->investigation_type)
                                                <small class="text-muted">({{ $medicalService->resultTemplate->investigation_type }})</small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">Not Set</span>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <strong>Turnaround Time:</strong>
                                        @if($medicalService->turnaround_time_hours)
                                            <span class="badge bg-primary">{{ $medicalService->turnaround_time_readable }}</span>
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <strong>Preparation Required:</strong>
                                        @if($medicalService->requires_preparation)
                                            <span class="badge bg-warning">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Usage Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="50%">Total Investigations:</th>
                                            <td><strong>{{ $medicalService->investigations()->count() }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>This Month:</th>
                                            <td>{{ $medicalService->investigations()->whereMonth('ordered_at', now()->month)->count() }}</td>
                                        </tr>
                                        <tr>
                                            <th>This Week:</th>
                                            <td>{{ $medicalService->investigations()->whereBetween('ordered_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</td>
                                        </tr>
                                        <tr>
                                            <th>Pending:</th>
                                            <td>{{ $medicalService->investigations()->whereIn('status', ['ordered', 'collected'])->count() }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($medicalService->description)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Description</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $medicalService->description }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Preparation Instructions -->
                        @if($medicalService->preparation_instructions)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Patient Preparation Instructions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        {{ $medicalService->preparation_instructions }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Form Type Configuration -->
                        @if($medicalService->requires_form && $medicalService->form_type)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Form Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file-alt"></i> 
                                        This service requires the <strong>{{ $medicalService->form_type }}</strong> form.
                                        <br><small class="text-muted">The form will be included using @@include directive during the investigation process.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Recent Investigations -->
                        @if($recentInvestigations->count() > 0)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Recent Investigations (Last 10)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Patient</th>
                                                    <th>Doctor</th>
                                                    <th>Status</th>
                                                    <th>Priority</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentInvestigations as $investigation)
                                                <tr>
                                                    <td>{{ $investigation->ordered_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @if($investigation->patient)
                                                            <strong>{{ $investigation->patient->first_name }} {{ $investigation->patient->last_name }}</strong>
                                                            <br><small class="text-muted">{{ $investigation->patient->mr_number }}</small>
                                                        @else
                                                            <span class="text-muted">Patient not found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($investigation->doctor)
                                                            {{ $investigation->doctor->first_name }} {{ $investigation->doctor->last_name }}
                                                        @else
                                                            <span class="text-muted">Doctor not found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'ordered' => 'warning',
                                                                'collected' => 'info',
                                                                'processing' => 'primary',
                                                                'completed' => 'success',
                                                                'cancelled' => 'danger'
                                                            ];
                                                            $statusColor = $statusColors[$investigation->status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($investigation->status) }}</span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $priorityColors = [
                                                                'stat' => 'danger',
                                                                'urgent' => 'warning',
                                                                'routine' => 'secondary'
                                                            ];
                                                            $priorityColor = $priorityColors[$investigation->priority] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $priorityColor }}">{{ ucfirst($investigation->priority) }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('investigations.show', $investigation) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .card {
        margin-bottom: 1rem;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
    }
    
    pre code {
        background-color: transparent;
        padding: 0;
    }
</style>
@endpush
