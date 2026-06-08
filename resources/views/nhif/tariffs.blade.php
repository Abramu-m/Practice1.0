@extends('layouts.app_main_layout')

@section('page_title', 'NHIF Tariffs Synchronization')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">NHIF Tariffs Synchronization</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">NHIF</li>
                        <li class="breadcrumb-item active">Sync Tariffs</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sync Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-download text-primary me-2"></i>
                        Synchronize NHIF Tariffs
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> This will download the latest NHIF tariffs and update your local database. 
                        Existing tariffs will be updated with new prices and services.
                    </div>

                    <form id="syncTariffsForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="facility_code" class="form-label">Facility Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="facility_code" name="facility_code" 
                                           value="{{ env('NHIF_FACILITY_CODE', '') }}" 
                                           placeholder="Enter your NHIF facility code" required>
                                    <small class="form-text text-muted">
                                        Your facility must be registered with NHIF to download tariffs.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-success d-block w-100">
                                        <i class="fas fa-sync me-1"></i> Sync Tariffs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Sync Progress -->
                    <div id="syncProgress" class="d-none">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="syncStatus" class="text-center text-muted">
                            Preparing to sync tariffs...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Last Sync Info -->
            @if(!empty($lastSync))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock text-info me-2"></i>
                        Last Synchronization
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> {{ $lastSync->last_updated->format('F d, Y H:i:s') }}</p>
                            <p><strong>Facility:</strong> {{ $lastSync->facility_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Tariffs:</strong> {{ $totalTariffs ?? 0 }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-success">Synchronized</span></p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sync Guide & Stats -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Synchronization Guide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary">
                        <h6 class="alert-heading">What happens during sync:</h6>
                        <ul class="mb-0">
                            <li>Downloads latest service prices</li>
                            <li>Updates existing tariffs</li>
                            <li>Adds new services</li>
                            <li>Marks excluded services</li>
                            <li>Updates package information</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Prerequisites:</h6>
                        <ul class="mb-0">
                            <li>Valid NHIF credentials</li>
                            <li>Registered facility code</li>
                            <li>Active internet connection</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tariff Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tariff Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-primary">{{ $totalTariffs ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Tariffs</p>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-warning">{{ $restrictedTariffs ?? 0 }}</h5>
                            <p class="text-muted mb-0 small">Restricted</p>
                        </div>
                        <div class="col-6">
                            <h5 class="text-danger">{{ $excludedTariffs ?? 0 }}</h5>
                            <p class="text-muted mb-0 small">Excluded</p>
                        </div>
                    </div>

                    <hr>

                    <div class="text-center">
                        <h5 class="text-info">{{ $schemes ?? 0 }}</h5>
                        <p class="text-muted mb-0 small">Active Schemes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Tariffs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Current NHIF Tariffs</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="exportTariffs()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshTable()">
                            <i class="fas fa-refresh me-1"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Search form for server-side filtering --}}
                    <form method="GET" action="{{ route('nhif.tariffs') }}" class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="search" name="search" class="form-control" placeholder="Search item code or name" value="{{ request('search') }}">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary">Search</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('nhif.tariffs') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    {{-- Controller provides $tariffs (paginated) --}}

                    <div id="tariffsPanel">

                    <div class="table-responsive">
                        <table class="table table-striped" id="tariffsTable">
                            <thead>
                                <tr>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Unit Price (TSH)</th>
                                    <th>Scheme ID</th>
                                    <th>Package ID</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tariffs as $tariff)
                                <tr>
                                    <td><code>{{ $tariff->item_code }}</code></td>
                                    <td>{{ $tariff->item_name }}</td>
                                    <td class="text-end">{{ number_format($tariff->unit_price, 0) }}</td>
                                    <td>{{ $tariff->scheme_id }}</td>
                                    <td>{{ $tariff->package_id }}</td>
                                    <td>
                                        @if($tariff->is_excluded)
                                            <span class="badge bg-danger">Excluded</span>
                                        @elseif($tariff->is_restricted)
                                            <span class="badge bg-warning">Restricted</span>
                                        @else
                                            <span class="badge bg-success">Available</span>
                                        @endif
                                    </td>
                                    <td>{{ $tariff->last_updated?->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No tariffs found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Showing {{ $tariffs->count() }} of {{ $tariffs->total() }} tariffs
                        </div>
                        <div>
                            {{ $tariffs->links() }}
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sync Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="responseContent"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Table is server-side paginated; skip DataTable client-side pagination to avoid conflicts.
    // If client-side features are desired later, initialize DataTables on the full page or via AJAX.

    // Tariffs sync form
    $('#syncTariffsForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show progress
        $('#syncProgress').removeClass('d-none');
        updateProgress(10, 'Connecting to NHIF servers...');
        
        submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Syncing...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("nhif.sync-tariffs") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                updateProgress(100, 'Sync completed successfully!');
                
                if (response.success) {
                    showResponse('Tariffs Sync Successful', response, 'success');
                    
                    // Refresh the page after 3 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    showResponse('Tariffs Sync Failed', response, 'error');
                }
            },
            error: function(xhr) {
                updateProgress(0, 'Sync failed!');
                const response = xhr.responseJSON || {};
                showResponse('Tariffs Sync Error', response, 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
                
                setTimeout(() => {
                    $('#syncProgress').addClass('d-none');
                }, 3000);
            }
        });
    });
    // Live search: debounce input and fetch updated table HTML
    let searchTimeout = null;
    const searchInput = $('input[name="search"]');
    if (searchInput.length) {
        searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            const q = $(this).val();
            searchTimeout = setTimeout(() => {
                // show quick progress
                updateProgress(5, 'Searching...');
                // fetch the current page with the search query
                $.get(window.location.pathname, { search: q }, function(html) {
                    try {
                        const tmp = $('<div>').html(html);
                        const newPanel = tmp.find('#tariffsPanel').first();
                        if (newPanel.length) {
                            $('#tariffsPanel').replaceWith(newPanel);
                        }
                        updateProgress(100, 'Search complete');
                    } catch (e) {
                        console.warn('Failed to parse search response', e);
                        updateProgress(0, 'Search failed');
                    }
                }).fail(function() {
                    updateProgress(0, 'Search failed');
                }).always(function() {
                    setTimeout(()=> { $('#syncProgress').addClass('d-none'); }, 800);
                });
            }, 400);
        });
    }

    // Intercept manual search form submit to use AJAX loader
    const searchForm = $('form[action="{{ route('nhif.tariffs') }}"]');
    searchForm.on('submit', function(e){
        e.preventDefault();
        const q = $(this).find('input[name="search"]').val();
        const url = window.location.pathname + (q ? ('?search=' + encodeURIComponent(q)) : '');
        loadTariffs(url, true);
    });

    // AJAX pagination: intercept pagination link clicks inside #tariffsPanel
    function loadTariffs(url, pushState = true) {
        if (!url) return;
        // show quick progress
        $('#syncProgress').removeClass('d-none');
        updateProgress(5, 'Loading...');

        $.get(url).done(function(html) {
            try {
                const tmp = $('<div>').html(html);
                const newPanel = tmp.find('#tariffsPanel').first();
                if (newPanel.length) {
                    $('#tariffsPanel').replaceWith(newPanel);
                }
                // update search input value if present in fetched html
                const newSearch = tmp.find('input[name="search"]').first().val();
                if (typeof newSearch !== 'undefined') {
                    $('input[name="search"]').val(newSearch);
                }

                updateProgress(100, 'Loaded');

                if (pushState && window.history && window.history.pushState) {
                    try { window.history.pushState({url: url}, '', url); } catch(e) { /* ignore */ }
                }
            } catch (e) {
                console.warn('Failed to parse pagination response', e);
                updateProgress(0, 'Load failed');
            }
        }).fail(function() {
            updateProgress(0, 'Load failed');
        }).always(function() {
            setTimeout(()=> { $('#syncProgress').addClass('d-none'); }, 400);
        });
    }

    // Delegate click handler so it works after panel replacement
    $(document).on('click', '#tariffsPanel .pagination a', function(e) {
        const href = $(this).attr('href');
        if (!href) return;
        // Only intercept same-origin links
        const origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
        if (href.indexOf(origin) === 0 || href.indexOf('/') === 0 || href.indexOf('?') === 0) {
            e.preventDefault();
            loadTariffs(href, true);
        }
    });

    // Handle back/forward
    window.addEventListener('popstate', function(e) {
        const url = document.location.href;
        loadTariffs(url, false);
    });
});

function updateProgress(percentage, message) {
    $('#syncProgress .progress-bar').css('width', percentage + '%');
    $('#syncStatus').text(message);
}

function exportTariffs() {
    window.open('/nhif/export-tariffs', '_blank');
}

function refreshTable() {
    location.reload();
}

function showResponse(title, response, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const content = `
        <div class="alert ${alertClass}">
            <strong>${title}</strong>
            <p class="mb-1">${response.message || 'No message provided'}</p>
            ${response.synced_count ? `<p class="mb-0">Synced: ${response.synced_count} tariff items</p>` : ''}
        </div>
        <pre class="bg-light p-3 mt-3" style="max-height: 300px; overflow-y: auto;">${JSON.stringify(response, null, 2)}</pre>
    `;
    
    $('#responseContent').html(content);
    $('#responseModal').modal('show');
}
</script>
@endsection
