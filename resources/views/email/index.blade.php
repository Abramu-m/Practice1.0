@extends('layouts.app_main_layout')

@section('page_title', 'Email')

@section('main_content')
<div class="container-fluid">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="bi bi-envelope me-2"></i>Email</h4>
        <div>
            <a href="{{ route('email.compose') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square me-1"></i> Compose
            </a>
            <form method="POST" action="{{ route('email.disconnect') }}" onsubmit="return confirm('Forget your saved mailbox password?')" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-circle me-1"></i> Disconnect Mailbox
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-4">
            <form method="GET" action="{{ route('email.index') }}">
                <label class="form-label fw-semibold">Folder</label>
                <select name="folder" class="form-select" onchange="this.form.submit()">
                    @foreach($folders as $folder)
                        <option value="{{ $folder['path'] }}" @selected($folder['path'] === $folderPath)>
                            {{ $folder['full_name'] }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="inbox-table" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $msg)
                            @php
                                $from = $msg->from->first();
                                $isUnseen = !$msg->flags->has('seen');
                                $subject = mb_decode_mimeheader((string) $msg->subject);
                            @endphp
                            <tr class="{{ $isUnseen ? 'fw-semibold' : '' }}">
                                <td>{{ mb_decode_mimeheader($from?->personal ?: '') ?: $from?->mail }}</td>
                                <td>{{ $subject ?: '(no subject)' }}</td>
                                <td>{{ $msg->date->toDate()->format('M j, Y g:i A') }}</td>
                                <td>
                                    <a href="{{ route('email.show', ['uid' => $msg->uid, 'folder' => $folderPath]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-envelope-open"></i> Open
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                {{ $messages->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#inbox-table').DataTable({
            responsive: true,
            paging: false,
            info: false,
            searching: false,
            language: {
                emptyTable: 'No messages in this folder.'
            },
            columnDefs: [
                { orderable: false, targets: [-1] }
            ]
        });
    });
</script>
@endsection
