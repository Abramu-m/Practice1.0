@extends('layouts.app_main_layout')

@section('page_title')
    Edit PAYE Band
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-pencil"></i> Edit PAYE Band</h3>
            <div class="card-tools">
                <a href="{{ route('hr.settings.paye-bands.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <form action="{{ route('hr.settings.paye-bands.update', $payeBand) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('hr.settings.paye_bands._form', ['band' => $payeBand])
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Band
                </button>
                <a href="{{ route('hr.settings.paye-bands.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
