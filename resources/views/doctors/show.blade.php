@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Doctor Details - ' . ($doctor->user->name ?? 'Unknown') }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <!-- Doctor Information Card -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-md"></i> Doctor Information
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('doctors.edit', $doctor->doctor_id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('doctors.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $doctor->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $doctor->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Designation:</strong></td>
                                    <td>{{ $doctor->designationInfo->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Specialization:</strong></td>
                                    <td>{{ $doctor->specialization ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>MCT Number:</strong></td>
                                    <td>{{ $doctor->mct_number ?? 'Not Provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($doctor->status == 1)
                                            <span class="badge bg-success text-black">Active</span>
                                        @else
                                            <span class="badge bg-danger text-black">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td>{{ $doctor->creator->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created Date:</strong></td>
                                    <td>{{ $doctor->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($doctor->drsignature)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Digital Signature:</h5>
                                <div class="alert alert-info">
                                    {{ $doctor->drsignature }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Statistics Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Visits</span>
                            <span class="info-box-number">{{ $doctor->visits->count() }}</span>
                        </div>
                    </div>
                    
                    @if($doctor->visits->count() > 0)
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-user-check"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Unique Patients</span>
                                <span class="info-box-number">{{ $doctor->visits->unique('patient')->count() }}</span>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-clock"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Last Visit</span>
                                <span class="info-box-number">{{ $doctor->visits->sortByDesc('visit_date')->first()->visit_date ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-3">
                        @if($doctor->visits->count() > 0)
                            <a href="{{ route('patient_visits.index', ['doctor_id' => $doctor->id]) }}" class="btn btn-primary w-100">
                                <i class="fas fa-list"></i> View All Visits
                            </a>
                        @endif
                        <a href="{{ route('patient_visits.create', ['doctor_id' => $doctor->id]) }}" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Create New Visit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Visits Section -->
    @if($doctor->visits->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i> Recent Visits
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('patient_visits.index', ['doctor_id' => $doctor->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Visit Date</th>
                                        <th>Patient</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($doctor->visits->sortByDesc('visit_date')->take(5) as $visit)
                                        <tr>
                                            <td>{{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ $visit->patientInfo->full_name ?? 'Unknown' }}</td>
                                            <td>{{ $visit->visitCategory->description ?? 'N/A' }}</td>
                                            <td>
                                                @if($visit->visit_status == 0)
                                                    <span class="badge bg-warning">In Treatment</span>
                                                @else
                                                    <span class="badge bg-success text-black">Discharged</span>
                                                @endif
                                            </td>
                                            <td>Tsh {{ number_format($visit->amount_cash + ($visit->amount_covered ?? 0), 2) }}</td>
                                            <td>
                                                <a href="{{ route('patient_visits.show', $visit->id) }}" class="btn btn-sm btn-info">
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
        </div>
    @endif
</div>
@endsection

@section('extra_footer_content')
@endsection