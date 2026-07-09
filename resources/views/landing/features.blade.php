<x-marketing-layout title="Features">
    <section class="rh-container py-16 sm:py-20">
        <x-ui.page-header eyebrow="Features" title="One workspace for resume creation, optimization, and application prep." description="ResumeHub AI combines premium templates, AI writing support, ATS checks, exports, sharing, cover letters, and interview prep in a consistent SaaS experience." />
        <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach (config('resumehub.feature_cards') as $feature)
                <x-ui.card interactive>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-ui.icon :name="$feature['icon']" class="h-6 w-6" />
                    </span>
                    <h2 class="mt-5 font-display text-headline-md text-on-surface">{{ $feature['title'] }}</h2>
                    <p class="mt-3 text-body-md text-on-surface-variant">{{ $feature['body'] }}</p>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            @foreach (['Tailor a resume to a job post', 'Run ATS and keyword checks', 'Export PDF, DOCX, and tracked links'] as $index => $step)
                <div class="rounded-xl border border-outline-variant/30 bg-surface-container-low p-6">
                    <span class="font-display text-4xl font-bold text-primary">0{{ $index + 1 }}</span>
                    <h2 class="mt-5 font-display text-headline-md text-on-surface">{{ $step }}</h2>
                    <p class="mt-3 text-body-md text-on-surface-variant">Each workflow uses the same soft cards, pill states, accessible controls, and AI violet accent from the Stitch system.</p>
                </div>
            @endforeach
        </div>
    </section>
</x-marketing-layout>
