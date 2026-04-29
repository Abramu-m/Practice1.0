@extends('layouts.app_main_layout')

@section('page_title')
    Medical Services
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Medical Services</h3>
                    <a href="{{ route('medical_services.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Service
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="category_filter" class="form-label">Category:</label>
                            <select id="category_filter" class="form-select">
                                <option value="">All Categories</option>
                                <option value="investigations">Investigations</option>
                                <option value="procedures">Procedures</option>
                                <optgroup label="Specific Categories">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="status_filter" class="form-label">Status:</label>
                            <select id="status_filter" class="form-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="requires_sample_filter" class="form-label">Requires Sample:</label>
                            <select id="requires_sample_filter" class="form-select">
                                <option value="">All</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="requires_form_filter" class="form-label">Requires Form:</label>
                            <select id="requires_form_filter" class="form-select">
                                <option value="">All</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="resetFilters" class="btn btn-secondary">Reset</button>
                        </div>
                    </div>

                    <!-- Services Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="servicesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Sample</th>
                                    <th>Form</th>
                                    <th>Result Template</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- Form Preview Modal --}}
<div class="modal fade" id="formPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i><span id="formPreviewTitle">Clinical Form Preview</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="formPreviewBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Result Template Preview Modal --}}
<div class="modal fade" id="resultTemplatePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-flask me-2"></i><span id="resultTemplatePreviewTitle">Result Template Preview</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs px-3 pt-2" id="resultTemplateTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-input" data-bs-toggle="tab" data-bs-target="#panel-input" type="button" role="tab">
                            <i class="fas fa-edit me-1"></i> Input Form
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-results" data-bs-toggle="tab" data-bs-target="#panel-results" type="button" role="tab">
                            <i class="fas fa-poll me-1"></i> Results Display
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-3">
                    <div class="tab-pane fade show active" id="panel-input" role="tabpanel">
                        <div id="resultTemplateInputBody">
                            <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="panel-results" role="tabpanel">
                        <div id="resultTemplateResultsBody">
                            <div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#servicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("medical_services.index") }}',
            data: function(d) {
                d.category_filter        = $('#category_filter').val();
                d.status_filter          = $('#status_filter').val();
                d.requires_sample_filter = $('#requires_sample_filter').val();
                d.requires_form_filter   = $('#requires_form_filter').val();
            }
        },
        columns: [
            { data: 'name_display',             name: 'name',                  orderable: true },
            { data: 'category_display',          name: 'serviceCategory.name',  orderable: true },
            { data: 'sample_display',            name: 'requires_sample',       orderable: true },
            { data: 'form_display',              name: 'requires_form',         orderable: true },
            { data: 'result_template_display',   name: 'resultTemplate.name',   orderable: true },
            { data: 'status_display',            name: 'is_active',             orderable: true },
            { data: 'actions',                   name: 'actions',               orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });

    // Re-draw on filter change
    $('#category_filter, #status_filter, #requires_sample_filter, #requires_form_filter').on('change', function() {
        table.draw();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#category_filter, #status_filter, #requires_sample_filter, #requires_form_filter').val('');
        table.draw();
    });

    // ── Form Preview ──────────────────────────────────────────────────────────
    var formModal = new bootstrap.Modal(document.getElementById('formPreviewModal'));

    $(document).on('click', '.preview-form-btn', function () {
        var formType = $(this).data('form-type');
        $('#formPreviewTitle').text('Clinical Form: ' + formType);
        $('#formPreviewBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        formModal.show();

        $.ajax({
            url: '/form-templates/' + encodeURIComponent(formType) + '/preview',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#formPreviewBody').html(html || '<div class="alert alert-info">No preview available for this form.</div>');
            },
            error: function () {
                $('#formPreviewBody').html('<div class="alert alert-danger">Failed to load form preview.</div>');
            }
        });
    });

    // ── Result Template Preview ───────────────────────────────────────────────
    var resultModal = new bootstrap.Modal(document.getElementById('resultTemplatePreviewModal'));

    $(document).on('click', '.preview-template-btn', function () {
        var id   = $(this).data('template-id');
        var name = $(this).data('template-name');
        $('#resultTemplatePreviewTitle').text(name);

        // Reset both panels to loading spinner
        var spinner = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        $('#resultTemplateInputBody').html(spinner);
        $('#resultTemplateResultsBody').html(spinner.replace('text-primary', 'text-secondary'));

        // Activate first tab
        $('#tab-input').tab('show');

        resultModal.show();

        // Load input form and results preview in parallel
        $.ajax({
            url: '/result-templates/' + encodeURIComponent(id) + '/preview',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#resultTemplateInputBody').html(html || '<div class="alert alert-info">No preview available.</div>');
            },
            error: function () {
                $('#resultTemplateInputBody').html('<div class="alert alert-danger">Failed to load template preview.</div>');
            }
        });

        $.ajax({
            url: '/result-templates/' + encodeURIComponent(id) + '/results-preview',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#resultTemplateResultsBody').html(html || '<div class="alert alert-info">No results preview available.</div>');
            },
            error: function () {
                $('#resultTemplateResultsBody').html('<div class="alert alert-danger">Failed to load results preview.</div>');
            }
        });
    });
});
</script>
@endsection

@push('styles')
<style>
    #servicesTable thead th {
        background-color: #343a40;
        color: #fff;
        font-weight: 600;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }

    .badge {
        font-size: 0.75em;
    }
</style>
@endpush
