@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-sm font-medium text-[#1d4ed8]']) }}>
        {{ $status }}
    </div>
@endif
