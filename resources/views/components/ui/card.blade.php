@props([
    'pad' => 'p-6',
    'interactive' => false,
])

<div {{ $attributes->merge(['class' => 'rh-card ' . $pad . ' ' . ($interactive ? 'transition duration-200 hover:-translate-y-1 hover:shadow-lift' : '')]) }}>
    {{ $slot }}
</div>
