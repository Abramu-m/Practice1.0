/**
 * Complex Results Modal JavaScript
 * Shows form-based investigation results in a modal
 *
 * Dependencies:
 * - jQuery
 * - Bootstrap 5
 *
 * Usage:
 * 1. Include the modal partial: @include('partials.complex_results_modal')
 * 2. Include this script: <script src="{{ asset('js/complex-results-modal.js') }}"></script>
 * 3. Call viewComplexResult(investigationId, templateResultId) to open the modal
 */

// Keep the Complex Results modal (and its backdrop) above any other open modal
// (e.g. the Lab Investigation modal), since both share the same default Bootstrap z-index.
document.getElementById('complexResultsModal').addEventListener('show.bs.modal', function () {
    const zIndex = 1060 + (document.querySelectorAll('.modal.show').length * 20);
    this.style.zIndex = zIndex;
    setTimeout(() => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        const lastBackdrop = backdrops[backdrops.length - 1];
        if (lastBackdrop) {
            lastBackdrop.style.zIndex = zIndex - 1;
        }
    }, 0);
});

document.getElementById('complexResultsModal').addEventListener('hidden.bs.modal', function () {
    this.style.zIndex = '';
});

/**
 * View complex (form-based) investigation results in the shared modal
 * @param {number} investigationId
 * @param {number} templateResultId
 */
window.viewComplexResult = function viewComplexResult(investigationId, templateResultId) {

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('complexResultsModal'));
    modal.show();

    // Show loading state
    const contentDiv = document.getElementById('complexResultsContent');
    contentDiv.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading investigation results...</p>
            </div>
        </div>
    `;

    // Update the print button link
    document.getElementById('printComplexResult').href = `/lab/template-results/${templateResultId}`;

    // Fetch the result details
    fetch(`/lab/template-results/${templateResultId}/modal`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch result details');
            }
            return response.text();
        })
        .then(html => {
            contentDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading complex result:', error);
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error:</strong> Failed to load investigation results.
                    <br><small class="text-muted">${error.message}</small>
                </div>
            `;
        });
}
