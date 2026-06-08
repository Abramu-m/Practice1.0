<div class="card-header">
    <h3 class="card-title">
        <i class="bi bi-people"></i>
        Patient Visits ({{ $visits->total() }} total)
    </h3>
    <div class="card-tools">
        <span class="badge bg-info">{{ $visits->count() }} shown</span>
    </div>
</div>
<div class="card-body table-responsive p-0">
    <table class="table table-hover text-nowrap">
        <thead>
            <tr>
                <th>Patient</th>
                <th>Visit Date</th>
                <th>Doctor</th>
                <th>Visit Status</th>
                <th>Investigations</th>
                <th>Prescriptions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($visits as $visit)
            <tr>
                <td>
                    @if($visit->patientInfo)
                        <strong>{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</strong>
                        @if($visit->patientInfo->middle_name)
                            {{ $visit->patientInfo->middle_name }}
                        @endif
                        <br>
                        <small class="text-muted">
                            {{ $visit->patientInfo->mr_number ?? 'C'.str_pad($visit->patientInfo->id, 4, '0', STR_PAD_LEFT) }}
                            @if($visit->patientInfo->dob)
                                | Age: {{ \Carbon\Carbon::parse($visit->patientInfo->dob)->age }}
                            @endif
                        </small>
                    @else
                        <span class="text-danger">Patient not found</span>
                    @endif
                </td>
                <td>
                    {{ $visit->visit_date ? $visit->visit_date->format('M d, Y') : 'N/A' }}
                    <br>
                    <small class="text-muted">{{ $visit->visit_date ? $visit->visit_date->format('h:i A') : '' }}</small>
                </td>
                <td>
                    @if(optional($visit->doctorInfo)->user)
                        {{ optional($visit->doctorInfo->user)->name }}
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $visit->visit_status_badge_class }}">
                        {{ $visit->visit_status_label }}
                    </span>
                </td>
                <td class="text-center">
                    @if($visit->investigations_count > 0)
                        <button class="btn btn-sm btn-outline-info mb-1"
                                onclick="viewInvestigations({{ $visit->id }})">
                            <i class="bi bi-eye"></i>
                            {{ $visit->investigations_count }} Investigation{{ $visit->investigations_count > 1 ? 's' : '' }}
                        </button>
                        <br>
                        <div class="small">
                            @php
                                $paidInv = $visit->investigations->where('is_paid', true)->count();
                                $unpaidInv = $visit->investigations_count - $paidInv;
                            @endphp
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> {{ $paidInv }} Paid</span>
                            @if($unpaidInv > 0)
                                <span class="badge bg-warning"><i class="bi bi-clock"></i> {{ $unpaidInv }} Pending</span>
                            @endif
                        </div>
                    @else
                        <span class="text-muted">No investigations</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($visit->prescriptions_count > 0)
                        <button class="btn btn-sm btn-outline-success mb-1"
                                onclick="viewPrescriptions({{ $visit->id }})">
                            <i class="bi bi-eye"></i>
                            {{ $visit->prescriptions_count }} Prescription{{ $visit->prescriptions_count > 1 ? 's' : '' }}
                        </button>
                        <br>
                        <div class="small">
                            @php
                                $paidRx = $visit->prescriptions->where('is_paid', true)->count();
                                $unpaidRx = $visit->prescriptions_count - $paidRx;
                            @endphp
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> {{ $paidRx }} Paid</span>
                            @if($unpaidRx > 0)
                                <span class="badge bg-warning"><i class="bi bi-clock"></i> {{ $unpaidRx }} Pending</span>
                            @endif
                        </div>
                    @else
                        <span class="text-muted">No prescriptions</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <h5>No patient visits found</h5>
                        <p>Try adjusting your filters</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($visits->hasPages())
<div class="card-footer">
    <div class="row">
        <div class="col-sm-12 col-md-5">
            <div class="dataTables_info">
                Showing {{ $visits->firstItem() }} to {{ $visits->lastItem() }} of {{ $visits->total() }} entries
            </div>
        </div>
        <div class="col-sm-12 col-md-7">
            <div class="dataTables_paginate">
                {{ $visits->links() }}
            </div>
        </div>
    </div>
</div>
@endif
