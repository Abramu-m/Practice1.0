@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  <div class="row mb-3">
    <div class="col-12 d-flex align-items-center justify-content-between">
      <h4 class="mb-0">System Logs</h4>
      <form method="get" action="{{ route('system.logs.index') }}" class="d-flex gap-2 align-items-center">
        <div class="input-group me-2" style="width: 260px;">
          <label class="input-group-text" for="file">File</label>
          <select id="file" name="file" class="form-select" onchange="this.form.submit()">
            @foreach($files as $file)
              <option value="{{ $file }}" {{ $currentFile === $file ? 'selected' : '' }}>{{ $file }}</option>
            @endforeach
          </select>
        </div>
        <div class="input-group me-2" style="width: 180px;">
          <label class="input-group-text" for="per_page">Per page</label>
          <select id="per_page" name="per_page" class="form-select" onchange="this.form.submit()">
            @foreach([25,50,100,150,200] as $opt)
              <option value="{{ $opt }}" {{ request('per_page', 50)==$opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
          </select>
        </div>
        <div class="input-group">
          <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search message...">
          <button class="btn btn-primary" type="submit">Search</button>
        </div>
        <button type="button" class="btn btn-danger" onclick="confirmClearLogs('{{ $currentFile }}')">
          <i class="fas fa-trash"></i> Clear Logs
        </button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 200px;">Date</th>
              <th style="width: 90px;">Level</th>
              <th>Message</th>
              <th style="width: 60px;"></th>
            </tr>
          </thead>
          <tbody>
            @forelse($entries as $entry)
              <tr>
                <td><code>{{ $entry['date'] }}</code></td>
                <td>
                  @php $level = strtolower($entry['level'] ?? ''); @endphp
                  <span class="badge bg-{{ in_array($level, ['error','critical','alert','emergency']) ? 'danger' : ($level==='warning' ? 'warning text-dark' : ($level==='info' ? 'info' : 'secondary')) }} text-uppercase">{{ $entry['level'] }}</span>
                </td>
                <td>
                  <div class="fw-semibold">{{ $entry['message'] }}</div>
                  @if(!empty($entry['context']))
                    <pre class="mb-0 small bg-light p-2 rounded"><code>{{ json_encode($entry['context'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</code></pre>
                  @endif
                  @if(!empty($entry['stack']))
                    <details>
                      <summary class="small">Stack trace</summary>
                      <pre class="mb-0 small"><code>{{ implode("\n", $entry['stack']) }}</code></pre>
                    </details>
                  @endif
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-secondary" onclick="copyRaw(this)" data-raw="{{ base64_encode($entry['raw']) }}">Copy</button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No log entries found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <div>
        Showing {{ $entries->firstItem() ?? 0 }} to {{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} entries
      </div>
      <div>
        {{ $entries->appends(request()->query())->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>

<script>
function copyRaw(btn) {
  try {
    const rawB64 = btn.getAttribute('data-raw');
    const raw = atob(rawB64);
    navigator.clipboard.writeText(raw).then(() => {
      btn.textContent = 'Copied';
      setTimeout(() => btn.textContent = 'Copy', 1200);
    });
  } catch (e) {
    alert('Copy failed');
  }
}

function confirmClearLogs(currentFile) {
  const message = currentFile 
    ? `Are you sure you want to clear the log file "${currentFile}"? This action cannot be undone.`
    : 'Are you sure you want to clear all log files? This action cannot be undone.';
  
  if (confirm(message)) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("system.logs.clear") }}';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    if (currentFile) {
      const fileInput = document.createElement('input');
      fileInput.type = 'hidden';
      fileInput.name = 'file';
      fileInput.value = currentFile;
      form.appendChild(fileInput);
    }
    
    document.body.appendChild(form);
    form.submit();
  }
}
</script>
@endsection
