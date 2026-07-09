@props([
    'label',
    'value',
    'icon' => 'chart-bar',
    'trend' => null,
    'tone' => 'primary',
])

@php
    $tones = [
        'primary' => 'bg-primary/10 text-primary',
        'ai' => 'bg-ai-accent/10 text-ai-accent',
        'success' => 'bg-emerald-50 text-emerald-700',
        'warning' => 'bg-amber-50 text-amber-700',
    ];
@endphp

<x-ui.card pad="p-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-body-sm text-on-surface-variant">{{ $label }}</p>
            <p class="mt-2 font-display text-2xl font-bold text-on-surface">{{ $value }}</p>
            @if ($trend)
                <p class="mt-2 text-label-sm text-success">{{ $trend }}</p>
            @endif
        </div>
        <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['primary'] }}">
            <x-ui.icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
</x-ui.card>
