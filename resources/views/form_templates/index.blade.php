@extends('layouts.app_main_layout')

@section('main_content')
<div class="container py-4">
    <h4 class="mb-3">Form Templates (Consultation Partials)</h4>

    <div class="row">
        @foreach($forms as $form)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <?php $partial = preg_replace('/(\.blade(\.php)?)$/i', '', $form); ?>
                    <div class="card-header">{{ strtoupper($partial) }} Template</div>
                    <div class="card-body">
                        <p class="text-muted">Preview the full form in a modal to avoid long inline rendering.</p>
                        <button class="btn btn-sm btn-primary view-form-btn" data-form="{{ $partial }}">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@vite('resources/js/form-templates-preview.js')
