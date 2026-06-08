@extends('layouts.app_main_layout')

@section('page_title', 'Store Items - Unified System')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h4><i class="fas fa-info-circle"></i> System Updated</h4>
                <p>The store management system has been unified. All store items (medications, consumables, equipment, etc.) are now managed through the unified medications system.</p>
                <a href="{{ route('medications.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Go to Unified Store Management
                </a>
                <a href="{{ route('medications.index', ['type' => 'consumable']) }}" class="btn btn-info">
                    <i class="fas fa-filter"></i> View Consumables Only
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-redirect after 5 seconds
setTimeout(function() {
    window.location.href = "{{ route('medications.index') }}";
}, 5000);
</script>
@endsection
