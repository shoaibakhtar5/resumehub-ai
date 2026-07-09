<x-marketing-layout title="500">
    <section class="rh-container flex min-h-[70vh] items-center py-16">
        <div class="mx-auto max-w-2xl text-center">
            <x-ui.badge variant="warning" icon="server-stack">500</x-ui.badge>
            <h1 class="mt-6 font-display text-5xl font-bold text-on-surface">Something needs attention.</h1>
            <p class="mt-4 text-body-lg text-on-surface-variant">ResumeHub AI hit an unexpected server issue. The interface is ready for recovery and support routing.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <x-ui.button href="{{ route('home') }}" icon="home">Back home</x-ui.button>
                <x-ui.button href="{{ route('contact') }}" variant="secondary">Contact support</x-ui.button>
            </div>
        </div>
    </section>
</x-marketing-layout>
