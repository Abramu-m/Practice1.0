@props(['severity' => 'info', 'message' => '', 'rationale' => null])

<div class="rounded-md p-3 mb-2 border {{
    $severity === 'critical' ? 'bg-red-50 border-red-300 text-red-800' :
    ($severity === 'high' ? 'bg-orange-50 border-orange-300 text-orange-800' :
    ($severity === 'medium' ? 'bg-yellow-50 border-yellow-300 text-yellow-800' :
    'bg-blue-50 border-blue-300 text-blue-800'))
}}">
    <div class="font-semibold">{{ ucfirst($severity) }}: {{ $message }}</div>
    @if($rationale)
        <div class="text-sm mt-1 opacity-80">Why: {{ $rationale }}</div>
    @endif
</div>
