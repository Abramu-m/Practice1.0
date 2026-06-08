@extends('layouts.app_main_layout')

@section('page_title', 'NHIF Service Mapping')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-diagram-3 me-1"></i> NHIF Service Mapping
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('medical-service-insurance-map.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Mapping
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <select id="serviceFilter" class="form-control select2-service" style="width: 100%;">
                                <option value="">All Medical Services</option>
                                @if($selectedService)
                                    <option value="{{ $selectedService->id }}" selected>
                                        {{ $selectedService->name }}{{ $selectedService->code ? ' (' . $selectedService->code . ')' : '' }}
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
                        <table id="nhifServiceMappingTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Medical Service</th>
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
    $('#serviceFilter').select2({
        placeholder: 'Type to search for medical service...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '/api/medical-services/search',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { q: params.term, page: params.page || 1 };
            },
            processResults: function(data) {
                var results = $.isArray(data) ? data : (data.results || data.data || []);
                return {
                    results: results.map(function(s) {
                        return { id: s.id, text: s.name + (s.code ? ' (' + s.code + ')' : '') };
                    })
                };
            },
            cache: true
        }
    });

    var table = $('#nhifServiceMappingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("nhif.service-mapping") }}',
            data: function(d) {
                d.medical_service_id = $('#serviceFilter').val();
            }
        },
        columns: [
            { data: 'service_display',     name: 'medicalService.name',         orderable: true },
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

    $('#serviceFilter').on('change', function() {
        table.draw();
    });

    $('#clearFilter').on('click', function() {
        $('#serviceFilter').val(null).trigger('change');
    });
});
</script>
@endsection
