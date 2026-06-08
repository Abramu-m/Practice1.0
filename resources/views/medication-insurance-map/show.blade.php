@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Insurance Mapping Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-insurance-map.edit', $medicationInsuranceMap->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('medication-insurance-map.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Medication</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $medicationInsuranceMap->medication->generic_name }}</strong>
                            @if($medicationInsuranceMap->medication->brand_name)
                                <br><small class="text-muted">{{ $medicationInsuranceMap->medication->brand_name }}</small>
                            @endif
                            @if($medicationInsuranceMap->medication->strength)
                                <br><small class="text-muted">{{ $medicationInsuranceMap->medication->strength }}</small>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Patient Category</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-info text-black">{{ $medicationInsuranceMap->patientCategory->description }}</span>
                        </dd>

                        <dt class="col-sm-4">Insurance Item Code</dt>
                        <dd class="col-sm-8">
                            <code>{{ $medicationInsuranceMap->insurance_item_code }}</code>
                        </dd>

                        @if(isset($tariffItem))
                        <dt class="col-sm-4">Insurance Item Name</dt>
                        <dd class="col-sm-8">{{ $tariffItem->item_name }}</dd>

                        <dt class="col-sm-4">Tariff Price</dt>
                        <dd class="col-sm-8">TSh {{ number_format($tariffItem->unit_price, 2) }}</dd>
                        @endif

                        <dt class="col-sm-4">Created At</dt>
                        <dd class="col-sm-8">{{ $medicationInsuranceMap->created_at->format('M d, Y H:i') }}</dd>

                        <dt class="col-sm-4">Last Updated</dt>
                        <dd class="col-sm-8">{{ $medicationInsuranceMap->updated_at->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>

                <div class="card-footer">
                    <form action="{{ route('medication-insurance-map.destroy', $medicationInsuranceMap->id) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this mapping?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
