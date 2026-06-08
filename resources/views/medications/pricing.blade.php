@extends('layouts.app_main_layout')

@section('page_title', 'Medication Pricing')

@section('main_content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Medication Selling Prices</h3>
            <div class="d-flex align-items-center gap-3">
                <span id="saveStatus" class="text-muted small"></span>
                <a href="{{ route('medications.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Medications
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="pricingTable">
                    <thead class="table-light text-uppercase fs-7 text-muted">
                        <tr>
                            <th>Medication</th>
                            <th>Strength</th>
                            <th>Category</th>
                            <th style="width:160px">Selling Price (TSH)</th>
                            <th style="width:140px">Discount (%)</th>
                            <th style="width:80px" class="text-center">Action</th>
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
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $med->storeCategory->name ?? '—' }}</span></td>
                            <td>
                                <input type="number" class="form-control form-control-sm price-input"
                                       value="{{ number_format((float)$med->selling_price, 2, '.', '') }}"
                                       min="0" step="0.01" placeholder="0.00">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm discount-input"
                                       value="{{ number_format((float)$med->discount_percentage, 2, '.', '') }}"
                                       min="0" max="100" step="0.01" placeholder="0.00">
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary save-btn" title="Save">
                                    <i class="bi bi-check-lg"></i>
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
    $('#pricingTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, -1],
        columnDefs: [
            { orderable: false, targets: [3, 4, 5] }
        ]
    });

    document.querySelectorAll('.save-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row      = this.closest('tr');
            const id       = row.dataset.id;
            const price    = row.querySelector('.price-input').value;
            const discount = row.querySelector('.discount-input').value;
            const status   = document.getElementById('saveStatus');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`/medications/${id}/price`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ selling_price: price, discount_percentage: discount }),
            })
            .then(r => r.json())
            .then(data => {
                btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                btn.classList.replace('btn-primary', 'btn-success');
                status.textContent = data.message;
                setTimeout(() => {
                    btn.classList.replace('btn-success', 'btn-primary');
                    btn.disabled = false;
                    status.textContent = '';
                }, 2000);
            })
            .catch(() => {
                btn.innerHTML = '<i class="bi bi-x-lg"></i>';
                btn.classList.replace('btn-primary', 'btn-danger');
                btn.disabled = false;
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                    btn.classList.replace('btn-danger', 'btn-primary');
                }, 2000);
            });
        });
    });

    document.querySelectorAll('.price-input, .discount-input').forEach(function (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') this.closest('tr').querySelector('.save-btn').click();
        });
    });
});
</script>
@endsection
