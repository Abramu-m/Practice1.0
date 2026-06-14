<form method="POST" action="{{ route('icd10.update', $item->id) }}" class="form-inline icd10-assign-form" data-url="{{ route('icd10.update', $item->id) }}">
    @csrf
    @method('PATCH')

    <select name="mtuha_diagnosis" class="form-control form-control-sm me-2">
        <option value="">-- none --</option>
        @foreach($mtuha as $m)
            <option value="{{ $m->id }}" {{ $item->mtuha && $item->mtuha->id == $m->id ? 'selected' : '' }}>{{ $m->description ?? 'ID: '.$m->id }}</option>
        @endforeach
    </select>

    <button class="btn btn-primary btn-sm save-mtuha" type="submit">Save</button>
</form>
