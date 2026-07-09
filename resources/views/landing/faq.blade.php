<x-marketing-layout title="FAQ">
    <section class="rh-container py-16 sm:py-20">
        <x-ui.page-header eyebrow="FAQ" title="Questions job seekers and teams ask first." description="Straight answers about AI writing, ATS quality, templates, exports, teams, and admin workflows." />
        <div class="mt-12 space-y-4" x-data="{ active: 0 }">
            @foreach (config('resumehub.faqs') as $index => $faq)
                <div class="rounded-xl border border-border-light bg-white shadow-soft">
                    <button type="button" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left rh-focus" x-on:click="active = active === {{ $index }} ? -1 : {{ $index }}">
                        <span class="font-display text-headline-md text-on-surface">{{ $faq['question'] }}</span>
                        <x-ui.icon name="plus" class="h-5 w-5 text-primary" />
                    </button>
                    <div x-show="active === {{ $index }}" x-transition class="px-6 pb-6 text-body-md text-on-surface-variant">
                        {{ $faq['answer'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-marketing-layout>
