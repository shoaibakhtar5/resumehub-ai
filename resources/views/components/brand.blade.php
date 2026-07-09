@props([
    'href' => Route::has('home') ? route('home') : url('/'),
    'compact' => false,
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 rounded-md rh-focus']) }}>
    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary text-white shadow-ai">
        <x-ui.icon name="sparkles" class="h-5 w-5" />
    </span>
    @unless ($compact)
        <span class="font-display text-xl font-bold text-on-surface sm:text-2xl">ResumeHub AI</span>
    @endunless
</a>
