@extends('layouts.app_main_layout')

@section('page_title', 'Patient Consultation')

@section('patient_info')
    <span class="text-muted">Patient: {{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->middle_name ?? '' }} {{ $visit->patientInfo->last_name }}</span>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/lab-investigation-modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/prescription-modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/medical-history-modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/vitals-modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/systemic-examination-modal.css') }}">
@endsection

@section('main_content')
<div class="container-fluid">
    
    <!-- Navigation Pills -->
    <ul class="nav nav-pills nav-justified mb-4 bg-light p-3 rounded" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#home">
                <i class="fas fa-user-injured"></i> Patient Profile
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="pill" href="#clinical-information" data-tab="clinical-information">
                <i class="fas fa-stethoscope"></i> Clinical Information
                <span class="unsaved-indicator d-none ms-1" title="Unsaved changes">
                    <i class="fas fa-circle text-warning" style="font-size: 8px;"></i>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#examinations" data-tab="examinations">
                <i class="fas fa-heartbeat"></i> Examinations
                <span class="unsaved-indicator d-none ms-1" title="Unsaved changes">
                    <i class="fas fa-circle text-warning" style="font-size: 8px;"></i>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#diagnosis" data-tab="diagnosis">
                <i class="fas fa-diagnoses"></i> Diagnosis
                <span class="unsaved-indicator d-none ms-1" title="Unsaved changes">
                    <i class="fas fa-circle text-warning" style="font-size: 8px;"></i>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#investigations" data-tab="investigations">
                <i class="fas fa-flask"></i> Investigations/Procedures
                <span class="unsaved-indicator d-none ms-1" title="Unsaved changes">
                    <i class="fas fa-circle text-warning" style="font-size: 8px;"></i>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#treatment" data-tab="treatment">
                <i class="fas fa-pills"></i> Treatment
                <span class="unsaved-indicator d-none ms-1" title="Unsaved changes">
                    <i class="fas fa-circle text-warning" style="font-size: 8px;"></i>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#remarks" data-tab="remarks">
                <i class="fas fa-comment-medical"></i> Remarks
                <span class="unsaved-indicator d-none ms-1" title="Unsaved changes">
                    <i class="fas fa-circle text-warning" style="font-size: 8px;"></i>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#results" data-tab="results">
                <i class="fas fa-chart-line"></i> Results
                <span class="unsaved-indicator d-none ms-1" title="Unsaved changes">
                    <i class="fas fa-circle text-warning" style="font-size: 8px;"></i>
                </span>
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content border rounded p-4" style="min-height: 500px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        
        <!-- Patient Profile Tab -->
        <div id="home" class="tab-pane fade">
            <h3 class="mb-4"><i class="fas fa-user-injured text-primary"></i> Patient Profile</h3>
            
            <!-- Profile Sub-Tabs -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab">
                        <i class="fas fa-id-card"></i> Basic Information & Contact
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="visit-info-tab" data-bs-toggle="tab" data-bs-target="#visit-info" type="button" role="tab">
                        <i class="fas fa-calendar-check"></i> Current Visit
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="medical-alerts-tab" data-bs-toggle="tab" data-bs-target="#medical-alerts" type="button" role="tab">
                        <i class="fas fa-exclamation-triangle"></i> Medical History & Alerts
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="statistics-tab" data-bs-toggle="tab" data-bs-target="#statistics" type="button" role="tab">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- Basic Information & Contact Tab -->
                <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-id-card"></i> Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%"><strong>MR Number:</strong></td>
                                    <td><span class="badge bg-info">{{ $visit->patientInfo->mr_number }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->middle_name ?? '' }} {{ $visit->patientInfo->last_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Gender:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $visit->patientInfo->gender === 'Male' ? 'primary' : 'success' }}">
                                            {{ $visit->patientInfo->gender }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Date of Birth:</strong></td>
                                    <td>
                                        {{ $visit->patientInfo->date_of_birth ? \Carbon\Carbon::parse($visit->patientInfo->date_of_birth)->format('d/m/Y') : 'N/A' }}
                                        @if($visit->patientInfo->date_of_birth)
                                            <small class="text-muted">({{ \Carbon\Carbon::parse($visit->patientInfo->date_of_birth)->age }} years old)</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Age:</strong></td>
                                    <td>
                                        <strong class="text-primary">{{ $visit->patientInfo->age ?? 'N/A' }}</strong>
                                        @if($visit->patientInfo->age)
                                            years
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Blood Type:</strong></td>
                                    <td>
                                        @if($visit->patientInfo->blood_type)
                                            <span class="badge bg-danger">{{ $visit->patientInfo->blood_type }}</span>
                                        @else
                                            <span class="text-muted">Not recorded</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Marital Status:</strong></td>
                                    <td>{{ $visit->patientInfo->marital_status ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Occupation:</strong></td>
                                    <td>{{ $visit->patientInfo->occupation ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Religion:</strong></td>
                                    <td>{{ $visit->patientInfo->religion ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Emergency Contact:</strong></td>
                                    <td>
                                        @if($visit->patientInfo->emergency_contact_name)
                                            <strong>{{ $visit->patientInfo->emergency_contact_name }}</strong><br>
                                            <small class="text-muted">{{ $visit->patientInfo->emergency_contact_phone ?? 'No phone' }}</small><br>
                                            <small class="text-muted">{{ $visit->patientInfo->emergency_contact_relationship ?? 'Relationship not specified' }}</small>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Contact & Location Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Contact & Location</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%"><strong>Primary Phone:</strong></td>
                                    <td>
                                        @if($visit->patientInfo->phonenumber)
                                            <a href="tel:{{ $visit->patientInfo->phonenumber }}" class="text-decoration-none">
                                                <i class="fas fa-phone text-success"></i> {{ $visit->patientInfo->phonenumber }}
                                            </a>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Alternative Phone:</strong></td>
                                    <td>
                                        @if($visit->patientInfo->alternative_phone)
                                            <a href="tel:{{ $visit->patientInfo->alternative_phone }}" class="text-decoration-none">
                                                <i class="fas fa-phone text-info"></i> {{ $visit->patientInfo->alternative_phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        @if($visit->patientInfo->email)
                                            <a href="mailto:{{ $visit->patientInfo->email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope text-primary"></i> {{ $visit->patientInfo->email }}
                                            </a>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td>
                                        @if($visit->patientInfo->address)
                                            <address class="mb-0">
                                                {{ $visit->patientInfo->address }}<br>
                                                @if($visit->patientInfo->city){{ $visit->patientInfo->city }}, @endif
                                                @if($visit->patientInfo->state){{ $visit->patientInfo->state }} @endif
                                                @if($visit->patientInfo->postal_code){{ $visit->patientInfo->postal_code }}@endif
                                                @if($visit->patientInfo->country)<br>{{ $visit->patientInfo->country }}@endif
                                            </address>
                                        @else
                                            <span class="text-muted">Address not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>National ID:</strong></td>
                                    <td>{{ $visit->patientInfo->national_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nationality:</strong></td>
                                    <td>{{ $visit->patientInfo->nationality ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
                </div>
                <!-- End Basic Information & Contact Tab -->
                
                <!-- Current Visit Information Tab -->
                <div class="tab-pane fade" id="visit-info" role="tabpanel">
                    <div class="row justify-content-center">
                        <!-- Current Visit Information -->
                        <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Current Visit Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%"><strong>Visit Date:</strong></td>
                                    <td>
                                        <i class="fas fa-calendar text-primary"></i> 
                                        {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y') : 'N/A' }}
                                        <small class="text-muted">{{ $visit->visit_date ? '(' . \Carbon\Carbon::parse($visit->visit_date)->diffForHumans() . ')' : '' }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Visit Time:</strong></td>
                                    <td>
                                        <i class="fas fa-clock text-info"></i> 
                                        {{ $visit->visit_time ?? \Carbon\Carbon::parse($visit->created_at)->format('H:i') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Visit Type:</strong></td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $visit->visitType->type_name ?? 'General Consultation' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Patient Category:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $visit->patientInfo->patientCategory->categoryname }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Consulting Doctor:</strong></td>
                                    <td>
                                        <i class="fas fa-user-md text-success"></i> 
                                        {{ optional(optional($visit->doctorInfo)->user)->first_name ?? 'Dr.' }} {{ optional(optional($visit->doctorInfo)->user)->last_name ?? 'Unknown' }}
                                        @if(is_object($visit->doctorInfo) && $visit->doctorInfo->designationInfo)
                                            <br><small class="text-muted">{{ optional($visit->doctorInfo->designationInfo)->description }}</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Visit Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $visit->visit_status === 'completed' ? 'success' : 
                                            ($visit->visit_status === 'in_progress' ? 'warning' : 'secondary') 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $visit->visit_status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Consultation Fee:</strong></td>
                                    <td>
                                        @if($visit->consultation_fee)
                                            <strong class="text-success">${{ number_format($visit->consultation_fee, 2) }}</strong>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $visit->payment_status === 'paid' ? 'success' : 
                                            ($visit->payment_status === 'pending' ? 'warning' : 'danger') 
                                        }}">
                                            {{ ucfirst($visit->payment_status ?? 'pending') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
                <!-- End Current Visit Information Tab -->
                
                <!-- Medical History & Alerts Tab -->
                <div class="tab-pane fade" id="medical-alerts" role="tabpanel">
                    <div class="row">
                        <!-- Medical History & Alerts -->
                        <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Medical Alerts & History</h5>
                        </div>
                        <div class="card-body" id="medicalAlertsCardBody">
                            <!-- Allergies -->
                            <div class="mb-3" id="allergiesSection">
                                <h6 class="text-danger mb-1"><i class="fas fa-exclamation-circle"></i> Allergies</h6>
                                <div id="drugAllergiesDisplay" class="mb-2">
                                    <strong class="text-danger">Drug Allergies:</strong>
                                    <div class="mt-1" id="drugAllergiesList">
                                        <span class="text-muted">Loading...</span>
                                    </div>
                                </div>
                                <div id="otherAllergiesDisplay">
                                    <strong class="text-danger">Other Allergies:</strong>
                                    @if($pastMedicalHistory && $pastMedicalHistory->allergies)
                                        <div class="alert alert-danger py-2 mt-1 mb-0">
                                            <strong>⚠️</strong> {{ $pastMedicalHistory->allergies }}
                                        </div>
                                    @else
                                        <p class="text-muted mb-2 mt-1">None recorded</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Chronic Conditions -->
                            <div class="mb-3" id="chronicConditionsSection">
                                <h6 class="text-warning"><i class="fas fa-heartbeat"></i> Chronic Conditions:</h6>
                                @if($pastMedicalHistory && $pastMedicalHistory->chronic_conditions)
                                    <div class="alert alert-warning py-2">
                                        {{ $pastMedicalHistory->chronic_conditions }}
                                    </div>
                                @else
                                    <p class="text-muted mb-2">No chronic conditions recorded</p>
                                @endif
                            </div>

                            <!-- Current Medications -->
                            <div class="mb-3" id="currentMedicationsSection">
                                <h6 class="text-info"><i class="fas fa-pills"></i> Current Medications:</h6>
                                @if($pastMedicalHistory && $pastMedicalHistory->current_medications)
                                    <div class="alert alert-info py-2">
                                        {{ $pastMedicalHistory->current_medications }}
                                    </div>
                                @else
                                    <p class="text-muted mb-2">No current medications</p>
                                @endif
                            </div>

                            <!-- Previous Surgeries -->
                            <div class="mb-3" id="previousSurgeriesSection">
                                <h6 class="text-secondary"><i class="fas fa-cut"></i> Previous Surgeries:</h6>
                                @if($pastMedicalHistory && $pastMedicalHistory->previous_surgeries)
                                    <div class="alert alert-secondary py-2">
                                        {{ $pastMedicalHistory->previous_surgeries }}
                                    </div>
                                @else
                                    <p class="text-muted mb-2">No previous surgeries</p>
                                @endif
                            </div>

                            <!-- Family History -->
                            <div class="mb-3" id="familyHistorySection">
                                <h6 class="text-primary"><i class="fas fa-users"></i> Family History:</h6>
                                @if($pastMedicalHistory && $pastMedicalHistory->family_history)
                                    <div class="alert alert-light border py-2">
                                        {{ $pastMedicalHistory->family_history }}
                                    </div>
                                @else
                                    <p class="text-muted mb-2">No significant family history</p>
                                @endif
                            </div>

                            <!-- Social History -->
                            <div class="mb-3" id="socialHistorySection">
                                <h6 class="text-dark"><i class="fas fa-user-friends"></i> Social History:</h6>
                                @if($pastMedicalHistory && ($pastMedicalHistory->smoking_status || $pastMedicalHistory->alcohol_use || $pastMedicalHistory->social_history))
                                    <div class="row">
                                        @if($pastMedicalHistory->smoking_status)
                                            <div class="col-6">
                                                <small><strong>Smoking:</strong> 
                                                    <span class="badge bg-{{ $pastMedicalHistory->smoking_status === 'non_smoker' ? 'success' : 'warning' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $pastMedicalHistory->smoking_status)) }}
                                                    </span>
                                                </small>
                                            </div>
                                        @endif
                                        @if($pastMedicalHistory->alcohol_use)
                                            <div class="col-6">
                                                <small><strong>Alcohol:</strong> 
                                                    <span class="badge bg-{{ $pastMedicalHistory->alcohol_use === 'none' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($pastMedicalHistory->alcohol_use) }}
                                                    </span>
                                                </small>
                                            </div>
                                        @endif
                                        @if($pastMedicalHistory->social_history)
                                            <div class="col-12 mt-2">
                                                <small>{{ $pastMedicalHistory->social_history }}</small>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-muted mb-2">No social history recorded</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
                <!-- End Medical History & Alerts Tab -->
                
                <!-- Statistics Tab -->
                <div class="tab-pane fade" id="statistics" role="tabpanel">
                    <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Patient Visit Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-1">{{ $patient_stats['total_visits'] ?? 0 }}</h4>
                                        <small class="text-muted">Total Visits</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-success mb-1">{{ $patient_stats['last_visit'] ?? 'N/A' }}</h4>
                                        <small class="text-muted">Last Visit</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-warning mb-1">{{ $patient_stats['pending_followups'] ?? 0 }}</h4>
                                        <small class="text-muted">Pending Follow-ups</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-info mb-1">{{ $patient_stats['registration_date'] ?? 'N/A' }}</h4>
                                    <small class="text-muted">First Registration</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
                <!-- End Statistics Tab -->
            </div>
            <!-- End Profile Sub-Tabs Content -->
        </div>

        <!-- Clinical Information Tab -->
        <div id="clinical-information" class="tab-pane fade in active show">
            <h3 class="mb-4"><i class="fas fa-stethoscope text-primary"></i> Clinical Information</h3>
            
            <div class="row">
                <!-- Current Consultation -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user-injured"></i> Current Consultation</h5>
                        </div>
                        <div class="card-body">
                            <form id="consultationForm">
                                @csrf
                                <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                                
                                <div class="mb-3">
                                    <label for="history_of_present_illness" class="form-label fw-bold">
                                        <i class="fas fa-comment-dots"></i> Chief Complaints / History of Present Illness:
                                    </label>
                                    <textarea class="form-control" id="history_of_present_illness" name="history_of_present_illness" rows="4" 
                                            placeholder="Write patient presenting illness..." 
                                            style="resize: vertical;">{{ $consultation->history_of_present_illness }}</textarea>
                                </div>
                                
                                <div class="text-end">
                                    <button type="button" class="btn btn-primary" onclick="saveConsultation()" id="saveConsultationBtn">
                                        <i class="fas fa-save"></i> 
                                        <span class="btn-text">Save Consultation</span>
                                        <span class="unsaved-text d-none text-warning">• Unsaved</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Past Medical History -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Past Medical History</h5>
                            <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="openMedicalHistoryModal({id: {{ $visit->patientInfo->id }}, name: {{ json_encode($visit->patientInfo->first_name . ' ' . $visit->patientInfo->last_name) }}})">
                                <i class="fas fa-edit"></i> Manage History
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Display Current Past Medical History -->
                            <div id="medicalHistoryDisplay">
                            @if($pastMedicalHistory)
                                <div class="mb-3">
                                    <!-- Compact Full PMH Display -->
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <small>
                                                <p class="mb-1"><strong>Allergies:</strong></p>
                                                @if($drugAllergySummary || $otherAllergiesSummary)
                                                    @if($drugAllergySummary)
                                                        <p class="mb-1">
                                                            Drugs:
                                                            <span class="text-danger" title="Full drug allergy list">
                                                                {{ $drugAllergySummary }}@if($drugAllergyOverflow) +{{ $drugAllergyOverflow }} more @endif
                                                            </span>
                                                        </p>
                                                    @else
                                                        <p class="mb-1"><span class="text-muted">Drugs: None</span></p>
                                                    @endif
                                                    @if($otherAllergiesSummary)
                                                        <p class="mb-0">Other:<span class="text-danger ms-1" title="Other allergies full text"> {{ $otherAllergiesSummary }}</span></p>
                                                    @else
                                                        <p class="mb-0">Other:<span class="text-muted ms-1"> None</span></p>
                                                    @endif
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>

                                        <div class="col-6">
                                            <small><strong>Chronic Conditions:</strong>
                                                @if($pastMedicalHistory->chronic_conditions)
                                                    <span class="text-warning">{{ Str::limit($pastMedicalHistory->chronic_conditions, 80) }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small><strong>Current Medications:</strong>
                                                @if($pastMedicalHistory->current_medications)
                                                    <span class="text-info">{{ Str::limit($pastMedicalHistory->current_medications, 80) }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>

                                        <div class="col-6">
                                            <small><strong>Previous Surgeries:</strong>
                                                @if($pastMedicalHistory->previous_surgeries)
                                                    <span class="text-secondary">{{ Str::limit($pastMedicalHistory->previous_surgeries, 80) }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small><strong>Family History:</strong>
                                                @if($pastMedicalHistory->family_history)
                                                    <span class="text-dark">{{ Str::limit($pastMedicalHistory->family_history, 80) }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>

                                        <div class="col-6">
                                            <small>
                                                <strong>Smoking:</strong>
                                                <span class="badge bg-{{ $pastMedicalHistory->smoking_status === 'non_smoker' ? 'success' : 'warning' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $pastMedicalHistory->smoking_status ?? 'Unknown')) }}
                                                </span>
                                                <span class="ms-2"><strong>Alcohol:</strong>
                                                    <span class="badge bg-{{ $pastMedicalHistory->alcohol_use === 'none' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($pastMedicalHistory->alcohol_use ?? 'Unknown') }}
                                                    </span>
                                                </span>
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small><strong>Social History:</strong>
                                                @if($pastMedicalHistory->social_history)
                                                    <span>{{ Str::limit($pastMedicalHistory->social_history, 80) }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>

                                        <div class="col-6">
                                            <small><strong>Occupational History:</strong>
                                                @if($pastMedicalHistory->occupational_history)
                                                    <span>{{ Str::limit($pastMedicalHistory->occupational_history, 80) }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small><strong>Immunizations:</strong>
                                                @if($pastMedicalHistory->immunization_history)
                                                    <span>{{ Str::limit($pastMedicalHistory->immunization_history, 80) }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>

                                        <div class="col-12">
                                            <small><strong>Reproductive History:</strong>
                                                @if($pastMedicalHistory->reproductive_history)
                                                    <span>{{ Str::limit($pastMedicalHistory->reproductive_history, 120) }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> No past medical history recorded for this patient.
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Examinations Tab -->
        <div id="examinations" class="tab-pane fade">
            <h3 class="mb-4"><i class="fas fa-heartbeat text-primary"></i> Examinations</h3>
            
            <div class="row">
                <!-- Vitals Overview & Link -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-thermometer-half"></i> Vital Signs Overview</h5>
                        </div>
                        <div class="card-body">
                            @if($vitals)
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Temperature:</strong></td>
                                            <td><span id="quick_temperature">{{ $vitals->temperature }}</span> °C</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Blood Pressure:</strong></td>
                                            <td><span id="quick_bp">{{ $vitals->systolic_bp }}/{{ $vitals->diastolic_bp }}</span> mmHg</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pulse Rate:</strong></td>
                                            <td><span id="quick_pulse">{{ $vitals->pulse_rate }}</span> bpm</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Resp. Rate:</strong></td>
                                            <td><span id="quick_respiratory">{{ $vitals->respiratory_rate }}</span>/min</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Oxygen Saturation:</strong></td>
                                            <td><span id="quick_spo2">{{ $vitals->oxygen_saturation }}</span> %</td>
                                        </tr>
                                        @if($vitals->bmi)
                                        <tr>
                                            <td><strong>BMI:</strong></td>
                                            <td><span id="quick_bmi">{{ $vitals->bmi }}</span></td>
                                        </tr>
                                        @endif
                                    </table>
                                    <small class="text-muted" id="quick_created_at">
                                        Recorded: {{ $vitals->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-info btn-sm" 
                                            onclick="openVitalsModal({id: {{ $visit->id }}}, {id: {{ $visit->patientInfo->id }}, name: {{ json_encode($visit->patientInfo->first_name . ' ' . $visit->patientInfo->last_name) }}})">
                                        <i class="fas fa-chart-line"></i> View Full Vitals & Record
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#quickVitalsForm">
                                        <i class="fas fa-edit"></i> Quick Update Vitals
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No vital signs recorded yet.
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-info btn-sm"
                                            onclick="openVitalsModal({id: {{ $visit->id }}}, {id: {{ $visit->patientInfo->id }}, name: {{ json_encode($visit->patientInfo->first_name . ' ' . $visit->patientInfo->last_name) }}})">
                                        <i class="fas fa-plus"></i> Record Initial Vitals
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#quickVitalsForm">
                                        <i class="fas fa-plus"></i> Quick Add Vitals
                                    </button>
                                </div>
                            @endif
                            
                            <!-- Quick Vitals Form -->
                            <div class="collapse mt-3" id="quickVitalsForm">
                                <div class="border-top pt-3">
                                    <form id="quickVitalsFormElement">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Temp (°C)</label>
                                                <input type="text" step="0.1" class="form-control form-control-sm" name="temperature" value="{{ $vitals->temperature ?? '' }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Pulse (bpm)</label>
                                                <input type="text" class="form-control form-control-sm" name="pulse_rate" value="{{ $vitals->pulse_rate ?? '' }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">O2%</label>
                                                <input type="text" class="form-control form-control-sm" name="oxygen_saturation" value="{{ $vitals->oxygen_saturation ?? '' }}">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Systolic BP</label>
                                                <input type="text" class="form-control form-control-sm" name="systolic_bp" value="{{ $vitals->systolic_bp ?? '' }}">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Diastolic BP</label>
                                                <input type="text" class="form-control form-control-sm" name="diastolic_bp" value="{{ $vitals->diastolic_bp ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="text-end mt-2">
                                            <button type="button" class="btn btn-sm btn-secondary me-1" data-bs-toggle="collapse" data-bs-target="#quickVitalsForm">
                                                Cancel
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" onclick="saveQuickVitals()" id="saveQuickVitalsBtn">
                                                <i class="fas fa-save"></i> 
                                                <span class="btn-text">Save</span>
                                                <span class="unsaved-text d-none text-warning">• Unsaved</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Systemic Examination Section -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-user-md"></i> Systemic Examination</h5>
                        </div>
                        <div class="card-body">
                            <!-- Existing Examinations Display -->
                            <div id="examinationsList">
                                @if($examinations->count() > 0)
                                    <div class="mb-3">
                                        @foreach($examinations as $exam)
                                        <div class="card mb-2">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">{{ $exam->examination_type ?? 'Systemic Examination' }}</h6>
                                                    <small class="text-muted">{{ $exam->created_at->format('d/m/Y H:i') }}</small>
                                                </div>
                                                
                                                @if($exam->general_findings)
                                                <div class="mb-2">
                                                    <strong>General Findings:</strong>
                                                    <p class="mb-1">{{ $exam->general_findings }}</p>
                                                </div>
                                                @endif
                                                
                                                <div class="row">
                                                    @if($exam->cardiovascular_system)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Cardiovascular:</strong> {{ $exam->cardiovascular_system }}
                                                    </div>
                                                    @endif
                                                    @if($exam->respiratory_system)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Respiratory:</strong> {{ $exam->respiratory_system }}
                                                    </div>
                                                    @endif
                                                    @if($exam->gastrointestinal_system)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>GI System:</strong> {{ $exam->gastrointestinal_system }}
                                                    </div>
                                                    @endif
                                                    @if($exam->nervous_system)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>CNS:</strong> {{ $exam->nervous_system }}
                                                    </div>
                                                    @endif
                                                    @if($exam->musculoskeletal_system)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Musculoskeletal:</strong> {{ $exam->musculoskeletal_system }}
                                                    </div>
                                                    @endif
                                                    @if($exam->genitourinary_system)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>GU System:</strong> {{ $exam->genitourinary_system }}
                                                    </div>
                                                    @endif
                                                    @if($exam->skin_examination)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Skin:</strong> {{ $exam->skin_examination }}
                                                    </div>
                                                    @endif
                                                    @if($exam->psychiatric_assessment)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Psychiatric:</strong> {{ $exam->psychiatric_assessment }}
                                                    </div>
                                                    @endif
                                                </div>
                                                
                                                @if($exam->notes)
                                                <div class="mt-2">
                                                    <strong>Additional Notes:</strong>
                                                    <p class="mb-0 text-muted">{{ $exam->notes }}</p>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No systemic examinations recorded yet.
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Modal Button -->
                            <button type="button" class="btn btn-success" 
                                    onclick="openSystemicExaminationModal({id: {{ $visit->id }}}, {{ json_encode($visit->patientInfo->first_name . ' ' . $visit->patientInfo->last_name) }}, 'consultation', {{ $consultation->id }})">
                                <i class="fas fa-plus"></i> Add/Manage Systemic Examinations
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diagnosis Tab -->
<!-- Diagnosis Tab -->
<div id="diagnosis" class="tab-pane fade">
    <h3 class="mb-4"><i class="fas fa-diagnoses text-primary"></i> Diagnosis</h3>
    
    <div class="card">
        <div class="card-body">
            <form id="diagnosisForm">
                @csrf
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="provisional_diagnosis" class="form-label fw-bold">
                                <i class="fas fa-clipboard-check"></i> Provisional Diagnosis:
                            </label>
                            <textarea class="form-control" id="provisional_diagnosis" name="provisional_diagnosis" rows="4" 
                                    placeholder="Enter provisional diagnosis..." 
                                    style="resize: vertical;" required>{{ $consultation->provisional_diagnosis ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="final_diagnosis" class="form-label fw-bold">
                                <i class="fas fa-diagnoses"></i> Final Diagnosis:
                            </label>
                            <textarea class="form-control" id="final_diagnosis" name="final_diagnosis" rows="4" 
                                    placeholder="Enter final diagnosis..." 
                                    style="resize: vertical;">{{ $consultation->final_diagnosis ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
                
                <!-- ICD-10 Code Selection Section -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-code"></i> ICD-10 Code Selection</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="icd_type" class="form-label">ICD Type</label>
                                    <select id="icd_type" class="form-control">
                                        <option value="provisional">Provisional</option>
                                        <option value="final">Final</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="icd10_code" class="form-label">ICD 10 Code</label>
                                    <input type="text" class="form-control" id="icd10_code" name="icd10_code" 
                                           placeholder="Type ICD-10 code (e.g., J45.9)" autocomplete="off">
                                    <small class="text-muted">Type at least 2 characters</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="icd10_description" class="form-label">ICD 10 Description</label>
                                    <input type="text" class="form-control" id="icd10_description" name="icd10_description" 
                                           placeholder="Type ICD-10 description (e.g., Asthma)" autocomplete="off">
                                    <small class="text-muted">Type at least 3 characters</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-success" onclick="addIcdDiagnosis()" id="addIcdBtn" disabled>
                                <i class="fas fa-plus"></i> Add ICD Diagnosis
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ICD Diagnoses List -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-list"></i> ICD-10 Diagnoses</h6>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="syncAllIcdToText()" title="Sync all ICD diagnoses to text">
                                        <i class="fas fa-sync"></i> Sync All to Text
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="icd_diagnoses_list">
                                    @if(isset($icd_diagnoses) && $icd_diagnoses->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>ICD Code</th>
                                                        <th>Description</th>
                                                        <th>Category</th>
                                                        <th>Date Added</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($icd_diagnoses as $icd)
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-{{ $icd->type === 'provisional' ? 'warning' : 'success' }}">
                                                                {{ ucfirst($icd->type) }}
                                                            </span>
                                                        </td>
                                                        <td><strong class="text-primary">{{ $icd->icd_code }}</strong></td>
                                                        <td>{{ $icd->description }}</td>
                                                        <td>
                                                            <small class="text-muted">{{ $icd->category }}</small>
                                                            @if($icd->subcategory)
                                                                <br><small class="text-info">{{ $icd->subcategory }}</small>
                                                            @endif
                                                        </td>
                                                        <td><small>{{ $icd->created_at->format('d/m/Y') }}</small></td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="removeIcdDiagnosis({{ $icd->id }})"
                                                                    title="Remove this diagnosis">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">No ICD-10 diagnoses added yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="button" class="btn btn-primary" onclick="saveDiagnosis()" id="saveDiagnosisBtn">
                        <i class="fas fa-save"></i> 
                        <span class="btn-text">Save Diagnosis</span>
                        <span class="unsaved-text d-none text-warning">• Unsaved</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Investigations/Procedures Tab -->
        <div id="investigations" class="tab-pane fade">
            <h3 class="mb-4"><i class="fas fa-flask text-primary"></i> Investigations & Procedures</h3>
            
            <div class="row">
                <!-- Investigations Section -->
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-microscope"></i> Investigations</h5>
                        </div>
                        <div class="card-body">
                            <div id="investigations-table-container">
                                @if($investigations->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Service</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($investigations as $investigation)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $investigation->medicalService->name ?? 'N/A' }}</strong><br>
                                                        <small class="text-muted">Code: {{ $investigation->medicalService->code ?? '' }}</small>
                                                    </td>
                                                    <td>{{ $investigation->quantity }}</td>
                                                    <td>
                                                        @if($investigation->unit_price > 0)
                                                            <span class="text-success">TSh {{ number_format($investigation->unit_price, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">Not set</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($investigation->total_price > 0)
                                                            <strong class="text-primary">TSh {{ number_format($investigation->total_price, 2) }}</strong>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $investigation->status === 'completed' ? 'success' : 
                                                            ($investigation->status === 'in_progress' ? 'warning' : 'secondary') 
                                                        }}">
                                                            {{ ucfirst(str_replace('_', ' ', $investigation->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $investigation->created_at->format('d/m/Y H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="viewInvestigation({{ $investigation->id }})">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        @if(!$investigation->is_paid)
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="removeInvestigation({{ $investigation->id }})">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            @if($investigations->count() > 0)
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th colspan="3" class="text-end">Total Investigations Cost:</th>
                                                    <th class="text-primary">
                                                        @php
                                                            $totalCost = $investigations->sum('total_price');
                                                        @endphp
                                                        TSh {{ number_format($totalCost, 2) }}
                                                    </th>
                                                    <th colspan="3"></th>
                                                </tr>
                                            </tfoot>
                                            @endif
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No investigations ordered yet.</p>
                                @endif
                            </div>
                            
                            <!-- Investigation Button - Opens Reusable Modal -->
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                    onclick="openLabModal({{ $visit->patient }}, {{ $visit->id }}, {{ json_encode(($visit->patientInfo->first_name ?? '') . ' ' . ($visit->patientInfo->middle_name ?? '') . ' ' . ($visit->patientInfo->last_name ?? '')) }}, 'consultation')">
                                <i class="fas fa-plus"></i> Order Investigation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Tab -->
        <div id="treatment" class="tab-pane fade">
            <h3 class="mb-4"><i class="fas fa-prescription text-primary"></i> Treatment & Prescriptions</h3>
            
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Prescriptions Section -->
                        <div class="col-md-8">
                            <h5><i class="fas fa-pills"></i> Prescriptions</h5>
                            
                            <div id="prescriptions-list">
                                @if($prescriptions->count() > 0)
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Medicine</th>
                                                    <th>Dosage</th>
                                                    <th>Frequency</th>
                                                    <th>Duration</th>
                                                    <th>Qty</th>
                                                    <th>Unit Price</th>
                                                    <th>Total</th>
                                                    <th>Route</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($prescriptions as $prescription)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $prescription->medication->generic_name ?? $prescription->medication->name ?? 'N/A' }}</strong>
                                                        @if($prescription->medication && $prescription->medication->brand_name)
                                                            <br><small class="text-muted">{{ $prescription->medication->brand_name }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $prescription->dosage }}</td>
                                                    <td>{{ $prescription->frequency->frequency_name ?? 'N/A' }}</td>
                                                    <td>{{ $prescription->duration_days ?? $prescription->duration }} days</td>
                                                    <td>{{ $prescription->quantity }}</td>
                                                    <td>${{ number_format($prescription->unit_price ?? 0, 2) }}</td>
                                                    <td class="fw-bold text-success">${{ number_format($prescription->total_price ?? 0, 2) }}</td>
                                                    <td>{{ $prescription->administrationRoute->route_name ?? 'PO' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $prescription->status === 'dispensed' ? 'success' : 
                                                            ($prescription->status === 'prescribed' ? 'primary' : 'secondary') 
                                                        }}">
                                                            {{ ucfirst($prescription->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if(!$prescription->is_paid)
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                                    onclick="updatePrescriptionStatus({{ $prescription->id }})"
                                                                    title="Update Status">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="deletePrescription({{ $prescription->id }})">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-3">No prescriptions added yet.</p>
                                @endif
                            </div>
                            
                            <!-- Prescription Button - Opens Reusable Modal -->
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="openPrescriptionModal({{ $visit->patient }}, {{ $visit->id }}, {{ json_encode(($visit->patientInfo->first_name ?? '') . ' ' . ($visit->patientInfo->middle_name ?? '') . ' ' . ($visit->patientInfo->last_name ?? '')) }}, 'consultation')">
                                <i class="fas fa-plus"></i> Add Prescription
                            </button>
                        </div>

                        <!-- Clinical Decision Support Alerts -->
                        <div class="col-md-4">
                            <div class="card" id="cds-alerts-card">
                                <div class="card-header text-white" style="background-color: #28a745;" id="cds-alerts-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Clinical Decision Support
                                        <span id="cds-alert-count-badge" class="badge bg-light text-dark ms-2">
                                            {{ $cdsAlerts->count() }}
                                        </span>
                                    </h6>
                                </div>
                                <div class="card-body" id="cds-alerts-body">
                                    @if($cdsAlerts && $cdsAlerts->count() > 0)
                                        <div id="cds-alerts-list">
                                            @foreach($cdsAlerts as $alert)
                                            <div class="alert alert-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'high' ? 'warning' : 'info') }} alert-sm mb-2 p-2" data-alert-id="{{ $alert->id }}">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold small">{{ ucfirst($alert->severity) }} Alert</div>
                                                        <div class="small mb-1">{{ $alert->message }}</div>
                                                        @if($alert->rationale)
                                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                                {{ Str::limit($alert->rationale, 100) }}
                                                            </div>
                                                        @endif
                                                        <div class="mt-2">
                                                            <button class="btn btn-sm btn-outline-success me-1" onclick="ackCdsAlert({{ $alert->id }}, 'accept')" title="Accept Alert">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-warning me-1" onclick="ackCdsAlertWithReason({{ $alert->id }}, 'override')" title="Override with Reason">
                                                                <i class="fas fa-exclamation"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-secondary" onclick="ackCdsAlert({{ $alert->id }}, 'dismiss')" title="Dismiss">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div id="no-alerts-message" class="text-center text-muted py-3">
                                            <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                            <div>No clinical alerts</div>
                                            <small>System monitoring for safety issues</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remarks Tab -->
        <div id="remarks" class="tab-pane fade">
            <h3 class="mb-4"><i class="fas fa-sticky-note text-primary"></i> Remarks & Follow-up</h3>
            
            <div class="card">
                <div class="card-body">
                    <form id="remarksForm">
                        @csrf
                        <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="csremarks" class="form-label fw-bold">
                                        <i class="fas fa-comment-medical"></i> Clinical Remarks:
                                    </label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="6" 
                                            placeholder="Additional clinical remarks and observations..." 
                                            style="resize: vertical;">{{ $consultation->remarks }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="followup_date" class="form-label fw-bold">
                                        <i class="fas fa-calendar-check"></i> Follow-up Date:
                                    </label>
                                    <input type="date" class="form-control" id="followup_date" name="followup_date" 
                                           value="{{ $consultation->followup_date ? \Carbon\Carbon::parse($consultation->followup_date)->format('Y-m-d') : '' }}">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="followup_instructions" class="form-label fw-bold">
                                        <i class="fas fa-clipboard-list"></i> Follow-up Instructions:
                                    </label>
                                    <textarea class="form-control" id="followup_instructions" name="followup_instructions" rows="4" 
                                            placeholder="Instructions for next visit..." 
                                            style="resize: vertical;">{{ $consultation->followup_instructions }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-primary" onclick="saveRemarks()" id="saveRemarksBtn">
                                <i class="fas fa-save"></i> 
                                <span class="btn-text">Save Remarks</span>
                                <span class="unsaved-text d-none text-warning">• Unsaved</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Tab -->
        <div id="results" class="tab-pane fade">
            <h3 class="mb-4"><i class="fas fa-chart-line text-primary"></i> Test Results & Reports</h3>
            
            <div class="row">
                <!-- Test Results Section -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if(isset($testResults) && $testResults->count() > 0)
                                <div class="results-list">
                                    @foreach($testResults as $result)
                                    <div class="border p-3 mb-3 rounded bg-light">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                @if($result->is_simple && isset($result->form_data['parameters']))
                                                    {{-- Display simple results directly --}}
                                                    <div class="table-responsive mt-2">
                                                        <table class="table table-sm table-borderless">
                                                            <thead>
                                                                <tr class="text-muted" style="font-size: 0.85em;">
                                                                    <th></th>
                                                                    <th>Value</th>
                                                                    <th>Unit</th>
                                                                    <th>Normal Range</th>
                                                                    <th>Status</th>
                                                                    <th class="text-muted">Reported</th>
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

                                                                    // Small helpers for parsing values and ranges
                                                                    $toFloat = function ($val) {
                                                                        if ($val === null || $val === '') return null;
                                                                        if (is_numeric($val)) return (float)$val;
                                                                        // Extract first numeric (handles strings like "<5", ">= 3.2", "5 mg/dL")
                                                                        if (preg_match('/-?\d+(?:[\.,]\d+)?/', (string)$val, $m)) {
                                                                            return (float) str_replace(',', '.', $m[0]);
                                                                        }
                                                                        return null;
                                                                    };

                                                                    $computeStatusFromRange = function ($valueRaw, $rangeRaw) use ($toFloat) {
                                                                        $val = $toFloat($valueRaw);
                                                                        if ($val === null || !$rangeRaw) return null;
                                                                        $r = trim((string)$rangeRaw);
                                                                        // Normalize unicode dashes to hyphen
                                                                        $r = str_replace(["–", "—", "−"], "-", $r);
                                                                        // Common patterns: "a-b" or "a to b"
                                                                        if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*(?:-|to)\s*(-?\d+(?:\.\d+)?)\s*$/i', $r, $mm)) {
                                                                            $lo = (float)$mm[1];
                                                                            $hi = (float)$mm[2];
                                                                            if ($val < $lo) return 'low';
                                                                            if ($val > $hi) return 'high';
                                                                            return 'normal';
                                                                        }
                                                                        // Comparator patterns: "< x", "<= x", "> x", ">= x"
                                                                        if (preg_match('/^\s*([<>]=?)\s*(-?\d+(?:\.\d+)?)\s*$/', $r, $mm)) {
                                                                            $op = $mm[1];
                                                                            $cut = (float)$mm[2];
                                                                            if ($op === '<')  return $val <  $cut ? 'normal' : 'high';
                                                                            if ($op === '<=') return $val <= $cut ? 'normal' : 'high';
                                                                            if ($op === '>')  return $val >  $cut ? 'normal' : 'low';
                                                                            if ($op === '>=') return $val >= $cut ? 'normal' : 'low';
                                                                        }
                                                                        // Fallback: cannot determine
                                                                        return null;
                                                                    };
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

                                                                        // Support both schema variants: parameter_name vs parameter
                                                                        $pname = $param['parameter_name'] ?? ($param['parameter'] ?? 'N/A');
                                                                        $pvalue = $param['value'] ?? null;
                                                                        $punit = $param['unit'] ?? '';
                                                                        $prange = $param['normal_range'] ?? '';
                                                                        // Prefer existing status if provided, else compute from range
                                                                        $computed = $param['status'] ?? null;
                                                                        if (!$computed) {
                                                                            $computed = $computeStatusFromRange($pvalue, $prange) ?? 'unknown';
                                                                        }
                                                                        $status = $computed;
                                                                        $badgeClass = match($status) {
                                                                            'high' => 'bg-danger',
                                                                            'low' => 'bg-warning',
                                                                            'normal' => 'bg-success',
                                                                            'critical' => 'bg-danger',
                                                                            default => 'bg-secondary'
                                                                        };
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="fw-medium">
                                                                            {{ $pname }}
                                                                            <span class="badge bg-{{ $result->form_status === 'final' ? 'success' : ($result->form_status === 'preliminary' ? 'warning' : 'secondary') }} ms-1" style="font-size:0.7em;">{{ ucfirst($result->form_status) }}</span>
                                                                        </td>
                                                                        <td>{{ $pvalue ?? 'N/A' }}</td>
                                                                        <td class="text-muted">{{ $punit }}</td>
                                                                        <td class="text-muted">{{ $prange }}</td>
                                                                        <td><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
                                                                        <td class="text-muted" style="font-size:0.85em;white-space:nowrap;">
                                                                            {{ $result->reported_at->format('d/m/Y H:i') }}
                                                                            @if($result->reported_by)<br>{{ $result->reported_by }}@endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                    @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
                                                        <div class="mt-2">
                                                            <strong class="text-muted">Comments:</strong>
                                                            <p class="mb-0">{{ $result->form_data['additional_comments'] }}</p>
                                                        </div>
                                                    @endif
                                                @elseif($result->is_manual)
                                                    {{-- Display manual results --}}
                                                    <div class="mt-2">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <strong>Result:</strong> {{ $result->form_data['result_value'] ?? 'N/A' }}
                                                            </div>
                                                            @if(isset($result->form_data['reference_range']) && $result->form_data['reference_range'])
                                                            <div class="col-md-6">
                                                                <strong>Reference Range:</strong> {{ $result->form_data['reference_range'] }}
                                                            </div>
                                                            @endif
                                                        </div>
                                                        
                                                        @if(isset($result->form_data['interpretation']) && $result->form_data['interpretation'])
                                                        <div class="mt-2">
                                                            <strong>Interpretation:</strong>
                                                            <p class="mb-1">{{ $result->form_data['interpretation'] }}</p>
                                                        </div>
                                                        @endif
                                                        
                                                        <div class="mt-2">
                                                            <span class="badge bg-{{ isset($result->form_data['is_abnormal']) && $result->form_data['is_abnormal'] ? 'warning' : 'success' }}">
                                                                {{ isset($result->form_data['is_abnormal']) && $result->form_data['is_abnormal'] ? 'Abnormal' : 'Normal' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- Generic fallback: show key fields --}}
                                                    @foreach($result->form_data as $key => $value)
                                                        @if(!in_array($key, ['_token', 'action']) && !empty($value) && !is_array($value))
                                                            <div class="small"><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                                

                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No test results available yet.</p>
                            @endif
                            
                            <!-- Test Result Form removed: test results are managed in the Lab module. -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
                <div class="card-body text-center">
                    <button type="button" class="btn btn-secondary me-2" id="saveAllBtn" onclick="saveAll(false)">
                        <i class="fas fa-save"></i> Save All
                    </button>
                    <button type="button" class="btn btn-outline-secondary me-2" id="saveAndBackBtn" onclick="saveAll(true)">
                        <i class="fas fa-save"></i> Save & Back
                    </button>
                    <a id="backToVisitsBtn" href="{{ route('patient_visits.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Visits
                    </a>
                <button type="button" class="btn btn-success" onclick="dischargePatient()">
                    <i class="fas fa-user-check"></i> Discharge Patient
                </button>
                <button type="button" class="btn btn-primary" onclick="printConsultation()">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Investigation Details Modal -->
<div class="modal fade" id="investigationDetailsModal" tabindex="-1" role="dialog" aria-labelledby="investigationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="investigationDetailsModalLabel">
                    <i class="fas fa-flask"></i> Investigation Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="investigationDetailsContent" style="max-height: 70vh; overflow-y: auto;">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewFullInvestigation" class="btn btn-primary" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Full Details
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Complex Results Modal -->
<div class="modal fade" id="complexResultsModal" tabindex="-1" role="dialog" aria-labelledby="complexResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="complexResultsModalLabel">
                    <i class="fas fa-chart-line"></i> Investigation Results
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="complexResultsContent" style="max-height: 70vh; overflow-y: auto;">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="printComplexResult" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Results
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Edit Prescription Modal -->
<div class="modal fade" id="editPrescriptionModal" tabindex="-1" role="dialog" aria-labelledby="editPrescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="editPrescriptionModalContent">
            <!-- Modal content will be loaded here -->
        </div>
    </div>
</div>

<!-- Lab Investigation Modal Component -->
@include('partials.lab_investigation_modal')

<!-- Prescription Modal Component -->
@include('partials.prescription_modal')

<!-- Past Medical History Modal Component -->
@include('partials.past_medical_history_modal')

<!-- Vitals Modal Component -->
@include('partials.vitals_modal')

<!-- Systemic Examination Modal Component -->
@include('partials.systemic_examination_modal')

@endsection

@section('scripts')
<!-- Consultation Module Scripts -->
<script src="{{ asset('js/consultation/change-tracking.js') }}"></script>
<script src="{{ asset('js/consultation/form-saves.js') }}"></script>
<script src="{{ asset('js/consultation/examinations.js') }}"></script>
<script src="{{ asset('js/consultation/icd10.js') }}"></script>
<script src="{{ asset('js/lab-investigation-modal.js') }}"></script>
<script src="{{ asset('js/prescription-modal.js') }}"></script>
<script src="{{ asset('js/medical-history-modal.js') }}"></script>
<script src="{{ asset('js/vitals-modal.js') }}"></script>
<script src="{{ asset('js/systemic-examination-modal.js') }}"></script>
<script src="{{ asset('js/consultation/app.js') }}"></script>
<script>
    // Set global consultation ID for use in modules
    window.consultationId = {{ $consultation->id }};
    
    // Load investigations table - called after saving investigations from modal
    window.loadInvestigations = function() {
        $.ajax({
            url: `/consultations/${window.consultationId}/investigations-partial`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#investigations-table-container').html(response.html);
                } else {
                    toastr.error('Failed to load investigations');
                }
            },
            error: function(xhr) {
                console.error('Failed to load investigations:', xhr);
                toastr.error('Failed to refresh investigations list');
            }
        });
    };
    
    // Load prescriptions table - called after saving prescriptions from modal
    window.loadPrescriptions = function() {
        $.ajax({
            url: `/consultations/${window.consultationId}/prescriptions-partial`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#prescriptions-list').html(response.html);
                } else {
                    toastr.error('Failed to load prescriptions');
                }
            },
            error: function(xhr) {
                console.error('Failed to load prescriptions:', xhr);
                toastr.error('Failed to refresh prescriptions list');
            }
        });
    };
    
    // View investigation details
    window.viewInvestigation = function(investigationId) {
        // This function can be implemented to show investigation details/results in a modal
        toastr.info('View investigation details - ID: ' + investigationId);
        // TODO: Implement investigation details view
    };
    
    // Remove investigation
    window.removeInvestigation = function(investigationId) {
        if (!confirm('Are you sure you want to remove this investigation?')) {
            return;
        }
        
        $.ajax({
            url: `/consultations/investigations/${investigationId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success('Investigation removed successfully');
                
                // Refresh the main page investigations table
                loadInvestigations();
                
                // Also refresh the modal's investigations list if it's open
                const modalVisitId = $('#modal_visit_id').val();
                if (modalVisitId && typeof window.loadExistingInvestigations === 'function') {
                    window.loadExistingInvestigations(modalVisitId, 'consultation');
                }
                
                if (typeof markFormAsSaved === 'function') {
                    markFormAsSaved();
                }
            },
            error: function(xhr) {
                console.error('Failed to remove investigation:', xhr);
                toastr.error(xhr.responseJSON?.message || 'Failed to remove investigation');
            }
        });
    };
    
    // Flag used to suppress the browser beforeunload prompt while we perform programmatic saves/navigation
    window._suppressBeforeUnload = false;
    // Backup holder for any existing beforeunload handler; use setSuppressBeforeUnload(true) to
    // temporarily clear window.onbeforeunload and prevent other handlers from triggering.
    window._beforeUnloadBackup = null;
    function setSuppressBeforeUnload(val) {
        if (val) {
            if (!window._suppressBeforeUnload) {
                try {
                    window._beforeUnloadBackup = window.onbeforeunload || null;
                } catch (e) {
                    window._beforeUnloadBackup = null;
                }
                try { window.onbeforeunload = null; } catch (e) {}
                window._suppressBeforeUnload = true;
            }
        } else {
            if (window._suppressBeforeUnload) {
                window._suppressBeforeUnload = false;
                try {
                    window.onbeforeunload = window._beforeUnloadBackup;
                } catch (e) {
                    // ignore
                }
                window._beforeUnloadBackup = null;
            }
        }
    }
    // Overlay removed: make show/hide no-ops so save flows proceed without a blocking overlay
    function showSaveOverlay(message = 'Saving, please wait...') {
        // intentionally no-op while overlay is disabled
        return;
    }
    function hideSaveOverlay() {
        // intentionally no-op while overlay is disabled
        return;
    }

    // =====================
    // Drug Allergy Capture
    // =====================
    // Modal HTML (injected if not present)
    if(!document.getElementById('drugAllergyModal')) {
        const modalHtml = `
        <div class="modal fade" id="drugAllergyModal" tabindex="-1" aria-labelledby="drugAllergyModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="drugAllergyModalLabel"><i class='fas fa-pills'></i> Add Drug Allergy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-2"><strong>Drug:</strong> <span id="modalDrugName" class="text-danger"></span></div>
                <div class="mb-3">
                    <label class="form-label">Reaction (optional)</label>
                    <input type="text" id="modalReaction" class="form-control" placeholder="e.g. rash, anaphylaxis">
                </div>
                <div class="mb-3">
                    <label class="form-label">Severity (optional)</label>
                    <select id="modalSeverity" class="form-select">
                        <option value="">-- Select --</option>
                        <option value="mild">Mild</option>
                        <option value="moderate">Moderate</option>
                        <option value="severe">Severe</option>
                    </select>
                </div>
                <small class="text-muted">Leave blank if uncertain; you can update later.</small>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveDrugAllergyModalBtn" class="btn btn-danger">
                    <span class="btn-text">Save Allergy</span>
                </button>
              </div>
            </div>
          </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    (function(){
        const selectEl = document.getElementById('drugAllergiesSelect');
        const addBtn = document.getElementById('addDrugAllergyBtn');
        const tagsWrap = document.getElementById('drugAllergyTags');
        const hiddenInput = document.getElementById('drugAllergiesInput');
        const modalEl = document.getElementById('drugAllergyModal');
        const modalDrugName = document.getElementById('modalDrugName');
        const modalReaction = document.getElementById('modalReaction');
        const modalSeverity = document.getElementById('modalSeverity');
        const saveModalBtn = document.getElementById('saveDrugAllergyModalBtn');
        if(!selectEl || !addBtn || !tagsWrap || !hiddenInput) return;

        const bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;
        let pendingDrug = null;
        let allergies = []; // objects {id, substance_name, reaction, severity, is_active}

        function activeAllergies(){
            const act = allergies.filter(a => a.is_active !== false); // treat missing/1/true as active
            console.log('[Allergies] Active list computed:', act);
            return act;
        }

        function renderTags() {
            console.log('[Allergies] Rendering tags. Full allergies array:', allergies);
            const actives = activeAllergies();
            tagsWrap.innerHTML = actives.map((a, idx) => `
                <span class="badge bg-danger me-1 mb-1 drug-allergy-tag" data-edit-id="${a.id}" style="cursor:pointer;font-size:0.8rem;" title="Click to edit | ${a.reaction || 'No reaction specified'}${a.severity ? ' | ' + a.severity : ''}">
                    <span class="me-1">${a.substance_name}</span>
                    ${a.severity ? `<span class='badge bg-light text-dark me-1'>${a.severity.charAt(0).toUpperCase()+a.severity.slice(1)}</span>` : ''}
                    <button type="button" class="btn btn-sm btn-link text-white p-0" data-id="${a.id}" data-action="remove" style="line-height:1;">&times;</button>
                </span>
            `).join('');
            hiddenInput.value = actives.map(a => a.substance_name).join(',');

            // Update display list
            const listContainer = document.getElementById('drugAllergiesList');
            if(listContainer) {
                if(actives.length === 0) {
                    listContainer.innerHTML = '<span class="text-muted">None recorded</span>';
                } else {
                    listContainer.innerHTML = actives.map(a => `<span class=\"badge bg-danger me-1 mb-1\" title=\"${a.reaction || 'No reaction'}${a.severity ? ' | '+a.severity : ''}\">${a.substance_name}</span>`).join('');
                }
            }
        }

        // Tag click handlers (edit or remove)
        tagsWrap.addEventListener('click', function(e){
            const removeBtn = e.target.closest('button[data-action="remove"][data-id]');
            if(removeBtn) {
                const id = removeBtn.getAttribute('data-id');
                const allergy = allergies.find(a => a.id == id);
                if(!allergy) return;
                if(!confirm(`Deactivate allergy: ${allergy.substance_name}?`)) return;
                $.post(`/allergies/${id}/deactivate`, {_token: '{{ csrf_token() }}'}).done(resp => {
                    allergy.is_active = false;
                    renderTags();
                    toastr.info('Allergy deleted');
                }).fail(xhr => {
                    toastr.error('Failed to delete allergy');
                });
                return;
            }
            const tag = e.target.closest('.drug-allergy-tag[data-edit-id]');
            if(tag) {
                const id = tag.getAttribute('data-edit-id');
                const allergy = allergies.find(a => a.id == id);
                if(!allergy) return;
                pendingDrug = allergy.substance_name; // keep name for update
                modalDrugName.textContent = allergy.substance_name + ' (edit)';
                modalReaction.value = allergy.reaction || '';
                modalSeverity.value = allergy.severity || '';
                saveModalBtn.setAttribute('data-update-id', allergy.id);
                if(bsModal) bsModal.show();
            }
        });

        addBtn.addEventListener('click', function(){
            const val = selectEl.value?.trim();
            if(!val) return;
            // duplicate check (active)
            if(activeAllergies().some(a => a.substance_name.toLowerCase() === val.toLowerCase())) {
                toastr.warning('Drug allergy already recorded.');
                return;
            }
            pendingDrug = val;
            modalDrugName.textContent = val;
            modalReaction.value = '';
            modalSeverity.value = '';
            if(bsModal) bsModal.show();
        });

        saveModalBtn.addEventListener('click', function(){
            if(!pendingDrug) return;
            // If severity severe ensure reaction not empty (client-side guard)
            if(modalSeverity.value === 'severe' && !modalReaction.value.trim()) {
                toastr.error('Reaction is required for severe allergies.');
                return;
            }
            saveModalBtn.disabled = true;
            const btnText = saveModalBtn.querySelector('.btn-text');
            const origText = btnText.textContent;
            btnText.textContent = 'Saving...';
            const patientId = {{ $visit->patientInfo->id }};
            const updateId = saveModalBtn.getAttribute('data-update-id');
            const payload = {
                _token: '{{ csrf_token() }}',
                substance_name: pendingDrug,
                reaction: modalReaction.value.trim(),
                severity: modalSeverity.value
            };
            let ajaxOpts;
            if(updateId) {
                ajaxOpts = { url: `/allergies/${updateId}`, method: 'PUT', data: payload };
            } else {
                ajaxOpts = { url: `/patients/${patientId}/allergies`, method: 'POST', data: payload };
            }
            $.ajax(ajaxOpts).done(resp => {
                if(resp && resp.data) {
                    if(updateId) {
                        const idx = allergies.findIndex(a => a.id == updateId);
                        if(idx !== -1) allergies[idx] = resp.data; else allergies.unshift(resp.data);
                        toastr.success('Allergy updated');
                    } else {
                        allergies.unshift(resp.data);
                        toastr.success('Drug allergy added');
                    }
                    renderTags();
                    // Refetch from server to normalize (casts etc.)
                    setTimeout(() => { if(window.fetchDrugAllergiesList) window.fetchDrugAllergiesList(); }, 300);
                }
                if(bsModal) bsModal.hide();
            }).fail(xhr => {
                let msg = 'Failed to save allergy';
                if(xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                else if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                else if(xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                toastr.error(msg);
            }).always(() => {
                saveModalBtn.disabled = false;
                btnText.textContent = origText;
                pendingDrug = null;
                selectEl.value = '';
                saveModalBtn.removeAttribute('data-update-id');
            });
        });

        const patientId = {{ $visit->patientInfo->id }};
        
        // Only setup the tag management if we have the prescription form elements
        if(selectEl && addBtn && tagsWrap && hiddenInput) {
            window.fetchDrugAllergiesList = function(){
                console.log('[Allergies] Fetching for patient:', patientId);
                $.getJSON(`/patients/${patientId}/allergies`, function(resp){
                    console.log('[Allergies] Fetch response:', resp);
                    if(resp && resp.data) {
                        allergies = resp.data.map(a => ({...a, is_active: a.is_active === 1 || a.is_active === true}));
                        renderTags();
                    } else {
                        console.warn('[Allergies] No data in response');
                        allergies = [];
                        renderTags();
                    }
                }).fail(function(xhr) {
                    console.error('[Allergies] Failed to fetch:', xhr);
                    allergies = [];
                    renderTags();
                });
            };
            window.fetchDrugAllergiesList();
        }
    })();
    
    // Standalone drug allergies loader for display lists (works independently of prescription form)
    (function() {
        const patientId = {{ $visit->patientInfo->id }};
        const displayListContainer = document.getElementById('drugAllergiesList');
        
        if (!displayListContainer) {
            console.log('[Allergies Display] drugAllergiesList element not found, skipping');
            return;
        }
        
        // If the prescription form function wasn't created (form not present), create standalone version
        if (typeof window.fetchDrugAllergiesList !== 'function') {
            window.fetchDrugAllergiesList = function() {
                console.log('[Allergies Display] Fetching for patient:', patientId);
                $.getJSON(`/patients/${patientId}/allergies`, function(resp) {
                    console.log('[Allergies Display] Fetch response:', resp);
                    const allergies = (resp && resp.data) ? resp.data.filter(a => a.is_active !== false && a.is_active !== 0) : [];
                    
                    if (allergies.length === 0) {
                        displayListContainer.innerHTML = '<span class="text-muted">None recorded</span>';
                    } else {
                        displayListContainer.innerHTML = allergies.map(a => 
                            `<span class="badge bg-danger me-1 mb-1" title="${a.reaction || 'No reaction'}${a.severity ? ' | '+a.severity : ''}">${a.substance_name}</span>`
                        ).join('');
                    }
                }).fail(function(xhr) {
                    console.error('[Allergies Display] Failed to fetch:', xhr);
                    displayListContainer.innerHTML = '<span class="text-muted">Failed to load</span>';
                });
            };
        }
        
        // Load allergies on page load
        window.fetchDrugAllergiesList();
    })();

    /**
     * Load and display complete medical history
     * Called after saving medical history from modal
     */
    window.loadMedicalHistoryDisplay = function() {
        const patientId = {{ $visit->patientInfo->id }};
        
        console.log('[Medical History] Loading display for patient:', patientId);
        
        $.ajax({
            url: `/patients/${patientId}/medical-history`,
            method: 'GET'
        }).done(function(response) {
            console.log('[Medical History] Display data loaded:', response);
            
            const history = response.data;
            
            // Update Other Allergies
            const otherAllergiesHtml = history && history.allergies ? 
                `<div class="alert alert-danger py-2 mt-1 mb-0"><strong>⚠️</strong> ${history.allergies}</div>` :
                `<p class="text-muted mb-2 mt-1">None recorded</p>`;
            $('#otherAllergiesDisplay').html(`
                <strong class="text-danger">Other Allergies:</strong>
                ${otherAllergiesHtml}
            `);
            
            // Update Chronic Conditions
            const chronicConditionsHtml = history && history.chronic_conditions ?
                `<div class="alert alert-warning py-2">${history.chronic_conditions}</div>` :
                `<p class="text-muted mb-2">No chronic conditions recorded</p>`;
            $('#chronicConditionsSection').html(`
                <h6 class="text-warning"><i class="fas fa-heartbeat"></i> Chronic Conditions:</h6>
                ${chronicConditionsHtml}
            `);
            
            // Update Current Medications
            const currentMedicationsHtml = history && history.current_medications ?
                `<div class="alert alert-info py-2">${history.current_medications}</div>` :
                `<p class="text-muted mb-2">No current medications</p>`;
            $('#currentMedicationsSection').html(`
                <h6 class="text-info"><i class="fas fa-pills"></i> Current Medications:</h6>
                ${currentMedicationsHtml}
            `);
            
            // Update Previous Surgeries
            const previousSurgeriesHtml = history && history.previous_surgeries ?
                `<div class="alert alert-secondary py-2">${history.previous_surgeries}</div>` :
                `<p class="text-muted mb-2">No previous surgeries</p>`;
            $('#previousSurgeriesSection').html(`
                <h6 class="text-secondary"><i class="fas fa-cut"></i> Previous Surgeries:</h6>
                ${previousSurgeriesHtml}
            `);
            
            // Update Family History
            const familyHistoryHtml = history && history.family_history ?
                `<div class="alert alert-light border py-2">${history.family_history}</div>` :
                `<p class="text-muted mb-2">No significant family history</p>`;
            $('#familyHistorySection').html(`
                <h6 class="text-primary"><i class="fas fa-users"></i> Family History:</h6>
                ${familyHistoryHtml}
            `);
            
            // Update Social History
            let socialHistoryContent = '';
            
            if (history && (history.smoking_status || history.alcohol_use || history.social_history)) {
                socialHistoryContent = '<div class="row">';
                
                if (history.smoking_status) {
                    const smokingBadge = history.smoking_status === 'non_smoker' ? 'success' : 'warning';
                    const smokingLabel = history.smoking_status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                    socialHistoryContent += `
                        <div class="col-6">
                            <small><strong>Smoking:</strong> 
                                <span class="badge bg-${smokingBadge}">${smokingLabel}</span>
                            </small>
                        </div>
                    `;
                }
                
                if (history.alcohol_use) {
                    const alcoholBadge = history.alcohol_use === 'none' ? 'success' : 'warning';
                    const alcoholLabel = history.alcohol_use.charAt(0).toUpperCase() + history.alcohol_use.slice(1);
                    socialHistoryContent += `
                        <div class="col-6">
                            <small><strong>Alcohol:</strong> 
                                <span class="badge bg-${alcoholBadge}">${alcoholLabel}</span>
                            </small>
                        </div>
                    `;
                }
                
                if (history.social_history) {
                    socialHistoryContent += `
                        <div class="col-12 mt-2">
                            <small>${history.social_history}</small>
                        </div>
                    `;
                }
                
                socialHistoryContent += '</div>';
            } else {
                socialHistoryContent = '<p class="text-muted mb-2">No social history recorded</p>';
            }
            
            $('#socialHistorySection').html(`
                <h6 class="text-dark"><i class="fas fa-user-friends"></i> Social History:</h6>
                ${socialHistoryContent}
            `);
            
            // Also refresh drug allergies list
            if (typeof window.fetchDrugAllergiesList === 'function') {
                window.fetchDrugAllergiesList();
            }
            
            console.log('[Medical History] Display updated successfully');
        }).fail(function(xhr) {
            console.error('[Medical History] Failed to load display:', xhr);
            toastr.error('Failed to refresh medical history display');
        });
    };
    
    /**
     * Update the compact medical history display in clinical information tab
     */
    window.updateClinicalMedicalHistoryDisplay = function(history, drugAllergies = []) {
        console.log('[Medical History] Updating clinical info display', {history, drugAllergies});
        
        // Build the updated HTML
        let html = '<div class="mb-3"><div class="row g-2">';
        
        // Allergies - Drug and Other
        html += `<div class="col-12"><small><p class="mb-1"><strong>Allergies:</strong></p>`;
        
        // Drug allergies
        if (drugAllergies && drugAllergies.length > 0) {
            const activeAllergies = drugAllergies.filter(a => a.is_active !== false);
            if (activeAllergies.length > 0) {
                const displayLimit = 3;
                const displayAllergies = activeAllergies.slice(0, displayLimit).map(a => a.substance_name).join(', ');
                const overflow = activeAllergies.length > displayLimit ? ` +${activeAllergies.length - displayLimit} more` : '';
                html += `<p class="mb-1">Drugs: <span class="text-danger" title="Full drug allergy list">${displayAllergies}${overflow}</span></p>`;
            } else {
                html += `<p class="mb-1"><span class="text-muted">Drugs: None</span></p>`;
            }
        } else {
            html += `<p class="mb-1"><span class="text-muted">Drugs: None</span></p>`;
        }
        
        // Other allergies
        if (history && history.allergies) {
            const otherAllergies = history.allergies.substring(0, 80) + (history.allergies.length > 80 ? '...' : '');
            html += `<p class="mb-0">Other:<span class="text-danger ms-1" title="Other allergies full text"> ${otherAllergies}</span></p>`;
        } else {
            html += `<p class="mb-0">Other:<span class="text-muted ms-1"> None</span></p>`;
        }
        
        html += `</small></div>`;
        
        // Chronic Conditions
        html += `
            <div class="col-6">
                <small><strong>Chronic Conditions:</strong>
                    ${history && history.chronic_conditions ?
                        `<span class="text-warning">${history.chronic_conditions.substring(0, 80)}${history.chronic_conditions.length > 80 ? '...' : ''}</span>` :
                        `<span class="text-muted">None</span>`}
                </small>
            </div>
        `;
        
        // Current Medications
        html += `
            <div class="col-6">
                <small><strong>Current Medications:</strong>
                    ${history && history.current_medications ?
                        `<span class="text-info">${history.current_medications.substring(0, 80)}${history.current_medications.length > 80 ? '...' : ''}</span>` :
                        `<span class="text-muted">None</span>`}
                </small>
            </div>
        `;
        
        // Previous Surgeries
        html += `
            <div class="col-6">
                <small><strong>Previous Surgeries:</strong>
                    ${history && history.previous_surgeries ?
                        `<span class="text-secondary">${history.previous_surgeries.substring(0, 80)}${history.previous_surgeries.length > 80 ? '...' : ''}</span>` :
                        `<span class="text-muted">None</span>`}
                </small>
            </div>
        `;
        
        // Family History
        html += `
            <div class="col-6">
                <small><strong>Family History:</strong>
                    ${history && history.family_history ?
                        `<span class="text-dark">${history.family_history.substring(0, 80)}${history.family_history.length > 80 ? '...' : ''}</span>` :
                        `<span class="text-muted">None</span>`}
                </small>
            </div>
        `;
        
        // Smoking and Alcohol
        const smokingBadge = history && history.smoking_status === 'non_smoker' ? 'success' : 'warning';
        const smokingLabel = history && history.smoking_status ? 
            history.smoking_status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Unknown';
        const alcoholBadge = history && history.alcohol_use === 'none' ? 'success' : 'warning';
        const alcoholLabel = history && history.alcohol_use ? 
            history.alcohol_use.charAt(0).toUpperCase() + history.alcohol_use.slice(1) : 'Unknown';
            
        html += `
            <div class="col-6">
                <small>
                    <strong>Smoking:</strong>
                    <span class="badge bg-${smokingBadge}">${smokingLabel}</span>
                    <span class="ms-2"><strong>Alcohol:</strong>
                        <span class="badge bg-${alcoholBadge}">${alcoholLabel}</span>
                    </span>
                </small>
            </div>
        `;
        
        // Social History
        html += `
            <div class="col-6">
                <small><strong>Social History:</strong>
                    ${history && history.social_history ?
                        `<span>${history.social_history.substring(0, 80)}${history.social_history.length > 80 ? '...' : ''}</span>` :
                        `<span class="text-muted">None</span>`}
                </small>
            </div>
        `;
        
        // Occupational History
        html += `
            <div class="col-6">
                <small><strong>Occupational History:</strong>
                    ${history && history.occupational_history ?
                        `<span>${history.occupational_history.substring(0, 80)}${history.occupational_history.length > 80 ? '...' : ''}</span>` :
                        `<span class="text-muted">None</span>`}
                </small>
            </div>
        `;
        
        // Immunizations
        html += `
            <div class="col-6">
                <small><strong>Immunizations:</strong>
                    ${history && history.immunization_history ?
                        `<span>${history.immunization_history.substring(0, 80)}${history.immunization_history.length > 80 ? '...' : ''}</span>` :
                        `<span class="text-muted">None</span>`}
                </small>
            </div>
        `;
        
        // Reproductive History
        html += `
            <div class="col-12">
                <small><strong>Reproductive History:</strong>
                    ${history && history.reproductive_history ?
                        `<span>${history.reproductive_history.substring(0, 120)}${history.reproductive_history.length > 120 ? '...' : ''}</span>` :
                        `<span class="text-muted">None</span>`}
                </small>
            </div>
        `;
        
        html += '</div></div>';
        
        $('#medicalHistoryDisplay').html(html);
    };
    
    /**
     * Main function to refresh all medical history displays
     * Called from the medical history modal after save
     */
    window.refreshAllMedicalHistory = function() {
        const patientId = {{ $visit->patientInfo->id }};
        
        // Fetch both medical history and drug allergies
        const medicalHistoryPromise = $.ajax({
            url: `/patients/${patientId}/medical-history`,
            method: 'GET'
        });
        
        const allergiesPromise = $.ajax({
            url: `/patients/${patientId}/allergies`,
            method: 'GET'
        });
        
        // Wait for both to complete
        $.when(medicalHistoryPromise, allergiesPromise).done(function(medicalHistoryResp, allergiesResp) {
            const history = medicalHistoryResp[0].data;
            const drugAllergies = allergiesResp[0].data || [];
            
            // Update the clinical information tab display with drug allergies
            window.updateClinicalMedicalHistoryDisplay(history, drugAllergies);
            
            // Update the patient profile tab display (this also refreshes drug allergies via fetchDrugAllergiesList)
            window.loadMedicalHistoryDisplay();
            
            console.log('[Medical History] All displays refreshed');
        }).fail(function(xhr) {
            console.error('[Medical History] Failed to refresh:', xhr);
        });
    };

    // Mark a pane as saved: clear its unsaved indicator and switch back to Clinical Information tab
    function markPaneSaved(paneId) {
        try {
            const navLink = document.querySelector(`a[data-tab="${paneId}"]`) || document.querySelector(`a[href="#${paneId}"]`);
            // Hide unsaved indicator only. Do not touch active/show classes or
            // aria-selected attributes so the currently-visible tab remains
            // selected — otherwise removing active classes can leave no tab
            // selected and produce a blank area after add/delete operations.
            if (navLink) {
                const ind = navLink.querySelector('.unsaved-indicator');
                if (ind) ind.classList.add('d-none');
            }

            // NOTE: do not programmatically switch/focus tabs here. Clearing the
            // unsaved indicator and removing active/show classes on the saved pane
            // is sufficient; letting the UI's active markers remain in control
            // avoids unexpected focus jumps when saving.
        } catch (e) {
            console.error('markPaneSaved error', e);
        }
    }
    
    // Function to view complex results in modal
    function viewComplexResult(investigationId, templateResultId) {
        console.log('Viewing complex result:', investigationId, templateResultId);
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('complexResultsModal'));
        modal.show();
        
        // Show loading state
        const contentDiv = document.getElementById('complexResultsContent');
        contentDiv.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading investigation results...</p>
                </div>
            </div>
        `;
        
        // Update the print button link
        document.getElementById('printComplexResult').href = `/lab/template-results/${templateResultId}`;
        
        // Fetch the result details
        fetch(`/lab/template-results/${templateResultId}/modal`)
            .then(response => {
                console.log(response);
                if (!response.ok) {
                    throw new Error('Failed to fetch result details');
                }
                return response.text();
            })
            .then(html => {
                contentDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading complex result:', error);
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error:</strong> Failed to load investigation results.
                        <br><small class="text-muted">${error.message}</small>
                    </div>
                `;
            });
    }
    
    // saveTestResult removed; test results are handled in the Lab module.

    // Save All helper: only call save handlers for panes that are active or marked unsaved.
    // If an active pane has required fields missing, abort and focus the first invalid field.
    async function saveAll(redirectBack = false) {
        // Prevent the beforeunload dialog from appearing while we're actively saving/navigating
        setSuppressBeforeUnload(true);
    // show overlay immediately
    showSaveOverlay();
        const btn = document.getElementById(redirectBack ? 'saveAndBackBtn' : 'saveAllBtn');
        const saveAndBackBtn = document.getElementById('saveAndBackBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }
        if (saveAndBackBtn) saveAndBackBtn.disabled = true;

        // Helper to call a save function if it exists and returns a Promise. Returns {ok, value|error}
        async function callIfExists(fnName) {
            let result;
            try {
                const fn = window[fnName];
                if (typeof fn === 'function') {
                    result = fn();
                    if (result && typeof result.then === 'function') {
                        result = await result;
                    }
                }
                return { ok: true, value: result };
            } catch (err) {
                console.error('Error calling ' + fnName, err);
                return { ok: false, error: err };
            }
        }

        // Helpers to determine pane state and validate required fields
        function isPaneActive(paneId) {
            const pane = document.getElementById(paneId);
            return pane && pane.classList.contains('show') && pane.classList.contains('active');
        }

        function paneHasUnsaved(paneId) {
            const navLink = document.querySelector(`a[data-tab="${paneId}"]`) || document.querySelector(`a[href="#${paneId}"]`);
            const ind = navLink ? navLink.querySelector('.unsaved-indicator') : null;
            return ind && !ind.classList.contains('d-none');
        }

        function validatePane(paneId) {
            const pane = document.getElementById(paneId);
            if (!pane) return true;
            const forms = pane.querySelectorAll('form');
            for (const form of forms) {
                // Use HTML5 validation where available
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    const invalid = form.querySelector(':invalid');
                    if (invalid) {
                        // Focus and show browser validation UI if supported
                        try { invalid.focus(); } catch (e) {}
                        if (typeof invalid.reportValidity === 'function') {
                            invalid.reportValidity();
                        }
                        invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    // Show a simple toast/alert to guide the user (prefer toastr if available)
                    const titleEl = pane.querySelector('h3') || pane.querySelector('h5');
                    const title = titleEl ? titleEl.innerText.trim() : paneId;
                    const msg = 'Please fill required fields in the "' + title + '" section before saving.';
                    if (typeof toastr !== 'undefined' && toastr && typeof toastr.warning === 'function') {
                        toastr.warning(msg);
                    } else {
                        alert(msg);
                    }
                    return false;
                }
            }
            return true;
        }

        // Map panes to save function names. Only panes that are active or have unsaved markers will be executed.
        // Use the actual function names implemented in the consultation JS modules so saveAll triggers them.
        const paneSaveMap = [
            // Clinical info: consultation main form (medical history now in modal)
            { pane: 'clinical-information', fn: 'saveConsultation' },
            { pane: 'diagnosis', fn: 'saveDiagnosis' },
            { pane: 'remarks', fn: 'saveRemarks' },
            { pane: 'investigations', fn: 'saveInvestigation' },
            { pane: 'treatment', fn: 'savePrescription' },
            // Examinations: quick vitals (systemic examination now in modal)
            { pane: 'examinations', fn: 'saveQuickVitals' }
        ];

        for (const map of paneSaveMap) {
            const paneId = map.pane;
            // Decide whether to attempt save for this mapping.
            // Default: attempt if pane is active or marked unsaved.
            const paneActive = isPaneActive(paneId);
            const paneUnsaved = paneHasUnsaved(paneId);
            if (paneActive || paneUnsaved) {
                // Validate required fields in that pane first. If invalid, abort the whole saveAll.
                if (!validatePane(paneId)) {
                    // Re-enable beforeunload since we aborted
                    setSuppressBeforeUnload(false);
                    // hide overlay on abort
                    hideSaveOverlay();
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save All'; }
                    if (saveAndBackBtn) saveAndBackBtn.disabled = false;
                    // Indicate saveAll aborted due to validation
                    return false;
                }
                const res = await callIfExists(map.fn);
                // If the save function resolved successfully, and it's a prescription or investigation save,
                // consider the pane saved and switch focus back to Clinical Information so the pane is no longer active.
                if (res && res.ok) {
                    if (map.fn === 'savePrescription' || map.fn === 'saveInvestigation') {
                        // mark the pane saved and deactivate it visually
                        try { markPaneSaved(paneId); } catch (e) { /* ignore */ }
                    }
                }
            }
        }

        // Small delay to let UI settle
        await new Promise(r => setTimeout(r, 300));

        if (btn) {
            btn.disabled = false;
            if (redirectBack) {
                btn.innerHTML = '<i class="fas fa-check"></i> Saved';
            } else {
                btn.innerHTML = '<i class="fas fa-save"></i> Save All';
            }
        }
        if (saveAndBackBtn) saveAndBackBtn.disabled = false;

        if (redirectBack) {
            // Clear UI indicators immediately so beforeunload sees no unsaved state
            document.querySelectorAll('.unsaved-indicator').forEach(i => i.classList.add('d-none'));
            // Ensure beforeunload is suppressed while navigating
            setSuppressBeforeUnload(true);
            // Disable Back link to avoid duplicate navigation
            const backLink = document.getElementById('backToVisitsBtn');
            if (backLink) {
                backLink.classList.add('disabled');
                backLink.dataset._wasDisabled = '1';
            }
            // Give the UI a moment to update then navigate
            setTimeout(() => {
                window.location.href = document.getElementById('backToVisitsBtn').href;
            }, 150);
            return true;
        }

    // Re-enable beforeunload now that save finished and we're not navigating
    setSuppressBeforeUnload(false);
    // hide overlay since we're staying on the page
    hideSaveOverlay();
        // Re-enable Back link if we previously disabled it
        const backLink = document.getElementById('backToVisitsBtn');
        if (backLink && backLink.dataset._wasDisabled) {
            backLink.classList.remove('disabled');
            delete backLink.dataset._wasDisabled;
        }
        // Indicate success
        return true;
    }

    // Fetch the examinations partial for this consultation and replace the DOM fragment.
    // Called by modal to refresh main page display
    window.refreshExaminationsList = function() {
        const consultationId = '{{ $consultation->id ?? '' }}';
        if (!consultationId) return;
        const url = '/consultations/' + consultationId + '/examinations-partial';
        const token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: url,
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': token }
        }).done(function(response) {
            if (response && response.success && response.html) {
                $('#examinationsList').html(response.html);
            } else {
                console.warn('Failed to refresh examinations partial', response);
            }
        }).fail(function(xhr) {
            console.error('Error fetching examinations partial', xhr);
        });
    };

    // Track unsaved state per-tab via their .unsaved-indicator elements
    (function setupUnsavedTracking() {
        const saveAllBtn = document.getElementById('saveAllBtn');
        const saveAndBackBtn = document.getElementById('saveAndBackBtn');
        const backBtn = document.getElementById('backToVisitsBtn');

        function showSaveAndBack(show) {
            if (!saveAllBtn || !saveAndBackBtn || !backBtn) return;
            if (show) {
                saveAllBtn.style.display = 'inline-block';
                saveAndBackBtn.style.display = 'inline-block';
                backBtn.style.display = 'none';
            } else {
                saveAllBtn.style.display = 'inline-block';
                saveAndBackBtn.style.display = 'none';
                backBtn.style.display = 'inline-block';
            }
        }

        // Initialize: hide Save & Back until any tab shows unsaved
        showSaveAndBack(false);

        // Helper: find the nav indicator for a given tabId
        function indicatorForTab(tabId) {
            if (!tabId) return null;
            // Prefer data-tab attribute first
            const navLink = document.querySelector(`a[data-tab="${tabId}"]`) || document.querySelector(`a[href="#${tabId}"]`);
            return navLink ? navLink.querySelector('.unsaved-indicator') : null;
        }

        // Compute whether any unsaved indicator is visible (live query)
        function anyUnsaved() {
            return document.querySelectorAll('.unsaved-indicator:not(.d-none)').length > 0;
        }

        // Set unsaved state for a specific tab
        function setUnsavedForTab(tabId, unsaved = true) {
            const ind = indicatorForTab(tabId);
            if (!ind) return;
            if (unsaved) {
                ind.classList.remove('d-none');
            } else {
                ind.classList.add('d-none');
            }
            // Update global Save & Back visibility based on any indicator visible (live)
            showSaveAndBack(anyUnsaved());
        }

        // Clear all unsaved indicators (used after successful saveAll)
        function clearUnsavedIndicators() {
            document.querySelectorAll('.unsaved-indicator').forEach(i => i.classList.add('d-none'));
            showSaveAndBack(false);
        }

        // Observe the document for changes that might affect unsaved indicators (class toggles or DOM replacements)
        const docObserver = new MutationObserver((mutations) => {
            let changed = false;
            for (const m of mutations) {
                if (m.type === 'attributes' && m.target && m.target.classList && m.target.classList.contains('unsaved-indicator')) {
                    changed = true; break;
                }
                if (m.type === 'childList') {
                    // If nodes added/removed, recompute
                    changed = true; break;
                }
            }
            if (changed) showSaveAndBack(anyUnsaved());
        });
        docObserver.observe(document.body, { attributes: true, childList: true, subtree: true, attributeFilter: ['class'] });

        // Inputs to watch: map to the pane they belong to and mark only that pane's indicator
        const watchedSelectors = [
            '#diagnosisForm textarea, #diagnosisForm input, #diagnosisForm select, #diagnosisForm [contenteditable="true"]',
            '#remarksForm textarea, #remarksForm input, #remarksForm select, #remarksForm [contenteditable="true"]',
            '#prescriptionFormElement input, #prescriptionFormElement textarea, #prescriptionFormElement select',
            '#quickVitalsForm input, #quickVitalsForm textarea, #quickVitalsForm select'
            // systemicExaminationForm removed - now in modal component
        ];

        // Attach input listeners to elements within a container (document or newly-added node)
        function attachInputListeners(container) {
            if (!container) return;
            const selector = watchedSelectors.join(', ');
            let elements = [];
            try {
                elements = container.querySelectorAll ? container.querySelectorAll(selector) : [];
            } catch (e) {
                elements = [];
            }
            elements.forEach(el => {
                // avoid adding duplicate listeners
                if (el.dataset._unsavedListener) return;
                const handler = (e) => {
                    const pane = el.closest('.tab-pane');
                    const paneId = pane ? pane.id : null;
                    if (paneId) {
                        setUnsavedForTab(paneId, true);
                    } else {
                        setUnsavedForTab('clinical-information', true);
                    }
                };
                el.addEventListener('input', handler);
                el.addEventListener('change', handler);
                // mark as handled
                el.dataset._unsavedListener = '1';
            });
        }

        // Attach listeners initially across the document
        attachInputListeners(document);

        // Observe the DOM for added nodes (forms/inputs injected dynamically) and attach listeners
        const nodeObserver = new MutationObserver(mutations => {
            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (node.nodeType !== 1) continue;
                    // Attach listeners to the added node and its descendants
                    attachInputListeners(node);
                    if (node.querySelectorAll) attachInputListeners(node);
                }
            }
        });
        nodeObserver.observe(document.body, { childList: true, subtree: true });

        // When saveAll completes successfully, clear indicators
        const originalSaveAll = window.saveAll;
        if (typeof originalSaveAll === 'function') {
            window.saveAll = async function (redirectBack = false) {
                try {
                    const ok = await originalSaveAll(redirectBack);
                    if (ok) {
                        // After successful save: clear indicators
                        clearUnsavedIndicators();
                    }
                    return ok;
                } catch (e) {
                    console.error('saveAll wrapper error', e);
                    return false;
                }
            };
        }

        // Warn on page unload if any tab has unsaved changes, unless suppression flag is set.
        // Use a capturing listener to short-circuit other beforeunload handlers when suppression is active.
        window.addEventListener('beforeunload', function (e) {
            if (window._suppressBeforeUnload) {
                // Prevent other listeners from running and avoid showing the prompt
                if (typeof e.stopImmediatePropagation === 'function') {
                    e.stopImmediatePropagation();
                }
                // Do not set returnValue; simply exit so the browser won't show a prompt
                return;
            }
            // Use the anyUnsaved() helper which performs a live query for unsaved indicators.
            try {
                const hasUnsaved = anyUnsaved();
                if (hasUnsaved) {
                    // Some browsers require returnValue to be set for a prompt to appear
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            } catch (err) {
                // If anything goes wrong, do not block unload; log for debugging
                console.error('beforeunload check failed', err);
                return;
            }
        }, true);
    })();

    // Discard helpers: reset and collapse forms, clear unsaved indicators
    function discardPrescriptionForm() {
        try {
            const form = document.getElementById('prescriptionFormElement');
            if (!form) return;
            form.reset();
            // clear any selected medication id and suggestions
            const sel = document.getElementById('selected_medication_id'); if (sel) sel.value = '';
            const medInput = document.getElementById('medication_search'); if (medInput) { medInput.value = ''; }
            const sug = document.getElementById('medication_suggestions'); if (sug) sug.classList.add('d-none');
            // collapse the form
            const collapseEl = document.getElementById('prescriptionForm');
            if (collapseEl && typeof bootstrap !== 'undefined') {
                try { new bootstrap.Collapse(collapseEl, { toggle: false }).hide(); } catch (e) {}
            }
            // dispatch change/input so tracking marks it cleared
            form.querySelectorAll('input,textarea,select').forEach(i => {
                try { i.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) {}
                try { i.dispatchEvent(new Event('input', { bubbles: true })); } catch (e) {}
            });
            // clear unsaved indicator for treatment tab
            try { markPaneSaved('treatment'); } catch (e) {}
        } catch (e) { console.error('discardPrescriptionForm', e); }
    }


    // Defensive: ensure the save overlay is hidden on initial load and when
    // the page is restored from bfcache (back/forward navigation).
    try {
        // Immediate attempt to hide in case it was left visible
        hideSaveOverlay();
        // On normal load
        window.addEventListener('DOMContentLoaded', function () { try { hideSaveOverlay(); } catch (e) {} });
        // On pageshow (handles bfcache restores where DOM state can be preserved)
        window.addEventListener('pageshow', function (ev) { try { hideSaveOverlay(); } catch (e) {} });
        // Also ensure any body-level overlay active class is cleared
        document.addEventListener('DOMContentLoaded', function () { try { document.body.classList.remove('save-overlay-active'); } catch (e) {} });

        // If the overlay element is added to the DOM after this script runs (for example
        // because the template places the element below the scripts, or the browser
        // restored the DOM from bfcache), ensure we still hide it as soon as it appears.
        // Observe the document for the #saveOverlay insertion and hide it once found.
        (function observeOverlayInsertion() {
            try {
                if (document.getElementById('saveOverlay')) {
                    // Already present; ensure it's hidden
                    hideSaveOverlay();
                    return;
                }
                const mo = new MutationObserver((mutations, observer) => {
                    if (document.getElementById('saveOverlay')) {
                        try { hideSaveOverlay(); } catch (e) {}
                        try { document.body.classList.remove('save-overlay-active'); } catch (e) {}
                        observer.disconnect();
                    }
                });
                mo.observe(document.documentElement || document.body, { childList: true, subtree: true });
            } catch (e) {
                console.error('overlay insertion observer failed', e);
            }
        })();
    } catch (e) {
        console.error('Error initializing save overlay guard', e);
    }

    // --- CDS helpers ---
    function ackCdsAlert(alertId, action) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(`/consultations/{{ $consultation->id }}/cds-alerts/${alertId}/ack`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ action })
        }).then(r => r.json()).then((res) => {
            if (res.success) {
                try { 
                    toastr.success('Alert ' + action + 'ed successfully'); 
                } catch (e) {}
                
                // Remove the specific alert from the sidebar
                const alertElement = document.querySelector(`[data-alert-id="${alertId}"]`);
                if (alertElement) {
                    alertElement.remove();
                }
                
                // Update the alert count
                refreshCdsAlertCount();
            } else {
                try { 
                    toastr.error(res.message || 'Failed to update alert'); 
                } catch (e) {}
            }
        }).catch(() => {
            try { toastr.error('Failed to update alert'); } catch (e) {}
        });
    }

    function ackCdsAlertWithReason(alertId, action) {
        const reason = prompt('Provide override reason (optional):');
        if (reason === null) return; // User cancelled
        
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(`/consultations/{{ $consultation->id }}/cds-alerts/${alertId}/ack`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ action, reason })
        }).then(r => r.json()).then((res) => {
            if (res.success) {
                try { 
                    toastr.success('Override recorded successfully'); 
                } catch (e) {}
                
                // Remove the specific alert from the sidebar
                const alertElement = document.querySelector(`[data-alert-id="${alertId}"]`);
                if (alertElement) {
                    alertElement.remove();
                }
                
                // Update the alert count
                refreshCdsAlertCount();
            } else {
                try { 
                    toastr.error(res.message || 'Failed to override alert'); 
                } catch (e) {}
            }
        }).catch(() => {
            try { toastr.error('Failed to override'); } catch (e) {}
        });
    }
    
    // Refresh the CDS alert count and styling after alerts are acknowledged
    function refreshCdsAlertCount() {
        const remainingAlerts = document.querySelectorAll('[data-alert-id]').length;
        const countBadge = document.getElementById('cds-alert-count-badge');
        const header = document.getElementById('cds-alerts-header');
        const alertsBody = document.getElementById('cds-alerts-body');
        
        if (countBadge) {
            countBadge.textContent = remainingAlerts;
        }
        
        if (remainingAlerts === 0) {
            // Update header to show success state
            if (header) {
                header.style.backgroundColor = '#28a745';
                header.classList.add('text-white');
                header.classList.remove('text-dark');
            }
            
            // Show no alerts message
            if (alertsBody) {
                alertsBody.innerHTML = `
                    <div id="no-alerts-message" class="text-center text-muted py-3">
                        <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                        <div>No clinical alerts</div>
                        <small>System monitoring for safety issues</small>
                    </div>
                `;
            }
        }
    }
</script>
@endsection



@push('styles')
    
<style>
/* Overlay removed: styles intentionally omitted to avoid flash */

/* Add this to your CSS file */
.icd10-search-container {
    position: relative;
}

.icd10-suggestions {
    border-top: none !important;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

.icd10-suggestion-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.icd10-suggestion-item:hover,
.icd10-suggestion-item.active {
    background-color: #f8f9fa !important;
    border-left: 3px solid #007bff;
}

.icd10-suggestion-item:last-child {
    border-bottom: none !important;
}

.hover-bg-light:hover {
    background-color: #f8f9fa !important;
}

.cursor-pointer {
    cursor: pointer;
}

.card.border-primary {
    border: 1px solid #007bff !important;
}

.table th {
    font-weight: 600;
}

.badge {
    font-size: 0.875em;
}

#addIcdBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Visual feedback for textarea updates */
textarea.border-success {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

textarea.border-warning {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
}

/* Improved textarea styling */
#provisional_diagnosis,
#final_diagnosis {
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

/* ICD entry highlighting in textareas */
.icd-entry-highlight {
    background-color: #fff3cd;
    border-left: 3px solid #856404;
    padding-left: 10px;
    margin: 2px 0;
}

/* Prevent flash of unstyled content for collapse elements */
.collapse:not(.show) {
    display: none !important;
}

/* Ensure smooth collapse transitions */
.collapse {
    transition: height 0.35s ease;
}

/* Make sure forms inside collapsed elements don't show during page load */
.collapse:not(.show) form {
    visibility: hidden;
}

.collapse.show form,
.collapse.collapsing form {
    visibility: visible;
}

/* Investigation Details Modal Styling */
#investigationDetailsModal .modal-dialog {
    max-width: 1200px;
}

#investigationDetailsModal .card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#investigationDetailsModal .card-body {
    padding: 1rem;
}

#investigationDetailsModal .table td:first-child {
    width: 30%;
    font-weight: 500;
}

#investigationDetailsModal .badge {
    font-size: 0.8em;
    padding: 0.25em 0.6em;
}

#investigationDetailsModal .alert {
    margin-bottom: 0;
    padding: 0.75rem;
}

/* Loading spinner in modal */
#investigationDetailsModal .spinner-border {
    width: 2rem;
    height: 2rem;
}

/* Investigation Form Display Styles */
#investigationDetailsModal .investigation-form-readonly {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
}

#investigationDetailsModal .investigation-form-readonly input,
#investigationDetailsModal .investigation-form-readonly select,
#investigationDetailsModal .investigation-form-readonly textarea {
    background-color: #fff !important;
    border-color: #ced4da !important;
}

#investigationDetailsModal .investigation-form-readonly input:disabled,
#investigationDetailsModal .investigation-form-readonly select:disabled,
#investigationDetailsModal .investigation-form-readonly textarea:disabled {
    background-color: #e9ecef !important;
    opacity: 1;
}

#investigationDetailsModal .clinical-data-display {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 0.75rem;
}

#investigationDetailsModal .clinical-data-display .col-md-6 {
    margin-bottom: 0.5rem;
    padding: 0.25rem 0.75rem;
    background-color: white;
    border-radius: 0.25rem;
    border-left: 3px solid #007bff;
}

/* Dynamic Form Styling */
#dynamic-form-container {
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

#dynamic-form-container.show {
    border-color: #0d6efd;
    background-color: #e7f3ff;
}

#dynamic-form-container h6 {
    color: #495057;
    margin-bottom: 1rem;
}

#dynamic-form-container .form-label {
    font-weight: 500;
    color: #212529;
}

#dynamic-form-container .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

#dynamic-form-container .text-danger {
    font-weight: 600;
}

/* Service suggestions with form requirements indicator */
.service-suggestion-item {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.service-suggestion-item:hover {
    border-left-color: #0d6efd;
    background-color: #f8f9fa !important;
}

.service-suggestion-item .text-warning {
    font-weight: 500;
}

/* Form validation styling */
.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    font-size: 0.875em;
    color: #dc3545;
    margin-top: 0.25rem;
}

/* Responsive adjustments for modal */
@media (max-width: 768px) {
    #investigationDetailsModal .modal-dialog {
        max-width: 95%;
        margin: 0.5rem;
    }
    
    #investigationDetailsModal .row {
        margin: 0;
    }
    
    #investigationDetailsModal .col-md-6 {
        padding: 0.25rem;
    }
    
    #dynamic-form-container {
        margin: 0.5rem 0;
        padding: 1rem;
    }
    
    #complexResultsModal .modal-dialog {
        max-width: 95%;
        margin: 0.5rem;
    }
}

/* Test Results Display Styling */
.results-list .table-sm th,
.results-list .table-sm td {
    padding: 0.375rem;
    font-size: 0.875rem;
}

.results-list .table-sm th {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
}

.results-list .badge {
    font-size: 0.75rem;
    font-weight: 500;
}

/* Complex Results Modal Styling */
#complexResultsModal .modal-dialog {
    max-width: 1000px;
}

#complexResultsModal .card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

#complexResultsModal .table-responsive {
    max-height: 400px;
    overflow-y: auto;
}

#complexResultsModal .alert-primary {
    background-color: #e7f3ff;
    border-color: #b8daff;
    color: #004085;
}

#complexResultsModal .alert-light {
    background-color: #fefefe;
    border-color: #f0f0f0;
    color: #6c757d;
}

/* Patient Profile Sub-Tabs Styling */
#home .nav-tabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 1.5rem;
}

#home .nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

#home .nav-tabs .nav-link:hover {
    border-color: transparent;
    color: #0d6efd;
    background-color: #f8f9fa;
}

#home .nav-tabs .nav-link.active {
    color: #0d6efd;
    background-color: transparent;
    border-color: transparent;
    border-bottom-color: #0d6efd;
}

#home .nav-tabs .nav-link i {
    margin-right: 0.5rem;
}

#home .tab-content {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = $('#drugAllergiesSelect');
    if (el.length && !el.hasClass('select2-hidden-accessible')) {
        el.select2({
            width: '100%',
            placeholder: el.data('placeholder') || 'Search medication',
            allowClear: true,
            matcher: function(params, data) {
                if ($.trim(params.term) === '') { return data; }
                if (typeof data.text === 'undefined') { return null; }
                const term = params.term.toLowerCase();
                const text = data.text.toLowerCase();
                if (text.indexOf(term) > -1) { return data; }
                return null;
            }
        });
    }
});
</script>
@endsection
