@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Pricing</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-pricing.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Pricing
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('medication-pricing.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="medication_id" class="form-control select2-medication" style="width: 100%;">
                                    <option value="">All Medications</option>
                                    @if($selectedMedication)
                                        <option value="{{ $selectedMedication->id }}" selected>
                                            {{ $selectedMedication->generic_name }}
                                            @if($selectedMedication->brand_name)
                                                ({{ $selectedMedication->brand_name }})
                                            @endif
                                            @if($selectedMedication->strength)
                                                - {{ $selectedMedication->strength }}
                                            @endif
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="patient_category_id" class="form-control">
                                    <option value="">All Patient Categories</option>
                                    @foreach($patientCategories as $category)
                                        <option value="{{ $category->id }}" {{ request('patient_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="effective_status" class="form-control">
                                    <option value="">All Effective Status</option>
                                    <option value="current" {{ request('effective_status') == 'current' ? 'selected' : '' }}>Current</option>
                                    <option value="future" {{ request('effective_status') == 'future' ? 'selected' : '' }}>Future</option>
                                    <option value="expired" {{ request('effective_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('medication-pricing.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Pricing Table -->
                    <div class="table-responsive">
                        <table id="pricingTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Patient Category</th>
                                    <th>Selling Price</th>
                                    <th>Markup %</th>
                                    <th>Discount %</th>
                                    <th>Effective Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for medication filter
    $('.select2-medication').select2({
        placeholder: 'Type to search for medication...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("medications.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data) {
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        }
    });

    var table = $('#pricingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("medication-pricing.index") }}',
            data: function(d) {
                d.medication_id = $('select[name="medication_id"]').val();
                d.patient_category_id = $('select[name="patient_category_id"]').val();
                d.status = $('select[name="status"]').val();
                d.effective_status = $('select[name="effective_status"]').val();
            }
        },
        columns: [
            { data: 'medication_display', name: 'medication.generic_name', orderable: true },
            { data: 'category_display', name: 'patientCategory.description', orderable: true },
            { data: 'selling_price_display', name: 'selling_price', orderable: true },
            { data: 'markup_display', name: 'markup_percentage', orderable: true },
            { data: 'discount_display', name: 'discount_percentage', orderable: true },
            { data: 'effective_period', name: 'effective_from', orderable: true },
            { data: 'status_display', name: 'is_active', orderable: true },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']],
        pageLength: 20,
        responsive: true
    });

    // Filter on change
    $('select[name="medication_id"], select[name="patient_category_id"], select[name="status"], select[name="effective_status"]').on('change', function() {
        table.draw();
    });

    // Prevent form submission and use DataTables filtering instead
    $('form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });
});
</script>
@endsection
