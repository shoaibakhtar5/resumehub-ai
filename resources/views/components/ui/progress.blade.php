@props([
    'value' => 0,
    'label' => null,
])

@php
    $bucket = (int) round(max(0, min(100, (int) $value)) / 10) * 10;
    $widths = [
        0 => 'w-0',
        10 => 'w-1/12',
        20 => 'w-1/5',
        30 => 'w-1/3',
        40 => 'w-2/5',
        50 => 'w-1/2',
        60 => 'w-3/5',
        70 => 'w-2/3',
        80 => 'w-4/5',
        90 => 'w-11/12',
        100 => 'w-full',
    ];
@endphp

<div>
    @if ($label)
        <div class="mb-2 flex items-center justify-between text-label-sm text-on-surface-variant">
            <span>{{ $label }}</span>
            <span>{{ $value }}%</span>
        </div>
    @endif
    <div class="h-2 overflow-hidden rounded-full bg-surface-container">
        <div class="h-full rounded-full bg-primary transition-all duration-500 {{ $widths[$bucket] }}"></div>
    </div>
</div>
