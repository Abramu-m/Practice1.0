{{-- Complex Investigation Results Modal Component --}}
{{-- This modal can be included in any view to show form-based investigation results --}}
{{-- Usage: @include('partials.complex_results_modal') --}}
{{-- Trigger with: viewComplexResult(investigationId, templateResultId) --}}

<div class="modal fade" id="complexResultsModal" tabindex="-1" role="dialog" aria-labelledby="complexResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="complexResultsModalLabel">
                    <i class="fas fa-chart-line"></i> Investigation Results
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="complexResultsContent" style="max-height: 70vh; overflow-y: auto;">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="printComplexResult" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Results
                </a>
            </div>
        </div>
    </div>
</div>
