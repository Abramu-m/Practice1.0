@extends('layouts.app_main_layout')

@section('page_title', 'Sync Conflicts')

@section('main_content')
<div class="container-fluid">
    <h3 class="mb-3">Sync conflicts</h3>
    <p class="text-muted">
        Rows raised by the bidirectional sync between this instance and its counterpart when an
        incoming change collided with a local edit made since the last sync (last-write-wins
        couldn't resolve automatically). Review each conflict and choose which version to keep.
    </p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
    <table id="conflicts-table" class="table table-sm table-striped align-middle">
        <thead>
            <tr>
                <th>Table</th>
                <th>Record UUID</th>
                <th>Detected At</th>
                <th>Status</th>
                <th>Details</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse($conflicts as $conflict)
            <tr>
                <td>{{ $conflict->table_name }}</td>
                <td><code>{{ $conflict->record_uuid }}</code></td>
                <td>{{ \Illuminate\Support\Carbon::parse($conflict->detected_at)->format('Y-m-d H:i') }}</td>
                <td>
                    @if($conflict->resolved_at)
                        <span class="badge bg-success">Resolved ({{ str_replace('_', ' ', $conflict->resolution) }})</span>
                        <div class="small text-muted">{{ \Illuminate\Support\Carbon::parse($conflict->resolved_at)->format('Y-m-d H:i') }}</div>
                    @else
                        <span class="badge bg-warning">Pending review</span>
                    @endif
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#conflict-modal-{{ $conflict->id }}">
                        View
                    </button>
                </td>
                <td>
                    @if(!$conflict->resolved_at)
                        <div class="d-flex gap-1">
                            <form method="POST" action="{{ route('admin.sync.conflicts.resolve', $conflict->id) }}">
                                @csrf
                                <input type="hidden" name="resolution" value="kept_local">
                                <button type="submit" class="btn btn-sm btn-primary">Keep Local</button>
                            </form>
                            <form method="POST" action="{{ route('admin.sync.conflicts.resolve', $conflict->id) }}">
                                @csrf
                                <input type="hidden" name="resolution" value="kept_incoming">
                                <button type="submit" class="btn btn-sm btn-info">Keep Incoming</button>
                            </form>
                            <form method="POST" action="{{ route('admin.sync.conflicts.resolve', $conflict->id) }}">
                                @csrf
                                <input type="hidden" name="resolution" value="merged">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Mark Merged</button>
                            </form>
                        </div>
                    @else
                        <span class="text-muted">&mdash;</span>
                    @endif
                </td>
            </tr>

            <div class="modal fade" id="conflict-modal-{{ $conflict->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $conflict->table_name }} — {{ $conflict->record_uuid }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Local (kept unless "Keep Incoming")</h6>
                                    <pre class="bg-light p-2 small" style="max-height: 400px; overflow:auto;">{{ json_encode($conflict->local_payload, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                <div class="col-md-6">
                                    <h6>Incoming (from remote)</h6>
                                    <pre class="bg-light p-2 small" style="max-height: 400px; overflow:auto;">{{ json_encode($conflict->incoming_payload, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">No sync conflicts have been recorded.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#conflicts-table').DataTable({
        responsive: true,
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [-1, -2] }
        ]
    });
});
</script>
@endsection
