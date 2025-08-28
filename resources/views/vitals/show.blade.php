@extends('layouts.app_main_layout')

@section('page_title', 'Vital Signs Management')
@section('Content_Description', 'Record and Manage Vital Signs')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-thermometer-half text-info"></i> Vital Signs Management</h2>
            
            <!-- Patient Visit Info -->
            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Patient Visit Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Visit ID:</strong> {{ $visit->id }}
                        </div>
                        <div class="col-md-3">
                            <strong>Visit Date:</strong> {{ $visit->visit_date ? $visit->visit_date->format('d/m/Y') : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Patient:</strong> {{ $visit->patientInfo->full_name }}
                            <br><small class="text-muted">MR: {{ $visit->patientInfo->mr_number }}</small>
                        </div>
                        <div class="col-md-3">
                            <strong>Doctor: </strong> {{ optional(optional($visit->doctorInfo)->user)->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vitals Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-heartbeat"></i> Record Vital Signs</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vitals.store', $visit->id) }}" method="POST" id="vitalsForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Blood Pressure -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Blood Pressure</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="systolic_bp" 
                                               placeholder="Systolic" min="0" max="300" value="{{ $vitals->systolic_bp ?? '' }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="diastolic_bp" 
                                               placeholder="Diastolic" min="0" max="200" value="{{ $vitals->diastolic_bp ?? '' }}">
                                    </div>
                                </div>
                                <small class="text-muted">mmHg (e.g., 120/80)</small>
                            </div>

                            <!-- Pulse Rate -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Pulse Rate</label>
                                <input type="text" class="form-control" name="pulse_rate" 
                                       placeholder="Enter pulse rate" min="0" max="200" step="0.1" 
                                       value="{{ $vitals->pulse_rate ?? '' }}">
                                <small class="text-muted">beats/min</small>
                            </div>

                            <!-- Temperature -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Temperature</label>
                                <input type="text" class="form-control" name="temperature" 
                                       placeholder="Enter temperature" min="90" max="110" step="0.1" 
                                       value="{{ $vitals->temperature ?? '' }}">
                                <small class="text-muted">°F</small>
                            </div>

                            <!-- Respiratory Rate -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Respiratory Rate</label>
                                <input type="text" class="form-control" name="respiratory_rate" 
                                       placeholder="Enter rate" min="0" max="60" 
                                       value="{{ $vitals->respiratory_rate ?? '' }}">
                                <small class="text-muted">breaths/min</small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Weight -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Weight</label>
                                <input type="text" class="form-control" name="weight" 
                                       placeholder="Enter weight" min="0" max="500" step="0.1" 
                                       value="{{ $vitals->weight ?? '' }}">
                                <small class="text-muted">kg</small>
                            </div>

                            <!-- Height -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Height</label>
                                <input type="text" class="form-control" name="height" 
                                       placeholder="Enter height" min="0" max="300" step="0.1" 
                                       value="{{ $vitals->height ?? '' }}">
                                <small class="text-muted">cm</small>
                            </div>

                            <!-- Oxygen Saturation -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Oxygen Saturation</label>
                                <input type="text" class="form-control" name="oxygen_saturation" 
                                       placeholder="Enter SpO2" min="0" max="100" step="0.1" 
                                       value="{{ $vitals->oxygen_saturation ?? '' }}">
                                <small class="text-muted">%</small>
                            </div>

                            <!-- BMI (Auto-calculated) -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">BMI</label>
                                <input type="text" class="form-control" name="bmi" 
                                       placeholder="Auto-calculated" readonly 
                                       value="{{ $vitals->bmi ?? '' }}">
                                <small class="text-muted">kg/m²</small>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Additional observations or notes...">{{ $vitals->notes ?? '' }}</textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            @if(auth()->user()->isNurse())
                            <a href="{{ url('vitals') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Vitals
                            </a>
                            @else
                            <a href="{{ url('vitals') }}" class="btn btn-success">
                                <i class="fas fa-arrow-left"></i> Back to Vitals
                            </a>
                            <a href="{{ route('consultations.show', $visit->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Consultation
                            </a>
                            @endif

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ $vitals ? 'Update' : 'Save' }} Vitals
                            </button>
                        </div>
                    </form>
                </div>
                @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if(session('success'))
                <div class="alert alert-success mt-3">
                    {{ session('success') }}
                    <a href="{{ url('vitals') }}" class="btn btn-outline-success btn-sm ms-2">
                        <i class="fas fa-list"></i> View All Vitals
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate BMI when weight or height changes
document.addEventListener('DOMContentLoaded', function() {
    const weightInput = document.querySelector('input[name="weight"]');
    const heightInput = document.querySelector('input[name="height"]');
    const bmiInput = document.querySelector('input[name="bmi"]');

    function calculateBMI() {
        const weight = parseFloat(weightInput.value);
        const height = parseFloat(heightInput.value) / 100; // Convert cm to meters
        
        if (weight && height && height > 0) {
            const bmi = weight / (height * height);
            bmiInput.value = Math.round(bmi * 10) / 10; // Round to 1 decimal place
        } else {
            bmiInput.value = '';
        }
    }

    weightInput.addEventListener('input', calculateBMI);
    heightInput.addEventListener('input', calculateBMI);
});
</script>
@endsection
