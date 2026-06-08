<!-- Rule Detail Modal (shared partial) -->
<div class="modal fade" id="ruleDetailModal" tabindex="-1" aria-labelledby="ruleDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ruleDetailModalLabel">
                    <i class="fas fa-shield-alt me-2 text-primary"></i>
                    <span id="rdmName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Overview -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-3">
                        <small class="text-uppercase text-muted fw-bold d-block mb-1">Rule ID</small>
                        <code id="rdmId"></code>
                    </div>
                    <div class="col-sm-3">
                        <small class="text-uppercase text-muted fw-bold d-block mb-1">Type</small>
                        <span id="rdmType" class="badge bg-info"></span>
                    </div>
                    <div class="col-sm-3">
                        <small class="text-uppercase text-muted fw-bold d-block mb-1">Priority</small>
                        <span id="rdmPriority" class="badge"></span>
                    </div>
                    <div class="col-sm-3">
                        <small class="text-uppercase text-muted fw-bold d-block mb-1">Severity</small>
                        <span id="rdmSeverity" class="badge"></span>
                    </div>
                    <div class="col-sm-3">
                        <small class="text-uppercase text-muted fw-bold d-block mb-1">Status</small>
                        <span id="rdmStatus" class="badge"></span>
                    </div>
                    <div class="col-sm-9">
                        <small class="text-uppercase text-muted fw-bold d-block mb-1">Description</small>
                        <span id="rdmDescription" class="text-muted fst-italic"></span>
                    </div>
                </div>

                <!-- Alert Message -->
                <div id="rdmMessageWrap" class="mb-3">
                    <small class="text-uppercase text-muted fw-bold d-block mb-1">Alert Message</small>
                    <div id="rdmMessage" class="alert mb-0"></div>
                </div>

                <!-- Conditions -->
                <h6 class="fw-bold mb-2">Conditions <span id="rdmCondCount" class="badge bg-secondary ms-1"></span></h6>
                <div id="rdmCondWrap" class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Field</th><th>Operator</th><th>Value</th><th>Type</th><th>Logic</th></tr>
                        </thead>
                        <tbody id="rdmCondBody"></tbody>
                    </table>
                </div>
                <p id="rdmNoConds" class="text-muted small text-center py-1" style="display:none">No conditions defined.</p>

                <!-- Parameters -->
                <h6 class="fw-bold mb-2">Parameters <span id="rdmParamCount" class="badge bg-secondary ms-1"></span></h6>
                <div id="rdmParamWrap" class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Parameter</th><th>Value</th></tr>
                        </thead>
                        <tbody id="rdmParamBody"></tbody>
                    </table>
                </div>
                <p id="rdmNoParams" class="text-muted small text-center py-1" style="display:none">No parameters configured.</p>

            </div>
            <div class="modal-footer">
                <a id="rdmEditLink" href="#" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit Rule
                </a>
                <a id="rdmViewLink" href="#" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-eye me-1"></i> Full Details
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('ruleDetailModal').addEventListener('show.bs.modal', function (event) {
    const btn  = event.relatedTarget;
    const rule = JSON.parse(btn.dataset.rule);

    document.getElementById('rdmName').textContent        = rule.name;
    document.getElementById('rdmId').textContent          = '#' + rule.id;
    document.getElementById('rdmType').textContent        = rule.type_display;
    document.getElementById('rdmDescription').textContent = rule.description || 'No description provided.';
    document.getElementById('rdmEditLink').href           = rule.edit_url;
    document.getElementById('rdmViewLink').href           = rule.show_url;

    // Priority
    const pBadge = document.getElementById('rdmPriority');
    pBadge.className = 'badge bg-' + (rule.priority >= 8 ? 'danger' : rule.priority >= 5 ? 'warning' : 'secondary');
    pBadge.textContent = rule.priority + ' / 10';

    // Severity
    const sBadge = document.getElementById('rdmSeverity');
    sBadge.className = 'badge bg-' + (rule.severity === 'critical' ? 'danger' : rule.severity === 'warning' ? 'warning' : 'info');
    sBadge.textContent = rule.severity.charAt(0).toUpperCase() + rule.severity.slice(1);

    // Status
    const stBadge = document.getElementById('rdmStatus');
    stBadge.className = 'badge bg-' + (rule.is_active ? 'success' : 'secondary');
    stBadge.textContent = rule.is_active ? 'Active' : 'Inactive';

    // Alert message
    const msgWrap = document.getElementById('rdmMessageWrap');
    const msgEl   = document.getElementById('rdmMessage');
    msgEl.className = 'alert mb-0 alert-' + (rule.severity === 'critical' ? 'danger' : rule.severity === 'warning' ? 'warning' : 'info');
    if (rule.message) {
        msgEl.textContent = rule.message;
        msgWrap.style.display = '';
    } else {
        msgEl.innerHTML = '<em class="text-muted">No custom alert message — system default will be used.</em>';
        msgWrap.style.display = '';
    }

    // Conditions
    const conds    = rule.conditions || [];
    const condBody = document.getElementById('rdmCondBody');
    const condWrap = document.getElementById('rdmCondWrap');
    const noConds  = document.getElementById('rdmNoConds');
    document.getElementById('rdmCondCount').textContent = conds.length;
    condBody.innerHTML = '';
    if (conds.length === 0) {
        condWrap.style.display = 'none';
        noConds.style.display  = '';
    } else {
        condWrap.style.display = '';
        noConds.style.display  = 'none';
        conds.forEach(function (c, i) {
            const logic = i > 0
                ? `<span class="badge bg-${(c.logical_operator||'AND').toUpperCase()==='OR'?'warning':'primary'}">${(c.logical_operator||'AND').toUpperCase()}</span>`
                : '—';
            condBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center text-muted">${i + 1}</td>
                    <td><code>${c.field_name || c.field || '—'}</code></td>
                    <td>${(c.operator||'').replace(/_/g,' ').replace(/\b\w/g,x=>x.toUpperCase())}</td>
                    <td><strong>${c.value ?? '—'}</strong></td>
                    <td><span class="badge bg-light text-dark border">${c.value_type || 'string'}</span></td>
                    <td>${logic}</td>
                </tr>`);
        });
    }

    // Parameters
    const params    = rule.parameters || [];
    const paramBody = document.getElementById('rdmParamBody');
    const paramWrap = document.getElementById('rdmParamWrap');
    const noParams  = document.getElementById('rdmNoParams');
    document.getElementById('rdmParamCount').textContent = params.length;
    paramBody.innerHTML = '';
    if (params.length === 0) {
        paramWrap.style.display = 'none';
        noParams.style.display  = '';
    } else {
        paramWrap.style.display = '';
        noParams.style.display  = 'none';
        params.forEach(function (p) {
            paramBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td><code>${p.name}</code></td>
                    <td><strong>${p.value}</strong></td>
                </tr>`);
        });
    }
});
</script>
@endpush
