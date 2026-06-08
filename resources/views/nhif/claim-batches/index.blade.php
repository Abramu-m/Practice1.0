@extends('layouts.app_main_layout')

@section('page_title', 'Claim Batch Registration')

@section('main_content')
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold text-secondary-emphasis">NHIF Claim Batches</h1>
                <p class="text-muted small mb-0">Overview of active and historical insurance batch submissions.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newBatchModal">
                + New Batch
            </button>
        </div>

        <!-- Success Alert Hook -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Data Table Card -->
        <div class="card shadow-sm border-light-subtle overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-muted">
                        <tr>
                            <th class="ps-4 py-3">Claim No</th>
                            <th class="py-3">Year</th>
                            <th class="py-3">Month</th>
                            <th class="py-3 text-end">Folios</th>
                            <th class="py-3 text-end">Amount Claimed</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="pe-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fs-6">
                        @forelse($batches as $batch)
                            <tr>
                                <td class="ps-4 font-monospace fw-semibold text-dark">{{ $batch->claim_no }}</td>
                                <td class="text-secondary">{{ $batch->claim_year }}</td>
                                <td class="text-secondary">{{ date("F", mktime(0, 0, 0, $batch->claim_month, 10)) }}</td>
                                <td class="text-end fw-medium">{{ number_format($batch->claims_count) }}</td>
                                <td class="text-end fw-bold text-dark">Tsh {{ number_format($batch->amount_claimed, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3">
                                        {{ $batch->status }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="deleteBatch({{ $batch->id }}, '{{ $batch->claim_no }}')">
                                            Delete
                                        </button>
                                        <button type="button" id="{{ $batch->id }}" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#batchDetailsModal">Details</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No batches found. Click "+ New Batch" to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            @if($batches->hasPages())
                <div class="card-footer bg-light border-top border-light-subtle p-3">
                    {{ $batches->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Batch Details Modal -->
    <div class="modal fade" id="batchDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batch Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="batchDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function deleteBatch(batchId, claimNo) {
        if (!confirm(`Delete batch ${claimNo} and all its draft claims? This cannot be undone.`)) return;

        $.ajax({
            url: '/nhif/claim-batches/' + batchId,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                alert('Error: ' + msg);
            }
        });
    }

    $('#batchDetailsModal').on('show.bs.modal', function (event) {

        var button = $(event.relatedTarget);
        var batchId = button.attr('id'); // (better: use data-id instead)
        var modal = $(this);

        modal.find('#batchDetailsContent').html(
            '<div class="text-center my-3">' +
            '<div class="spinner-border text-primary"></div>' +
            '<p class="mt-2">Fetching batch records...</p>' +
            '</div>'
        );

        $.ajax({
            url: '/nhif/claim-batches/' + batchId,
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            success: function (response) {
                modal.find('#batchDetailsContent').html(response.html);

                // initialize collapse safely
                modal.find('.collapse').collapse({ toggle: false });

                modal.find('.clickable-row').off('click').on('click', function (e) {
                    e.preventDefault();

                    var id = $(this).data('id');
                    var target = $('#claim-details-' + id);

                    // close others first (optional accordion behavior)
                    $('.claim-details-row').not(target).slideUp(150);

                    // toggle current
                    target.stop(true, true).slideToggle(200);
                });
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                modal.find('#batchDetailsContent').html(
                    '<div class="alert alert-danger m-2">Failed to load batch template structure.</div>'
                );
            }
        });
    });
</script>
@endsection

@section('styles')
<style>

</style>
@endsection