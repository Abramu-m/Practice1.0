@extends('layouts.app_main_layout')

@section('page_title', 'Verify Email')

@section('main_content')
<div class="container-fluid">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="bi bi-envelope-check me-2"></i>Verify Email</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
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

    <div class="card">
        <div class="card-body table-responsive">
            <table id="email-verification-table" class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>S/N</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Email Verified</th>
                        <th>Mailbox Connected</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
                                <br><small class="text-muted">{{ $user->email_verified_at->format('d/m/Y') }}</small>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Not Verified</span>
                            @endif
                        </td>
                        <td>
                            @if($user->hasMailboxConnected())
                                <span class="badge bg-success"><i class="bi bi-envelope-check"></i> Connected</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-envelope-x"></i> Not Connected</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('users.email-verification.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square"></i> {{ $user->email_verified_at ? 'Update' : 'Verify Email' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#email-verification-table').DataTable({
            responsive: true,
            pageLength: 25,
            columnDefs: [
                { orderable: false, targets: [-1] }
            ]
        });
    });
</script>
@endsection
