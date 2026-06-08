@extends('layouts.app_main_layout')

@section('page_title', 'Tracer Medicines')

@section('main_content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Tracer Medicines Mapping</h3>
            <div class="d-flex align-items-center gap-3">
                <span id="saveStatus" class="text-muted small"></span>
                <a href="{{ route('medications.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Medications
                </a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Click <i class="bi bi-star-fill text-warning"></i> to add a medication to the tracer list, or click again to remove it.
                The tracer medicines report will only include the items mapped here.
            </p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tracerTable">
                    <thead class="table-light text-uppercase text-muted" style="font-size:.75rem">
                        <tr>
                            <th>Medication</th>
                            <th>Strength</th>
                            <th>Category</th>
                            <th class="text-center" style="width:100px">In Stock</th>
                            <th class="text-center" style="width:90px">Tracer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medications as $med)
                        <tr data-id="{{ $med->id }}">
                            <td>
                                <div class="fw-semibold">{{ $med->generic_name }}</div>
                                @if($med->brand_name)
                                    <small class="text-muted">{{ $med->brand_name }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $med->strength ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    {{ $med->storeCategory->name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($med->stock_quantity > 0)
                                    <span class="badge bg-success-subtle text-success">{{ number_format($med->stock_quantity) }}</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm tracer-btn {{ $med->is_tracer ? 'btn-warning' : 'btn-outline-secondary' }}"
                                        data-url="{{ route('medications.toggle-tracer', $med) }}"
                                        title="{{ $med->is_tracer ? 'Remove from tracer list' : 'Add to tracer list' }}">
                                    <i class="bi {{ $med->is_tracer ? 'bi-star-fill' : 'bi-star' }}"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#tracerTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, -1],
        columnDefs: [
            { orderable: false, targets: [3, 4] }
        ]
    });

    // Delegated handler — required when DataTables manages rows
    $('#tracerTable tbody').on('click', '.tracer-btn', function () {
        const btn    = this;
        const url    = btn.dataset.url;
        const status = document.getElementById('saveStatus');

        btn.disabled = true;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            const isTracer = data.is_tracer;
            btn.classList.toggle('btn-warning', isTracer);
            btn.classList.toggle('btn-outline-secondary', !isTracer);
            btn.querySelector('i').className = isTracer ? 'bi bi-star-fill' : 'bi bi-star';
            btn.title = isTracer ? 'Remove from tracer list' : 'Add to tracer list';
            status.className = 'text-success small';
            status.textContent = data.message;
            setTimeout(function () { status.textContent = ''; status.className = 'text-muted small'; }, 2500);
        })
        .catch(function (err) {
            status.className = 'text-danger small';
            status.textContent = 'Error: ' + err.message;
            setTimeout(function () { status.textContent = ''; status.className = 'text-muted small'; }, 3000);
        })
        .finally(function () { btn.disabled = false; });
    });
});
</script>
@endsection
