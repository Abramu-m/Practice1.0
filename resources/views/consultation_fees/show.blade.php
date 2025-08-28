@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Consultation Fee Details' }}
 @endsection

@section('Content_Description')
    {{ 'View detailed information for this consultation fee structure.' }}
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave"></i> Consultation Fee Details
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('consultation_fees.edit', $consultationFee->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('consultation_fees.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Doctor:</strong></td>
                                    <td>{{ $consultationFee->doctor->user->name ?? 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Patient Category:</strong></td>
                                    <td>{{ $consultationFee->patientCategory->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Visit Type:</strong></td>
                                    <td>{{ $consultationFee->visitType->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fee Amount:</strong></td>
                                    <td>
                                        <span class="badge badge-success badge-lg">
                                            ${{ number_format($consultationFee->fee_amount, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($consultationFee->status == 1)
                                            <span class="badge badge-success text-black">Active</span>
                                        @else
                                            <span class="badge badge-danger text-black">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td>{{ $consultationFee->creator->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created Date:</strong></td>
                                    <td>{{ $consultationFee->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $consultationFee->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($consultationFee->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Description:</h5>
                                <div class="alert alert-info">
                                    {{ $consultationFee->description }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Actions Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('consultation_fees.edit', $consultationFee->id) }}" class="btn btn-warning mb-2">
                            <i class="fas fa-edit"></i> Edit Fee Structure
                        </a>
                        
                        <a href="{{ route('consultation_fees.create') }}" class="btn btn-success mb-2">
                            <i class="fas fa-plus"></i> Create New Fee
                        </a>
                        
                        <a href="{{ route('patient_visits.create', ['doctor_id' => $consultationFee->doctor_id]) }}" class="btn btn-primary mb-2">
                            <i class="fas fa-plus-circle"></i> Create Visit for this Doctor
                        </a>
                        
                        <form action="{{ route('consultation_fees.destroy', $consultationFee->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this consultation fee?')" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Delete Fee Structure
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Related Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Related Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-user-md"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Doctor Specialization</span>
                            <span class="info-box-number">{{ $consultationFee->doctor->specialization ?? 'N/A' }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Doctor's Total Visits</span>
                            <span class="info-box-number">{{ $consultationFee->doctor->visits->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
@endsection
