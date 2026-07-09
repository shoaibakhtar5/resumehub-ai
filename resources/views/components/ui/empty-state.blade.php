@props([
    'icon' => 'sparkles',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rh-card p-8 text-center']) }}>
    <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-surface-container text-primary">
        <x-ui.icon :name="$icon" class="h-7 w-7" />
    </span>
    <h3 class="mt-5 font-display text-headline-md text-on-surface">{{ $title }}</h3>
    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-body-md text-on-surface-variant">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-6 flex justify-center">
            {{ $slot }}
        </div>
    @endif
</div>
