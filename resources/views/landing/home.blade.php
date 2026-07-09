<x-marketing-layout title="Modern AI Resume Builder">
    <section class="rh-container grid gap-10 py-14 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:py-20">
        <div>
            <x-ui.badge variant="ai" icon="sparkles">AI-powered excellence</x-ui.badge>
            <h1 class="mt-8 max-w-3xl font-display text-5xl font-bold leading-tight text-on-surface sm:text-6xl lg:text-7xl">
                Get Hired with <span class="text-primary">Precision</span> AI.
            </h1>
            <p class="mt-6 max-w-2xl text-body-lg text-on-surface-variant sm:text-xl sm:leading-8">
                Modern, elegant, and effective. Build a professional resume in minutes with an AI co-pilot that optimizes for humans and ATS systems.
            </p>
            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                <x-ui.button href="{{ route('register') }}" size="lg" iconAfter="arrow-right">Build Your Resume</x-ui.button>
                <x-ui.button href="{{ route('resume.templates') }}" variant="secondary" size="lg">View Templates</x-ui.button>
            </div>
            <div class="mt-10 flex flex-wrap items-center gap-4">
                <div class="flex -space-x-3">
                    <span class="h-10 w-10 rounded-full border-2 border-background bg-primary/20"></span>
                    <span class="h-10 w-10 rounded-full border-2 border-background bg-ai-accent/20"></span>
                    <span class="h-10 w-10 rounded-full border-2 border-background bg-surface-container-highest"></span>
                </div>
                <p class="font-display text-label-md text-on-surface-variant">Joined by 10k+ professionals this month</p>
            </div>
        </div>

        <div class="relative mx-auto w-full max-w-xl">
            <div class="absolute -inset-4 rounded-xl bg-primary/10 blur-2xl"></div>
            <div class="relative grid gap-4 sm:grid-cols-[0.68fr_1fr] sm:items-end">
                <div class="rh-panel overflow-hidden p-2">
                    <img src="{{ asset('assets/stitch/landing-mobile.png') }}" alt="ResumeHub AI mobile landing experience" class="h-full w-full rounded-lg object-cover" loading="eager">
                </div>
                <div class="space-y-4">
                    <div class="rh-card p-5">
                        <div class="flex items-center justify-between">
                            <p class="font-display text-label-md uppercase text-primary">Resume score</p>
                            <x-ui.badge variant="success">94%</x-ui.badge>
                        </div>
                        <div class="mt-5 space-y-4">
                            <x-ui.progress label="ATS fit" value="94" />
                            <x-ui.progress label="Keyword match" value="88" />
                            <x-ui.progress label="Recruiter scan" value="96" />
                        </div>
                    </div>
                    <div class="rounded-xl bg-on-background p-6 text-white shadow-panel">
                        <x-ui.badge variant="ai" icon="sparkles" class="bg-white/10 text-on-primary-container ring-white/20">AI Studio active</x-ui.badge>
                        <p class="mt-5 font-display text-3xl font-bold leading-tight">Elevate your profile with targeted impact.</p>
                        <p class="mt-3 text-body-sm text-white/70">Role-specific bullets, ATS checks, and cover letters stay connected in one workflow.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-border-light bg-white/55 py-12">
        <div class="rh-container grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (config('resumehub.feature_cards') as $feature)
                <x-ui.card interactive>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-ui.icon :name="$feature['icon']" class="h-5 w-5" />
                    </span>
                    <h2 class="mt-5 font-display text-headline-md text-on-surface">{{ $feature['title'] }}</h2>
                    <p class="mt-3 text-body-md text-on-surface-variant">{{ $feature['body'] }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </section>

    <section class="rh-container py-16">
        <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <x-ui.badge variant="soft" icon="squares-2x2">Template system</x-ui.badge>
                <h2 class="mt-5 font-display text-headline-lg text-on-surface sm:text-5xl">Premium templates engineered for real applications.</h2>
                <p class="mt-4 text-body-lg text-on-surface-variant">The gallery carries the Stitch design language forward: pill filters, soft surfaces, AI badges, and high-resolution previews that work across desktop and mobile.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <x-ui.badge>Creative</x-ui.badge>
                    <x-ui.badge>Corporate</x-ui.badge>
                    <x-ui.badge>Tech</x-ui.badge>
                    <x-ui.badge>Healthcare</x-ui.badge>
                </div>
            </div>
            <div class="rh-panel overflow-hidden p-3">
                <img src="{{ asset('assets/stitch/templates-gallery.png') }}" alt="Premium ResumeHub AI template gallery" class="max-h-[520px] w-full rounded-lg object-cover object-top" loading="lazy">
            </div>
        </div>
    </section>
</x-marketing-layout>
