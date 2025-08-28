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
                        <table class="table table-bordered table-hover">
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
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td>
                                            <strong>{{ $template->name }}</strong>
                                            @if($template->description)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $template->code }}</code>
                                        </td>
                                        <td>
                                            @if($template->serviceCategory)
                                                <span class="badge bg-secondary">{{ $template->serviceCategory->name }}</span>
                                            @else
                                                <span class="text-muted">All Categories</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->investigation_type)
                                                <span class="badge bg-info">{{ $template->investigation_type }}</span>
                                            @else
                                                <span class="text-muted">All Types</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $template->sort_order }}</span>
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('result-templates.show', $template) }}" 
                                                   class="btn btn-info" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                        <button type="button" 
                            class="btn btn-primary preview-template-btn" 
                            title="Preview Template"
                            data-id="{{ $template->id }}"
                            data-name="{{ e($template->name) }}"
                            data-code="{{ e($template->code) }}"
                            data-description="{{ e($template->description) }}"
                            data-service_category="{{ $template->serviceCategory ? e($template->serviceCategory->name) : '' }}"
                            data-investigation_type="{{ e($template->investigation_type) }}"
                            data-fields='@json($template->template_fields)'>
                                                    <i class="fas fa-eye-dropper"></i>
                                                </button>
                                                <a href="{{ route('result-templates.edit', $template) }}" 
                                                   class="btn btn-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('result-templates.toggle-status', $template) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-{{ $template->is_active ? 'secondary' : 'success' }}" 
                                                            title="{{ $template->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $template->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" 
                                                      action="{{ route('result-templates.destroy', $template) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No result templates found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Showing {{ $templates->firstItem() ?? 0 }} to {{ $templates->lastItem() ?? 0 }} 
                                of {{ $templates->total() }} templates
                            </p>
                        </div>
                        <div class="col-md-6">
                            {{ $templates->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
