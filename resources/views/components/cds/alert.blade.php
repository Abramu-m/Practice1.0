@props(['severity' => 'info', 'message' => '', 'rationale' => null])

@php
    $classes = match($severity) {
        'critical' => 'bg-red-50 border-red-300 text-red-800',
        'high'     => 'bg-orange-50 border-orange-300 text-orange-800',
        'medium'   => 'bg-yellow-50 border-yellow-300 text-yellow-800',
        default    => 'bg-blue-50 border-blue-300 text-blue-800',
    };
@endphp

<div class="rounded-md p-3 mb-2 border {{ $classes }}">
    <div class="font-semibold">{{ ucfirst($severity) }}: {{ $message }}</div>
    @if($rationale)
        <div class="text-sm mt-1 opacity-80">Why: {{ $rationale }}</div>
    @endif
</div>
