<x-guest-layout title="Create account" eyebrow="Start free" heading="Build a sharper resume workspace.">
    <div class="mb-7">
        <x-ui.badge variant="ai" icon="sparkles">AI resume co-pilot</x-ui.badge>
        <h1 class="mt-5 font-display text-headline-lg text-on-surface">Create your account</h1>
        <p class="mt-2 text-body-md text-on-surface-variant">Start with premium templates, ATS checks, and AI-guided resume editing.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-md border border-border-light bg-white px-4 py-3 font-display text-label-md text-on-surface shadow-soft transition hover:border-primary/30 hover:text-primary rh-focus">
            <x-ui.icon name="globe-alt" class="h-5 w-5" />
            Google
        </button>
        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-md border border-border-light bg-white px-4 py-3 font-display text-label-md text-on-surface shadow-soft transition hover:border-primary/30 hover:text-primary rh-focus">
            <x-ui.icon name="briefcase" class="h-5 w-5" />
            LinkedIn
        </button>
    </div>

    <div class="my-6 flex items-center gap-3">
        <span class="h-px flex-1 bg-border-light"></span>
        <span class="text-label-sm uppercase text-on-surface-variant">or email</span>
        <span class="h-px flex-1 bg-border-light"></span>
    </div>

    <form method="POST" action="{{ route('register') }}" class="grid gap-5">
        @csrf
        <x-ui.input label="Full name" id="name" name="name" type="text" :value="old('name')" required autofocus autocomplete="name" :error="$errors->first('name')" />
        <x-ui.input label="Email address" id="email" name="email" type="email" :value="old('email')" required autocomplete="username" :error="$errors->first('email')" />
        <x-ui.input label="Password" id="password" name="password" type="password" required autocomplete="new-password" :error="$errors->first('password')" />
        <x-ui.input label="Confirm password" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" :error="$errors->first('password_confirmation')" />

        <x-ui.button type="submit" iconAfter="arrow-right" class="w-full">Create account</x-ui.button>
    </form>

    <p class="mt-6 text-center text-body-sm text-on-surface-variant">
        Already registered?
        <a href="{{ route('login') }}" class="font-display text-primary rh-focus">Log in</a>
    </p>
</x-guest-layout>
