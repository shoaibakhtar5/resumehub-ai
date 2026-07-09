<x-marketing-layout title="401">
    <section class="rh-container flex min-h-[70vh] items-center py-16">
        <div class="mx-auto max-w-2xl text-center">
            <x-ui.badge variant="warning" icon="lock-closed">401</x-ui.badge>
            <h1 class="mt-6 font-display text-5xl font-bold text-on-surface">Authentication required.</h1>
            <p class="mt-4 text-body-lg text-on-surface-variant">This ResumeHub AI area needs a verified session before you can continue.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <x-ui.button href="{{ route('login') }}" icon="lock-closed">Log in</x-ui.button>
                <x-ui.button href="{{ route('home') }}" variant="secondary">Back home</x-ui.button>
            </div>
        </div>
    </section>
</x-marketing-layout>
