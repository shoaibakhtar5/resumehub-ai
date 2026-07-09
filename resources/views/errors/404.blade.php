<x-marketing-layout title="404">
    <section class="rh-container flex min-h-[70vh] items-center py-16">
        <div class="mx-auto max-w-2xl text-center">
            <x-ui.badge variant="ai" icon="sparkles">404</x-ui.badge>
            <h1 class="mt-6 font-display text-5xl font-bold text-on-surface">This page drifted off-template.</h1>
            <p class="mt-4 text-body-lg text-on-surface-variant">The link may be outdated, private, or moved into a different ResumeHub AI workspace.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <x-ui.button href="{{ route('home') }}" icon="home">Back home</x-ui.button>
                <x-ui.button href="{{ route('dashboard') }}" variant="secondary" icon="sparkles">Open studio</x-ui.button>
            </div>
        </div>
    </section>
</x-marketing-layout>
