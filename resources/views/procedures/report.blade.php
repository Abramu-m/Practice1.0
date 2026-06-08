@extends('layouts.app_main_layout')

@section('page_title', 'Procedure Report - ' . ($investigation->medicalService->name ?? 'Unknown Procedure'))

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-file-medical text-primary"></i>
                        Procedure Report
                    </h4>
                    <div class="no-print">
                        <a href="{{ route('procedures.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Procedures
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Patient Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header py-2">
                                    <h6 class="mb-0"><i class="fas fa-user text-primary"></i> Patient Information</h6>
                                </div>
                                <div class="card-body py-3">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>
                                                @if($investigation->patient)
                                                    {{ $investigation->patient->first_name }} {{ $investigation->patient->last_name }}
                                                @else
                                                    <span class="text-muted">Unknown Patient</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>MR Number:</strong></td>
                                            <td>{{ $investigation->patient->mr_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Age:</strong></td>
                                            <td>{{ $investigation->patient->age ?? 'N/A' }} years</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Gender:</strong></td>
                                            <td>{{ $investigation->patient->gender ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $investigation->patient->phone_number ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header py-2">
                                    <h6 class="mb-0"><i class="fas fa-stethoscope text-success"></i> Procedure Information</h6>
                                </div>
                                <div class="card-body py-3">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td><strong>Procedure:</strong></td>
                                            <td>{{ $investigation->medicalService->name ?? 'Unknown Procedure' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Category:</strong></td>
                                            <td>{{ $investigation->medicalService->serviceCategory->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ordered By:</strong></td>
                                            <td>
                                                @if($investigation->doctor)
                                                    Dr. {{ $investigation->doctor->first_name }} {{ $investigation->doctor->last_name }}
                                                @else
                                                    <span class="text-muted">Unknown Doctor</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Priority:</strong></td>
                                            <td>
                                                <span class="badge {{ $investigation->priority_badge_class }}">
                                                    {{ strtoupper($investigation->priority) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Order Date:</strong></td>
                                            <td>{{ $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y H:i') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge {{ $investigation->status_badge_class }}">
                                                    {{ ucfirst($investigation->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Procedure Results -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Procedure Results</h5>
                                </div>
                                <div class="card-body">
                                    @if($investigation->results && $investigation->results->count() > 0)
                                        <!-- Display results based on the investigation_template_results structure -->
                                        @foreach($investigation->results as $result)
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="text-primary mb-0">
                                                        <i class="fas fa-file-medical"></i>
                                                        {{ $result->template_name ?? 'Procedure Report' }}
                                                    </h6>
                                                    <div>
                                                        <span class="badge {{ $result->form_status === 'final' ? 'bg-success' : ($result->form_status === 'preliminary' ? 'bg-warning' : 'bg-secondary') }}">
                                                            {{ ucfirst($result->form_status) }}
                                                        </span>
                                                        <small class="text-muted ms-2">
                                                            Version {{ $result->template_version ?? '1.0' }}
                                                        </small>
                                                    </div>
                                                </div>

                                                @if($result->form_data)
                                                    @php
                                                        $formData = is_string($result->form_data) ? json_decode($result->form_data, true) : $result->form_data;
                                                    @endphp
                                                    
                                                    @if($formData && is_array($formData))
                                                        <!-- Handle different form data structures -->
                                                        
                                                        <!-- Simple Parameters Display -->
                                                        @if(isset($formData['parameters']) && is_array($formData['parameters']))
                                                            <div class="table-responsive mb-4">
                                                                <table class="table table-bordered">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Parameter</th>
                                                                            <th>Result</th>
                                                                            <th>Unit</th>
                                                                            <th>Normal Range</th>
                                                                            <th>Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($formData['parameters'] as $parameter)
                                                                            <tr>
                                                                                <td><strong>{{ $parameter['parameter'] ?? 'N/A' }}</strong></td>
                                                                                <td>{{ $parameter['value'] ?? 'N/A' }}</td>
                                                                                <td>{{ $parameter['unit'] ?? '' }}</td>
                                                                                <td>{{ $parameter['normal_range'] ?? '' }}</td>
                                                                                <td>
                                                                                    @if(isset($parameter['status']))
                                                                                        @if($parameter['status'] === 'normal')
                                                                                            <span class="badge bg-success">Normal</span>
                                                                                        @elseif($parameter['status'] === 'abnormal')
                                                                                            <span class="badge bg-warning">Abnormal</span>
                                                                                        @elseif($parameter['status'] === 'critical')
                                                                                            <span class="badge bg-danger">Critical</span>
                                                                                        @else
                                                                                            <span class="badge bg-secondary">{{ $parameter['status'] }}</span>
                                                                                        @endif
                                                                                    @else
                                                                                        <span class="text-muted">-</span>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif

                                                        <!-- Display all other form fields -->
                                                        @php
                                                            $excludeKeys = ['parameters', '_token', '_method', 'result_type', 'action', 'investigation_id'];
                                                        @endphp
                                                        
                                                        <div class="row">
                                                            @foreach($formData as $key => $value)
                                                                @if(!in_array($key, $excludeKeys) && !empty($value))
                                                                    <div class="col-md-6 mb-3">
                                                                        <div class="card bg-light">
                                                                            <div class="card-body py-2">
                                                                                <h6 class="card-title text-primary mb-1">
                                                                                    {{ ucwords(str_replace(['_', '-'], ' ', $key)) }}
                                                                                </h6>
                                                                                <div class="card-text">
                                                                                    @if(is_array($value))
                                                                                        @if(in_array($key, ['primary_images', 'additional_images', 'procedure_images']))
                                                                                            <!-- Handle image arrays -->
                                                                                            <div class="row">
                                                                                                @foreach($value as $imagePath)
                                                                                                    <div class="col-md-4 mb-2">
                                                                                                        <img src="{{ asset('storage/' . $imagePath) }}" 
                                                                                                             alt="Procedure Image" 
                                                                                                             class="img-thumbnail" 
                                                                                                             style="width: 100%; max-height: 150px; object-fit: cover;"
                                                                                                             onclick="showImageModal('{{ asset('storage/' . $imagePath) }}')">
                                                                                                    </div>
                                                                                                @endforeach
                                                                                            </div>
                                                                                        @elseif(isset($value[0]) && is_array($value[0]))
                                                                                            <!-- Handle array of objects (like vital signs) -->
                                                                                            @foreach($value as $item)
                                                                                                <div class="mb-2 p-2 border-start border-3 border-info">
                                                                                                    @foreach($item as $subKey => $subValue)
                                                                                                        <strong>{{ ucwords(str_replace(['_', '-'], ' ', $subKey)) }}:</strong> {{ $subValue }}<br>
                                                                                                    @endforeach
                                                                                                </div>
                                                                                            @endforeach
                                                                                        @else
                                                                                            <!-- Handle simple array -->
                                                                                            @foreach($value as $subKey => $subValue)
                                                                                                <strong>{{ ucwords(str_replace(['_', '-'], ' ', $subKey)) }}:</strong> {{ $subValue }}<br>
                                                                                            @endforeach
                                                                                        @endif
                                                                                    @elseif(in_array($key, ['result_image']) && !empty($value))
                                                                                        <!-- Handle single image -->
                                                                                        <img src="{{ asset('storage/' . $value) }}" 
                                                                                             alt="Result Image" 
                                                                                             class="img-thumbnail" 
                                                                                             style="max-width: 300px; max-height: 200px;"
                                                                                             onclick="showImageModal('{{ asset('storage/' . $value) }}')">
                                                                                    @else
                                                                                        {{ $value }}
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Unable to parse form data.
                                                        </div>
                                                    @endif
                                                @endif

                                                <!-- Metadata display if available -->
                                                @if($result->metadata)
                                                    @php
                                                        $metadata = is_string($result->metadata) ? json_decode($result->metadata, true) : $result->metadata;
                                                    @endphp
                                                    
                                                    @if($metadata && is_array($metadata))
                                                        <div class="mt-3">
                                                            <h6 class="text-info"><i class="fas fa-info-circle"></i> Additional Information</h6>
                                                            <div class="border p-3 bg-light">
                                                                @foreach($metadata as $key => $value)
                                                                    <strong>{{ ucwords(str_replace(['_', '-'], ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}<br>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif

                                                <!-- Report information -->
                                                <div class="mt-3 pt-2 border-top">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <small class="text-muted">
                                                                <i class="fas fa-user"></i>
                                                                Reported by: {{ $result->reportedBy->name ?? 'Unknown' }}
                                                            </small>
                                                        </div>
                                                        <div class="col-md-6 text-end">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i>
                                                                {{ $result->reported_at ? $result->reported_at->format('M d, Y H:i') : 'N/A' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    
                                                    @if($result->verified_by && $result->verified_at)
                                                        <div class="row mt-1">
                                                            <div class="col-md-6">
                                                                <small class="text-success">
                                                                    <i class="fas fa-check-circle"></i>
                                                                    Verified by: {{ $result->verifiedBy->name ?? 'Unknown' }}
                                                                </small>
                                                            </div>
                                                            <div class="col-md-6 text-end">
                                                                <small class="text-success">
                                                                    <i class="fas fa-calendar-check"></i>
                                                                    {{ $result->verified_at->format('M d, Y H:i') }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if(!$loop->last)
                                                <hr class="my-4">
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                                            <h5>No Results Available</h5>
                                            <p class="mb-0">Results have not been entered for this procedure yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Footer -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="border-top pt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i>
                                            Report Generated: {{ now()->format('M d, Y H:i') }}
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            <i class="fas fa-hospital"></i>
                                            {{ config('app.name', 'Medical Practice') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
@media print {
    .app-header,
    .app-sidebar,
    .app-footer,
    .no-print { display: none !important; }

    .app-wrapper, .app-main, .app-content, .container-fluid {
        margin: 0 !important; padding: 0 !important;
        width: 100% !important; background: #fff !important;
    }

    body { font-size: 12px; }
    .card { border: none; box-shadow: none; }

    @page { margin: 10mm 12mm; }
}

.table-borderless td {
    border: none;
    padding: 0.25rem 0.5rem;
}

.procedure-result-section {
    margin-bottom: 2rem;
}

.result-parameter {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin-bottom: 1rem;
}

.vital-card {
    transition: transform 0.2s;
}

.vital-card:hover {
    transform: translateY(-2px);
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any interactive functionality here if needed
    console.log('Procedure report loaded for investigation:', {{ $investigation->id }});
});

// Function to show image in modal
function showImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const downloadLink = document.getElementById('downloadImageLink');
    
    if (modal && modalImage) {
        modalImage.src = imageSrc;
        if (downloadLink) {
            downloadLink.href = imageSrc;
        }
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } else {
        // Fallback - open in new window
        window.open(imageSrc, '_blank');
    }
}
</script>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Procedure Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Procedure Image" class="img-fluid">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadImageLink" href="" download class="btn btn-primary">Download</a>
            </div>
        </div>
    </div>
</div>
@endsection
