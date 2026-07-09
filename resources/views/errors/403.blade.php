<x-marketing-layout title="403">
    <section class="rh-container flex min-h-[70vh] items-center py-16">
        <div class="mx-auto max-w-2xl text-center">
            <x-ui.badge variant="warning" icon="shield-check">403</x-ui.badge>
            <h1 class="mt-6 font-display text-5xl font-bold text-on-surface">Access is restricted.</h1>
            <p class="mt-4 text-body-lg text-on-surface-variant">Your account does not have permission to open this workspace or admin surface.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <x-ui.button href="{{ route('dashboard') }}" icon="home">Dashboard</x-ui.button>
                <x-ui.button href="{{ route('contact') }}" variant="secondary">Contact support</x-ui.button>
            </div>
        </div>
    </section>
</x-marketing-layout>
