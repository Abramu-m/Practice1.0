@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Account Verification Required' }}
@endsection

@section('main_content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Account Verification Required
                        </h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-user-clock fa-5x text-warning"></i>
                        </div>
                        
                        <h5>Your account is pending verification</h5>
                        <p class="text-muted mb-4">
                            Your account has been created successfully, but it needs to be verified by an administrator before you can access the system.
                        </p>

                        <div class="alert alert-info">
                            <strong>What happens next?</strong><br>
                            An administrator will review your account and verify it shortly. You'll receive access once your account is approved.
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-user"></i> Account Details</h6>
                                        <p class="mb-1"><strong>Name:</strong> {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                        <p class="mb-1"><strong>Email:</strong> {{ auth()->user()->email }}</p>
                                        <p class="mb-0"><strong>Role:</strong> {{ ucfirst(auth()->user()->role) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-clock"></i> Registration Info</h6>
                                        <p class="mb-1"><strong>Registered:</strong> {{ auth()->user()->created_at->format('d/m/Y H:i') }}</p>
                                        <p class="mb-0"><strong>Status:</strong> 
                                            <span class="badge badge-warning">Pending Verification</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                            
                            <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                                <i class="fas fa-sync-alt"></i> Refresh Status
                            </button>
                        </div>

                        <hr class="my-4">
                        
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-question-circle"></i> 
                                Need help? Contact your system administrator.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
// Auto-refresh every 30 seconds to check verification status
setTimeout(function() {
    window.location.reload();
}, 30000);
</script>
@endsection
