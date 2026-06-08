@extends('layouts.app_main_layout')

@section('page_title', 'Setup Required')

@section('main_content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="text-center" style="max-width: 480px;">
        <div class="mb-4">
            <i class="fas fa-hospital-alt text-warning" style="font-size: 4rem;"></i>
        </div>
        <h3 class="fw-bold mb-2">Facility Setup Incomplete</h3>
        <p class="text-muted mb-4">
            The system is not yet configured with facility details.
            Please contact your system administrator to complete the setup before you can continue.
        </p>
        <div class="alert alert-info text-start">
            <i class="fas fa-info-circle me-2"></i>
            An administrator needs to log in and fill in the facility details under
            <strong>Settings → Facility Details</strong>.
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-sign-out-alt me-2"></i>Sign Out
            </button>
        </form>
    </div>
</div>
@endsection
