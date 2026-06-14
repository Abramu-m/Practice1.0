@extends('layouts.app_main_layout')

@section('page_title', 'ICD-10 assignments')

@section('main_content')
<div class="container-fluid">
    <h3 class="mb-3">ICD-10 list — assign Mtuha diagnosis</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="icd10-filter-form" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select id="term_select" name="term" class="form-select w-100"></select>
            </div>
            <div class="col-md-5">
                <select id="mtuha_select" name="mtuha_diagnosis" class="form-select w-100" aria-label="Filter by Mtuha diagnosis">
                    <option value="">-- Filter by Mtuha diagnosis --</option>
                    @foreach($mtuha as $m)
                        <option value="{{ $m->id }}">{{ $m->description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-center justify-content-end">
                <button class="btn btn-secondary" type="submit">Search</button>
                <a href="{{ route('icd10.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Code</th>
                <th>Description</th>
                <th>Category</th>
                <th>Mtuha diagnosis</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    </div>
</div>
@endsection

@section('footer_scripts')
    <!-- Select2 and jQuery are loaded globally in the layout; keep initialization only -->

    <script>
        (function($){
            // Server-side DataTable: fetches a page of ICD-10 codes at a time
            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [[0, 'asc']],
                pageLength: 25,
                ajax: {
                    url: '{{ route('icd10.index') }}',
                    data: function(d) {
                        d.term = $('#term_select').val();
                        d.mtuha_diagnosis = $('#mtuha_select').val();
                    }
                },
                columns: [
                    { data: 'code', name: 'code' },
                    { data: 'description', name: 'description' },
                    { data: 'category', name: 'category' },
                    { data: 'mtuha_display', name: 'mtuha_display', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    search: "Search ICD-10:",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ ICD-10 codes",
                    infoEmpty: "No ICD-10 codes found",
                    infoFiltered: "(filtered from _MAX_ total codes)"
                }
            });

            // Mtuha select regular select2 with local options
            $('#mtuha_select').select2({
                theme: 'classic',
                placeholder: '-- Filter by Mtuha diagnosis --',
                allowClear: true,
                width: 'resolve',
                ajax: {
                    url: '/api/mtuha/search',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) { return { query: params.term, limit: 20 }; },
                    processResults: function(data) {
                        if (!data.success) return { results: [] };
                        return { results: data.data.map(function(item) { return { id: item.id, text: item.description }; }) };
                    }
                }
            });

            // Term select: AJAX search to backend
            $('#term_select').select2({
                theme: 'classic',
                placeholder: 'Search code or description',
                minimumInputLength: 1,
                allowClear: true,
                ajax: {
                    url: '/api/icd10/search',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            query: params.term,
                            type: 'code',
                            limit: 20
                        };
                    },
                    processResults: function(data) {
                        if (!data.success) return { results: [] };
                        return {
                            results: data.data.map(function(item) {
                                return { id: item.code, text: item.code + ' - ' + item.description };
                            })
                        };
                    }
                }
            });

            // Re-fetch the table whenever a filter changes
            $('#term_select, #mtuha_select').on('change', function() {
                table.draw();
            });

            $('#icd10-filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            // Save a row's mtuha assignment via AJAX without losing the
            // current page/filters (delegated: rows are redrawn by DataTables)
            $('.table tbody').on('submit', '.icd10-assign-form', function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('.save-mtuha');

                btn.prop('disabled', true);
                $.ajax({
                    url: form.data('url'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(resp) {
                        toastr.success(resp.message || 'ICD-10 mapping updated.');
                    },
                    error: function() {
                        toastr.error('Could not update the mapping. Please try again.');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });
        })(jQuery);
    </script>
@endsection
