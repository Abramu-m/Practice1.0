document.addEventListener('DOMContentLoaded', function(){
    const modalHtml = `
    <div class="modal fade" id="formPreviewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Form Preview</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="formPreviewBody">
            <div class="text-center py-5"><div class="spinner-border"></div></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const previewModalEl = document.getElementById('formPreviewModal');
    const previewBody = document.getElementById('formPreviewBody');
    const bsModal = new bootstrap.Modal(previewModalEl);

    document.querySelectorAll('.view-form-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            const form = this.getAttribute('data-form');
            previewBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border"></div></div>';
            bsModal.show();
            fetch(`/form-templates/${encodeURIComponent(form)}/preview`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    previewBody.innerHTML = html;
                }).catch(err => {
                    previewBody.innerHTML = '<div class="alert alert-danger">Failed to load preview.</div>';
                });
        });
    });
});
