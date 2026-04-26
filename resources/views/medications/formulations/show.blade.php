@extends('layouts.app_main_layout')

@section('page_title', 'Formulation Details')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Formulation Details: {{ $formulation->description }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('medications.formulations.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('medications.formulations.edit', $formulation->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @if(!$formulation->isInUse())
                        <form action="{{ route('medications.formulations.destroy', $formulation->id) }}" 
                              method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this formulation?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th width="30%">ID:</th>
                                    <td>{{ $formulation->id }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td><strong>{{ $formulation->description }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="text-black badge badge-{{ $formulation->is_active ? 'success' : 'secondary' }} p-2">
                                            {{ $formulation->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Medications Using:</th>
                                    <td>
                                        <span class="badge bg-info p-2">
                                            {{ $formulation->medications()->count() }} medication(s)
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th width="30%">Created At:</th>
                                    <td>{{ $formulation->created_at->format('F d, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ $formulation->updated_at->format('F d, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>In Use:</th>
                                    <td>
                                        @if($formulation->isInUse())
                                            <span class="badge bg-warning p-2">
                                                <i class="fas fa-exclamation-triangle"></i> Yes
                                            </span>
                                        @else
                                            <span class="badge bg-success p-2">
                                                <i class="fas fa-check"></i> No
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($formulation->medications()->count() > 0)
                    <hr>
                    <h5><i class="fas fa-pills"></i> Medications Using This Formulation</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Generic Name</th>
                                    <th>Brand Name</th>
                                    <th>Strength</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formulation->medications()->take(10)->get() as $medication)
                                <tr>
                                    <td>{{ $medication->generic_name }}</td>
                                    <td>{{ $medication->brand_name ?? '-' }}</td>
                                    <td>{{ $medication->strength ?? '-' }}</td>
                                    <td>
                                        <span class="text-black badge badge-{{ $medication->is_active ? 'success' : 'secondary' }}">
                                            {{ $medication->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('medications.show', $medication->id) }}" 
                                           class="btn btn-sm btn-info" title="View Medication">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($formulation->medications()->count() > 10)
                        <p class="text-muted text-center">
                            Showing 10 of {{ $formulation->medications()->count() }} medications. 
                            <a href="{{ route('medications.index') }}?formulation={{ $formulation->id }}">View all</a>
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Stats</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-pills"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Medications</span>
                            <span class="info-box-number">{{ $formulation->medications()->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Medications</span>
                            <span class="info-box-number">{{ $formulation->medications()->where('is_active', true)->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$formulation->is_active)
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> Inactive Formulation
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        This formulation is currently inactive and will not appear in medication creation forms.
                    </p>
                    <a href="{{ route('medications.formulations.edit', $formulation->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Activate Formulation
                    </a>
                </div>
            </div>
            @endif

            @if($formulation->isInUse())
            <div class="card border-info">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Usage Warning
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        This formulation is currently being used by medications. Be careful when making changes as they will affect existing records.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
