<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error @yield('code') - {{ $facility->name ?? config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f4f6f9;
            padding: 1rem;
        }
        .error-card {
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .error-icon {
            font-size: 3.5rem;
            line-height: 1;
        }
        .error-code {
            font-size: 2.5rem;
            font-weight: 700;
            color: #6c757d;
        }
        .error-logo {
            max-height: 60px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="card error-card shadow-sm">
        <div class="card-body p-4">
            <img src="{{ asset('images/logo.png') }}" alt="{{ $facility->name ?? config('app.name') }}" class="error-logo">
            <div class="error-icon mb-2">@yield('icon', '⚠️')</div>
            <div class="error-code">@yield('code')</div>
            <h4 class="mb-2">@yield('title')</h4>
            <p class="text-muted">@yield('message')</p>
            <div class="d-flex justify-content-center gap-2 mt-3">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Go Back</button>
            </div>
        </div>
        @if(($facility->phone ?? null) || ($facility->email ?? null))
            <div class="card-footer text-muted small">
                If this keeps happening, contact {{ $facility->name ?? config('app.name') }}
                @if($facility->phone ?? null)
                    at {{ $facility->phone }}
                @endif
                @if($facility->email ?? null)
                    ({{ $facility->email }})
                @endif
            </div>
        @endif
    </div>
</body>
</html>
