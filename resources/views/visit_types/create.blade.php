@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Create Visit Type' }}
 @endsection


@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create New Visit Type</div>
                <div class="card-body">
                    <form action="{{ route('visit_types.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                   id="description" name="description" value="{{ old('description') }}" required>
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Create Visit Type</button>
                            <a href="{{ route('visit_types.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
@endsection