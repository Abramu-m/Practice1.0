@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fa fa-eye"></i> Medication Batch Details
          </h3>
          <div class="card-tools">
            <a href="{{ route('medication-ledger.index') }}" class="btn btn-secondary btn-sm">
              <i class="fa fa-arrow-left"></i> Back to Ledger
            </a>
            <a href="{{ route('medication-ledger.edit', $batch->id) }}" class="btn btn-primary btn-sm">
              <i class="fa fa-edit"></i> Edit
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th width="30%">Medication</th>
                    <td>{{ $batch->medication->name }}</td>
                  </tr>
                  <tr>
                    <th>Batch Number</th>
                    <td>
                      <span class="badge badge-info text-black">{{ $batch->batch_number }}</span>
                    </td>
                  </tr>
                  <tr>
                    <th>Status</th>
                    <td>
                      @if($batch->status == 'active')
                        <span class="badge badge-success text-black">Active</span>
                      @elseif($batch->status == 'expired')
                        <span class="badge badge-danger text-black">Expired</span>
                      @elseif($batch->status == 'recalled')
                        <span class="badge badge-warning">Recalled</span>
                      @elseif($batch->status == 'damaged')
                        <span class="badge badge-dark">Damaged</span>
                      @elseif($batch->status == 'depleted')
                        <span class="badge badge-secondary">Depleted</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Supplier</th>
                    <td>{{ $batch->supplier_name ?: 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th>GRN Reference</th>
                    <td>
                      @if($batch->goodsReceivedNote)
                        GRN #{{ $batch->goodsReceivedNote->id }} - {{ $batch->goodsReceivedNote->date }}
                      @else
                        N/A
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Storage Location</th>
                    <td>{{ $batch->storage_location ?: 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <div class="col-md-6">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th width="30%">Initial Quantity</th>
                    <td>{{ number_format($batch->initial_quantity) }}</td>
                  </tr>
                  <tr>
                    <th>Current Quantity</th>
                    <td>
                      <span class="badge {{ $batch->current_quantity > 0 ? 'badge-success' : 'badge-danger' }}">
                        {{ number_format($batch->current_quantity) }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th>Dispensed Quantity</th>
                    <td>{{ number_format($batch->initial_quantity - $batch->current_quantity) }}</td>
                  </tr>
                  <tr>
                    <th>Cost per Unit</th>
                    <td>
                      @if($batch->cost_per_unit)
                        ${{ number_format($batch->cost_per_unit, 2) }}
                      @else
                        N/A
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Total Cost</th>
                    <td>
                      @if($batch->total_cost)
                        ${{ number_format($batch->total_cost, 2) }}
                      @else
                        N/A
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Remaining Value</th>
                    <td>
                      @if($batch->cost_per_unit)
                        ${{ number_format($batch->current_quantity * $batch->cost_per_unit, 2) }}
                      @else
                        N/A
                      @endif
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th width="30%">Manufacture Date</th>
                    <td>
                      @if($batch->manufacture_date)
                        {{ $batch->manufacture_date->format('M d, Y') }}
                      @else
                        N/A
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Expiry Date</th>
                    <td>
                      @if($batch->expiry_date)
                        <span class="badge {{ $batch->expiry_date < now() ? 'badge-danger' : ($batch->expiry_date < now()->addDays(30) ? 'badge-warning' : 'badge-success') }}">
                          {{ $batch->expiry_date->format('M d, Y') }}
                        </span>
                      @else
                        N/A
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Created</th>
                    <td>{{ $batch->created_at->format('M d, Y g:i A') }}</td>
                  </tr>
                  <tr>
                    <th>Last Updated</th>
                    <td>{{ $batch->updated_at->format('M d, Y g:i A') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <div class="col-md-6">
              @if($batch->notes)
                <div class="form-group">
                  <label><strong>Notes:</strong></label>
                  <div class="border rounded p-3 bg-light">
                    {{ $batch->notes }}
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Dispensing History -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fa fa-history"></i> Dispensing History
          </h3>
        </div>
        <div class="card-body">
          @if($batch->dispensingTransactions->count() > 0)
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Quantity Dispensed</th>
                    <th>Remaining After</th>
                    <th>Patient</th>
                    <th>Prescription</th>
                    <th>Dispensed By</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($batch->dispensingTransactions as $transaction)
                    <tr>
                      <td>{{ $transaction->transaction_date->format('M d, Y g:i A') }}</td>
                      <td>{{ number_format($transaction->quantity) }}</td>
                      <td>{{ number_format($transaction->remaining_quantity) }}</td>
                      <td>
                        @if($transaction->patient)
                          {{ $transaction->patient->name }}
                        @else
                          N/A
                        @endif
                      </td>
                      <td>
                        @if($transaction->prescription)
                          #{{ $transaction->prescription->id }}
                        @else
                          N/A
                        @endif
                      </td>
                      <td>
                        @if($transaction->dispensedBy)
                          {{ $transaction->dispensedBy->name }}
                        @else
                          N/A
                        @endif
                      </td>
                      <td>{{ $transaction->notes ?: 'N/A' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info">
              <i class="fa fa-info-circle"></i> No dispensing history found for this batch.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fa fa-cogs"></i> Actions
          </h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              @if($batch->status == 'active' && $batch->current_quantity > 0)
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#dispenseModal">
                  <i class="fa fa-pills"></i> Dispense Medication
                </button>
              @endif
              
              <a href="{{ route('medication-ledger.edit', $batch->id) }}" class="btn btn-primary">
                <i class="fa fa-edit"></i> Edit Batch
              </a>
            </div>
            
            <div class="col-md-6 text-right">
              <button type="button" class="btn btn-warning" onclick="updateStatus('{{ $batch->id }}', 'expired')">
                <i class="fa fa-exclamation-triangle"></i> Mark as Expired
              </button>
              
              <button type="button" class="btn btn-danger" onclick="updateStatus('{{ $batch->id }}', 'recalled')">
                <i class="fa fa-ban"></i> Mark as Recalled
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Dispense Modal -->
<div class="modal fade" id="dispenseModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Dispense Medication</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="dispenseForm" action="{{ route('medication-ledger.dispense', $batch->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label for="quantity">Quantity to Dispense</label>
            <input type="text" name="quantity" id="quantity" class="form-control" 
                   max="{{ $batch->current_quantity }}" min="1" required>
            <small class="text-muted">Available: {{ $batch->current_quantity }}</small>
          </div>
          
          <div class="form-group">
            <label for="patient_id">Patient (Optional)</label>
            <select name="patient_id" id="patient_id" class="form-control">
              <option value="">Select Patient</option>
              <!-- Populate with patients -->
            </select>
          </div>
          
          <div class="form-group">
            <label for="prescription_id">Prescription (Optional)</label>
            <input type="text" name="prescription_id" id="prescription_id" class="form-control">
          </div>
          
          <div class="form-group">
            <label for="dispense_notes">Notes</label>
            <textarea name="notes" id="dispense_notes" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Dispense</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updateStatus(batchId, status) {
  if (confirm('Are you sure you want to update the batch status to ' + status + '?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/medication-ledger/${batchId}/update-status`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = status;
    
    form.appendChild(csrfToken);
    form.appendChild(statusInput);
    document.body.appendChild(form);
    form.submit();
  }
}
</script>
@endsection
