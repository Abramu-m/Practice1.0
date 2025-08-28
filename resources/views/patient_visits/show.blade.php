@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Patient Visit Details' }}
 @endsection

@section('Content_Description')
    {{ 'View detailed information about the patient visit.' }}
@endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Patient Visit Details</h4>
                    <div>
                        @if(($patientVisit->visit_status == 0 || $patientVisit->visit_status == 1) && auth()->user()->role === 'doctor')
                            <a href="{{ route('consultations.show', $patientVisit->id) }}" class="btn btn-success">
                                <i class="fas fa-user-md"></i> {{ $patientVisit->visit_status == 0 ? 'Start Consultation' : 'Continue Consultation' }}
                            </a>
                        @endif
                        <a href="{{ route('patient_visits.edit', $patientVisit->id) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('patient_visits.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Visit ID:</strong></div>
                        <div class="col-md-9">{{ $patientVisit->id }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>MR Number:</strong></div>
                        <div class="col-md-9">{{ $patientVisit->patientInfo->mr_number }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Doctor:</strong></div>
                        <div class="col-md-9">{{ $patientVisit->doctor->user->name ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Visit Type:</strong></div>
                        <div class="col-md-9">{{ $patientVisit->visitType->description ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Amount:</strong></div>
                        <div class="col-md-9">${{ number_format($patientVisit->c_amount, 2) }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>SIC Number:</strong></div>
                        <div class="col-md-9">{{ $patientVisit->sic_no ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Visit Date:</strong></div>
                        <div class="col-md-9">{{ $patientVisit->createdon }} at {{ $patientVisit->createdontime }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Visit Status:</strong></div>
                        <div class="col-md-9">
                            <span class="badge {{ $patientVisit->visit_status_badge_class }} text-black">
                                {{ $patientVisit->visit_status_label }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Vital Status:</strong></div>
                        <div class="col-md-9">
                            <span class="badge {{ $patientVisit->vital_status_badge_class }} text-white">
                                {{ $patientVisit->vital_status_label }}
                            </span>
                            @if($patientVisit->vitals_at)
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-clock"></i> Vitals taken: {{ $patientVisit->vitals_at }}
                                </small>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Created By:</strong></div>
                        <div class="col-md-9">{{ $patientVisit->creator->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
@endsection