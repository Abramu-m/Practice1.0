{{-- Test page to preview investigation forms --}}
@extends('layouts.app_main_layout')

@section('page_title', 'Investigation Form Preview')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Investigation Form Preview</h5>
                </div>
                <div class="card-body">
                    @include('consultations.partials.investigation_forms.general')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
