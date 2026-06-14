@extends('layouts.app_main_layout')

@section('page_title', 'Email')

@section('main_content')
<div class="container">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="bi bi-envelope me-2"></i>Email</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($error ?? session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ $error ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-mailbox me-2"></i>Connect Your Work Mailbox</h5>
        </div>
        <div class="card-body">
            <p>
                Your work email address is <strong>{{ auth()->user()->email }}</strong>.
                Enter your mailbox password below to connect and view your inbox.
                Your password is stored encrypted and is only used to sign in to the mail server.
            </p>

            <form method="POST" action="{{ route('email.connect') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mailbox Password</label>
                    <input type="password" name="imap_password" class="form-control @error('imap_password') is-invalid @enderror" required>
                    @error('imap_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plug me-1"></i> Connect Mailbox
                    </button>
                </div>
            </form>

            @if(auth()->user()->hasMailboxConnected())
                <hr>
                <form method="POST" action="{{ route('email.disconnect') }}" onsubmit="return confirm('Forget your saved mailbox password?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Forget Saved Password
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
