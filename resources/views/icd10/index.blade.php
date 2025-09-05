@extends('layouts.app_main_layout')

@section('page_title', 'ICD-10 assignments')

@section('main_content')
<div class="container-fluid">
    <h3 class="mb-3">ICD-10 list — assign Mtuha diagnosis</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('icd10.index') }}" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select id="term_select" name="term" class="form-select w-100"></select>
            </div>
            <div class="col-md-5">
                <select id="mtuha_select" name="mtuha_diagnosis" class="form-select w-100" aria-label="Filter by Mtuha diagnosis">
                    <option value="">-- Filter by Mtuha diagnosis --</option>
                    @foreach($mtuha as $m)
                        <option value="{{ $m->id }}" {{ (string) request('mtuha_diagnosis') === (string) $m->id ? 'selected' : '' }}>{{ $m->description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-center justify-content-end">
                <button class="btn btn-secondary" type="submit">Search</button>
                <a href="{{ route('icd10.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Code</th>
                <th>Description</th>
                <th>Category</th>
                <th>Mtuha diagnosis</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($icd10 as $item)
            <tr>
                <td>{{ $item->code }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->category }}</td>
                <td>
                    @if($item->mtuha)
                        {{ $item->mtuha_name }}
                    @else
                        <em class="text-muted">(unassigned)</em>
                    @endif
                </td>
                <td style="min-width:240px;">
                    <form method="POST" action="{{ route('icd10.update', $item->id) }}" class="form-inline">
                        @csrf
                        @method('PATCH')

                        <select name="mtuha_diagnosis" class="form-control form-control-sm me-2">
                            <option value="">-- none --</option>
                            @foreach($mtuha as $m)
                                <option value="{{ $m->id }}" {{ $item->mtuha && $item->mtuha->id == $m->id ? 'selected' : '' }}>{{ $m->description ?? 'ID: '.$m->id }}</option>
                            @endforeach
                        </select>

                        <button class="btn btn-primary btn-sm" type="submit">Save</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    <div class="mt-3">
        {{ $icd10->links() }}
    </div>
    
</div>
@endsection

@section('footer_scripts')
    <!-- Select2 and jQuery are loaded globally in the layout; keep initialization only -->

    <script>
        (function($){
            // Mtuha select regular select2 with local options
            $('#mtuha_select').select2({
                theme: 'classic',
                placeholder: '-- Filter by Mtuha diagnosis --',
                allowClear: true,
                width: 'resolve',
                ajax: {
                    url: '/api/mtuha/search',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) { return { query: params.term, limit: 20 }; },
                    processResults: function(data) {
                        if (!data.success) return { results: [] };
                        return { results: data.data.map(function(item) { return { id: item.id, text: item.description }; }) };
                    }
                }
            });

            // Term select: AJAX search to backend
            $('#term_select').select2({
                theme: 'classic',
                placeholder: 'Search code or description',
                minimumInputLength: 1,
                allowClear: true,
                ajax: {
                    url: '/api/icd10/search',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            query: params.term,
                            type: 'code',
                            limit: 20
                        };
                    },
                    processResults: function(data) {
                        if (!data.success) return { results: [] };
                        return {
                            results: data.data.map(function(item) {
                                return { id: item.code, text: item.code + ' - ' + item.description };
                            })
                        };
                    }
                }
            });

            // Prefill term and mtuha selects if a request value exists
            var existingTerm = '{{ request('term') }}';
            if (existingTerm) {
                var option = new Option(existingTerm, existingTerm, true, true);
                $('#term_select').append(option).trigger('change');
            }

            var existingMtuha = '{{ request('mtuha_diagnosis') }}';
            if (existingMtuha) {
                // fetch the display text for the mtuha id and set option
                $.getJSON('/api/mtuha/search', { query: '', limit: 200 }, function(resp) {
                    var found = resp.data.find(function(i){ return String(i.id) === String(existingMtuha); });
                    if (found) {
                        var opt = new Option(found.description, found.id, true, true);
                        $('#mtuha_select').append(opt).trigger('change');
                    }
                });
            }
        })(jQuery);
    </script>
@endsection
