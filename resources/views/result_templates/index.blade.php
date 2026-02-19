@extends('layouts.app_main_layout')

@section('page_title', 'Result Templates')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Result Templates</h3>
                        <p class="card-subtitle text-muted">Manage result templates for medical services</p>
                    </div>
                    <a href="{{ route('result-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Template
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('result-templates.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control" 
                                           value="{{ request('search') }}" 
                                           placeholder="Search by name, code, or description">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Service Category</label>
                                    <select name="service_category_id" class="form-control">
                                        <option value="">All Categories</option>
                                        @foreach($serviceCategories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ request('service_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('result-templates.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Templates Table -->
                    <div class="table-responsive">
                        <table id="templatesTable" class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Template Name</th>
                                    <th>Code</th>
                                    <th>Service Category</th>
                                    <th>Investigation Type</th>
                                    <th>Sort Order</th>
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
    var table = $('#templatesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("result-templates.index") }}',
            data: function(d) {
                d.search = $('input[name="search"]').val();
                d.service_category_id = $('select[name="service_category_id"]').val();
                d.status = $('select[name="status"]').val();
            }
        },
        columns: [
            { data: 'name_display', name: 'name', orderable: true },
            { data: 'code_display', name: 'code', orderable: true },
            { data: 'category_display', name: 'serviceCategory.name', orderable: true },
            { data: 'type_display', name: 'investigation_type', orderable: true },
            { data: 'sort_order_display', name: 'sort_order', orderable: true },
            { data: 'status_display', name: 'is_active', orderable: true },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'asc'], [0, 'asc']],
        pageLength: 20,
        responsive: true
    });

    // Reload table when filter button is clicked
    $('form button[type="submit"]').on('click', function(e) {
        e.preventDefault();
        table.draw();
    });

    // Reload on filter changes
    $('select[name="service_category_id"], select[name="status"]').on('change', function() {
        table.draw();
    });

    // Reload on search input (with delay)
    let searchTimeout;
    $('input[name="search"]').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.draw();
        }, 500);
    });
});
</script>
@endsection

@section('extra_footer_content')
<script>
    // Auto-submit form on filter changes
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelects = document.querySelectorAll('select[name="service_category_id"], select[name="status"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });
</script>
<!-- Preview Modal -->
<div class="modal fade" id="templatePreviewModal" tabindex="-1" aria-labelledby="templatePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templatePreviewModalLabel">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h6 id="previewName"></h6>
                        <p class="text-muted mb-2" id="previewMeta"></p>
                        <div id="previewRendered" class="border rounded p-3" style="min-height:200px; background:#fff;">
                            <!-- Rendered template mockup will be injected here -->
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
        document.addEventListener('DOMContentLoaded', function() {
                const previewButtons = document.querySelectorAll('.preview-template-btn');
                const previewModalEl = document.getElementById('templatePreviewModal');
                // Use Bootstrap's modal API
                const previewModal = new bootstrap.Modal(previewModalEl);

                previewButtons.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const name = this.dataset.name || '';
                            const code = this.dataset.code || '';
                            const description = this.dataset.description || '';
                            const serviceCategory = this.dataset.service_category || '';
                            const investigationType = this.dataset.investigation_type || '';
                            let fields = {};
                            if (this.dataset.fields) {
                                try {
                                    fields = JSON.parse(this.dataset.fields || '{}');
                                } catch (e) {
                                    fields = {};
                                }
                            }

                            // Fill modal meta early
                            document.getElementById('previewName').textContent = name + ' (' + code + ')';
                            document.getElementById('previewMeta').textContent = [serviceCategory, investigationType].filter(Boolean).join(' • ') + (description ? ' — ' + description : '');

                            const rendered = document.getElementById('previewRendered');
                            rendered.innerHTML = '<div class="text-center text-muted p-4">Loading preview…</div>';

                            // Try server-rendered preview via AJAX (use ID for implicit model binding)
                            const previewId = this.dataset.id || this.dataset.code || '';
                            const previewUrl = '{{ url('') }}' + '/result-templates/' + encodeURIComponent(previewId) + '/preview';

                            fetch(previewUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                .then(resp => {
                                    if (!resp.ok) throw new Error('Server returned ' + resp.status);
                                    return resp.text();
                                })
                                .then(text => {
                                    // Server may return JSON { html: '...' } or raw HTML. Try parse JSON first.
                                    let used = false;
                                    try {
                                        const parsed = JSON.parse(text);
                                        if (parsed && parsed.html) {
                                            rendered.innerHTML = parsed.html;
                                            used = true;
                                        }
                                    } catch (e) {
                                        // not JSON
                                    }

                                    if (!used) {
                                        // If the server returned HTML, insert it. Otherwise, fallback to mock.
                                        if (text && text.trim()) {
                                            rendered.innerHTML = text;
                                        } else {
                                            rendered.innerHTML = '<div class="text-muted">Preview not available from server.</div>';
                                            buildClientMock(fields, rendered);
                                        }
                                    }

                                    previewModal.show();
                                })
                                .catch(err => {
                                    // Fallback: show helpful message and build client-side mock
                                    rendered.innerHTML = '<div class="text-muted">Preview not available from server.</div>';
                                    buildClientMock(fields, rendered);
                                    previewModal.show();
                                });
                        });
                });
        });

                function buildClientMock(fields, rendered) {
                    if (fields && typeof fields === 'object' && Object.keys(fields).length > 0) {
                        const container = document.createElement('div');
                        container.className = 'table-responsive';
                        const table = document.createElement('table');
                        table.className = 'table table-sm';
                        const tbody = document.createElement('tbody');

                        const fieldList = Array.isArray(fields) ? fields : Object.values(fields);
                        fieldList.forEach(f => {
                            const tr = document.createElement('tr');
                            const tdLabel = document.createElement('td');
                            tdLabel.style.width = '30%';
                            tdLabel.innerHTML = '<strong>' + (f.label || f.name || 'Field') + '</strong>';
                            const tdValue = document.createElement('td');
                            if (f.type === 'textarea' || f.type === 'longtext') {
                                tdValue.innerHTML = '<div class="p-2 border rounded bg-white text-muted">[Multiline text]</div>';
                            } else if (f.type === 'number') {
                                tdValue.innerHTML = '<input class="form-control form-control-sm" value="0" disabled />';
                            } else if (f.type === 'checkbox') {
                                tdValue.innerHTML = '<input type="checkbox" disabled />';
                            } else {
                                tdValue.innerHTML = '<input class="form-control form-control-sm" value="" disabled />';
                            }
                            tr.appendChild(tdLabel);
                            tr.appendChild(tdValue);
                            tbody.appendChild(tr);
                        });

                        table.appendChild(tbody);
                        container.appendChild(table);
                        rendered.appendChild(container);
                    } else {
                        rendered.innerHTML = '<p class="text-muted">No template fields defined for this template.</p>';
                    }
                }
</script>
@endsection
