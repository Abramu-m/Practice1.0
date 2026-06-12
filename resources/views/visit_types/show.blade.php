@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Visit Types' }}
 @endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Visit Type Details</h4>
                    <div>
                        <a href="{{ route('visit_types.edit', $visitType) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('visit_types.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>ID:</strong></div>
                        <div class="col-md-9">{{ $visitType->id }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Description:</strong></div>
                        <div class="col-md-9">{{ $visitType->description }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>NHIF Visit Type Code:</strong></div>
                        <div class="col-md-9">
                            @if($visitType->nhif_visit_type_code)
                                <span class="badge bg-primary">{{ $visitType->nhif_visit_type_code }}</span>
                            @else
                                <span class="text-muted">Not used for NHIF authorization</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Allowed Patient Categories:</strong></div>
                        <div class="col-md-9">
                            @if($visitType->patientCategories->isEmpty())
                                <span class="badge bg-secondary">All categories</span>
                            @else
                                @foreach($visitType->patientCategories as $category)
                                    <span class="badge bg-info text-dark">{{ $category->description }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Created At:</strong></div>
                        <div class="col-md-9">{{ $visitType->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Updated At:</strong></div>
                        <div class="col-md-9">{{ $visitType->updated_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
@endsection