@extends('layouts.app_main_layout')

@section('page_title', 'Verify Email')

@section('main_content')
<div class="container-fluid">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="bi bi-envelope-check me-2"></i>Verify Email — {{ $user->first_name }} {{ $user->last_name }}</h4>
        <a href="{{ route('users.email-verification.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$facility->email_domain || !$facility->imap_host)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            The facility's email domain and/or mail server have not been configured yet.
            <a href="{{ route('settings.index') }}">Configure them in Settings</a> before assigning work emails.
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Mailbox Details
                    @if($user->email_verified_at)
                        <span class="badge bg-success ms-2"><i class="bi bi-check-circle"></i> Verified</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Enter the mailbox address and password created for this user on the facility's mail
                        server (e.g. in cPanel). The system will test the connection before saving — once
                        verified, this becomes the user's login email and they can view their inbox under
                        the Email section.
                    </p>

                    <form method="POST" action="{{ route('users.email-verification.update', $user->id) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Work Email Address</label>
                            <div class="input-group">
                                <input type="email" id="email" name="email" class="form-control"
                                       value="{{ old('email', $user->email) }}" required>
                            </div>
                            @if($facility->email_domain)
                                <div class="form-text">Must end with <strong>@{{ $facility->email_domain }}</strong></div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="imap_password" class="form-label">Mailbox Password</label>
                            <input type="password" id="imap_password" name="imap_password" class="form-control" required>
                            <div class="form-text">The password set for this mailbox account on the mail server.</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Verify &amp; Connect
                            </button>
                        </div>
                    </form>

                    @if($user->email_verified_at || $user->hasMailboxConnected())
                        <hr>
                        <form method="POST" action="{{ route('users.email-verification.destroy', $user->id) }}"
                              onsubmit="return confirm('This will revoke email verification and forget the saved mailbox password for this user. Continue?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Revoke Email Access
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
