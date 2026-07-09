<x-guest-layout title="Forgot password" eyebrow="Account recovery" heading="Reset access without losing momentum.">
    <div class="mb-7">
        <x-ui.badge variant="soft" icon="key">Password reset</x-ui.badge>
        <h1 class="mt-5 font-display text-headline-lg text-on-surface">Forgot your password?</h1>
        <p class="mt-2 text-body-md text-on-surface-variant">Enter your email and we will send a secure reset link for your ResumeHub AI workspace.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="grid gap-5">
        @csrf
        <x-ui.input label="Email address" id="email" name="email" type="email" :value="old('email')" required autofocus autocomplete="email" :error="$errors->first('email')" />
        <x-ui.button type="submit" iconAfter="arrow-right" class="w-full">Email reset link</x-ui.button>
    </form>

    <p class="mt-6 text-center text-body-sm text-on-surface-variant">
        Remembered it?
        <a href="{{ route('login') }}" class="font-display text-primary rh-focus">Back to log in</a>
    </p>
</x-guest-layout>
