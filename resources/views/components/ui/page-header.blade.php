@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-5 md:flex-row md:items-end md:justify-between']) }}>
    <div class="max-w-3xl">
        @if ($eyebrow)
            <p class="mb-3 font-display text-label-md uppercase text-primary">{{ $eyebrow }}</p>
        @endif
        <h1 class="font-display text-headline-lg text-on-surface sm:text-display-lg">{{ $title }}</h1>
        @if ($description)
            <p class="mt-3 max-w-2xl text-body-lg text-on-surface-variant">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
