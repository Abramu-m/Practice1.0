@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medical Service Insurance Mapping Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('medical-service-insurance-map.edit', $medicalServiceInsuranceMap->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('medical-service-insurance-map.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Medical Service</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $medicalServiceInsuranceMap->medicalService->name }}</strong>
                            @if($medicalServiceInsuranceMap->medicalService->code)
                                <br><small class="text-muted">{{ $medicalServiceInsuranceMap->medicalService->code }}</small>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Patient Category</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-info text-black">{{ $medicalServiceInsuranceMap->patientCategory->description }}</span>
                        </dd>

                        <dt class="col-sm-4">Insurance Item Code</dt>
                        <dd class="col-sm-8">
                            <code>{{ $medicalServiceInsuranceMap->insurance_item_code }}</code>
                        </dd>

                        @if(isset($tariffItem))
                        <dt class="col-sm-4">Insurance Item Name</dt>
                        <dd class="col-sm-8">{{ $tariffItem->item_name }}</dd>

                        <dt class="col-sm-4">Tariff Price</dt>
                        <dd class="col-sm-8">TSh {{ number_format($tariffItem->unit_price, 2) }}</dd>
                        @endif

                        <dt class="col-sm-4">Created At</dt>
                        <dd class="col-sm-8">{{ $medicalServiceInsuranceMap->created_at->format('M d, Y H:i') }}</dd>

                        <dt class="col-sm-4">Last Updated</dt>
                        <dd class="col-sm-8">{{ $medicalServiceInsuranceMap->updated_at->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>

                <div class="card-footer">
                    <form action="{{ route('medical-service-insurance-map.destroy', $medicalServiceInsuranceMap->id) }}" method="POST"
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
