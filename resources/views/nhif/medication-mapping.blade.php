@extends('layouts.app_main_layout')

@section('page_title', 'NHIF Medication Mapping')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-capsule me-1"></i> NHIF Medication Mapping
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-insurance-map.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Mapping
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <select id="medicationFilter" class="form-control select2-medication" style="width: 100%;">
                                <option value="">All Medications</option>
                                @if($selectedMedication)
                                    <option value="{{ $selectedMedication->id }}" selected>
                                        {{ $selectedMedication->generic_name }}{{ $selectedMedication->strength ? ' ' . $selectedMedication->strength : '' }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="clearFilter" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="nhifMedicationMappingTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>NHIF Category</th>
                                    <th>NHIF Item Code</th>
                                    <th>NHIF Item Name</th>
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
    $('#medicationFilter').select2({
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

    var table = $('#nhifMedicationMappingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("nhif.medication-mapping") }}',
            data: function(d) {
                d.medication_id = $('#medicationFilter').val();
            }
        },
        columns: [
            { data: 'medication_display',  name: 'medication.generic_name',      orderable: true },
            { data: 'category_display',    name: 'patientCategory.description',  orderable: true },
            { data: 'insurance_item_code', name: 'insurance_item_code',          orderable: true },
            { data: 'tariff_item_name',    name: 'tariff_item_name',             orderable: false, searchable: false },
            { data: 'actions',             name: 'actions',                      orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [3, 4] }
        ]
    });

    $('#medicationFilter').on('change', function() {
        table.draw();
    });

    $('#clearFilter').on('click', function() {
        $('#medicationFilter').val(null).trigger('change');
    });
});
</script>
@endsection
