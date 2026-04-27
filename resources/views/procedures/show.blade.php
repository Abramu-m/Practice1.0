@extends('layouts.app_main_layout')

@section('page_title', 'Enter Procedure Results')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Investigation Details Header -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-0">
                                <i class="fas fa-clipboard-check"></i>
                                {{ $investigation->medicalService->name ?? 'Procedure Results' }}
                            </h4>
                            <small>Investigation ID: #{{ $investigation->id }}</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge {{ $investigation->priority_badge_class }} fs-6 me-2">
                                {{ strtoupper($investigation->priority) }}
                            </span>
                            <span class="badge {{ $investigation->status_badge_class }} fs-6">
                                {{ strtoupper($investigation->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><strong>Patient Information</strong></h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $investigation->patient->first_name ?? '' }} {{ $investigation->patient->last_name ?? '' }}</td>
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
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Investigation Details</strong></h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Ordered By:</strong></td>
                                    <td>
                                        @if($investigation->doctor)
                                            Dr. {{ $investigation->doctor->first_name }} {{ $investigation->doctor->last_name }}
                                        @else
                                            Unknown Doctor
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Date Ordered:</strong></td>
                                    <td>{{ $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>{{ $investigation->medicalService->category ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sample Required:</strong></td>
                                    <td>
                                        @if($investigation->medicalService->requires_sample ?? false)
                                            <span class="badge bg-info">{{ $investigation->medicalService->sample_type ?? 'Yes' }}</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Entry Form -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus' }}"></i>
                            {{ $editMode ? 'Edit Results' : 'Enter Results' }} - {{ $procedureType['title'] }}
                        </h5>
                        @if($editMode && isset($existingData['_result_status']))
                            <div>
                                @if($existingData['_result_status'] === 'draft')
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-edit"></i> Draft Results
                                    </span>
                                @elseif($existingData['_result_status'] === 'preliminary')
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Preliminary Results
                                    </span>
                                @elseif($existingData['_result_status'] === 'final')
                                    <span class="badge bg-success">
                                        <i class="fas fa-lock"></i> Final Results (Read-only)
                                    </span>
                                @endif
                                @if(isset($existingData['_updated_at']))
                                    <small class="text-muted ms-2">
                                        Last updated: {{ \Carbon\Carbon::parse($existingData['_updated_at'])->format('M d, Y H:i') }}
                                    </small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('procedures.store-result', $investigation->id) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="result_type" value="{{ $procedureType['type'] ?: 'default' }}">
                        @include('lab.result_templates.' . ($procedureType['type'] ?: 'default'))
                        @if($errors->any())
                            <div class="alert alert-danger mt-3">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="action" value="final" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i> Submit Final
                            </button>
                            <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                                <i class="fas fa-clock me-1"></i> Save Preliminary
                            </button>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                                <i class="fas fa-save me-1"></i> Save Draft
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Previous Results (if any) -->
            @if($investigation->results && $investigation->results->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i>
                            Previous Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($investigation->results->sortByDesc('created_at') as $result)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">
                                            {{ $result->result_type ?? 'General Result' }}
                                            <small class="text-muted ms-2">{{ $result->created_at->format('M d, Y H:i') }}</small>
                                        </h6>
                                        <div class="timeline-body">
                                            @if($result->form_data)
                                                @php
                                                    $rTplCode = $result->metadata['template_code'] ?? $result->template_name ?? '';
                                                @endphp
                                                @if($rTplCode === 'narrative_lab' && isset($result->form_data['parameters']))
                                                    @php
                                                        $rParams = $result->form_data['parameters'];
                                                        if (is_string($rParams)) $rParams = json_decode($rParams, true);
                                                        $rText = $rParams[0]['value'] ?? null;
                                                    @endphp
                                                    <div class="border rounded p-2 bg-light" style="white-space:pre-wrap;font-size:0.9rem;">{{ $rText ?? '—' }}</div>
                                                    @if(!empty($result->form_data['additional_comments']))
                                                        <div class="mt-2 text-muted small"><strong>Comments:</strong> {{ $result->form_data['additional_comments'] }}</div>
                                                    @endif
                                                @else
                                                @foreach($result->form_data as $key => $value)
                                                    @if(!is_array($value))
                                                        <div class="row mb-1">
                                                            <div class="col-3"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></div>
                                                            <div class="col-9">{{ $value }}</div>
                                                        </div>
                                                    @elseif(is_array($value) && in_array($key, ['primary_images', 'additional_images', 'procedure_images']))
                                                        <div class="row mb-2">
                                                            <div class="col-3"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></div>
                                                            <div class="col-9">
                                                                @foreach($value as $imagePath)
                                                                    <div class="mb-2">
                                                                        <img src="{{ asset('storage/' . $imagePath) }}" 
                                                                             alt="Procedure Image" 
                                                                             class="img-thumbnail" 
                                                                             style="max-width: 200px; max-height: 150px;"
                                                                             onclick="showImageModal('{{ asset('storage/' . $imagePath) }}')">
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                                @if(isset($result->form_data['result_image']))
                                                    <div class="row mb-2">
                                                        <div class="col-3"><strong>Result Image:</strong></div>
                                                        <div class="col-9">
                                                            <img src="{{ asset('storage/' . $result->form_data['result_image']) }}" 
                                                                 alt="Result Image" 
                                                                 class="img-thumbnail" 
                                                                 style="max-width: 200px; max-height: 150px;"
                                                                 onclick="showImageModal('{{ asset('storage/' . $result->form_data['result_image']) }}')">
                                                        </div>
                                                    </div>
                                                @endif
                                                @endif {{-- end @else for non-narrative --}}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #007bff;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: -29px;
    top: 17px;
    width: 2px;
    height: calc(100% + 5px);
    background-color: #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #007bff;
}

.timeline-title {
    margin-bottom: 10px;
    color: #495057;
}

.timeline-body {
    color: #6c757d;
}

.form-floating .form-select {
    padding-top: 1.625rem;
    padding-bottom: 0.625rem;
}

.procedure-form {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #dee2e6;
}

.required-field {
    border-left: 3px solid #dc3545;
}

.normal-range {
    font-size: 0.875rem;
    color: #6c757d;
    font-style: italic;
}
</style>
@endsection

@section('scripts')
<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).innerHTML = 
                `<img src="${e.target.result}" class="img-fluid rounded shadow" style="max-height: 200px;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function addDynamicField(containerId, fieldName) {
    const container = document.getElementById(containerId);
    const fieldCount = container.children.length;
    
    const newField = document.createElement('div');
    newField.className = 'row mb-3';
    newField.innerHTML = `
        <div class="col-md-4">
            <input type="text" name="${fieldName}[${fieldCount}][parameter]" 
                   class="form-control" placeholder="Parameter Name" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="${fieldName}[${fieldCount}][value]" 
                   class="form-control" placeholder="Value" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="${fieldName}[${fieldCount}][unit]" 
                   class="form-control" placeholder="Unit">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newField);
}

function calculateBMI() {
    const weight = parseFloat(document.getElementById('weight').value) || 0;
    const height = parseFloat(document.getElementById('height').value) || 0;
    
    if (weight > 0 && height > 0) {
        const heightInMeters = height / 100;
        const bmi = weight / (heightInMeters * heightInMeters);
        document.getElementById('bmi').value = bmi.toFixed(1);
        
        // BMI Category
        let category = '';
        if (bmi < 18.5) category = 'Underweight';
        else if (bmi < 25) category = 'Normal weight';
        else if (bmi < 30) category = 'Overweight';
        else category = 'Obesity';
        
        document.getElementById('bmi_category').value = category;
    }
}

// Auto-save functionality removed to prevent CSRF token conflicts
// Users can manually save drafts using the Save as Draft button

document.addEventListener('DOMContentLoaded', function() {
    // Validate required fields
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    }
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

// Clean up auto-save on page unload
window.addEventListener('beforeunload', function() {
    if (autoSaveInterval) {
        clearInterval(autoSaveInterval);
    }
});
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
