<div class="container mt-5">
    {{-- Facility Header --}}
    <div class="facility-header border p-3 mb-4" style="background-color: #f8f9fa;">
        <div class="row">
            <div class="col-md-4 text-center">
                <strong>CTC-Centre 2006</strong><br>
                <h5 class="fw-bold">BRIGITA</h5>
                <div style="font-family: serif;">
                    <span style="font-size: 3rem;">🦕</span><br>
                    <small><strong>Founded 1993</strong></small>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <strong>Diabetes-Clinic 2009</strong><br>
                <h4 class="fw-bold">General Clinic<sup>TM</sup></h4>
            </div>
            <div class="col-md-4">
                {{-- Space for additional facility info if needed --}}
            </div>
        </div>
    </div>

    <div class="text-center mb-4">
        <h5 class="fw-bold text-decoration-underline">CD4 REQUEST FORM</h5>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 fw-semibold text-end">Date of request:</div>
        <div class="col-md-3"><span class="fw-semibold">{{ \Carbon\Carbon::parse($visit->date ?? now())->format('d/m/Y') }}</span></div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 fw-semibold text-end">Unique CTC Number:</div>
        <div class="col-md-3">
            <input type="text" class="form-control form-control-sm" value="{{ $visit->patientInfo->ctc_number ?? '' }}">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-3 fw-semibold text-end">Patient Names:</div>
        <div class="col-md-3"><a href="#" class="fw-semibold text-dark text-decoration-underline">{{ $visit->patientInfo->full_name ?? '' }}</a></div>
    </div>

    <div class="row mb-2">
        <div class="col-md-3 fw-semibold text-end">Gender:</div>
        <div class="col-md-2"><span class="fw-semibold">{{ $visit->patientInfo->gender ?? '' }}</span></div>
        <div class="col-md-1 fw-semibold">Age:</div>
        <div class="col-md-3"><a href="#" class="fw-semibold text-dark text-decoration-underline">{{ $visit->patientInfo->age ?? '' }}</a></div>
    </div>

    <div class="row mb-2">
        <div class="col-md-3 fw-semibold text-end">Address:</div>
        <div class="col-md-3"><a href="#" class="fw-semibold text-dark text-decoration-underline">{{ $visit->patientInfo->address ?? '' }}</a></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 fw-semibold text-end">Ordered by:</div>
    <div class="col-md-3"><a href="#" class="fw-semibold text-dark text-decoration-underline">{{ optional(optional($visit->doctorInfo)->user)->name ?? '' }}</a></div>
    </div>

    <hr>

    <div class="text-center mt-4 mb-3">
        <h6 class="fw-bold text-primary">Indication for CD4:</h6>
    </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-check d-flex align-items-center mb-2">
                    <label class="form-check-label flex-grow-1">Reactive Bioline and Unigold tests:</label>
                    <input class="form-check-input" type="radio" name="cd4_indication" id="cd4_indication_reactive_bioline_unigold" value="reactive_bioline_unigold">
                </div>
                <div class="form-check d-flex align-items-center mb-2">
                    <label class="form-check-label flex-grow-1">ART 6 months routine test:</label>
                    <input class="form-check-input" type="radio" name="cd4_indication" id="cd4_indication_art_6_months_routine" value="art_6_months_routine">
                </div>
                <div class="form-check d-flex align-items-center mb-2">
                    <label class="form-check-label flex-grow-1">Unknown but needed CD4 test:</label>
                    <input class="form-check-input" type="radio" name="cd4_indication" id="cd4_indication_unknown_but_needed" value="unknown_but_needed">
                </div>
                <div class="form-check d-flex align-items-center mb-2">
                    <label class="form-check-label flex-grow-1">Bad condition of the patient:</label>
                    <input class="form-check-input" type="radio" name="cd4_indication" id="cd4_indication_bad_condition" value="bad_condition">
                </div>
                <div class="form-check d-flex align-items-center mb-2">
                    <label class="form-check-label flex-grow-1">Others, please specify:</label>
                    <input class="form-check-input me-2" type="radio" name="cd4_indication" id="cd4_indication_others" value="others">
                    <input type="text" name="cd4_indication_other" id="cd4_indication_other" class="form-control form-control-sm w-auto" placeholder="Please specify.." disabled>
                </div>
            </div>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var othersRadio = document.getElementById('cd4_indication_others');
            var otherInput = document.getElementById('cd4_indication_other');
            var radios = document.querySelectorAll('input[name="cd4_indication"]');

            function updateOtherInput() {
                if (othersRadio && otherInput) {
                    if (othersRadio.checked) {
                        otherInput.disabled = false;
                        otherInput.focus();
                    } else {
                        otherInput.disabled = true;
                        // clear when not using others so backend doesn't get stale value
                        otherInput.value = '';
                    }
                }
            }

            radios.forEach(function (r) {
                r.addEventListener('change', updateOtherInput);
            });

            // in case the view is re-rendered with old input selected
            updateOtherInput();
        });
    </script>
</div>
