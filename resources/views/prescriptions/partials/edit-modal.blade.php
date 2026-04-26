<div class="modal-header">
    <h5 class="modal-title" id="editPrescriptionModalLabel">Edit Prescription</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editPrescriptionForm" data-prescription-id="{{ $prescription->id }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="edit_medication_name" class="form-label">Medication</label>
            <input type="text" id="edit_medication_name" class="form-control" value="{{ $prescription->medication->generic_name ?? $prescription->medication->name ?? 'Unknown Medication' }}" readonly>
            @if($prescription->medication && $prescription->medication->brand_name)
                <small class="text-muted">Brand: {{ $prescription->medication->brand_name }}</small>
            @endif
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit_dosage" class="form-label">Dosage</label>
                <input type="text" name="dosage" id="edit_dosage" class="form-control" value="{{ $prescription->dosage }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="edit_quantity" class="form-label">Quantity</label>
                <input type="text" name="quantity" id="edit_quantity" class="form-control" value="{{ $prescription->quantity }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit_frequency_id" class="form-label">Frequency</label>
                <select name="frequency_id" id="edit_frequency_id" class="form-control">
                    @foreach($frequencies as $frequency)
                        <option value="{{ $frequency->id }}" {{ $prescription->frequency_id == $frequency->id ? 'selected' : '' }}>
                            {{ $frequency->frequency_name }}{{ $frequency->frequency_code ? ' (' . $frequency->frequency_code . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="edit_duration" class="form-label">Duration (in days)</label>
                <input type="number" name="duration_days" id="edit_duration" class="form-control" value="{{ $prescription->duration_days }}" min="1">
            </div>
        </div>

        <div class="mb-3">
            <label for="edit_instructions" class="form-label">Instructions</label>
            <textarea name="instructions" id="edit_instructions" class="form-control" rows="3">{{ $prescription->instructions }}</textarea>
        </div>

    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="submitPrescriptionUpdate" onclick="submitPrescriptionUpdate()">Save Changes</button>
</div>
