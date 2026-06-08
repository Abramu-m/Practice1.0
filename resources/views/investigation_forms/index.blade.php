@extends('layouts.app_main_layout')

@section('main_content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Investigation Forms</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createFormModal">
            <i class="bi bi-plus-circle me-1"></i> New Form
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Registered forms --}}
    <div class="card mb-4">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Blade View</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $form->name }}</td>
                            <td><code>{{ $form->blade_view }}</code></td>
                            <td>{{ $form->description ?? '—' }}</td>
                            <td>{{ $form->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info view-form-btn"
                                    data-blade-view="{{ $form->blade_view }}"
                                    data-name="{{ $form->name }}"
                                    title="View form">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary edit-btn"
                                    data-id="{{ $form->id }}"
                                    data-name="{{ $form->name }}"
                                    data-description="{{ $form->description }}"
                                    data-blade-view="{{ $form->blade_view }}"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $form->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No forms registered yet. Register one from the templates below.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Available but unregistered templates from lab/result_templates/ --}}
    @if(count($availableTemplates) > 0)
    <h6 class="text-muted mb-2">
        <i class="bi bi-folder2-open me-1"></i>
        Available Templates <small class="fw-normal">(in <code>lab/result_templates/</code> — not yet registered)</small>
    </h6>
    <div class="row g-2 mb-4">
        @foreach($availableTemplates as $tpl)
        <div class="col-md-4 col-lg-3">
            <div class="card border-dashed h-100">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <code class="text-secondary">{{ $tpl }}</code>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-info view-form-btn"
                            data-blade-view="{{ $tpl }}"
                            data-name="{{ $tpl }}"
                            title="Preview">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success quick-register-btn"
                            data-blade-view="{{ $tpl }}"
                            title="Register">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- Preview Modal --}}
<div class="modal fade" id="formPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formPreviewTitle">Form Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="formPreviewBody">
                <div class="text-center py-5"><div class="spinner-border"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createFormModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="createFormForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Investigation Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Blade View <span class="text-danger">*</span></label>
                        <input type="text" name="blade_view" class="form-control" placeholder="e.g. tb" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editFormModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editFormForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Investigation Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editFormId">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="editName" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Blade View <span class="text-danger">*</span></label>
                        <input type="text" id="editBladeView" name="blade_view" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="editDescription" name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const baseUrl    = '{{ url('investigation-forms') }}';
const csrf       = '{{ csrf_token() }}';
const previewUrl = baseUrl + '/{form}/preview';

const previewModalEl = document.getElementById('formPreviewModal');
const previewBody    = document.getElementById('formPreviewBody');
const previewTitle   = document.getElementById('formPreviewTitle');
const previewModal   = new bootstrap.Modal(previewModalEl);

// View form
document.querySelectorAll('.view-form-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const bladeView = this.dataset.bladeView;
        const name      = this.dataset.name;
        previewTitle.textContent = name + ' — Preview';
        previewBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border"></div></div>';
        previewModal.show();
        fetch(`${baseUrl}/${encodeURIComponent(bladeView)}/preview`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.text())
            .then(html => { previewBody.innerHTML = html; })
            .catch(() => { previewBody.innerHTML = '<div class="alert alert-danger">Failed to load preview.</div>'; });
    });
});

// Quick-register from available templates
document.querySelectorAll('.quick-register-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const bladeView = this.dataset.bladeView;
        // Pre-fill the create form and open its modal
        document.querySelector('#createFormModal input[name="name"]').value = bladeView.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        document.querySelector('#createFormModal input[name="blade_view"]').value = bladeView;
        new bootstrap.Modal(document.getElementById('createFormModal')).show();
    });
});

// Create
document.getElementById('createFormForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    const res  = await fetch(baseUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(data),
    });
    if (res.ok) location.reload();
    else alert('Failed to save. Check required fields.');
});

// Populate edit modal
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editFormId').value       = this.dataset.id;
        document.getElementById('editName').value         = this.dataset.name;
        document.getElementById('editBladeView').value    = this.dataset.bladeView;
        document.getElementById('editDescription').value  = this.dataset.description;
        new bootstrap.Modal(document.getElementById('editFormModal')).show();
    });
});

// Update
document.getElementById('editFormForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id   = document.getElementById('editFormId').value;
    const data = Object.fromEntries(new FormData(this));
    const res  = await fetch(`${baseUrl}/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(data),
    });
    if (res.ok) location.reload();
    else alert('Failed to update.');
});

// Delete
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Delete this investigation form?')) return;
        const res = await fetch(`${baseUrl}/${this.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf },
        });
        if (res.ok) location.reload();
        else alert('Failed to delete.');
    });
});
</script>
@endpush
@endsection
