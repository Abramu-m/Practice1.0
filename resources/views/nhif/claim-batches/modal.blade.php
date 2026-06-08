@if($claims->isEmpty())
    <div class="alert alert-warning m-2 text-center">
        No claim records found inside this batch folder.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover align-middle m-0" style="font-size: 13px;">
            <thead class="text-white text-uppercase" style="background-color: #0056b3;">
                <tr>
                    <th class="text-center" style="width: 4%;">#</th>
                    <th>Card No.</th>
                    <th>Authorization No.</th>
                    <th>Full Name</th>
                    <th class="text-center" style="width: 8%;">Gender</th>
                    <th class="text-end" style="width: 14%;">Amount Claimed</th>
                </tr>
            </thead>

            <tbody>
                @foreach($claims as $index => $claim)

                    @php
                        $authNo = (!empty($claim->authorization_no) && $claim->authorization_no !== 'N/A')
                            ? $claim->authorization_no
                            : ($claim->patientVisit ? $claim->patientVisit->authorization_no : 'N/A');

                        $patientName = $claim->patient
                            ? trim(
                                ($claim->patient->first_name ?? '') . ' ' .
                                ($claim->patient->middle_name ?? '') . ' ' .
                                ($claim->patient->last_name ?? '')
                            )
                            : 'N/A';

                        $gender = strtolower($claim->patient->gender ?? '');
                    @endphp

                    {{-- Main Claim Row --}}
                    <tr class="clickable-row" data-id="{{ $claim->id }}" style="cursor: pointer;">

                        <td class="text-center">{{ $index + 1 }}</td>

                        <td class="font-monospace">
                            {{ $claim->card_no ?? ($claim->patient->card_number ?? 'N/A') }}
                        </td>

                        <td class="font-monospace">{{ $authNo }}</td>

                        <td class="fw-semibold text-uppercase">
                            {{ $patientName }}
                            <i class="fa fa-eye text-muted ms-1" style="font-size: 11px;"></i>
                        </td>

                        <td class="text-center text-uppercase">
                            {{ ucfirst($gender) ?: 'N/A' }}
                        </td>

                        <td class="text-end fw-semibold">
                            {{ number_format((float)$claim->total_amount_claimed, 2) }}
                        </td>
                    </tr>

                    {{-- Expanded Details Row --}}
                    <tr id="claim-details-{{ $claim->id }}" class="claim-details-row bg-light" style="display: none;">
                        <td colspan="6" class="p-3">

                            <div class="row">

                                {{-- Diagnoses --}}
                                <div class="col-md-5">
                                    <h6 class="fw-bold text-secondary border-bottom pb-1 mb-2">
                                        <i class="fa fa-heartbeat"></i>
                                        ICD Diagnoses ({{ $claim->claimDiseases->count() }})
                                    </h6>

                                    @if($claim->claimDiseases->isNotEmpty())
                                        <ul class="list-group list-group-flush" style="font-size: 12px;">
                                            @foreach($claim->claimDiseases as $disease)
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1 px-2">
                                                    <div>
                                                        <span class="badge bg-secondary me-1">{{ $disease->disease_code }}</span>
                                                        <span>{{ $disease->disease_name ?? 'Diagnosis Code Details' }}</span>
                                                    </div>
                                                    <span class="badge rounded-pill {{ $disease->remarks === 'final' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                        {{ strtoupper($disease->remarks) }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted small p-2">No diagnosis information recorded for this claim.</p>
                                    @endif
                                </div>

                                {{-- Claim Items --}}
                                <div class="col-md-7">
                                    <h6 class="fw-bold text-secondary border-bottom pb-1 mb-2">
                                        <i class="fa fa-medkit"></i>
                                        Claim Items / Medications ({{ $claim->claimItems->count() }})
                                    </h6>

                                    @if($claim->claimItems->isNotEmpty())
                                        <table class="table table-sm table-bordered m-0 bg-white" style="font-size: 12px;">
                                            <thead class="bg-secondary text-white">
                                                <tr>
                                                    <th>Item Name</th>
                                                    <th class="text-center" style="width: 10%;">Qty</th>
                                                    <th class="text-end" style="width: 20%;">Claimed Amt</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($claim->claimItems as $item)
                                                    <tr>
                                                        <td>
                                                            <span class="fw-semibold d-block">{{ $item->item_name }}</span>
                                                            <small class="text-muted">{{ $item->other_details }}</small>
                                                        </td>
                                                        <td class="text-center">{{ $item->item_quantity }}</td>
                                                        <td class="text-end fw-semibold">{{ number_format((float)$item->amount_claimed, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted small p-2">No individual medication or service line items found.</p>
                                    @endif
                                </div>

                            </div>

                        </td>
                    </tr>

                @endforeach

                {{-- Grand Total --}}
                <tr class="fw-bold">
                    <td colspan="5" class="text-end text-uppercase py-2" style="color: #0056b3; font-size: 13px;">
                        Total Amount Claimed:
                    </td>
                    <td class="text-end py-2" style="font-size: 14px; border-bottom: 3px double #0056b3; color: #0056b3;">
                        {{ number_format((float)($totalAmount ?? $claims->sum('total_amount_claimed')), 2) }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
@endif
