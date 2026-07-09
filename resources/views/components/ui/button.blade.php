@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconAfter' => null,
])

@php
    $variants = [
        'primary' => 'border-transparent bg-primary text-white shadow-ai hover:bg-primary-container',
        'secondary' => 'border-outline-variant/40 bg-surface-container text-on-surface hover:bg-surface-container-high',
        'dark' => 'border-transparent bg-on-background text-white shadow-lift hover:bg-inverse-surface',
        'ghost' => 'border-transparent bg-transparent text-on-surface-variant hover:bg-surface-container hover:text-primary',
        'white' => 'border-border-light bg-white text-on-surface shadow-soft hover:border-primary/30 hover:text-primary',
        'danger' => 'border-transparent bg-danger text-white shadow-soft hover:bg-red-700',
    ];

    $sizes = [
        'sm' => 'min-h-9 px-3 py-2 text-label-sm',
        'md' => 'min-h-11 px-5 py-3 text-label-md',
        'lg' => 'min-h-14 px-7 py-4 text-label-md',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-md border font-display transition duration-200 active:scale-[0.98] rh-focus ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" class="h-5 w-5" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconAfter)
            <x-ui.icon :name="$iconAfter" class="h-5 w-5" />
        @endif
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" {{ $attributes->except('type')->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" class="h-5 w-5" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconAfter)
            <x-ui.icon :name="$iconAfter" class="h-5 w-5" />
        @endif
    </button>
@endif
