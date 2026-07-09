@props([
    'variant' => 'soft',
    'icon' => null,
])

@php
    $variants = [
        'soft' => 'bg-surface-container text-primary',
        'ai' => 'bg-ai-accent/10 text-ai-accent ring-1 ring-ai-accent/20',
        'success' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
        'warning' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        'dark' => 'bg-on-background text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-label-sm font-display ' . ($variants[$variant] ?? $variants['soft'])]) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-3.5 w-3.5" />
    @endif
    {{ $slot }}
</span>
