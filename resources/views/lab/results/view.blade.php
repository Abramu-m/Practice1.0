@extends('layouts.app_main_layout')

@section('page_title', 'Lab Result - ' . $result->investigation->medicalService->name)

@section('main_content')
<div class="container-fluid">
    <!-- Investigation Information Header -->
    <div class="alert alert-primary mb-4">
        <div class="row">
            <div class="col-md-8">
                <h6><i class="fas fa-vial"></i> Investigation Details</h6>
                <strong>{{ $result->investigation->medicalService->name }}</strong>
                @if($result->investigation->medicalService->code)
                    <span class="badge bg-light text-dark ms-2">{{ $result->investigation->medicalService->code }}</span>
                @endif
                <br>
                <small>
                    Patient: <strong>{{ $result->investigation->patient->first_name }} {{ $result->investigation->patient->last_name }}</strong> |
                    Investigation ID: {{ $result->investigation->id }} |
                    Priority: 
                    <span class="badge bg-{{ $result->investigation->priority === 'stat' ? 'danger' : ($result->investigation->priority === 'urgent' ? 'warning' : 'secondary') }}">
                        {{ strtoupper($result->investigation->priority) }}
                    </span>
                </small>
            </div>
            <div class="col-md-4 text-end">
                <div>
                    <strong>Ordered:</strong> {{ $result->investigation->ordered_at ? $result->investigation->ordered_at->format('M d, Y H:i') : 'N/A' }}<br>
                    <strong>Doctor:</strong> 
                    @if($result->investigation->doctor && $result->investigation->doctor->user)
                        Dr. {{ $result->investigation->doctor->user->first_name }} {{ $result->investigation->doctor->user->last_name }}
                    @else
                        <span class="text-muted">Not specified</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('lab.results.form', $result->investigation->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Results Form
            </a>
        </div>
        <div>
            <span class="badge bg-{{ $result->form_status === 'final' ? 'success' : ($result->form_status === 'preliminary' ? 'info' : 'warning') }}">
                {{ ucfirst($result->form_status) }} Report
            </span>
        </div>
    </div>

    <!-- Result Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-file-medical text-primary"></i> 
                Template Result Details
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th>Template:</th>
                            <td>{{ ucfirst($result->template_name) }}</td>
                        </tr>
                        <tr>
                            <th>Version:</th>
                            <td>{{ $result->template_version }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $result->form_status === 'final' ? 'success' : ($result->form_status === 'preliminary' ? 'info' : 'warning') }}">
                                    {{ ucfirst($result->form_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th>Reported By:</th>
                            <td>{{ $result->reportedBy->name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <th>Reported At:</th>
                            <td>{{ $result->reported_at ? $result->reported_at->format('M d, Y H:i A') : 'N/A' }}</td>
                        </tr>
                        @if($result->verified_by && $result->verified_at)
                        <tr>
                            <th>Verified By:</th>
                            <td>{{ $result->verifiedBy->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Verified At:</th>
                            <td>{{ $result->verified_at ? $result->verified_at->format('M d, Y H:i A') : 'N/A' }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Data -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-line text-success"></i> 
                {{ $result->investigation->medicalService->name }} Results
            </h5>
        </div>
        <div class="card-body">
            @if(($result->template_name === 'simple' || $result->template_name === 'simple_lab') && isset($result->form_data['parameters']))
                {{-- Simple lab results display --}}
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Value</th>
                                <th>Unit</th>
                                <th>Normal Range</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Handle both array and object formats
                                $parameters = $result->form_data['parameters'];
                                if (is_string($parameters)) {
                                    $parameters = json_decode($parameters, true);
                                }
                                // If it's still not an array, try to make it one
                                if (!is_array($parameters)) {
                                    $parameters = [$parameters];
                                }
                            @endphp
                            
                            @foreach($parameters as $param)
                                @php
                                    // Handle both array and object parameter formats
                                    if (is_string($param)) {
                                        $param = json_decode($param, true);
                                    }
                                    
                                    // Ensure we have an array
                                    if (!is_array($param)) {
                                        continue;
                                    }
                                @endphp
                                <tr>
                                    <td class="fw-medium">{{ $param['parameter_name'] ?? 'N/A' }}</td>
                                    <td>{{ $param['value'] ?? 'N/A' }}</td>
                                    <td class="text-muted">{{ $param['unit'] ?? '' }}</td>
                                    <td class="text-muted">{{ $param['normal_range'] ?? '' }}</td>
                                    <td>
                                        @php
                                            $status = $param['status'] ?? 'normal';
                                            $badgeClass = match($status) {
                                                'high', 'critical' => 'bg-danger',
                                                'low' => 'bg-warning',
                                                'normal' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td class="text-muted">{{ $param['remarks'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
                    <div class="mt-3">
                        <h6>Additional Comments:</h6>
                        <div class="alert alert-light">
                            {{ $result->form_data['additional_comments'] }}
                        </div>
                    </div>
                @endif

                @if(isset($result->form_data['analyzed_by']) || isset($result->form_data['analysis_date']))
                    <div class="mt-3">
                        <h6>Analysis Information:</h6>
                        <div class="row">
                            @if(isset($result->form_data['analyzed_by']))
                            <div class="col-md-6">
                                <strong>Analyzed By:</strong> {{ $result->form_data['analyzed_by'] }}
                            </div>
                            @endif
                            @if(isset($result->form_data['analysis_date']))
                            <div class="col-md-6">
                                <strong>Analysis Date:</strong> {{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('M d, Y H:i') }}
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

            @elseif($result->template_name === 'tb')
                {{-- TB results display --}}
                <div class="row">
                    @if(isset($result->form_data['microscopy_result']))
                    <div class="col-md-6">
                        <h6>Microscopy Results</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Result:</strong></td>
                                <td>{{ $result->form_data['microscopy_result'] ?? 'N/A' }}</td>
                            </tr>
                            @if(isset($result->form_data['microscopy_grade']))
                            <tr>
                                <td><strong>Grade:</strong></td>
                                <td>{{ $result->form_data['microscopy_grade'] ?? 'N/A' }}</td>
                            </tr>
                            @endif
                            @if(isset($result->form_data['examined_by']))
                            <tr>
                                <td><strong>Examined by:</strong></td>
                                <td>{{ $result->form_data['examined_by'] ?? 'N/A' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    @endif

                    @if(isset($result->form_data['xpert_result']))
                    <div class="col-md-6">
                        <h6>Xpert MTB/RIF Results</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>MTB Result:</strong></td>
                                <td>{{ $result->form_data['xpert_result'] ?? 'N/A' }}</td>
                            </tr>
                            @if(isset($result->form_data['rif_resistance']))
                            <tr>
                                <td><strong>RIF Resistance:</strong></td>
                                <td>{{ $result->form_data['rif_resistance'] ?? 'N/A' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    @endif
                </div>

                @if(isset($result->form_data['clinical_notes']) && $result->form_data['clinical_notes'])
                    <div class="mt-3">
                        <h6>Clinical Notes:</h6>
                        <div class="alert alert-light">
                            {{ $result->form_data['clinical_notes'] }}
                        </div>
                    </div>
                @endif

            @else
                {{-- Generic complex result display --}}
                <div class="row">
                    @foreach($result->form_data as $key => $value)
                        @if(!in_array($key, ['_token', 'template_', 'action']) && !empty($value))
                        <div class="col-md-6 mb-3">
                            <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                            <div class="mt-1">
                                @if(is_array($value))
                                    @if(count($value) > 0)
                                        @foreach($value as $subKey => $subValue)
                                            @if(is_array($subValue))
                                                <div><em>{{ ucwords(str_replace('_', ' ', $subKey)) }}:</em> 
                                                    <pre class="text-muted">{{ json_encode($subValue, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            @else
                                                <div><em>{{ ucwords(str_replace('_', ' ', $subKey)) }}:</em> {{ $subValue }}</div>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-muted">No data</span>
                                    @endif
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if($result->form_data && count($result->form_data) > 0)
        {{-- Keep original raw data section as backup --}}
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-code"></i> Raw Data (Debug)
                    <button class="btn btn-sm btn-outline-secondary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#rawDataCollapse">
                        Toggle
                    </button>
                </h6>
            </div>
            <div class="collapse" id="rawDataCollapse">
                <div class="card-body">
                    <div class="result-data-container">
                        @foreach($result->form_data as $key => $value)
                            @if(!is_null($value) && $value !== '')
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>{{ ucwords(str_replace(['_', '-'], ' ', $key)) }}:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        @if(is_array($value))
                                            @if(count($value) > 0)
                                                <pre class="bg-light p-2 small">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                <span class="text-muted">Empty array</span>
                                            @endif
                                        @else
                                            <span class="result-value">{{ $value }}</span>
                                        @endif
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                No result data available for this template result.
            </div>
        @endif
        </div>
    </div>

    <!-- Metadata (if available) -->
    @if($result->metadata && count($result->metadata) > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-info-circle text-muted"></i> 
                Additional Information
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($result->metadata as $key => $value)
                    @if(!is_null($value) && $value !== '')
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">
                                <strong>{{ ucwords(str_replace(['_', '-'], ' ', $key)) }}:</strong> 
                                {{ is_array($value) ? json_encode($value) : $value }}
                            </small>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('lab.results.form', $result->investigation->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-edit"></i> Edit Results
                    </a>
                    @if($result->investigation->consultation && $result->investigation->consultation->visit)
                        <a href="{{ route('lab.visits.investigations', $result->investigation->consultation->visit->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> All Investigations
                        </a>
                    @else
                        <a href="{{ route('lab.visits.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Lab Dashboard
                        </a>
                    @endif
                </div>
                <div>
                    <button class="btn btn-outline-success" onclick="printResult()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    @if($result->form_status !== 'final')
                        <button class="btn btn-warning" onclick="promoteToFinal()">
                            <i class="fas fa-check"></i> Mark as Final
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<style>
.result-value {
    font-weight: 500;
    color: #2c3e50;
}

.result-data-container hr {
    border-color: #e9ecef;
    opacity: 0.5;
}

.result-data-container pre {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    padding: 0.5rem;
    font-size: 0.875rem;
    max-height: 200px;
    overflow-y: auto;
}

.result-data-container .list-unstyled li {
    padding: 0.25rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.result-data-container .list-unstyled li:last-child {
    border-bottom: none;
}

@media print {
    .btn, .card-header {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .alert {
        border: 1px solid #ddd !important;
    }
}
</style>

<script>
function printResult() {
    window.print();
}

function promoteToFinal() {
    if (confirm('Are you sure you want to mark this result as final? This action cannot be undone.')) {
        // TODO: Implement promote to final functionality
        alert('Promote to final functionality - Result ID: {{ $result->id }}');
    }
}
</script>
