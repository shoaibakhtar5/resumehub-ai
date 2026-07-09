<x-marketing-layout :title="$page['title']">
    <section class="rh-container py-16 sm:py-20">
        <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :description="$page['description']">
            <x-ui.button href="{{ route('register') }}" iconAfter="arrow-right">Start free</x-ui.button>
        </x-ui.page-header>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ($page['sections'] as $section)
                <x-ui.card interactive>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-ui.icon :name="$section['icon']" class="h-6 w-6" />
                    </span>
                    <h2 class="mt-5 font-display text-headline-md text-on-surface">{{ $section['title'] }}</h2>
                    <p class="mt-3 text-body-md text-on-surface-variant">{{ $section['body'] }}</p>
                </x-ui.card>
            @endforeach
        </div>

        <div class="mt-12 rounded-xl bg-on-background p-8 text-white shadow-panel md:p-10">
            <div class="grid gap-8 md:grid-cols-[1fr_0.8fr] md:items-center">
                <div>
                    <x-ui.badge variant="ai" icon="sparkles" class="bg-white/10 text-on-primary-container ring-white/20">Product promise</x-ui.badge>
                    <h2 class="mt-5 font-display text-4xl font-bold leading-tight">A resume platform that feels intentional from first draft to final export.</h2>
                </div>
                <p class="text-body-lg text-white/70">The same design system powers marketing, authentication, resume workflows, and admin operations so every page feels like one product.</p>
            </div>
        </div>
    </section>
</x-marketing-layout>
