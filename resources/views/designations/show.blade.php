@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Designation Details' }}
 @endsection

@section('Content_Description')
    {{ 'View detailed information about the designation.' }}
@endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Designation Details</h4>
                    <div>
                        <a href="{{ route('designations.edit', $designation) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('designations.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>ID:</strong></div>
                        <div class="col-md-9">{{ $designation->id }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Designation Code:</strong></div>
                        <div class="col-md-9">{{ $designation->designation_code }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Description:</strong></div>
                        <div class="col-md-9">{{ $designation->description ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Created At:</strong></div>
                        <div class="col-md-9">{{ $designation->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Updated At:</strong></div>
                        <div class="col-md-9">{{ $designation->updated_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
@endsection