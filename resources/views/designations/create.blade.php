@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Create Designation' }}
 @endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create New Designation</div>
                <div class="card-body">
                    <form action="{{ route('designations.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="designation_code">Designation Code</label>
                            <input type="text" class="form-control @error('designation_code') is-invalid @enderror" 
                                   id="designation_code" name="designation_code" value="{{ old('designation_code') }}" required>
                            @error('designation_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Create Designation</button>
                            <a href="{{ route('designations.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
@endsection