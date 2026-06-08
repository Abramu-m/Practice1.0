@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Insurance Map</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-insurance-map.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Mapping
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('medication-insurance-map.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="medication_id" class="form-control select2-medication" style="width: 100%;">
                                    <option value="">All Medications</option>
                                    @if($selectedMedication)
                                        <option value="{{ $selectedMedication->id }}" selected>
                                            {{ $selectedMedication->generic_name }}
                                            @if($selectedMedication->brand_name) ({{ $selectedMedication->brand_name }}) @endif
                                            @if($selectedMedication->strength) - {{ $selectedMedication->strength }} @endif
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-4">
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
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('medication-insurance-map.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="mapTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Patient Category</th>
                                    <th>Insurance Item Code</th>
                                    <th>Insurance Item Name</th>
                                    <th>Selling Price</th>
                                    <th>Insurance Price</th>
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
    $('.select2-medication').select2({
        placeholder: 'Type to search for medication...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("medications.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { q: params.term, page: params.page || 1 };
            },
            processResults: function(data) {
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        }
    });

    var table = $('#mapTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("medication-insurance-map.index") }}',
            data: function(d) {
                d.medication_id = $('select[name="medication_id"]').val();
                d.patient_category_id = $('select[name="patient_category_id"]').val();
            }
        },
        columns: [
            { data: 'medication_display', name: 'medication.generic_name', orderable: true },
            { data: 'category_display', name: 'patientCategory.description', orderable: true },
            { data: 'insurance_item_code', name: 'insurance_item_code', orderable: true },
            { data: 'tariff_item_name', name: 'tariff_item_name', orderable: false, searchable: false },
            { data: 'selling_price', name: 'selling_price', orderable: false, searchable: false },
            { data: 'insurance_price', name: 'insurance_price', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 20,
        responsive: true
    });

    $('select[name="medication_id"], select[name="patient_category_id"]').on('change', function() {
        table.draw();
    });

    $('form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });
});
</script>
@endsection
